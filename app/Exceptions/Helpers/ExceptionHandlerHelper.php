<?php

namespace App\Exceptions\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper as Helper;
use App\Http\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ExceptionHandlerHelper extends Helper
{
    static function error(Throwable $ex,
                          int $api_code, int $http_code = null, string $error_message = null): HttpResponse
    {
        $ex_http_code = ($ex instanceof HttpException) ? $ex->getStatusCode() : $ex->getCode();
        $http_code = $http_code ?? $ex_http_code;
        $error_message = $error_message ?? '';

        // Check if we now have valid HTTP error code for this case or need to make one up.
        // We cannot throw any exception if codes are invalid because we are in Exception Handler already.
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // Not a valid code, let's try to get the exception status.
            $http_code = $ex_http_code;
        }
        // Can it be considered a valid HTTP error code?
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // We now handle uncaught exception, so we cannot throw another one if there's
            // something wrong with the configuration, so we try to recover and use built-in
            // codes instead.
            // FIXME: We should log this event as (warning or error?)
            $http_code = RB::DEFAULT_HTTP_CODE_ERROR;
        }

        // If we have trace data debugging enabled, let's gather some debug info and add to the response.
        $debug_data = null;
        if (Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $debug_data = [
                Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_KEY, RB::KEY_TRACE) => [
                    RB::KEY_CLASS => \get_class($ex),
                    RB::KEY_FILE  => $ex->getFile(),
                    RB::KEY_LINE  => $ex->getLine(),
                ],
            ];
        }

        // If this is ValidationException, add all the messages from MessageBag to the data node.
        $data = null;
        if ($ex instanceof ValidationException) {
            $data = [RB::KEY_MESSAGES => $ex->validator->errors()->messages()];
        }

        return RB::asError($api_code)
            ->withMessage($error_message)
            ->withHttpCode($http_code)
            ->withData($data)
            ->withDebugData($debug_data)
            ->build();
    }
}
