<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StudentParent;
use Illuminate\Database\Seeder;

class StudentParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 parents with random data
        StudentParent::factory()->count(50)->create();

        // Create some parents with specific data (example)
        StudentParent::updateOrCreate(
            ['phone_number' => '0987654321'],
            [
                'full_name' => 'Nguyễn Văn An',
            ]
        );

        StudentParent::updateOrCreate(
            ['phone_number' => '0987654322'],
            [
                'full_name' => 'Trần Thị Bình',
            ]
        );
    }
}
