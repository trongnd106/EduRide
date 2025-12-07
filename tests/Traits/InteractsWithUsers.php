<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

trait InteractsWithUsers
{
    private $user;

    public function setUpUser(string $model, array $attributes = [], $guard = null)
    {
        $this->logout();

        $this->user = $model::factory()->create($attributes);

        $providerName = null;
        foreach (config('auth.providers') as $name => $provider) {
            if ($provider['model'] === $model) {
                $providerName = $name;
                break;
            }
        }
        if ($providerName) {
            foreach (config('auth.guards') as $name => $guardData) {
                if ($guardData['provider'] === $providerName) {
                    $guard = $name;
                    break;
                }
            }
        }

        $this->login($guard);

        return $this;
    }

    public function login($guard = null)
    {
        Passport::actingAs($this->user, [], $guard);
    }

    public function logout()
    {
        $this->user = null;
    }
}
