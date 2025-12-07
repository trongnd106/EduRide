<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Tests\Traits\InteractsWithUsers;
use Tests\Traits\InteractWithDomain;
use Illuminate\Support\Facades\Hash;

class ApiTest extends TestCase
{
    use InteractWithDomain, InteractsWithUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:install', ['-vvv' => true]);
        $this->seed(UserSeeder::class);

        $this->setUpDomain(env('FRONTEND_LOCAL_DOMAIN'));
    }

    public function test_health_check_route()
    {
        $response = $this->get($this->getUrl('/'));

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'OK'
            ]);
    }

    public function test_user_register_route()
    {
        $userData = User::factory()->preRegister()->make()->getAttributes();

        $response = $this->post($this->getUrl('/register'), $userData);

        $response
            ->assertOk()
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token'])
            ->assertJson([
                'token_type' => 'Bearer'
            ]);
        $this->assertLessThanOrEqual($response['expires_in'], 31536000);
    }

    public function test_user_register_route_duplicated()
    {
        $userData = User::factory()->preRegister()->create()->getAttributes();

        $response = $this->post($this->getUrl('/register'), $userData);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'code' => 'InvalidParametersException',
                'errors' => [
                    'messages' => [
                        'username' => [__('validation.unique', ['attribute' => 'username'])],
                        'email' => [__('validation.unique', ['attribute' => 'email'])],
                    ]
                ]
            ])
            ->assertJsonStructure(['code', 'message', 'errors']);
    }

    public function test_user_login_route()
    {
        $password = '123456';
        $user = User::factory()->withPassword($password)->withDeleted()->create();

        $response = $this->post($this->getUrl('/login'), [
            'username' => $user->username,
            'password' => $password
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token' => ['token_type', 'expires_in', 'access_token', 'refresh_token'],
                'user' => []
            ])
            ->assertJson([
                'token' => ['token_type' => 'Bearer']
            ]);
        $this->assertLessThanOrEqual($response['token']['expires_in'], 31536000);
    }

    public function test_user_login_route_wrong_info()
    {
        $password = '123456';
        $user = User::factory()->withPassword($password)->withDeleted()->create();

        $response = $this->post($this->getUrl('/login'), [
            'username' => $user->username,
            'password' => $password . 7
        ]);

        $response
            ->assertForbidden()
            ->assertJson([
                'code' => 'UnauthorizedException',
                'message' => __('api.exception.invalid_credentials')
            ])
            ->assertJsonStructure(['code', 'message']);
    }

    public function test_user_logout_route()
    {
        $this->setUpUser(User::class);
        $response = $this->post($this->getUrl('/logout'));

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'OK'
            ]);
    }

    public function test_user_logout_route_unauthenticated()
    {
        $response = $this->post($this->getUrl('/logout'));

        $response
            ->assertUnauthorized()
            ->assertJson([
                'code' => 'UnauthenticatedException',
                'message' => __('api.exception.unauthorized')
            ])
            ->assertJsonStructure(['code', 'message']);
    }

    public function test_user_refresh_token_route()
    {
        $password = '123456';
        $this->setUpUser(User::class, ['password' => Hash::make($password)]);

        $loginResponse = $this->post($this->getUrl('/login'), [
            'username' => $this->user->username,
            'password' => $password
        ]);
        $refreshToken = $loginResponse['token']['refresh_token'];

        $refreshResponse = $this->post($this->getUrl('/refresh_token'), [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse
            ->assertOk()
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token'])
            ->assertJson([
                'token_type' => 'Bearer'
            ]);
        $this->assertLessThanOrEqual($refreshResponse['expires_in'], 31536000);
    }
}
