<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StudentParent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentParent>
 */
class StudentParentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentParent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tên tiếng Việt
        $ho = fake()->randomElement(['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Đinh', 'Đào', 'Mai', 'Tô']);
        $tenDem = fake()->randomElement(['Văn', 'Thị', 'Đức', 'Minh', 'Thanh', 'Quang', 'Hữu', 'Công', 'Đình', 'Xuân', 'Hồng', 'Thành', 'Tuấn', 'Anh', 'Bảo', '']);
        $ten = fake()->randomElement(['An', 'Bình', 'Chi', 'Dung', 'Hạnh', 'Hoa', 'Lan', 'Linh', 'Mai', 'Nga', 'Phương', 'Quỳnh', 'Thảo', 'Uyên', 'Vy', 'Yến', 'Anh', 'Bích', 'Hương', 'Linh', 'Cường', 'Dũng', 'Hùng', 'Khang', 'Long', 'Mạnh', 'Nam', 'Phong', 'Quang', 'Sơn', 'Thành', 'Tuấn', 'Việt']);
        
        $fullName = $tenDem ? $ho . ' ' . $tenDem . ' ' . $ten : $ho . ' ' . $ten;

        return [
            'full_name' => $fullName,
            'phone_number' => '0' . fake()->randomElement(['3', '5', '7', '8', '9']) . fake()->numerify('########'),
        ];
    }
}
