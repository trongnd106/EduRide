<?php

namespace App\Http\Controllers;

use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PointController extends Controller
{
    public function __construct(PointService $service)
    {
        parent::__construct($service);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/points/{id}",
     *     summary="Get point by ID",
     *     description="Retrieves detailed information about a point by its ID",
     *     operationId="getPointById",
     *     tags={"Points"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the point",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS"),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS"),
     *             @OA\Property(property="type", type="integer", example=0, description="0 = Điểm phụ, 1 = Điểm dừng"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Point not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $point = $this->service->show($id);
        return $this->respond($point);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/points",
     *     summary="Get a paginated list of points",
     *     description="Retrieves a paginated list of points with optional filtering",
     *     operationId="getPointsList",
     *     tags={"Points"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="address__like",
     *         in="query",
     *         description="Filter by address (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Đường Láng")
     *     ),
     *     @OA\Parameter(
     *         name="latitude__equal",
     *         in="query",
     *         description="Filter by latitude (exact match)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=21.028511)
     *     ),
     *     @OA\Parameter(
     *         name="longitude__equal",
     *         in="query",
     *         description="Filter by longitude (exact match)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=105.804817)
     *     ),
     *     @OA\Parameter(
     *         name="type__equal",
     *         in="query",
     *         description="Filter by type (0 = Điểm phụ, 1 = Điểm dừng)",
     *         required=false,
     *         @OA\Schema(type="integer", example=0)
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
     *                     @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=21.028511),
     *                     @OA\Property(property="longitude", type="number", format="float", example=105.804817),
     *                     @OA\Property(property="type", type="integer", example=0, description="0 = Điểm phụ, 1 = Điểm dừng"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(property="total", type="integer", example=50, description="Total number of records"),
     *             @OA\Property(property="last_page", type="integer", example=5, description="Last page number"),
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
     *     path="/api/v1/points",
     *     summary="Create a new point",
     *     description="Creates a new point with the provided information",
     *     operationId="createPoint",
     *     tags={"Points"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"latitude", "longitude"},
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS (bắt buộc)"),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS (bắt buộc)"),
     *             @OA\Property(property="type", type="integer", example=0, description="Loại điểm (0 = Điểm phụ, 1 = Điểm dừng, optional, default: 0)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Point created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817),
     *             @OA\Property(property="type", type="integer", example=0),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="latitude", type="array",
     *                     @OA\Items(type="string", example="The latitude field is required.")
     *                 ),
     *                 @OA\Property(property="longitude", type="array",
     *                     @OA\Items(type="string", example="The longitude field is required.")
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
     *     path="/api/v1/points/{id}",
     *     summary="Update a point",
     *     description="Updates an existing point's information by ID",
     *     operationId="updatePoint",
     *     tags={"Points"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the point to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS (optional)"),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS (optional)"),
     *             @OA\Property(property="type", type="integer", example=1, description="Loại điểm (0 = Điểm phụ, 1 = Điểm dừng, optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Point updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817),
     *             @OA\Property(property="type", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-25T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Point not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
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
     *     path="/api/v1/points/{id}",
     *     summary="Delete a point",
     *     description="Deletes a point by ID",
     *     operationId="deletePoint",
     *     tags={"Points"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the point to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Point deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Point not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Point not found")
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
            return $this->respond($this->service->destroy($id));
        });
    }
}

