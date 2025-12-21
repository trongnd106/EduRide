<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Driver;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->numberBetween(0, 1);
        $firstName = $gender === 1 ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();

        $schoolId = School::inRandomOrder()->value('id');
        $age = fake()->numberBetween(25, 60);

        return [
            'full_name' => $lastName . ' ' . $firstName,
            'cccd' => fake()->unique()->numerify('##########'),
            'phone' => '0' . fake()->randomElement(['3', '5', '7', '8', '9']) . fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'gender' => $gender,
            'license_number' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F']) . fake()->numerify('#######'),
            'age' => $age,
            'address' => fake()->address(),
            'image_url' => fake()->optional()->imageUrl(200, 200, 'people', true, $lastName . ' ' . $firstName),
            'school_id' => $schoolId,
            'status' => fake()->randomElement([0, 1]),
        ];
    }

    /**
     * Indicate that the driver is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the driver is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the driver is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 1,
        ]);
    }

    /**
     * Indicate that the driver is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 0,
        ]);
    }
}
