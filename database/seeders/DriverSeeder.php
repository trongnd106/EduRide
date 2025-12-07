<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 30 drivers with random data
        Driver::factory()->count(30)->create();

        // Create 10 male drivers who are active
        Driver::factory()->count(10)->male()->active()->create();

        // Create 10 female drivers who are active
        Driver::factory()->count(10)->female()->active()->create();

        // Create some drivers with specific data (example)
        Driver::factory()->create([
            'full_name' => 'Nguyễn Văn An',
            'cccd' => '001234567890',
            'phone' => '0987654321',
            'gender' => 1,
            'license_number' => 'A1234567',
            'license_expiry' => '2026-12-31',
            'dob' => '1985-05-15',
            'school_id' => 1,
            'status' => 1,
        ]);

        Driver::factory()->create([
            'full_name' => 'Trần Thị Bình',
            'cccd' => '001234567891',
            'phone' => '0987654322',
            'gender' => 0,
            'license_number' => 'B1234567',
            'license_expiry' => '2027-06-30',
            'dob' => '1990-08-20',
            'school_id' => 1,
            'status' => 1,
        ]);
    }
}
