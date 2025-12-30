<?php

namespace App\Http\Controllers;

use App\Services\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VehicleController extends Controller
{
    public function __construct(VehicleService $service)
    {
        parent::__construct($service);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles/{id}",
     *     summary="Get vehicle by ID",
     *     description="Retrieves detailed information about a vehicle by their ID",
     *     operationId="getVehicleById",
     *     tags={"Vehicles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the vehicle",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="type", type="integer", example=1, description="Loại xe"),
     *             @OA\Property(property="plate_number", type="string", example="30A-12345"),
     *             @OA\Property(property="capacity", type="integer", example=16),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="brand", type="string", example="Ford Transit"),
     *             @OA\Property(property="model", type="string", example="Transit 350"),
     *             @OA\Property(property="color", type="string", example="Trắng"),
     *             @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $vehicle = $this->service->show($id);
        return $this->respond($vehicle);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicles",
     *     summary="Get a paginated list of vehicles",
     *     description="Retrieves a paginated list of vehicles with optional filtering",
     *     operationId="getVehiclesList",
     *     tags={"Vehicles"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="plate_number__like",
     *         in="query",
     *         description="Filter by plate number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="30A")
     *     ),
     *     @OA\Parameter(
     *         name="plate_number__equal",
     *         in="query",
     *         description="Filter by plate number (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="30A-12345")
     *     ),
     *     @OA\Parameter(
     *         name="capacity__equal",
     *         in="query",
     *         description="Filter by capacity (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=16)
     *     ),
     *     @OA\Parameter(
     *         name="year__equal",
     *         in="query",
     *         description="Filter by year (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=2020)
     *     ),
     *     @OA\Parameter(
     *         name="brand__like",
     *         in="query",
     *         description="Filter by brand (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Ford")
     *     ),
     *     @OA\Parameter(
     *         name="type__equal",
     *         in="query",
     *         description="Filter by type (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Không hoạt động, 1 = Đang hoạt động)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="integer", example=1),
     *                     @OA\Property(property="plate_number", type="string", example="30A-12345"),
     *                     @OA\Property(property="capacity", type="integer", example=16),
     *                     @OA\Property(property="year", type="integer", example=2020),
     *                     @OA\Property(property="brand", type="string", example="Ford Transit"),
     *                     @OA\Property(property="model", type="string", example="Transit 350"),
     *                     @OA\Property(property="color", type="string", example="Trắng"),
     *                     @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="total", type="integer", example=25, description="Total number of records"),
     *             @OA\Property(property="last_page", type="integer", example=3, description="Last page number"),
     *             @OA\Property(property="from", type="integer", example=1, description="Starting record index"),
     *             @OA\Property(property="to", type="integer", example=10, description="Ending record index"),
     *             @OA\Property(property="current_page", type="integer", example=1, description="Current page number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request): Response
    {
        $result = $this->service->paginate($request->all());
        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicles",
     *     summary="Create a new vehicle",
     *     description="Creates a new vehicle with the provided information",
     *     operationId="createVehicle",
     *     tags={"Vehicles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"plate_number", "capacity"},
     *             @OA\Property(property="type", type="integer", example=1, description="Loại xe (optional)"),
     *             @OA\Property(property="plate_number", type="string", example="30A-12345", description="Biển số xe (bắt buộc, unique)"),
     *             @OA\Property(property="capacity", type="integer", example=16, description="Sức chứa (số chỗ ngồi, bắt buộc)"),
     *             @OA\Property(property="year", type="integer", example=2020, description="Năm sản xuất (optional)"),
     *             @OA\Property(property="brand", type="string", example="Ford Transit", description="Hãng xe (optional)"),
     *             @OA\Property(property="model", type="string", example="Transit 350", description="Model xe (optional)"),
     *             @OA\Property(property="color", type="string", example="Trắng", description="Màu sắc (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động, optional, default: 0)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="type", type="integer", example=1),
     *             @OA\Property(property="plate_number", type="string", example="30A-12345"),
     *             @OA\Property(property="capacity", type="integer", example=16),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="brand", type="string", example="Ford Transit"),
     *             @OA\Property(property="model", type="string", example="Transit 350"),
     *             @OA\Property(property="color", type="string", example="Trắng"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="plate_number", type="array",
     *                     @OA\Items(type="string", example="The plate number has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function store(Request $request): Response
    {
        $attributes = $request->all();
        return DB::transaction(function () use ($attributes) {
            return $this->respond($this->service->store($attributes));
        }, 3);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vehicles/{id}",
     *     summary="Update a vehicle",
     *     description="Updates an existing vehicle's information by ID",
     *     operationId="updateVehicle",
     *     tags={"Vehicles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the vehicle to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", type="integer", example=1, description="Loại xe (optional)"),
     *             @OA\Property(property="plate_number", type="string", example="30A-12345", description="Biển số xe (unique)"),
     *             @OA\Property(property="capacity", type="integer", example=16, description="Sức chứa (số chỗ ngồi)"),
     *             @OA\Property(property="year", type="integer", example=2020, description="Năm sản xuất (optional)"),
     *             @OA\Property(property="brand", type="string", example="Ford Transit", description="Hãng xe (optional)"),
     *             @OA\Property(property="model", type="string", example="Transit 350", description="Model xe (optional)"),
     *             @OA\Property(property="color", type="string", example="Trắng", description="Màu sắc (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="type", type="integer", example=1),
     *             @OA\Property(property="plate_number", type="string", example="30A-12345"),
     *             @OA\Property(property="capacity", type="integer", example=16),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="brand", type="string", example="Ford Transit"),
     *             @OA\Property(property="model", type="string", example="Transit 350"),
     *             @OA\Property(property="color", type="string", example="Trắng"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:57.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="plate_number", type="array",
     *                     @OA\Items(type="string", example="The plate number has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function update(Request $request, $id): Response
    {
        $attributes = $request->all();
        $id = intval($id);
        return DB::transaction(function () use ($attributes, $id) {
            return $this->respond($this->service->update($id, $attributes));
        });
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vehicles/{id}",
     *     summary="Delete a vehicle",
     *     description="Deletes a vehicle by ID (soft delete)",
     *     operationId="deleteVehicle",
     *     tags={"Vehicles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the vehicle to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vehicle not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function destroy($id): Response
    {
        $id = intval($id);
        return DB::transaction(function () use ($id) {
            return $this->service->destroy($id);
        });
    }
}
