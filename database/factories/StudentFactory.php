<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->boolean();
        $firstName = $gender ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();
        
        return [
            'school_id' => fake()->numberBetween(1, 10),
            'student_number' => 'SV' . fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'full_name' => $lastName . ' ' . $firstName,
            'phone' => '0' . fake()->randomElement(['3', '5', '7', '8', '9']) . fake()->numerify('########'),
            'gender' => $gender,
            'dob' => fake()->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
            'grade' => fake()->randomElement([10, 11, 12]),
            'status' => fake()->randomElement([0, 1]),
            'address' => fake()->address(),
        ];
    }

    /**
     * Indicate that the student is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the student is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => true,
        ]);
    }

    /**
     * Indicate that the student is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => false,
        ]);
    }
}
