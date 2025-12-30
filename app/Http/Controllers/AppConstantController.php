<?php

namespace App\Http\Controllers;
use App\Http\ResponseBuilder\ResponseBuilder;
use App\Services\AppConstantService;
use Symfony\Component\HttpFoundation\Response;

class AppConstantController
{
    /**
     * @OA\Get(
     *     path="/api/v1/app-constants",
     *     summary="Get application constants",
     *     tags={"App Constants"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="key", type="string"),
     *                     @OA\Property(property="value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function appConstants() : Response
    {
        $service = new AppConstantService();
        $methods = get_class_methods($service);
        $constants = [];
        foreach ($methods as $method) {
            $constants = array_merge($constants, $service->$method());
        }
        return ResponseBuilder::asSuccess()->withData($constants)->build();
    }
}
