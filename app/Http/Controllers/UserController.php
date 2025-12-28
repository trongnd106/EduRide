<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\StudentParent;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(UserService $userService)
    {
        parent::__construct($userService);
    }

    /**
     * @OA\Get(
     *     path="users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieves detailed information about a user by their ID",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="nguyenvanan"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *             @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *             @OA\Property(property="type", type="integer", description="1 = Phụ huynh, 2 = Phụ xe", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
        $user = $this->service->show($id);
        return $this->respond($user);
    }

    /**
     * @OA\Get(
     *     path="users",
     *     summary="Get a paginated list of users",
     *     description="Retrieves a paginated list of users with optional filtering",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="email__like",
     *         in="query",
     *         description="Filter by email (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="example.com")
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Không hoạt động, 1 = Đang hoạt động)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="type__equal",
     *         in="query",
     *         description="Filter by type (1 = Phụ huynh, 2 = Phụ xe)",
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
     *                     @OA\Property(property="username", type="string", example="nguyenvanan"),
     *                     @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *                     @OA\Property(property="status", type="integer", description="0 = Không hoạt động, 1 = Đang hoạt động", example=1),
     *                     @OA\Property(property="type", type="integer", description="1 = Phụ huynh, 2 = Phụ xe", example=1)
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
     *     path="users",
     *     summary="Create a new user",
     *     description="Creates a new user with the provided information. If type = 1 (Phụ huynh), creates a student_parent record. If type = 2 (Phụ xe), creates a driver record with position = 2.",
     *     operationId="createUser",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"username", "email", "password", "type", "full_name", "phone_number"},
     *             @OA\Property(property="username", type="string", example="nguyenvanan", description="Tên đăng nhập (bắt buộc)"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com", description="Email (bắt buộc, unique)"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Mật khẩu (bắt buộc)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động, optional, default: 0)"),
     *             @OA\Property(property="type", type="integer", example=1, description="Loại người dùng (1 = Phụ huynh, 2 = Phụ xe, bắt buộc)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An", description="Họ và tên đầy đủ (bắt buộc)"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321", description="Số điện thoại (bắt buộc)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="nguyenvanan"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="type", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
            // Hash password
            if (isset($attributes['password'])) {
                $attributes['password'] = Hash::make($attributes['password']);
            }
            
            // Extract full_name and phone_number
            $fullName = $attributes['full_name'] ?? null;
            $phoneNumber = $attributes['phone_number'] ?? null;
            $type = $attributes['type'] ?? null;
            
            // Remove full_name and phone_number from user attributes
            unset($attributes['full_name'], $attributes['phone_number']);
            
            // Create user
            $user = $this->service->store($attributes);
            
            // Create related record based on type
            if ($type == 1 && $fullName && $phoneNumber) {
                // Type 1 = Phụ huynh: Create student_parent
                StudentParent::create([
                    'user_id' => $user->id,
                    'full_name' => $fullName,
                    'phone_number' => $phoneNumber,
                ]);
            } elseif ($type == 2 && $fullName && $phoneNumber) {
                // Type 2 = Phụ xe: Create driver with position = 2
                Driver::create([
                    'user_id' => $user->id,
                    'full_name' => $fullName,
                    'phone' => $phoneNumber,
                    'position' => 2,
                    'status' => $attributes['status'] ?? 0,
                ]);
            }
            
            return $this->respond($user);
        }, 3);
    }

    /**
     * @OA\Put(
     *     path="users/{id}",
     *     summary="Update a user",
     *     description="Updates an existing user's information by ID. Can also update related student_parent or driver record.",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="nguyenvanan", description="Tên đăng nhập"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com", description="Email"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123", description="Mật khẩu mới (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái (0 = Không hoạt động, 1 = Đang hoạt động)"),
     *             @OA\Property(property="type", type="integer", example=1, description="Loại người dùng (1 = Phụ huynh, 2 = Phụ xe)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn An", description="Họ và tên đầy đủ (optional)"),
     *             @OA\Property(property="phone_number", type="string", example="0987654321", description="Số điện thoại (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="nguyenvanan"),
     *             @OA\Property(property="email", type="string", format="email", example="nguyenvanan@example.com"),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="type", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-21T15:21:32.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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
            // Hash password if provided
            if (isset($attributes['password'])) {
                $attributes['password'] = Hash::make($attributes['password']);
            }
            
            // Extract full_name and phone_number
            $fullName = $attributes['full_name'] ?? null;
            $phoneNumber = $attributes['phone_number'] ?? null;
            $type = $attributes['type'] ?? null;
            
            // Remove full_name and phone_number from user attributes
            unset($attributes['full_name'], $attributes['phone_number']);
            
            // Update user
            $user = $this->service->update($id, $attributes);
            
            // Update related record based on type
            if ($type == 1 && ($fullName || $phoneNumber)) {
                // Type 1 = Phụ huynh: Update student_parent
                $studentParent = StudentParent::where('user_id', $id)->first();
                if ($studentParent) {
                    $updateData = [];
                    if ($fullName) $updateData['full_name'] = $fullName;
                    if ($phoneNumber) $updateData['phone_number'] = $phoneNumber;
                    $studentParent->update($updateData);
                } elseif ($fullName && $phoneNumber) {
                    // Create if doesn't exist
                    StudentParent::create([
                        'user_id' => $id,
                        'full_name' => $fullName,
                        'phone_number' => $phoneNumber,
                    ]);
                }
            } elseif ($type == 2 && ($fullName || $phoneNumber)) {
                // Type 2 = Phụ xe: Update driver
                $driver = Driver::where('user_id', $id)->first();
                if ($driver) {
                    $updateData = [];
                    if ($fullName) $updateData['full_name'] = $fullName;
                    if ($phoneNumber) $updateData['phone'] = $phoneNumber;
                    $driver->update($updateData);
                } elseif ($fullName && $phoneNumber) {
                    // Create if doesn't exist
                    Driver::create([
                        'user_id' => $id,
                        'full_name' => $fullName,
                        'phone' => $phoneNumber,
                        'position' => 2, // Phụ xe
                        'status' => $attributes['status'] ?? 0,
                    ]);
                }
            }
            
            return $this->respond($user);
        });
    }

    /**
     * @OA\Delete(
     *     path="users/{id}",
     *     summary="Delete a user",
     *     description="Deletes a user by ID (soft delete)",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
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
