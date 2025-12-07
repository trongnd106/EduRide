# Observer

Thông tin chi tiết [Observer](https://laravel.com/docs/8.x/eloquent#observers)

Sử dụng Observer khi ta muốn lắng nghe nhiều Event một lúc cho một Model (Thông tin về Event xem tại [đây](model.md#Event))

## Khi nào nên sử dụng Event - Observer

Rất nhiều trường hợp, sau khi tiến hành create, update 1 record nào đấy, ta muốn thực hiện một hành động
- Ví dụ: Sau khi tạo 1 User thì tiến hành gửi Email xác nhận,...

Và giả sử như, thao tác tạo User này được sử dụng ở nhiều nơi khác nhau. Thì thay vì ở chỗ nào cũng cần viết lại đoạn logic gửi Email, hoặc ngay cả tách hàm Helper thì cũng vẫn phải gọi lại hàm đấy lặp đi lặp lại => Ta có thể sử dụng tới Event - Observer để làm điều này

## Tạo Observer

Sử dụng command sau để tạo Observer

```
php artisan make:observer UserObserver --model=User
```

Sau khi tạo Observer, cần đăng ký nó ở trong Model

```php
use App\Models\User;
use App\Observers\UserObserver;

public function boot()
{
    User::observe(UserObserver::class);
}
```

## Sử dụng Observer

Đối với các event của vòng đời Model, muốn hook event nào thì ta cần tạo function có tên tương ứng ở trong Observer

Ví dụ: Khi muốn hook event `created`, `updated`, `deleted`, `forceDeleted` của model User

```php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
```

## Transaction

Khi sử dụng Transaction để rollback trong trường hợp lỗi, nếu ta muốn Observer chỉ chạy khi Transaction được commit, ta cần thêm thuộc tính `$afterCommit` vào Observer với giá trị `true`

```php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public $afterCommit = true;

    public function created(User $user)
    {
        //
    }
}
```
