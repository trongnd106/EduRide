# Logging

Base Laravel này đã implement sẵn cơ chế custom logging, làm theo hướng dẫn sau để sử dụng

## Cài đặt

Trong file `config/logging.php`, mục `channels` đã định nghĩa sẵn channel `general`

```php
        // Custom logging channel
        'general' => [
            'driver' => 'monolog',
            'handler' => \App\Logging\CustomizeRotatingFileHandler::class,
            'tap' => [App\Logging\CustomizeFormatter::class],
            'with' => [
                'filename' => (empty(env('LOG_DIR', '')) ? storage_path('logs') : env('LOG_DIR')) . '/general.log',
                'maxFiles' => 14
            ],
        ],
```

Có thể sử dụng channel này hoặc tùy biến thêm channel mới tùy vào mục đích sử dụng

## Sử dụng Channel Log General

### Cài đặt nơi lưu log

Chỉnh sửa biến `LOG_DIR` trong file .env

### Thay đổi format log:

Channel này hiện đang được lưu log theo format JSON

```
{"timestamp":"2021-09-25T14:29:01Z","message":"", [...context]}
```

`context` chính là tham số được truyền thêm khi gọi hàm log

```
Log::info($message, $context);
```

Để thay đổi format, sửa class `App\Logging\CustomizeLineFormatter.php` hàm `format`

```php
    public function format(array $record): string
    {
        $vars = NormalizerFormatter::format($record);
        $output = array_merge([
            'timestamp' => $vars['datetime'],
            'message' => $vars['message']
        ], $vars['context']);
        return json_encode($output) . "\n";
    }
```

### Thay đổi tên File Log

Mặc dù tên file trong config đang là `general.log`, tuy nhiên đó chỉ là placeholder, hiện đang custom lại tên file trong class `App\Logging\CustomizeFormatter.php`.
Có thể sửa lại tên file theo mục đích (hoặc bỏ đoạn code này đi để sử dụng tên file trong config)

```php
$handler->setFilenameFormat('{date}-' . config('app.env') . '-' . gethostname(), "Y-m-d");
```
