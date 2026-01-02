<?php

namespace App\Services;

use App\Constants\AppConst;
use App\Mail\EmailResetPassword;
use App\Mail\EmailUserRegister;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Passport\Client as OClient;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthService
{
    protected $oClient;
    protected $guards;

    public function __construct()
    {
        $this->_retrieveClients();
    }

    private function _retrieveClients()
    {
        $clients = OClient::query()->where('password_client', 1)->get();

        $configProviders = config('auth.providers');
        $configGuards = config('auth.guards');


        $providerGuardMaps = [];
        foreach ($configGuards as $guard => $configGuard) {
            if (isset($configGuard['provider']) && $configGuard['driver'] === 'session') {
                $providerGuardMaps[$configGuard['provider']] = $guard;
            }
        }
        foreach ($clients as $client) {
            if (isset($client['provider']) && isset($configProviders[$client['provider']])) {
                $model = $configProviders[$client['provider']]['model'];
                $this->oClient[$model] = $client;
                $this->guards[$model] = $providerGuardMaps[$client['provider']] ?? null;
            }
        }
    }

    private function _getClient(string $key)
    {
        return $this->oClient[$key] ?? null;
    }

    private function _getGuard(string $key)
    {
        return $this->guards[$key] ?? null;
    }

    /**
     * @throws Exception
     */
    public function register(string $modelNamespace, $username, $email, $password): array
    {
        $data = compact('username', 'email', 'password');
        $data['password'] = Hash::make($password);
        $user = $modelNamespace::create($data);
        $tokenData = $this->generateToken($modelNamespace, $username, $password);
        if (!$tokenData) {
            throw new BadRequestException();
        }

        return Arr::add($tokenData, 'user', $user);
    }

    /**
     * @throws Exception
     */
    public function login(string $modelNamespace, $email, $password)
    {
        $result = [];
        $result['token'] = $this->generateToken($modelNamespace, $email, $password);
        if (!$result['token']) {
            throw new AuthorizationException(__('api.exception.invalid_credentials'));
        }
        
        // Get user from email after token is successfully generated
        // Token generation already validates credentials, so we can safely get user
        $user = $modelNamespace::where('email', $email)
            ->where('status', AppConst::STATUS_ACTIVE)
            ->with('roles') // Load roles relationship
            ->first();
        
        if ($user) {
            $result['user'] = $user;
        }

        return $result;
    }

    public function logout($user)
    {
        return $this->revokeToken($user->token());
    }

    public function profile(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth()->user()->load([
            'adminUpdated' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            },
            'userUpdated' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }
        ]);
    }

    /**
     * @param string $modelNamespace
     * @param $email
     * @param $password
     * @return mixed|null
     * @throws Exception
     */
    public function generateToken(string $modelNamespace, $email, $password)
    {
        $oClient = $this->_getClient($modelNamespace);
        if (!$oClient) {
            \Log::error('OAuth client not found for model', [
                'model' => $modelNamespace,
                'available_clients' => array_keys($this->oClient ?? []),
                'hint' => 'Run: php artisan passport:client --password --provider users --name users'
            ]);
            return null;
        }

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => (string)$oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);
        $response = app()->handle($request);
        if ($response->getStatusCode() === HttpResponse::HTTP_OK) {
            return json_decode((string)$response->getContent(), true);
        }
        return null;
    }

    /**
     * @param string $modelNamespace
     * @param string $refreshToken
     * @return mixed|null
     * @throws Exception
     */
    public function refreshToken(string $modelNamespace, string $refreshToken)
    {
        $oClient = $this->_getClient($modelNamespace);
        if (!$oClient) return null;

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'refresh_token',
            'client_id' => (string)$oClient->id,
            'client_secret' => $oClient->secret,
            'refresh_token' => $refreshToken,
            'scope' => '*',
        ]);
        $response = app()->handle($request);
        if ($response->getStatusCode() === HttpResponse::HTTP_OK) {
            return json_decode((string)$response->getContent(), true);
        }
        return null;
    }

    /**
     * @param Token $token
     * @return mixed
     */
    public function revokeToken(Token $token)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $tokenRepository->revokeAccessToken($token->id);
        return $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
    }

    /**
     * @param Authenticatable $user
     * @param bool $include
     */
    public function revokeAllTokens(Authenticatable $user, bool $include = true)
    {
        $tokens = $user->tokens;
        $currentToken = auth()->user()->token();
        if (count($tokens)) {
            foreach ($tokens as $token) {
                if ($token->id === $currentToken->id && !$include) continue;
                $this->revokeToken($token);
            }
        }
    }

    public function forgotPassword(string $modelNamespace, string $domain, $data)
    {
        $user = $modelNamespace::where('email', $data['email'])->where('status', AppConst::STATUS_ACTIVE)->firstOrFail();
        if (!empty($user)) {
            $passwordReset = PasswordReset::updateOrCreate([
                'email' => $user->email,
                'model_type' => $modelNamespace,
            ], [
                'token' => Str::random(60),
            ]);
            $dataEmail = [
                "url" => $domain . '/change-password/?token=' . $passwordReset->token,
            ];
            Mail::to($passwordReset->email)->send(new EmailResetPassword($dataEmail));
        }
        return true;
    }

    public function changePassword(string $modelNamespace, $data)
    {
        $passwordReset = PasswordReset::where('token', $data['token'])->where("model_type", $modelNamespace)->first();
        if ($passwordReset) {
            if (Carbon::parse($passwordReset->updated_at)->addMinutes(AppConst::DEFAULT_EXP_RESET_PASSWORD)->isPast()) {
                throw new NotFoundHttpException('This password reset token is invalid.');
            }
            $user = $modelNamespace::where('email', $passwordReset->email)->firstOrFail();
            $password = $data['password'];
            $dataUpdate = [];
            $dataUpdate['password'] = Hash::make($password);
            $dataUpdate['remember_token'] = null;
            $updatePasswordUser = $user->update($dataUpdate);
            $passwordReset->delete();
            return $updatePasswordUser;
        } else {
            throw new NotFoundHttpException('This password reset token is invalid.');
        }
    }

    public function userApprove(string $modelNamespace, string $domain, $data)
    {
        $user = $modelNamespace::where('email', $data['email'])->firstOrFail();
        if (!empty($user)) {
            UserConcierge::query()->updateOrCreate([
                'user_id' => $user->id,
            ], [
                'concierge_id' => Auth::id(),
                'change_reason' => '本会員登録承認'
            ]);
            $passwordReset = PasswordReset::updateOrCreate([
                'email' => $user->email,
                'model_type' => $modelNamespace,
            ], [
                'token' => Str::random(60),
            ]);
            $dataEmail = [
                "url_change_password" => $domain . '/change-password/?token=' . $passwordReset->token,
                "url_login" => $domain . 'login',
                "full_name" => $user->last_name . ' ' . $user->first_name
            ];
            Mail::to($passwordReset->email)->send(new EmailUserApprove($dataEmail));
        }
        return true;
    }

    public function userRegister(string $modelNamespace, string $domain, $data)
    {
        $user = $modelNamespace::where('email', $data['email'])->firstOrFail();
        if (!empty($user)) {
            $passwordReset = PasswordReset::updateOrCreate([
                'email' => $user->email,
                'model_type' => $modelNamespace,
            ], [
                'token' => Str::random(60),
            ]);
            $dataEmail = [
                "full_name" => $user->last_name . ' ' . $user->first_name,
                "company_name" => $user->company_name,
                "email" => $user->email,
                "user_phone" => $user->user_phone,
                "division" => $user->division ? AppConst::DIVISIONS[$user->division] : '',
                "position" => $user->position ? AppConst::POSITIONS[$user->position]: '',
                "industry" => $user->industry ? AppConst::INDUSTRIES[$user->industry]: '',
                "employee_size" => $user->employee_size ? AppConst::EMPLOYEE_SIZES[$user->employee_size]: '',
                "how_found_us" => $user->how_found_us ? AppConst::HOW_FOUND_US_LIST[$user->how_found_us]: '',
                "url_site" => $domain
            ];
            Mail::to($passwordReset->email)->send(new EmailUserRegister($dataEmail));
        }
        return true;
    }
}
