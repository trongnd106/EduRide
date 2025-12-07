<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schoolId = School::inRandomOrder()->value('id');
        
        // Biển số xe Việt Nam: 30A-12345, 29B-67890, etc.
        $provinces = ['29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '43', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '88', '89', '90', '92', '93', '94', '95', '97', '98', '99'];
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'P', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z'];
        
        $province = fake()->randomElement($provinces);
        $letter = fake()->randomElement($letters);
        $numbers = fake()->numerify('#####');
        
        $brands = ['Ford Transit', 'Mercedes Sprinter', 'Toyota Hiace', 'Hyundai County', 'Isuzu NPR', 'Mitsubishi Fuso', 'Chevrolet', 'VinFast', 'Thaco', 'Suzuki'];
        
        return [
            'school_id' => $schoolId,
            'plate_number' => $province . $letter . '-' . $numbers,
            'capacity' => fake()->randomElement([16, 24, 29, 35, 45]),
            'year' => fake()->numberBetween(2015, 2024),
            'brand' => fake()->randomElement($brands),
            'status' => fake()->randomElement([0, 1]),
        ];
    }

    /**
     * Indicate that the vehicle is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the vehicle is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
