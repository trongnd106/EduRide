<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Driver;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->numberBetween(0, 1);
        
        // Tên tiếng Việt
        $ho = fake()->randomElement(['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Đinh', 'Đào', 'Mai', 'Tô']);
        $tenDem = fake()->randomElement(['Văn', 'Thị', 'Đức', 'Minh', 'Thanh', 'Quang', 'Hữu', 'Công', 'Đình', 'Xuân', 'Hồng', 'Thành', 'Tuấn', 'Anh', 'Bảo', '']);
        $ten = $gender === 1 
            ? fake()->randomElement(['An', 'Bình', 'Cường', 'Dũng', 'Hùng', 'Khang', 'Long', 'Mạnh', 'Nam', 'Phong', 'Quang', 'Sơn', 'Thành', 'Tuấn', 'Việt', 'Anh', 'Bảo', 'Đức', 'Huy', 'Khoa'])
            : fake()->randomElement(['An', 'Bình', 'Chi', 'Dung', 'Hạnh', 'Hoa', 'Lan', 'Linh', 'Mai', 'Nga', 'Phương', 'Quỳnh', 'Thảo', 'Uyên', 'Vy', 'Yến', 'Anh', 'Bích', 'Hương', 'Linh']);
        
        $fullName = $tenDem ? $ho . ' ' . $tenDem . ' ' . $ten : $ho . ' ' . $ten;

        $schoolId = School::inRandomOrder()->value('id');
        $age = fake()->numberBetween(25, 60);

        // Địa chỉ tiếng Việt
        $soNha = fake()->numberBetween(1, 999);
        $duong = fake()->randomElement(['Đường Láng', 'Đường Giải Phóng', 'Đường Lê Duẩn', 'Đường Nguyễn Trãi', 'Đường Hoàng Quốc Việt', 'Đường Cầu Giấy', 'Đường Đại Cồ Việt', 'Đường Trần Duy Hưng', 'Đường Phạm Văn Đồng', 'Đường Nguyễn Chí Thanh']);
        $phuong = fake()->randomElement(['Phường Láng Thượng', 'Phường Ô Chợ Dừa', 'Phường Khương Thượng', 'Phường Trung Liệt', 'Phường Phương Liên', 'Phường Kim Liên', 'Phường Ngã Tư Sở', 'Phường Thịnh Quang', 'Phường Trung Tự', 'Phường Khâm Thiên']);
        $quan = fake()->randomElement(['Quận Đống Đa', 'Quận Hai Bà Trưng', 'Quận Hoàn Kiếm', 'Quận Ba Đình', 'Quận Cầu Giấy', 'Quận Thanh Xuân', 'Quận Tây Hồ', 'Quận Long Biên', 'Quận Nam Từ Liêm', 'Quận Bắc Từ Liêm']);
        $address = "Số {$soNha}, {$duong}, {$phuong}, {$quan}, Hà Nội";

        // Email tiếng Việt (chuyển tên không dấu)
        $emailName = $this->removeVietnameseAccents(strtolower(str_replace(' ', '', $fullName)));
        $email = $emailName . '@' . fake()->randomElement(['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com']);

        return [
            'full_name' => $fullName,
            'cccd' => fake()->unique()->numerify('##########'),
            'phone' => '0' . fake()->randomElement(['3', '5', '7', '8', '9']) . fake()->numerify('########'),
            'email' => $email,
            'gender' => $gender,
            'license_number' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F']) . fake()->numerify('#######'),
            'age' => $age,
            'address' => $address,
            'image_url' => fake()->optional()->imageUrl(200, 200, 'people', true, $fullName),
            'school_id' => $schoolId,
            'status' => fake()->randomElement([0, 1]),
            'position' => fake()->randomElement([1, 2]), // 1 = Tài xế, 2 = Phụ xe
        ];
    }

    /**
     * Remove Vietnamese accents from string
     */
    private function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ'
        ];
        $noAccents = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];
        return str_replace($accents, $noAccents, $str);
    }

    /**
     * Indicate that the driver is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the driver is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Indicate that the driver is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 1,
        ]);
    }

    /**
     * Indicate that the driver is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 0,
        ]);
    }
}
