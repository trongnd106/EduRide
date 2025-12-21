<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 students with random data
        Student::factory()->count(50)->create();

        // Create 10 male students who are active
        Student::factory()->count(10)->male()->active()->create();

        // Create 10 female students who are active
        Student::factory()->count(10)->female()->active()->create();

        // Create some students with specific data (example)
        Student::factory()->create([
            'student_number' => '20225103',
            'email' => 'trong.nd225103@sis.hust.edu.vn',
            'full_name' => 'Nguyễn Đạt Trọng',
            'phone' => '098623828',
            'gender' => true,
            'dob' => '2004-06-10',
            'grade' => 4,
            'status' => 1,
            'address' => 'Ngõ 19 Duy Tân, Quận Nam Từ Liêm, Hà Nội',
            'student_parent_id' => 1,
            'latitude' => 21.028511,
            'longitude' => 105.804817,
        ]);

        Student::factory()->create([
            'student_number' => '20225104',
            'email' => 'tuan.ha225104@sis.hust.edu.vn',
            'full_name' => 'Hoàng Anh Tuấn',
            'phone' => '0987654321',
            'gender' => false,
            'dob' => '2005-03-20',
            'grade' => 11,
            'status' => 1,
            'address' => '456 Lê Lợi, Quận Cầu Giấy, Hà Nội',
            'student_parent_id' => 2,
            'latitude' => 21.030000,
            'longitude' => 105.800000,
        ]);
    }
}

