<?php

namespace Tests\Unit\Services;

use App\Models\Admin;
use App\Models\User;
use App\Services\AuthService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Token;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthService
     */
    private AuthService $authService;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('key:generate', ['-vvv' => true]);
        Artisan::call('passport:install', ['-vvv' => true]);
        Artisan::call('passport:client', ['--password' => true, '--provider' => 'admins', '--name' => 'admins']);
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->authService = $this->app->make(AuthService::class);
    }

    public function test_user_register()
    {
        $password = '123456';
        $user = User::factory()->withPassword($password)->unverified()->make();
        $userData = Arr::add(Arr::only($user->getAttributes(), ['username', 'email']), 'password', $password);

        $result = $this->authService->register(User::class, ...array_values($userData));

        $this->validateRegister('users', $user, $result);
    }

    private function validateRefreshToken($result)
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);

        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertNotNull($result['expires_in']);
        $this->assertIsInt($result['expires_in']);
        $this->assertNotNull($result['access_token']);
        $this->assertIsString( $result['access_token']);
        $this->assertNotNull($result['refresh_token']);
        $this->assertIsString($result['refresh_token']);
    }

    public function test_user_login_correct()
    {
        $loginData = [
            'username' => 'user',
            'password' => '123456'
        ];

        $user = User::query()->where('username', $loginData['username'])->first();

        $result = $this->authService->login(User::class, ...array_values($loginData));

        $this->validateLogin($user, $result);
    }

    public function test_admin_login_correct()
    {
        $loginData = [
            'username' => 'admin',
            'password' => '123456'
        ];

        $user = Admin::query()->where('username', $loginData['username'])->first();

        $result = $this->authService->login(Admin::class, ...array_values($loginData));

        $this->validateLogin($user, $result);
    }

    private function validateLogin($user, $result)
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertIsArray($result['token']);
        $token = $result['token'];
        $this->assertArrayHasKey('token_type', $token);
        $this->assertArrayHasKey('expires_in', $token);
        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('refresh_token', $token);
        $this->assertEquals('Bearer', $token['token_type']);
        $this->assertNotNull($token['expires_in']);
        $this->assertIsInt($token['expires_in']);
        $this->assertNotNull($token['access_token']);
        $this->assertIsString($token['access_token']);
        $this->assertNotNull($token['refresh_token']);
        $this->assertIsString($token['refresh_token']);

        $this->assertArrayHasKey('user', $result);
        $this->assertNotNull($result['user']);
        $this->assertEquals($user, $result['user']);
    }

    public function test_user_login_incorrect()
    {
        $loginData = [
            'username' => 'user',
            'password' => '1234567'
        ];

        $this->expectException(AuthorizationException::class);

        $this->authService->login(User::class, ...array_values($loginData));
    }

    public function test_admin_login_incorrect()
    {
        $loginData = [
            'username' => 'admin',
            'password' => '1234567'
        ];

        $this->expectException(AuthorizationException::class);

        $this->authService->login(Admin::class, ...array_values($loginData));
    }

    public function test_user_refresh_token()
    {
        $loginData = [
            'username' => 'user',
            'password' => '123456'
        ];

        $loginResult = $this->authService->login(User::class, ...array_values($loginData));

        $result = $this->authService->refreshToken(User::class, $loginResult['token']['refresh_token']);

        $this->validateRefreshToken($result);
    }

    public function test_admin_refresh_token()
    {
        $loginData = [
            'username' => 'admin',
            'password' => '123456'
        ];

        $loginResult = $this->authService->login(Admin::class, ...array_values($loginData));

        $result = $this->authService->refreshToken(Admin::class, $loginResult['token']['refresh_token']);

        $this->validateRefreshToken($result);
    }

    public function test_user_logout()
    {
        $loginData = [
            'username' => 'user',
            'password' => '123456'
        ];

        $loginResult = $this->authService->login(User::class, ...array_values($loginData));

        $accessToken = $loginResult['token']['access_token'];
        $tokenId = (new Parser(new JoseEncoder()))->parse($accessToken)->claims()->get('jti');
        $token = Token::query()->find($tokenId);

        $user = $loginResult['user'];
        $user->withAccessToken($token);

        $this->authService->logout($user);

        $this->validateLogout($tokenId);
    }

    private function validateLogout($tokenId)
    {
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokenId,
            'revoked' => true,
        ]);
        $this->assertDatabaseMissing('oauth_refresh_tokens', [
            'access_token_id' => $tokenId,
            'revoked' => false,
        ]);
        $this->assertDatabaseMissing('oauth_refresh_tokens', [
            'access_token_id' => $tokenId,
            'revoked' => null,
        ]);
    }

    public function test_user_revoke_all()
    {
        $loginData = [
            'username' => 'user',
            'password' => '123456'
        ];

        $loginResult = $this->authService->login(User::class, ...array_values($loginData));

        $user = $loginResult['user'];

        $this->authService->revokeAllTokens($user);
        $user->load(['tokens' => function ($query) {
            $query->where('revoked', '<>', true);
        }]);

        $this->assertCount(0, $user->tokens);
    }
}
