<?php

declare(strict_types=1);

namespace Database\Factories;

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
        // Biển số xe Việt Nam: 30A-12345, 29B-67890, etc.
        $provinces = ['29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '43', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '88', '89', '90', '92', '93', '94', '95', '97', '98', '99'];
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'P', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z'];
        
        $province = fake()->randomElement($provinces);
        $letter = fake()->randomElement($letters);
        $numbers = fake()->numerify('#####');
        
        $brands = ['Ford Transit', 'Mercedes Sprinter', 'Toyota Hiace', 'Hyundai County', 'Isuzu NPR', 'Mitsubishi Fuso', 'Chevrolet', 'VinFast', 'Thaco', 'Suzuki'];
        $models = ['Transit 350', 'Sprinter 316', 'Hiace 2020', 'County 29', 'NPR 75', 'Fuso Canter', 'Express', 'Lux SA2.0', 'Ollin', 'Carry'];
        $colors = ['Trắng', 'Đen', 'Bạc', 'Xám', 'Xanh dương', 'Xanh lá', 'Đỏ', 'Vàng', 'Cam', 'Nâu'];
        
        return [
            'type' => fake()->randomElement([1, 2]),
            'plate_number' => $province . $letter . '-' . $numbers,
            'capacity' => fake()->randomElement([16, 24, 29, 35, 45]),
            'year' => fake()->numberBetween(2015, 2024),
            'brand' => fake()->randomElement($brands),
            'model' => fake()->randomElement($models),
            'color' => fake()->randomElement($colors),
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
