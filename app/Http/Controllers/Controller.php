<?php

namespace App\Http\Controllers;

use App\Constants\ApiCodes;
use App\Http\ResponseBuilder\ResponseBuilder;
use App\Services\BaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *     version="1.0",
 *     title="HustEduRide"
 * )
 */
class Controller extends BaseController
{
    protected $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respond($data = null, $msg = null): Response
    {
        return ResponseBuilder::asSuccess()->withData($data)->withMessage($msg)->build();
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respondWithMessage($msg = null): Response
    {
        return ResponseBuilder::asSuccess()->withMessage($msg)->build();
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respondWithError($apiCode, $HttpCode, $message = null, $error = null): Response
    {
        return ResponseBuilder::asError($apiCode)->withHttpCode($HttpCode)->withMessage($message)->withData($error)->build();
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     */
    public function respondBadRequest($apiCode = ApiCodes::UNCAUGHT_EXCEPTION): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     */
    public function respondUnauthorizedRequest($apiCode = ApiCodes::UNAUTHORIZED_EXCEPTION): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     */
    public function respondNotFound($apiCode = ApiCodes::HTTP_NOT_FOUND): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_NOT_FOUND);
    }
}
