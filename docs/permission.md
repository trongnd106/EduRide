Codebase áp dụng phân quyền theo role (RBAC) để quản lý và phân quyền cho hệ thống.

Cách sử dụng cơ bản có thể xem ở link dưới đây:
https://spatie.be/docs/laravel-permission/v4/introduction

## Configuration
Codebase phân chia route và guard riêng biệt cho `backend` và `admin` được cấu hình trong file `config/auth.php`. <br>
```php
    //...
    'guards' => [
        //...
        // backend guard
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],

        // admin guard
        'api_admin' => [
            'driver' => 'passport',
            'provider' => 'admins',
        ],
    ],
    // ...
```

Mặc định permission chỉ được set cho guard `api_admin` được khai báo tại `database/seeders/PermissionSeeder.php`<br>
```php
use App\Constants\Permission as PermissionConstant;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use \App\Constants\Role as RoleConstant;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminGuard = Guard::getDefaultName(Admin::class);
        // Seeding roles
        /** @var Role $adminRole */
        $adminRole = Role::firstOrCreate(['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => $adminGuard]);
        /** @var Role $staff */
        $staff = Role::firstOrCreate(['name' => RoleConstant::ROLE_CONCIERGE, 'guard_name' => $adminGuard]);

        foreach (PermissionConstant::getAllPermissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $adminGuard]);
        }

        $adminRole->syncPermissions(PermissionConstant::getAdminPermissions());
        $staff->syncPermissions(PermissionConstant::getStaffPermissions());
    }
}
```

Class `PermissionSeeder` chịu trách nhiệm seeding role và permission vào database.<br>
Mặc định sẽ chỉ seeding cho guard `api_admin` thông qua field `guard_name` của permission và role.

Nếu như hệ thống yêu cầu cần có phân quyền ở phía user, khai báo thêm permission và role cho guard `api` trong function `run()`

```php
$userGuard = Guard::getDefaultName(User::class);

$modRole = Role::firstOrCreate(['name' => 'mod', 'guard_name' => $userGuard]);
$permission = Role::firstOrCreate(['name' => 'publish_post', 'guard_name' => $userGuard]);

$modRole->syncPermissions([$permission]);
```

**Lưu ý**: chỉ có thể sử dụng chung role và permission có chung `guard_name`, không thể add permission của guard `api` vào role của guard `api_admin`<br>
Chi tiết về multiple guards đọc thêm [tại đây](https://spatie.be/docs/laravel-permission/v4/basic-usage/multiple-guards)

## Khai báo Permission

Khai báo permission tại file `app\Constants\Permission.php`

```php
class Permission
{
    const POST_LIST = 'post.list';
    const POST_CREATE = 'post.create';
    const POST_VIEW = 'post.view';
    const POST_EDIT = 'post.edit';
    const POST_DELETE = 'post.delete';
    
    public static function getAllPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_CREATE,
            static::POST_VIEW,
            static::POST_EDIT,
            static::POST_DELETE,
            
            // ...
        ];
    }
    
    public static function getStaffPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_VIEW,
            
            // ...
        ];
    }

    public static function getAdminPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_CREATE,
            static::POST_VIEW,
            static::POST_EDIT,
            static::POST_DELETE,
            
            // ...
        ];
    }
}
```

Các function `getAllPermissions`, `getUserPermissions`, `getAdminPermissions` được dùng để seed data tại file `database/seeders/PermissionSeeder.php`

Tùy vào yêu cầu của hệ thống mà có thể define thêm các role khác tại đây

## Set permission cho các route

Update file `route/api.php`

```php
Route::middleware('auth:api')->group(function() {
    Route::prefix('post')->group(function() {
        Route::get('/', [PostController::class, 'index'])->middleware('can:post.list');  // list post
        Route::post('/', [PostController::class, 'store'])->middleware('can:post.create'); // create post
        Route::get('/{post}', [PostController::class, 'show'])->middleware('can:post.view'); // view post
        Route::post('/{post}', [PostController::class, 'update'])->middleware('can:post.update'); // update post
        Route::delete('/{post}', [PostController::class, 'delete'])->middleware('can:post.delete'); // delete post
    });
});
```

Laravel sẽ tự động check permission của user xem có permission tương ứng với route không trước khi tới Controller.

## Kết hợp với Policy

Trường hợp ngoài yêu cầu check permission còn cần check cả các điều kiện khác (VD như phải là owner)

Trước tiên, tạo Policy cho model `Post` bằng command sau:
```shell
php artisan make:policy PostPolicy --model=Post
```

Chi tiết về Policy đọc thêm [tại đây](https://laravel.com/docs/8.x/authorization#creating-policies)

Tiếp theo update file `app/Policies/PostPolicy.php`

```php
<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Post $post)
    {
        return $user->id == $post->user_id;
    }

    public function delete(User $user, Post $post)
    {
        return $user->id == $post->user_id;
    }
}
```

Cuối cùng update lại `route/api.php`
```php
Route::middleware('auth:api')->group(function() {
    Route::prefix('post')->group(function() {
        Route::get('/', [PostController::class, 'index'])->middleware('can:post.list');  // list post
        Route::post('/', [PostController::class, 'store'])->middleware('can:post.create'); // create post
        Route::get('/{post}', [PostController::class, 'show'])->middleware('can:post.view'); // view post
        Route::post('/{post}', [PostController::class, 'update'])
            ->middleware(['can:post.update', 'can:update,post']); // update post
        Route::delete('/{post}', [PostController::class, 'delete'])
            ->middleware(['can:post.delete', 'can:delete,post']); // delete post
    });
});
```
Lưu ý thay đổi ở đây
```php
# old
->middleware('can:post.delete')

#new
->middleware(['can:post.delete', 'can:delete,post']);
```
Với config mới, laravel sẽ check 2 điều kiện
* User hiện tại có permission `post.delete` không
* Kiểm tra theo logic được khai báo trong `PostPolicy::delete()`

Nếu 1 trong 2 điều kiện này không thỏa mãn thì sẽ tự động response với status code 403.
## Permission Seeding

Run command sau để seed permission vào database

```shell
php artisan db:seed --class=PermissionSeeder
```
