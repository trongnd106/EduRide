<?php

namespace App\Exceptions;

use App\Exceptions\Helpers\ExceptionHandlerHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\Exceptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return Response
     * @throws Exceptions\ArrayWithMixedKeysException
     * @throws Exceptions\ConfigurationNotFoundException
     * @throws Exceptions\IncompatibleTypeException
     * @throws Exceptions\InvalidTypeException
     * @throws Exceptions\MissingConfigurationKeyException
     * @throws Exceptions\NotIntegerException
     */
    public function render($request, Throwable $e)
    {
        $result = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ];
        if ($e instanceof HttpResponseException) {
            return response()->json($result, Response::HTTP_BAD_REQUEST);
        }
        if ($e instanceof ModelNotFoundException) {
            $result['message'] = __('api.exception.not_found');
            $status = Response::HTTP_NOT_FOUND;
            $method = $request->method();
            if ($method === SymfonyRequest::METHOD_POST || $method === SymfonyRequest::METHOD_DELETE) {
                $status = Response::HTTP_BAD_REQUEST;
            }
            return response()->json($result, $status);
        }
        return ExceptionHandlerHelper::render($request, $e);
    }
}
