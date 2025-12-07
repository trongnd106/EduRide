# Routing

## 1. Mô tả

Hiện tại, cấu trúc routing của Base có thể được tìm thấy tại file `app/Providers/RouteServiceProvider.php`

```php
$this->routes(function () {
    if (config('app.env') === 'local' || config('app.env')  === 'testing') {
        Route::domain(env('FRONTEND_LOCAL_DOMAIN'))
            ->middleware('api')
            ->namespace('App\\Http\\Controllers\\App')
            ->group(base_path('routes/api.php'));

        Route::domain(env('BACKEND_LOCAL_DOMAIN'))
            ->middleware('api_admin')
            ->namespace('App\\Http\\Controllers\\Admin')
            ->group(base_path('routes/api_admin.php'));
    } elseif (config('app.domain') === 'frontend') {
        Route::middleware('api')
            ->namespace('App\\Http\\Controllers\\App')
            ->group(base_path('routes/api.php'));
    } elseif (config('app.domain') === 'backend') {
        Route::middleware('api_admin')
            ->namespace('App\\Http\\Controllers\\Admin')
            ->group(base_path('routes/api_admin.php'));
    } else {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
});
```

* Nếu môi trường là `local` và `testing` thì sẽ route theo 2 domain được setting trong `.env` là 
`FRONTEND_LOCAL_DOMAIN` và `BACKEND_LOCAL_DOMAIN`
* Nếu là môi trường `production` hay `staging` thì sẽ chia theo biến env `APP_DOMAIN`
    * `frontend`: sẽ route tới file `routes/api.php`
    * `backend`: sẽ route tới file `routes/api_admin.php`
