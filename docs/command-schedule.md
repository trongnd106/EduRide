# Command

Thông tin cụ thể về Command tham khảo tại [đây](https://laravel.com/docs/8.x/artisan)

## Tạo command

Sử dụng command sau để tạo command mới

```
php artisan make:command SendEmails
```

```php
namespace App\Console\Commands;

use App\Models\User;
use App\Support\DripEmailer;
use Illuminate\Console\Command;

class SendEmails extends Command
{
    /**
     * Cú pháp thực thi command, trong đó {user} là tham số truyền vào
     *
     * @var string
     */
    protected $signature = 'mail:send {user}';

    protected $description = 'Send a marketing email to a user';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hàm thực thi logic của command
     *
     * @param  \App\Support\DripEmailer  $drip
     * @return mixed
     */
    public function handle(DripEmailer $drip)
    {
        // Sử dụng $this->argument('user') để lấy tham số 'user' khi chạy command
        $drip->send(User::find($this->argument('user')));
    }
}
```

- Tham số truyền vào được định nghĩa ở `$signature` và đặt trong dấu `{}`

```php
// Optional argument...
mail:send {user?}

// Optional argument with default value...
mail:send {user=foo}
```

- Options được đặt phía sau dấu `--`, nếu lúc dùng command có kèm theo options này thì giá trị của arg sẽ là true và ngược lại

```php
protected $signature = 'mail:send {user} {--queue}';
```

- Lấy tham số truyền vào bằng cách sử dụng hàm `$this->argument`

```php
public function handle()
{
    $userId = $this->argument('user');

    //
}
```

## Sử dụng command

- Gọi command ở console bằng `artisan`

```
php artisan mail:send 1 --queue
```

- Gọi command trong code bằng cách sử dụng `Artisan:call`

```php
use Illuminate\Support\Facades\Artisan;

Artisan::call('mail:send', [
    'user' => $user, '--queue' => 'default'
]);

// Hoặc dùng thằng câu command như ở console
Artisan::call('mail:send 1 --queue=default');
```

- Queue Command để xử lý bằng Queue Worker bằng `Artisan::queue`

```php
Artisan::queue('mail:send', [
    'user' => $user, '--queue' => 'default'
]);
```

- Gọi command chéo nhau bằng `$this->call()` hoặc `$this->callSilently()` nếu không muốn output console log của command được gọi

```php
public function handle()
{
    $this->call('mail:send', [
        'user' => 1, '--queue' => 'default'
    ]);

    //
}
```

# Schedule

Thông tin cụ thể về Schedule tham khảo tại [đây](https://laravel.com/docs/8.x/scheduling)

## Khai báo Schedule

Mọi khai báo schedule được thực hiện ở trong class `App\Console\Kernel`

Các loại Schedule:
- Closure (Callback)
- Artisan Command
- Job
- Shell Command

```php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule Closure
        $schedule->call(function () {
            DB::table('recent_users')->delete();
        })->daily();
        
        // Schedule Artisan Command
        $schedule->command('emails:send Taylor --force')->daily();

        $schedule->command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();
        
        // Schedule Job
        $schedule->job(new Heartbeat)->everyFiveMinutes();
        
        // Schedule Shell Command
        $schedule->exec('node /home/forge/script.js')->daily();
    }
}
```

## Cài đặt tần suất chạy Schedule

Dưới đây là danh sách các hàm tần suất mặc định có thể sử dụng cho từng schedule

| Method                            | Description                                   |
| --------------------------------- | --------------------------------------------- |
| ->cron('* * * * *');              | Theo lịch Cron                                |
| ->everyMinute();                  | Mỗi phút                                      |
| ->everyTwoMinutes();              | Mỗi 2 phút                                    |
| ->everyThreeMinutes();            | Mỗi 3 phút                                    |
| ->everyFourMinutes();             | Mỗi 4 phút                                    |
| ->everyFiveMinutes();             | Mỗi 5 phút                                    |
| ->everyTenMinutes();              | Mỗi 10 phút                                   |
| ->everyFifteenMinutes();          | Mỗi 15 phút                                   |
| ->everyThirtyMinutes();           | Mỗi 30 phút                                   |
| ->hourly();                       | Mỗi giờ                                       |
| ->hourlyAt(17);                   | Mỗi giờ vào phút thứ 17                       |
| ->everyTwoHours();                | Mỗi 2 giờ                                     |
| ->everyThreeHours();              | Mỗi 3 giờ                                     |
| ->everyFourHours();               | Mỗi 4 giờ                                     |
| ->everySixHours();                | Mỗi 6 giờ                                     |
| ->daily();                        | Mỗi ngày                                      |
| ->dailyAt('13:00');               | Mỗi ngày vào lúc 13:00                        |
| ->twiceDaily(1, 13);              | Mỗi ngày 2 lần vào lúc 1:00 và 13:00          |
| ->weekly();                       | Mỗi tuần vào thứ 2 lúc 00:00                  |
| ->weeklyOn(1, '8:00');            | Mỗi tuần vào thứ hai lúc 8:00                 |
| ->monthly();                      | Mỗi tháng lúc 00:00 ngày đầu tiên của tháng   |
| ->monthlyOn(4, '15:00');          | Mỗi tháng vào ngày mùng 4 lúc 15:00           |
| ->twiceMonthly(1, 16, '13:00');   | Mỗi tháng 2 lần vào ngày 1 và 16 lúc 13:00    |
| ->lastDayOfMonth('15:00');        | Ngày cuối cùng của tháng lúc 15:00            |
| ->quarterly();                    | Mỗi quý                                       |
| ->yearly();                       | Mỗi năm                                       |
| ->yearlyOn(6, 1, '17:00');        | Mỗi năm vào ngày 1 tháng 6 lúc 17:00          |
| ->timezone('America/New_York');   | Cài đặt Timezone                              |

## Chạy Schedule

- Ở trên Server, nếu muốn chạy Schedule thì đặt command sau trong crontab

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

- Khi Test ở Local thì có thể dùng command sau

```
php artisan schedule:work
```
