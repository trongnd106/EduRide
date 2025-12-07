<?php


namespace App\Constants;


use MarcinOrlowski\ResponseBuilder\ApiCodesHelpers;

class ApiCodes
{
    use ApiCodesHelpers;

    public const UNCAUGHT_EXCEPTION = 200;
    public const HTTP_NOT_FOUND = 201;
    public const HTTP_SERVICE_UNAVAILABLE = 202;
    public const HTTP_EXCEPTION = 203;
    public const UNAUTHORIZED_EXCEPTION = 204;
    public const VALIDATION_EXCEPTION = 205;
    public const UNAUTHENTICATED_EXCEPTION = 206;

    public const READABLE_CODE_MAP = [
        'default' => 'UnknownException',
        self::UNCAUGHT_EXCEPTION => 'UncaughtException',
        self::HTTP_NOT_FOUND => 'NotFoundException',
        self::HTTP_SERVICE_UNAVAILABLE => 'ServiceUnavailable',
        self::HTTP_EXCEPTION => 'HttpException',
        self::UNAUTHORIZED_EXCEPTION => 'UnauthorizedException',
        self::UNAUTHENTICATED_EXCEPTION => 'UnauthenticatedException',
        self::VALIDATION_EXCEPTION => 'InvalidParametersException',
    ];

    public static function convertToReadable(int $code): string
    {
        return self::READABLE_CODE_MAP[$code] ?? self::READABLE_CODE_MAP['default'];
    }
}
