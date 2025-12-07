<?php


namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new CustomizeLineFormatter(
                null,
                'Y-m-d\TH:i:s\Z',
                true,
                true
            ));
            if ($handler instanceof RotatingFileHandler) {
                $handler->setFilenameFormat('{date}-' . config('app.env') . '-' . gethostname(), "Y-m-d");
            }
        }
    }
}
