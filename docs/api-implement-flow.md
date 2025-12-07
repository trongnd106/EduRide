# Flow khi thêm api mới
Checkout source code sang branch `demo` để tham khảo

## 1. Update controller
Controller của backend và admin được đặt tại 2 folder khác nhau.

Backend path: `app\Http\Controllers\App`<br/>
Admin path: `app\Http\Controllers\Admin`

Có thể tạo bằng tay hoặc dùng command
```shell
# Create backend controller
php artisan make:controller "App\Http\Controllers\App\PostController"

# Create admin controller
php artisan make:controller "App\Http\Controllers\Admin\PostController"
```

Tiếp đến khai báo action.

```php
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        //...
    }
}
```

## 2. Khai báo request
Với các action xử lý post như create và update cần phải validate data, cần khai báo request và các validate rule bên trong nó.

Backend path: `app\Http\Requests\App`<br>
Admin path: `app\Http\Requests\Admin`

```php
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ];
    }
}
```

Trong controller, khai báo action như sau

```php
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        
    }
}
```
Laravel sẽ tự động thực hiện validate data theo rule khai báo trong request và response error nếu data không hợp lệ.<br>
Xem chi tiết [tại đây](https://laravel.com/docs/8.x/validation#form-request-validation)

## 3. Khai báo service
Main logic được khai báo trong các class service đặt tại `app\Services`. <br>
Dùng cơ chế Dependency Injection để inject các class service vào trong controller.

VD với case xử lý login, khai báo class AuthService
```php
class AuthService
{
    protected $oClient;

    public function __construct()
    {
        $this->_retrieveClients();
    }
    
    private function _retrieveClients()
    {
        $clients = OClient::where('password_client', 1)->get();
        $config = config('auth.providers');

        foreach ($clients as $client) {
            if (isset($client['provider']) && isset($config[$client['provider']])) {
                $this->oClient[$config[$client['provider']]['model']] = $client;
            }
        }
    }

    public function generateToken(string $modelNamespace, $username, $password)
    {
        $oClient = $this->_getClient($modelNamespace);
        if (!$oClient) return null;

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => (string)$oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $username,
            'password' => $password,
            'scope' => '*',
        ]);
        $response = app()->handle($request);
        if ($response->getStatusCode() === HttpResponse::HTTP_OK) {
            return json_decode((string) $response->getContent(), true);
        }
        return null;
    }
}
```

Tiếp đến trong AuthController khai báo service là một thuộc tính của controller.<br>
Trong constructor của controller, khai báo param đầu vào là service instance, số lượng param tùy thuộc vào số service mà controller phụ thuộc vào.
```php
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    public function login(LoginRequest $request)
    {
        $data = $request->only('username', 'password');

        $result = $this->authService->generateToken(Admin::class, $data['username'], $data['password']);
        if (!$result) {
            throw new UnauthorizedHttpException(__('api.exception.invalid_credentials'));
        }

        return $this->respond($result);
    }
}
```

## 4. Khai báo permission
Khai báo permission tương ứng action trong class `App\Constants\Permission`

```php
class Permission
{
    const POST_LIST = 'post.list';
    const POST_CREATE = 'post.create';
    const POST_VIEW = 'post.view';
    const POST_EDIT = 'post.edit';
    const POST_DELETE = 'post.delete';

    public static function getAdminPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_CREATE,
            static::POST_VIEW,
            static::POST_EDIT,
            static::POST_DELETE,
        ];
    }

    public static function getStaffPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_VIEW,
        ];
    }

    /**
     * For permissions seeding
     *
     * @return array
     */
    public static function getAllPermissions()
    {
        return [
            static::POST_LIST,
            static::POST_CREATE,
            static::POST_VIEW,
            static::POST_EDIT,
            static::POST_DELETE,
        ];
    }
}
```
Khi khai báo permission constant, update function `getAllPermissions()` return permission mới được khai báo để phục vụ cho seeding database.<br>

Tùy thuộc vào role nào có quyền thực hiện action đó mà add permission vào các function `getAdminPermissions()` và `getStaffPermissions()` (hoặc các function của các role khác).

Sau khi khai báo xong, run command sau để seed permission vào database
```shell
php artisan db:seed --class=PermissionSeeder
```

## 5. Khai báo route
Route được khai báo trong 2 file

Backend path: `routes\api.php`<br>
Admin path: `routes\api_admin.php`

```php
Route::middleware('auth:api_admin')->group(function() {
    Route::post('sotre', [PostController::class, 'create'])
        ->middleware('can:'.\App\Constants\Permission::POST_CREATE);
});
```

Với các action cần giới hạn permission, khai báo middleware với key `can:{permission}`.

Chi tiết về permission và route đọc thêm [tại đây](permission.md)
