<?php

namespace App\Http\Controllers;

use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DriverController extends Controller
{
    public function __construct(DriverService $service)
    {
        parent::__construct($service);
    }

    /**
     * @OA\Get(
     *     path="drivers/{id}",
     *     summary="Get driver by ID",
     *     description="Retrieves detailed information about a driver by their ID",
     *     operationId="getDriverById",
     *     tags={"Drivers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the driver",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="cccd", type="string", example="001234567890"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *             @OA\Property(property="gender", type="integer", description="1 = Nam, 0 = Nữ", example=1),
     *             @OA\Property(property="license_number", type="string", example="A1234567"),
     *             @OA\Property(property="age", type="integer", example=39),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/drivers/1.jpg"),
     *             @OA\Property(property="school_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *             @OA\Property(property="position", type="integer", description="1 = Tài xế, 2 = Phụ xe", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $driver = $this->service->show($id);
        return $this->respond($driver);
    }

    /**
     * @OA\Get(
     *     path="drivers",
     *     summary="Get a paginated list of drivers",
     *     description="Retrieves a paginated list of drivers with optional filtering",
     *     operationId="getDriversList",
     *     tags={"Drivers"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="full_name__like",
     *         in="query",
     *         description="Filter by full name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Nguyễn")
     *     ),
     *     @OA\Parameter(
     *         name="cccd__equal",
     *         in="query",
     *         description="Filter by CCCD (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="001234567890")
     *     ),
     *     @OA\Parameter(
     *         name="phone__like",
     *         in="query",
     *         description="Filter by phone number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="0987")
     *     ),
     *     @OA\Parameter(
     *         name="gender__equal",
     *         in="query",
     *         description="Filter by gender (1 = Nam, 0 = Nữ)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="license_number__like",
     *         in="query",
     *         description="Filter by license number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="A123")
     *     ),
     *     @OA\Parameter(
     *         name="school_id__equal",
     *         in="query",
     *         description="Filter by school ID (exact match)",
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
     *     @OA\Parameter(
     *         name="position__equal",
     *         in="query",
     *         description="Filter by position (1 = Tài xế, 2 = Phụ xe)",
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
     *                     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="cccd", type="string", example="001234567890"),
     *                     @OA\Property(property="phone", type="string", example="0987654321"),
     *                     @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *                     @OA\Property(property="gender", type="integer", description="1 = Nam, 0 = Nữ", example=1),
     *                     @OA\Property(property="license_number", type="string", example="A1234567"),
     *                     @OA\Property(property="age", type="integer", example=39),
     *                     @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *                     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/drivers/1.jpg"),
     *                     @OA\Property(property="school_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *                     @OA\Property(property="position", type="integer", description="1 = Tài xế, 2 = Phụ xe", example=1)
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
     *     path="drivers",
     *     summary="Create a new driver",
     *     description="Creates a new driver with the provided information",
     *     operationId="createDriver",
     *     tags={"Drivers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"full_name", "cccd", "gender", "license_number"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A", description="Họ và tên đầy đủ (bắt buộc)"),
     *             @OA\Property(property="cccd", type="string", example="001234567890", description="Số CCCD/CMND (bắt buộc, unique)"),
     *             @OA\Property(property="phone", type="string", example="0987654321", description="Số điện thoại (optional)"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com", description="Email (optional)"),
     *             @OA\Property(property="gender", type="integer", example=1, description="Giới tính (1 = Nam, 0 = Nữ, bắt buộc)"),
     *             @OA\Property(property="license_number", type="string", example="A1234567", description="Số bằng lái xe (bắt buộc)"),
     *             @OA\Property(property="age", type="integer", example=39, description="Tuổi (optional)"),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/images/drivers/1.jpg", description="URL ảnh đại diện (optional)"),
     *             @OA\Property(property="school_id", type="integer", example=1, description="ID trường học (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động, optional, default: 0)"),
     *             @OA\Property(property="position", type="integer", example=1, description="Chức vụ (1 = Tài xế, 2 = Phụ xe, optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="cccd", type="string", example="001234567890"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="gender", type="integer", example=1),
     *             @OA\Property(property="license_number", type="string", example="A1234567"),
     *             @OA\Property(property="age", type="integer", example=39),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/drivers/1.jpg"),
     *             @OA\Property(property="school_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="position", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="cccd", type="array",
     *                     @OA\Items(type="string", example="The cccd has already been taken.")
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
     *     path="drivers/{id}",
     *     summary="Update a driver",
     *     description="Updates an existing driver's information by ID",
     *     operationId="updateDriver",
     *     tags={"Drivers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the driver to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A", description="Họ và tên đầy đủ"),
     *             @OA\Property(property="cccd", type="string", example="001234567890", description="Số CCCD/CMND (unique)"),
     *             @OA\Property(property="phone", type="string", example="0987654321", description="Số điện thoại (optional)"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com", description="Email (optional)"),
     *             @OA\Property(property="gender", type="integer", example=1, description="Giới tính (1 = Nam, 0 = Nữ)"),
     *             @OA\Property(property="license_number", type="string", example="A1234567", description="Số bằng lái xe"),
     *             @OA\Property(property="age", type="integer", example=39, description="Tuổi (optional)"),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/images/drivers/1.jpg", description="URL ảnh đại diện (optional)"),
     *             @OA\Property(property="school_id", type="integer", example=1, description="ID trường học (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động)"),
     *             @OA\Property(property="position", type="integer", example=1, description="Chức vụ (1 = Tài xế, 2 = Phụ xe, optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="cccd", type="string", example="001234567890"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *             @OA\Property(property="gender", type="integer", example=1),
     *             @OA\Property(property="license_number", type="string", example="A1234567"),
     *             @OA\Property(property="age", type="integer", example=39),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/drivers/1.jpg"),
     *             @OA\Property(property="school_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="position", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T22:48:42.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="cccd", type="array",
     *                     @OA\Items(type="string", example="The cccd has already been taken.")
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
     *     path="drivers/{id}",
     *     summary="Delete a driver",
     *     description="Deletes a driver by ID (soft delete)",
     *     operationId="deleteDriver",
     *     tags={"Drivers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the driver to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Driver not found")
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
