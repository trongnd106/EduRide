<?php

namespace Database\Factories;

use App\Constants\Role;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('ja_JP');
        return [
            'email' => $faker->unique()->safeEmail(),
            'password' => Hash::make('Admin@1234'),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'spir_calendar_link' => $faker->url,
            'status'=>  $faker->randomElement([0,1])
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Admin $user) {
            $role = $this->faker->randomElement(array_keys(Role::ROLE_MAP));
            $user->syncRoles($role);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return AdminFactory
     */
    public function unverified(): AdminFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function preRegister(): AdminFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'password' => $this->faker->password(16),
                'remember_token' => null,
            ];
        });
    }

    public function withPassword(string $password): AdminFactory
    {
        return $this->state(function (array $attributes) use ($password) {
            return [
                'password' => Hash::make($password)
            ];
        });
    }

    public function withDeleted(Date $date = null): AdminFactory
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'deleted_at' => $date
            ];
        });
    }
}
