<?php

namespace App\Http\Controllers;

use App\Services\SchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SchoolController extends Controller
{
    public function __construct(SchoolService $service)
    {
        parent::__construct($service);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/schools/{id}",
     *     summary="Get school by ID",
     *     description="Retrieves detailed information about a school by their ID",
     *     operationId="getSchoolById",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the school",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội"),
     *             @OA\Property(property="code", type="string", example="HUST"),
     *             @OA\Property(property="phone", type="string", example="024-38692008"),
     *             @OA\Property(property="information", type="string", example="Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam"),
     *             @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="School not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $school = $this->service->show($id);
        return $this->respond($school);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/schools",
     *     summary="Get a paginated list of schools",
     *     description="Retrieves a paginated list of schools with optional filtering",
     *     operationId="getSchoolsList",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="name__like",
     *         in="query",
     *         description="Filter by school name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Bách Khoa")
     *     ),
     *     @OA\Parameter(
     *         name="code__equal",
     *         in="query",
     *         description="Filter by school code (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="HUST")
     *     ),
     *     @OA\Parameter(
     *         name="phone__like",
     *         in="query",
     *         description="Filter by phone number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="024")
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Không hoạt động, 1 = Đang hoạt động)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="address__like",
     *         in="query",
     *         description="Filter by address (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Hà Nội")
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
     *                     @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội"),
     *                     @OA\Property(property="code", type="string", example="HUST"),
     *                     @OA\Property(property="phone", type="string", example="024-38692008"),
     *                     @OA\Property(property="information", type="string", example="Trường đại học kỹ thuật hàng đầu"),
     *                     @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *                     @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội")
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
     *     path="/api/v1/schools",
     *     summary="Create a new school",
     *     description="Creates a new school with the provided information",
     *     operationId="createSchool",
     *     tags={"Schools"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội", description="Tên trường (bắt buộc)"),
     *             @OA\Property(property="code", type="string", example="HUST", description="Mã trường (optional)"),
     *             @OA\Property(property="phone", type="string", example="024-38692008", description="Số điện thoại (optional)"),
     *             @OA\Property(property="information", type="string", example="Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam", description="Thông tin mô tả (optional)"),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động, optional, default: 0)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="School created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội"),
     *             @OA\Property(property="code", type="string", example="HUST"),
     *             @OA\Property(property="phone", type="string", example="024-38692008"),
     *             @OA\Property(property="information", type="string", example="Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam"),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
     *     path="/api/v1/schools/{id}",
     *     summary="Update a school",
     *     description="Updates an existing school's information by ID",
     *     operationId="updateSchool",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the school to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội", description="Tên trường"),
     *             @OA\Property(property="code", type="string", example="HUST", description="Mã trường (optional)"),
     *             @OA\Property(property="phone", type="string", example="024-38692008", description="Số điện thoại (optional)"),
     *             @OA\Property(property="information", type="string", example="Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam", description="Thông tin mô tả (optional)"),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="School updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Đại học Bách Khoa Hà Nội"),
     *             @OA\Property(property="code", type="string", example="HUST"),
     *             @OA\Property(property="phone", type="string", example="024-38692008"),
     *             @OA\Property(property="information", type="string", example="Trường Đại học Bách Khoa Hà Nội là trường đại học kỹ thuật đầu tiên và lớn nhất Việt Nam"),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-07T19:30:56.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="School not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
     *     path="/api/v1/schools/{id}",
     *     summary="Delete a school",
     *     description="Deletes a school by ID (soft delete)",
     *     operationId="deleteSchool",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the school to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="School deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="School not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="School not found")
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
