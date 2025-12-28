<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 vehicles with random data
        Vehicle::factory()->count(20)->create();

        // Create 10 active vehicles
        Vehicle::factory()->count(10)->active()->create();

        // Create 5 inactive vehicles
        Vehicle::factory()->count(5)->inactive()->create();

        // Create some vehicles with specific data (example)
        Vehicle::factory()->create([
            'type' => 1,
            'plate_number' => '30A-12345',
            'capacity' => 16,
            'year' => 2020,
            'brand' => 'Ford Transit',
            'model' => 'Transit 350',
            'color' => 'Trắng',
            'status' => 1,
        ]);

        Vehicle::factory()->create([
            'type' => 2,
            'plate_number' => '30B-67890',
            'capacity' => 29,
            'year' => 2021,
            'brand' => 'Mercedes Sprinter',
            'model' => 'Sprinter 316',
            'color' => 'Đen',
            'status' => 1,
        ]);

        Vehicle::factory()->create([
            'type' => 1,
            'plate_number' => '29A-11111',
            'capacity' => 24,
            'year' => 2019,
            'brand' => 'Toyota Hiace',
            'model' => 'Hiace 2020',
            'color' => 'Bạc',
            'status' => 0,
        ]);
    }
}
