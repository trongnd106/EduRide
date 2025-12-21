<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentParent;
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
        
        $parentId = StudentParent::inRandomOrder()->value('id');
        
        // Tọa độ GPS Hà Nội (khoảng)
        $latitude = fake()->randomFloat(8, 20.8, 21.2);
        $longitude = fake()->randomFloat(8, 105.6, 106.0);
        
        return [
            'student_parent_id' => $parentId,
            'student_number' => 'SV' . fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'full_name' => $lastName . ' ' . $firstName,
            'phone' => '0' . fake()->randomElement(['3', '5', '7', '8', '9']) . fake()->numerify('########'),
            'gender' => $gender,
            'dob' => fake()->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
            'grade' => fake()->randomElement([10, 11, 12]),
            'status' => fake()->randomElement([0, 1]),
            'address' => fake()->address(),
            'latitude' => $latitude,
            'longitude' => $longitude,
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
