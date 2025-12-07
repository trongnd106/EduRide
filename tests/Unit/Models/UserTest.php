<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Database\Factories\TokenFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client as OClient;
use Laravel\Passport\Token;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $userModelClass = User::class;
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:install', ['-vvv' => true]);
        Artisan::call('passport:client', ['--password' => true, '--provider' => 'admins', '--name' => 'admins']);

        $this->user = User::factory()->make();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_user_can_be_authenticated()
    {
        $providersConfig = config('auth.providers');
        $userProvider = null;
        foreach ($providersConfig as $provider => $data) {
            if (isset($data['model']) && $data['model'] === User::class) {
                $userProvider = $provider;
                break;
            }
        }

        $this->assertNotNull($userProvider, "There is no Provider config match Model {$this->userModelClass}");

        $userClient = OClient::query()
            ->where('password_client', 1)
            ->where('provider', $userProvider)
            ->first();

        $this->assertNotNull($userClient, "No Client for Model {$this->userModelClass} can be found in database");

        $this->assertInstanceOf(HasMany::class, $this->user->tokens());
        $this->assertEquals('user_id', $this->user->tokens()->getForeignKeyName());
    }
}
