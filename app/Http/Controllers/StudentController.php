<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{
    public function __construct(StudentService $studentService)
    {
        parent::__construct($studentService);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/{id}",
     *     summary="Get student by ID",
     *     description="Retrieves detailed information about a student by their ID",
     *     operationId="getStudentById",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student",
     *         @OA\Schema(type="integer", example=73)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=73),
     *             @OA\Property(property="student_parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="student_number", type="string", example="20225105"),
     *             @OA\Property(property="email", type="string", format="email", example="nam.nguyen225105@sis.hust.edu.vn"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="gender", type="integer", description="1 = Nam, 0 = Nữ", example=1),
     *             @OA\Property(property="dob", type="string", format="date", example="2004-05-15"),
     *             @OA\Property(property="grade", type="integer", example=4),
     *             @OA\Property(property="status", type="integer", description="0 = Đang học, 1 = Tốt nghiệp", example=0),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=21.028511, description="Vĩ độ GPS"),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=105.804817, description="Kinh độ GPS"),
     *             @OA\Property(property="qr_code_image_url", type="string", nullable=true, example="http://example.com/storage/qr-codes/students/student_73.png", description="URL ảnh QR code để scan điểm danh (QR code chứa mã STUDENT_{student_id})"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id)
    {
        $student = $this->service->show($id);
        return $this->respond($student);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students",
     *     summary="Get a paginated list of students",
     *     description="Retrieves a paginated list of students with optional filtering",
     *     operationId="getStudentsList",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="student_number__equal",
     *         in="query",
     *         description="Filter by student number (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="20225105")
     *     ),
     *     @OA\Parameter(
     *         name="student_number__like",
     *         in="query",
     *         description="Filter by student number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="20225")
     *     ),
     *     @OA\Parameter(
     *         name="email__like",
     *         in="query",
     *         description="Filter by email (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="@sis.hust.edu.vn")
     *     ),
     *     @OA\Parameter(
     *         name="full_name__like",
     *         in="query",
     *         description="Filter by full name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Nguyễn")
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
     *         name="grade__equal",
     *         in="query",
     *         description="Filter by grade",
     *         required=false,
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Đang học, 1 = Tốt nghiệp)",
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
     *     @OA\Parameter(
     *         name="student_parent_id__equal",
     *         in="query",
     *         description="Filter by student parent ID (exact match)",
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
     *                     @OA\Property(property="id", type="integer", example=73),
     *                     @OA\Property(property="student_number", type="string", example="20225105"),
     *                     @OA\Property(property="email", type="string", format="email", nullable=true, example="nam.nguyen225105@sis.hust.edu.vn"),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="0987654321"),
     *                     @OA\Property(property="gender", type="integer", description="1 = Nam, 0 = Nữ", example=1),
     *                     @OA\Property(property="dob", type="string", format="date", nullable=true, example="2004-05-15"),
     *                     @OA\Property(property="grade", type="integer", nullable=true, example=4),
     *                     @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *                     @OA\Property(property="student_parent_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=21.028511, description="Vĩ độ GPS"),
     *                     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=105.804817, description="Kinh độ GPS"),
     *                     @OA\Property(property="qr_code_image_url", type="string", nullable=true, example="http://example.com/storage/qr-codes/students/student_73.png", description="URL ảnh QR code để scan điểm danh (QR code chứa mã STUDENT_{student_id})"),
     *                     @OA\Property(property="status", type="integer", description="0 = Đang học, 1 = Tốt nghiệp", example=0),
     *                     @OA\Property(
     *                         property="parent",
     *                         type="object",
     *                         nullable=true,
     *                         description="Thông tin phụ huynh",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                         @OA\Property(property="phone_number", type="string", example="0987654321")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="total", type="integer", example=72, description="Total number of records"),
     *             @OA\Property(property="last_page", type="integer", example=8, description="Last page number"),
     *             @OA\Property(property="from", type="integer", example=11, description="Starting record index"),
     *             @OA\Property(property="to", type="integer", example=20, description="Ending record index"),
     *             @OA\Property(property="current_page", type="integer", example=2, description="Current page number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    /**
     * @OA\Get(
     *     path="/api/v1/students/all",
     *     summary="Get all students without pagination",
     *     description="Retrieves a complete list of all students without pagination, with optional filtering",
     *     operationId="getAllStudents",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="student_number__equal",
     *         in="query",
     *         description="Filter by student number (exact match)",
     *         required=false,
     *         @OA\Schema(type="string", example="20225105")
     *     ),
     *     @OA\Parameter(
     *         name="student_number__like",
     *         in="query",
     *         description="Filter by student number (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="20225")
     *     ),
     *     @OA\Parameter(
     *         name="email__like",
     *         in="query",
     *         description="Filter by email (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="@sis.hust.edu.vn")
     *     ),
     *     @OA\Parameter(
     *         name="full_name__like",
     *         in="query",
     *         description="Filter by full name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Nguyễn")
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
     *         name="grade__equal",
     *         in="query",
     *         description="Filter by grade",
     *         required=false,
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Đang học, 1 = Tốt nghiệp)",
     *         required=false,
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Parameter(
     *         name="address__like",
     *         in="query",
     *         description="Filter by address (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Hà Nội")
     *     ),
     *     @OA\Parameter(
     *         name="student_parent_id__equal",
     *         in="query",
     *         description="Filter by student parent ID (exact match)",
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
     *                     @OA\Property(property="id", type="integer", example=73),
     *                     @OA\Property(property="student_number", type="string", example="20225105"),
     *                     @OA\Property(property="email", type="string", format="email", nullable=true, example="nam.nguyen225105@sis.hust.edu.vn"),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="0987654321"),
     *                     @OA\Property(property="gender", type="integer", description="1 = Nam, 0 = Nữ", example=1),
     *                     @OA\Property(property="dob", type="string", format="date", nullable=true, example="2004-05-15"),
     *                     @OA\Property(property="grade", type="integer", nullable=true, example=4),
     *                     @OA\Property(property="address", type="string", nullable=true, example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *                     @OA\Property(property="student_parent_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=21.028511, description="Vĩ độ GPS"),
     *                     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=105.804817, description="Kinh độ GPS"),
     *                     @OA\Property(property="qr_code_image_url", type="string", nullable=true, example="http://example.com/storage/qr-codes/students/student_73.png", description="URL ảnh QR code để scan điểm danh (QR code chứa mã STUDENT_{student_id})"),
     *                     @OA\Property(property="status", type="integer", description="0 = Đang học, 1 = Tốt nghiệp", example=0),
     *                     @OA\Property(
     *                         property="parent",
     *                         type="object",
     *                         nullable=true,
     *                         description="Thông tin phụ huynh",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                         @OA\Property(property="phone_number", type="string", example="0987654321")
     *                     )
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
    public function all(Request $request): Response
    {
        $column = [
            'id',
            'student_number',
            'email',
            'full_name',
            'phone',
            'gender',
            'dob',
            'grade',
            'address',
            'student_parent_id',
            'latitude',
            'longitude',
            'qr_code_image_url',
            'status'
        ];
        $relations = ["parent:id,full_name,phone_number"];
        $result = $this->service->paginate($request->all(), $relations, $column, false);
        return $this->respond(['data' => $result]);
    }

    public function index(Request $request): Response
    {
        $column = [
            'id',
            'student_number',
            'email',
            'full_name',
            'phone',
            'gender',
            'dob',
            'grade',
            'address',
            'student_parent_id',
            'latitude',
            'longitude',
            'qr_code_image_url',
            'status'
        ];
        $relations = ["parent:id,full_name,phone_number"];
        $result = $this->service->paginate($request->all(), $relations, $column);
        return $this->respond($result);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/students",
     *     summary="Create a new student",
     *     description="Creates a new student with the provided information",
     *     operationId="createStudent",
     *     tags={"Students"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"student_number", "full_name", "gender", "dob"},
     *             @OA\Property(property="student_number", type="string", example="20225105", description="Mã số sinh viên (unique)"),
     *             @OA\Property(property="email", type="string", format="email", example="nam.nguyen225105@sis.hust.edu.vn", description="Email sinh viên (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam", description="Họ và tên đầy đủ"),
     *             @OA\Property(property="phone", type="string", example="0987654321", description="Số điện thoại (optional)"),
     *             @OA\Property(property="gender", type="boolean", example=true, description="Giới tính (true = Nam, false = Nữ)"),
     *             @OA\Property(property="dob", type="string", format="date", example="2004-05-15", description="Ngày sinh (format: Y-m-d)"),
     *             @OA\Property(property="grade", type="integer", example=4, description="Khối lớp (optional)"),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="student_parent_id", type="integer", example=1, description="ID phụ huynh (optional)"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS (optional)"),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS (optional)"),
     *             @OA\Property(property="status", type="integer", example=0, description="Trạng thái (0 = Đang học, 1 = Tốt nghiệp, optional, default: 0)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=73),
     *             @OA\Property(property="student_parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="student_number", type="string", example="20225105"),
     *             @OA\Property(property="email", type="string", format="email", example="nam.nguyen225105@sis.hust.edu.vn"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="gender", type="boolean", example=true, description="true = Nam, false = Nữ"),
     *             @OA\Property(property="dob", type="string", format="date", example="2004-05-15"),
     *             @OA\Property(property="grade", type="integer", example=4),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=21.028511),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=105.804817),
     *             @OA\Property(property="qr_code_image_url", type="string", nullable=true, example="http://example.com/storage/qr-codes/students/student_73.png", description="URL ảnh QR code để scan điểm danh (QR code chứa mã STUDENT_{student_id}, tự động tạo khi tạo student)"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="student_number", type="array",
     *                     @OA\Items(type="string", example="The student number has already been taken.")
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
     *     path="/api/v1/students/{id}",
     *     summary="Update a student",
     *     description="Updates an existing student's information by ID",
     *     operationId="updateStudent",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student to update",
     *         @OA\Schema(type="integer", example=73)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="student_number", type="string", example="20225105", description="Mã số sinh viên (unique)"),
     *             @OA\Property(property="email", type="string", format="email", example="nam.nguyen225105@sis.hust.edu.vn", description="Email sinh viên (optional)"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam", description="Họ và tên đầy đủ"),
     *             @OA\Property(property="phone", type="string", example="0987654321", description="Số điện thoại (optional)"),
     *             @OA\Property(property="gender", type="boolean", example=true, description="Giới tính (true = Nam, false = Nữ)"),
     *             @OA\Property(property="dob", type="string", format="date", example="2004-05-15", description="Ngày sinh (format: Y-m-d)"),
     *             @OA\Property(property="grade", type="integer", example=4, description="Khối lớp (optional)"),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội", description="Địa chỉ (optional)"),
     *             @OA\Property(property="student_parent_id", type="integer", example=1, description="ID phụ huynh (optional)"),
     *             @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS (optional)"),
     *             @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS (optional)"),
     *             @OA\Property(property="status", type="integer", example=0, description="Trạng thái (0 = Đang học, 1 = Tốt nghiệp, optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=73),
     *             @OA\Property(property="student_parent_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="student_number", type="string", example="20225105"),
     *             @OA\Property(property="email", type="string", format="email", example="nam.nguyen225105@sis.hust.edu.vn"),
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn Nam"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="gender", type="boolean", example=true, description="true = Nam, false = Nữ"),
     *             @OA\Property(property="dob", type="string", format="date", example="2004-05-15"),
     *             @OA\Property(property="grade", type="integer", example=4),
     *             @OA\Property(property="address", type="string", example="123 Đường Láng, Quận Đống Đa, Hà Nội"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true, example=21.028511),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true, example=105.804817),
     *             @OA\Property(property="qr_code_image_url", type="string", nullable=true, example="http://example.com/storage/qr-codes/students/student_73.png", description="URL ảnh QR code để scan điểm danh (QR code chứa mã STUDENT_{student_id})"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-06T18:53:35.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="student_number", type="array",
     *                     @OA\Items(type="string", example="The student number has already been taken.")
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
        }, 3);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/students/{id}",
     *     summary="Delete a student",
     *     description="Deletes a student by ID (soft delete)",
     *     operationId="deleteStudent",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student to delete",
     *         @OA\Schema(type="integer", example=73)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Student not found")
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
        }, 3);
    }
}
