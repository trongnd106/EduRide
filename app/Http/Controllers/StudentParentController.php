<?php

namespace App\Http\Controllers;

use App\Services\StudentParentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StudentParentController extends Controller
{
    public function __construct(StudentParentService $service)
    {
        parent::__construct($service);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student-parents/{id}",
     *     summary="Get student parent by ID",
     *     description="Retrieves detailed information about a student parent by their ID",
     *     operationId="getStudentParentById",
     *     tags={"Student Parents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student parent",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student parent not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $parent = $this->service->show($id);
        return $this->respond($parent);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/student-parents",
     *     summary="Get a paginated list of student parents",
     *     description="Retrieves a paginated list of student parents with optional filtering",
     *     operationId="getStudentParentsList",
     *     tags={"Student Parents"},
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
     *         name="phone_number__like",
     *         in="query",
     *         description="Filter by phone number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="0987")
     *     ),
     *     @OA\Parameter(
     *         name="phone_number__equal",
     *         in="query",
     *         description="Filter by phone number (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="0987654321")
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
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                     @OA\Property(property="phone_number", type="string", example="0987654321")
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
     *     path="/api/v1/student-parents",
     *     summary="Create a new student parent",
     *     description="Creates a new student parent with the provided information",
     *     operationId="createStudentParent",
     *     tags={"Student Parents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"full_name", "phone_number"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An", description="Họ và tên đầy đủ (bắt buộc)"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321", description="Số điện thoại (bắt buộc)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student parent created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="full_name", type="array",
     *                     @OA\Items(type="string", example="The full name field is required.")
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
     *     path="/api/v1/student-parents/{id}",
     *     summary="Update a student parent",
     *     description="Updates an existing student parent's information by ID",
     *     operationId="updateStudentParent",
     *     tags={"Student Parents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student parent to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID người dùng (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An", description="Họ và tên đầy đủ"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321", description="Số điện thoại")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student parent updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T13:57:19.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student parent not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="full_name", type="array",
     *                     @OA\Items(type="string", example="The full name field is required.")
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
     *     path="/api/v1/student-parents/{id}",
     *     summary="Delete a student parent",
     *     description="Deletes a student parent by ID (soft delete)",
     *     operationId="deleteStudentParent",
     *     tags={"Student Parents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student parent to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student parent deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student parent not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Student parent not found")
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
