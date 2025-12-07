Thông tin chi tiết về [Model trong Laravel](https://laravel.com/docs/8.x/eloquent)

# Tóm tắt một số field hay dùng có thể thêm vào trong 1 Class Model

## Fillable và Guard

Trong một số trường hợp, khi muốn insert 1 record vào database, thay vì phải set từng field một sử dụng Eloquent Builder của Laravel, ta có thể sử dụng một mảng các cột theo format `'column' => 'value'` (Mass Assignment), ta cần thêm tên của những cột này vào trong field `$fillable`

```php
class Flight extends Model
{
    protected $fillable = ['name'];
}
```

Sau đó, ta có thể sử dụng cú pháp `create` để insert

```php
use App\Models\Flight;

$flight = Flight::create([
    'name' => 'London to Paris',
]);
```

Trong trường hợp ta muốn tất cả các cột có thể mass assign, ta có thể bỏ field `$fillable`, và sử dụng field `$guarded` để khai báo những cột không thể mass assign (hoặc để trống nếu muốn tất cả các cột đều có thể mass assign)

```php
protected $guarded = [];
```

## Giấu thông tin khi Select Record

Một số trường hợp khi select model, ta muốn giấu một số cột (`password`, `token`,...), thay vì phải sử dụng `select` mỗi lần query để chọn cột, ta có thể sử dụng đến field `$hidden`. Các cột có trong field này sẽ tự động được loại bỏ khỏi kết quả trả về khi select record

```php
class User extends Model
{
    protected $hidden = ['password', 'token'];
}
```

## Tên Table

Một Model được gắn với 1 Table trong Database, sử dụng field này để Map giữa Model với Table

- Lưu ý: phải viết ở dạng snake case

```php
class Flight extends Model
{
    protected $table = 'my_flights';
}
```

## Khóa chính

- Mặc định Laravel sẽ hiểu cột `id` là khóa chính của mọi Model, nếu cần overwrite lại thành cột khác thì cần thêm field này

```php
class Flight extends Model
{
    protected $primaryKey = 'flight_id';
}
```

- Mặc định Laravel sẽ coi khóa chính là dạng **auto increment**, đồng nghĩa với việc sẽ tự động ép kiểu trường này thành dạng `integer` khi lấy data. Nếu khóa chính không phải auto increment thì cần set field `$incrementing` thành `false`

```php
class Flight extends Model
{
    public $incrementing = false;
}
```

- Trong trường hợp khóa chính không phải dạng `integer` thì cũng cần khai báo kiểu của khóa chính

```php
class Flight extends Model
{
    protected $keyType = 'string';
}
```

## Timestamps

- Laravel mặc định là mọi bảng đều có cột `created_at` và `updated_at`, và đồng thời cũng tự động update 2 cột này khi tạo hoặc update record. Nếu không muốn Laravel tự động update cột này nữa thì cần set field `$timestamps` thành `false`

```php
class Flight extends Model
{
    public $timestamps = false;
}
```

- Nếu cần thay đổi format của các trường date thì cần thêm field `$dateFormat`

```php
class Flight extends Model
{
    protected $dateFormat = 'U';
}
```

- Nếu không muốn sử dụng tên cột là `created_at` và `updated_at` thì cần khai báo 2 constant `CREATED_AT` và `UPDATED_AT` với tên cột mới tương ứng

```php
class Flight extends Model
{
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'updated_date';
}
```

## Thay đổi connection cho Database

Trong trường hợp sử dụng nhiều Database cho dự án thì mỗi Model sẽ cần khai báo cụ thể nó nằm trong Database nào. Mỗi Database sẽ được config connection trong file config, sau đó, sử dụng tên của các connection này và khai báo trong field `$connection` của từng Model

```php
class Flight extends Model
{
    protected $connection = 'sqlite';
}
```

## Giá trị mặc định

Nếu muốn cột nào đó có giá trị mặc định khi 1 record được insert vào DB thì cần khai báo thêm field `$attributes`

```php
class Flight extends Model
{
    protected $attributes = [
        'delayed' => false,
    ];
}
```

## Relations

Thông tin cụ thể: [Relations](https://laravel.com/docs/8.x/eloquent-relationships#one-to-one-polymorphic-relations)

### Quan hệ 1-1

#### Có 1

- Khai báo **một Model có một Model khác**

```php
class User extends Model
{
    public function phone()
    {
        // Mặc định:
        // * Format khóa ngoại '[child_table_name]_id'
        // * Format khóa chính ở model con 'id'
        return $this->hasOne(Phone::class); // Khóa ngoại: phone_id
                                            // Khóa chính: id
        
        // Hoặc nếu muốn sử dụng khóa ngoại, khóa chính khác
        return $this->hasOne(Phone::class, 'foreign_key', 'local_key');
    }
}
```

- Sử dụng

```php
$phone = User::find(1)->phone;
```

#### Thuộc về

- Khai báo **một Model thuộc về Model khác**

```php
class Phone extends Model
{
    public function user()
    {
        // Mặc định:
        // * Format khóa ngoại '[parent_table_name]_id'
        // * Format khóa chính ở model cha 'id'
        return $this->belongsTo(User::class); // Khóa ngoại: user_id
                                              // Khóa chính: id
        
        // Hoặc nếu muốn sử dụng khóa ngoại, khóa chính khác
        return $this->belongsTo(User::class, 'foreign_key', 'owner_key');
    }
}
```

### Quan hệ 1-nhiều

#### Có nhiều

- Khai báo **một Model có nhiều Model khác**

```php
class Post extends Model
{
    public function comments()
    {
        // Mặc định:
        // * Format khóa ngoại '[child_table_name]_id'
        // * Format khóa chính ở model con 'id'
        return $this->hasMany(Comment::class); // Khóa ngoại: post_id
                                               // Khóa chính: id
         
        // Hoặc nếu muốn sử dụng khóa ngoại, khóa chính khác                             
        return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
    }
}
```

- Sử dụng

```php
$comment = Post::find(1)->comments()
                    ->where('title', 'foo')
                    ->first();
```

#### Thuộc về

- Khai báo **một Model thuộc về Model khác**

```php
class Comment extends Model
{
    public function post()
    {
        // Mặc định:
        // * Format khóa ngoại '[parent_table_name]_id'
        // * Format khóa chính ở model cha 'id'
        return $this->belongsTo(Post::class); // Khóa ngoại: post_id
                                              // Khóa chính: id
                                              
        // Hoặc nếu muốn sử dụng khóa ngoại, khóa chính khác                             
        return $this->hasMany(Comment::class, 'foreign_key', 'owner_key');
    }
}
```

- Sử dụng

```php
use App\Models\Comment;

$comment = Comment::find(1);

return $comment->post->title;
```

### Quan hệ nhiều-nhiều

Cấu trúc

```
users
    id - integer
    name - string

roles
    id - integer
    name - string

role_user             // Bảng trung gian
    user_id - integer
    role_id - integer
```

#### Thuộc về nhiều

- Khai báo **một Model thuộc về nhiều Model khác** (Áp dụng cho cả 2 phía)

```php
class User extends Model
{
    public function roles()
    {
        // Mặc định:
        // * Format tên table trung gian '[current_table_name]_[relation_table_name]'
        // * Format khóa trung gian cho model hiện tại '[current_table_name]_id'
        // * Format khóa trung gian cho model quan hệ '[relation_table_name]_id'
        return $this->belongsToMany(Role::class); // Table trung gian: role_user
                                                  // Khóa ngoại: post_id
                                                  // Khóa chính: id
        
        // Hoặc nếu muốn sử dụng tên table trung gian khác, khóa khác
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id'); 
    }
}
```

- Sử dụng

```php
use App\Models\User;

$user = User::find(1);

foreach ($user->roles as $role) {
    //
}

$roles = User::find(1)->roles()->orderBy('name')->get();
```

#### Query bảng trung gian

Trong một số trường hợp, ta lưu thêm thông tin vào bảng trung gian (ngày tạo relation,...) và muốn query thông tin này

```php
use App\Models\User;

$user = User::find(1);

foreach ($user->roles as $role) {
    echo $role->pivot->created_at;
}
```

Nếu bảng trung gian có thêm những thuộc tính ngoài các khóa, để query được, ta cần khai báo thêm chúng khi ta khai báo relation ở model

```php
return $this->belongsToMany(Role::class)->withPivot('active', 'created_by');
```

Nếu muốn table trung gian tự động có thêm timestamp khi insert, ta gọi tới function `withTimestamps()` khi định nghĩa relations

```php
return $this->belongsToMany(Role::class)->withTimestamps();
```

## Event

- Khi sử dụng Eloquent để tương tác với Database, những event của vòng đời Model được emit cho phép ta có thể hook vào:
  - `retrieved`: Khi model được query từ Database ra
  - `creating` và `created`: Khi model được lưu lần đầu tiên (tạo mới)
  - `updating` và `updated`: Khi một model được thay đổi và method `save` được sử dụng
  - `saving` và `saved`: Khi model được tạo mới hay update (ngay cả khi thuộc tính không thay đổi)
  - `deleting`, `deleted`, `restoring`, `restored`, `replicating`, `forceDeleted`,...
  - Event có đuôi `ing` được emit khi bắt đầu thay đổi và `ed` được emit khi thay đổi xong
  

- Để sử dụng event, thêm thuộc tính `$dispatchesEvents` vào Model, sau đó map những event muốn hook với các class [Event](https://laravel.com/docs/8.x/events)

```php
class User extends Authenticatable
{
    use Notifiable;

    protected $dispatchesEvents = [
        'saved' => UserSaved::class,
        'deleted' => UserDeleted::class,
    ];
}
```
