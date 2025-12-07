## Config

Base sử dụng Laravel Passport để authen API, xem thêm doc cụ thể về cách config tại đây: https://laravel.com/docs/8.x/passport

Một số lưu ý:
- Cần xác định rõ số lượng Model sẽ dùng để authen, thêm các interface cần thiết cho những Model này
- Config ở trong **auth.php**
- Nếu sử dụng nhiều hơn 1 model cần authen, sau khi chạy command `php artisan passport:install`, cần vào trong DB kiểm tra lại trong bảng `oauth_clients`, cột `provider` xem có đủ các driver được config trong file **auth.php** chưa, nếu chưa thì cần chạy command sau:

```
php artisan passport:client --password --provider provider-name
```

## Code

Sử dụng `App\Services\AuthService` để authen, có thể sửa lại theo ý muốn.
