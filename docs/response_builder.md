## Config

Sử dụng thư viện Response builder để tạo response, có thể xem thêm ở đây: https://github.com/MarcinOrlowski/laravel-api-response-builder/

File config chính: `response_builder.php`
- File này đã config sẵn các Exception cơ bản để catch và mã lỗi trả về tương ứng trong field `exception_handler`, nếu cần thêm các exception khác thì cũng thêm vào mục này theo format
```php
        Illuminate\Auth\AuthenticationException::class => [  // Class của exception
            'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\DefaultExceptionHandler::class,
            // Handler mặc định của thư viện, có thể extend và custom lại theo ý muốn
            'pri'     => 0,
            'config'  => [
                'api_code'  => ApiCodes::UNAUTHENTICATED_EXCEPTION,
                // Mã lỗi trong class ApiCodes, cần map với READABLE_CODE_MAP nếu muốn trả về API dạng text 
                'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                // Code HTTP trả về
                'msg_key' => 'api.exception.unauthenticated',
                // Key message trong file language
                'msg_enforce' => true
            ],
        ], 
```

## Sửa lại format response

Response Format đang được custom lại trong file `App\Http\ResponseBuilder\ResponseBuilder`

Có thể sửa lại theo ý muốn
