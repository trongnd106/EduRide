<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::updateOrCreate(
            ['code' => 'HUST'],
            [
                'name' => 'Đại học Bách Khoa Hà Nội',
                'phone' => '024-38692008',
                'information' => 'Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam, được thành lập ngày 15/03/1956. Trường đào tạo kỹ sư và các chuyên ngành kỹ thuật công nghệ chất lượng cao.',
                'address' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội',
                'status' => 1,
            ]
        );
    }
}
