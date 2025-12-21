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

        // Create some drivers with specific data (example) - using updateOrCreate to avoid duplicates
        Driver::updateOrCreate(
            ['cccd' => '001234567890'],
            [
                'full_name' => 'Nguyễn Văn An',
                'phone' => '0987654321',
                'email' => 'nguyenvanan@example.com',
                'gender' => 1,
                'license_number' => 'A1234567',
                'age' => 39,
                'address' => '123 Đường Láng, Quận Đống Đa, Hà Nội',
                'image_url' => null,
                'school_id' => 1,
                'status' => 1,
            ]
        );

        Driver::updateOrCreate(
            ['cccd' => '001234567891'],
            [
                'full_name' => 'Trần Thị Bình',
                'phone' => '0987654322',
                'email' => 'tranthibinh@example.com',
                'gender' => 0,
                'license_number' => 'B1234567',
                'age' => 34,
                'address' => '456 Lê Lợi, Quận Cầu Giấy, Hà Nội',
                'image_url' => null,
                'school_id' => 1,
                'status' => 1,
            ]
        );
    }
}
