# Job

Thông tin thêm về [Job](https://laravel.com/docs/8.x/queues)

## Khi nào nên sử dụng Queue - Job

Trong quá trình phát triển web, có nhiều khi ta cần thực hiện những task mất thời gian nhưng không cần thực hiện ngay, hoặc những task cần xử lý data số lượng lớn
- Ví dụ: Tạo User xong thì cần gửi Email xác nhận. Thông thường, việc gửi Email thường rất mất thời gian vì phải sử dụng service bên thứ ba, đồng thời cũng thường bị limit nên không thể gửi liên tiếp nhiều email đồng thời

Trong những trường hợp này, ta sử dụng tới Job Queue để giảm thiểu thời gian chờ đợi phía user, đồng thời, do việc gửi Email không bị gắn trực tiếp với việc tạo user nên khi gặp nhiều request tạo user đồng thời cũng không sợ server mail bị quá tải/limit

## Tạo Job

Sử dụng command sau để tạo Job

```
php artisan make:job ProcessPodcast
```

```php
namespace App\Jobs;

use App\Models\Podcast;
use App\Services\AudioProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The podcast instance.
     *
     * @var \App\Models\Podcast
     */
    protected $podcast;

    /**
     * Create a new job instance.
     *
     * @param  App\Models\Podcast  $podcast
     * @return void
     */
    public function __construct(Podcast $podcast)
    {
        $this->podcast = $podcast;
    }

    /**
     * Execute the job.
     *
     * @param  App\Services\AudioProcessor  $processor
     * @return void
     */
    public function handle(AudioProcessor $processor)
    {
        // Process uploaded podcast...
    }
}
```

## Sử dụng Model trong Job

- Như ở ví dụ trên, để truyền được Model (VD: `Podcast`) vào constructor, ta cần sử dụng tới trait `SerializesModels` (`use SerializesModels`)
- Khi Job được chạy, sẽ tự động tiến hành query lại DB để lấy Model đó ra
- Trong trường hợp không muốn load Relation để tránh việc phải query nhiều, ta có thể gọi tới hàm `withoutRelations()`

```php
public function __construct(Podcast $podcast)
{
    $this->podcast = $podcast->withoutRelations();
}
```

## Dependency Injection

Hàm handle chính là hàm chính để thực hiện chạy Job, tham số được truyền vào hàm này sẽ được tự động Inject bới Laravel

## Job Unique (độc nhất)

Khi muốn một Job là Unique (tại 1 thời điểm chỉ có 1 instance của Job này được chạy), ta implement thêm interface `ShouldBeUnique` vào class của Job

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
{
    /**
     * Thời gian Job là Unique, sau khoảng thời gian này thì Job tương tự có thể được phép chạy
     *
     * @var int
     */
    public $uniqueFor = 3600;  // Optional

    /**
     * ID của Job Unique này
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->product->id;  // Optional
    }
}
```

Lưu ý: Chỉ những cache driver hỗ trợ [lock](https://laravel.com/docs/8.x/cache#atomic-locks) mới có thể sử dụng tính năng này
- Những driver hỗ trợ: `memcached`, `redis`, `dynamodb`, `database`, `file`, `array`

## Sử dụng Job

- Sử dụng hàm `dispatch` để đẩy Job vào Queue

```php
use App\Jobs\ProcessPodcast;
use App\Models\Podcast;

public function store(Request $request)
{
    $podcast = Podcast::create(...);

    // ...

    ProcessPodcast::dispatch($podcast);
}
```

- Job Chaining: khai báo một list các Job sẽ chạy tuần tự và ngay lập tức mỗi khi Job trước hoàn thành

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new ProcessPodcast,
    new OptimizePodcast,
    new ReleasePodcast,
    function () {
        Podcast::update(...);
    },
])->catch(function (Throwable $e) {
    // Catch lỗi khi một Job bị fail
})->dispatch();
```

## Job Batch

Sử dụng Job Batch khi muốn thực hiện 1 Job lặp lại nhiều lần với mỗi lần sẽ có tham số đầu vào khác nhau, đồng thời có thể bắt được các event khi Batch hoàn thành hay thất bại

- Tạo table cho Job Batch

```
php artisan queue:batches-table

php artisan migrate
```

- Để tạo Job Batch, trước tiên cần tạo Job như bình thường, sau đó sử dụng thêm trait `Illuminate\Bus\Batchable`

```php
namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCsv implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            // Check xem batch có bị hủy hay không

            return;
        }

        // Import 1 phần của file CSV
    }
}
```

- Dispatch Job Batch (VD: import 1 file CSV lớn)

```php
use App\Jobs\ImportCsv;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

$batch = Bus::batch([
    new ImportCsv(1, 100),
    new ImportCsv(101, 200),
    new ImportCsv(201, 300),
    new ImportCsv(301, 400),
    new ImportCsv(401, 500),
])->then(function (Batch $batch) {
    // Tất cả Job hoàn thành
})->catch(function (Batch $batch, Throwable $e) {
    // Catch khi có một job bị lỗi
})->finally(function (Batch $batch) {
    // Batch được xử lý xong, ngay cả khi success hay fail
})->dispatch();

return $batch->id;
```

## Chạy Queue Worker để xử lý Job

Sử dụng Command sau để tiến hành xử lý Job

```
php artisan queue:work
```

Thường sẽ sử dụng các server để chạy ngầm các command này như `PM2`, `Supervisord`, `tmux`

## Xóa Job khỏi Queue

Để xóa toàn bộ Job khỏi queue, sử dụng command

```
php artisan queue:clear redis --queue=emails
```
