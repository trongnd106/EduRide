<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignStudentsRequest;
use App\Http\Requests\CreateTripRequest;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    public function __construct(TripService $tripService)
    {
        parent::__construct($tripService);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trips",
     *     summary="Create a new trip",
     *     description="Creates a new trip (route) with the provided information",
     *     operationId="createTrip",
     *     tags={"Trips"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Lộ trình đón buổi sáng", description="Tên lộ trình (optional)"),
     *             @OA\Property(property="driver_id", type="integer", example=1, description="ID tài xế (drivers.position = 1, optional)"),
     *             @OA\Property(property="assistant_id", type="integer", example=2, description="ID phụ xe (drivers.position = 2, phải khác driver_id, optional)"),
     *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID phương tiện (optional)"),
     *             @OA\Property(property="total_students", type="integer", example=0, description="Tổng số học sinh (optional, default: 0)"),
     *             @OA\Property(property="curr_students", type="integer", example=0, description="Số học sinh hiện tại (optional, default: 0)"),
     *             @OA\Property(property="type", type="integer", example=0, description="Loại chuyến: 0 = Đón, 1 = Trả (optional, default: 0)"),
     *             @OA\Property(property="status", type="integer", example=0, description="Trạng thái: 0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành (optional, default: 0)"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-28 07:00:00", description="Thời gian bắt đầu (optional)"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-28 08:30:00", description="Thời gian kết thúc (phải sau start_time, optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Lộ trình đón buổi sáng"),
     *             @OA\Property(property="driver_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="assistant_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="vehicle_id", type="integer", example=1, nullable=true),
     *             @OA\Property(property="total_students", type="integer", example=0),
     *             @OA\Property(property="curr_students", type="integer", example=0),
     *             @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *             @OA\Property(property="status", type="integer", example=0, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-28T07:00:00.000000Z", nullable=true),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-28T08:30:00.000000Z", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="assistant_id", type="array",
     *                     @OA\Items(type="string", example="The assistant id and driver id must be different.")
     *                 ),
     *                 @OA\Property(property="end_time", type="array",
     *                     @OA\Items(type="string", example="The end time must be a date after start time.")
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
    public function store(CreateTripRequest $request): Response
    {
        return DB::transaction(function () use ($request) {
            $trip = $this->service->create($request);
            return $this->respond($trip);
        }, 3);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trips/{id}/assign-students",
     *     summary="Assign students to a trip",
     *     description="Assigns a list of students to a trip. Removes existing assignments.",
     *     operationId="assignStudentsToTrip",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"trip_id", "student_ids"},
     *             @OA\Property(property="trip_id", type="integer", example=1, description="ID của lộ trình (bắt buộc)"),
     *             @OA\Property(
     *                 property="student_ids",
     *                 type="array",
     *                 description="Danh sách ID học sinh (bắt buộc, min: 1)",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Students assigned successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Lộ trình đón buổi sáng"),
     *             @OA\Property(property="total_students", type="integer", example=15),
     *             @OA\Property(property="curr_students", type="integer", example=0),
     *             @OA\Property(
     *                 property="trip_students",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="1_123_1234567890_1234"),
     *                     @OA\Property(property="trip_id", type="integer", example=1),
     *                     @OA\Property(property="student_id", type="integer", example=123),
     *                     @OA\Property(property="status", type="integer", example=0),
     *                     @OA\Property(
     *                         property="student",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=123),
     *                         @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                         @OA\Property(property="latitude", type="number", format="float", example=21.0285),
     *                         @OA\Property(property="longitude", type="number", format="float", example=105.8542)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="student_ids", type="string", example="Danh sách học sinh là bắt buộc")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function assignStudents(AssignStudentsRequest $request, $id): Response
    {
        $tripId = intval($id);
        return DB::transaction(function () use ($request, $tripId) {
            $studentIds = $request->validated()['student_ids'];
            return $this->service->assignStudents($tripId, $studentIds);
        }, 3);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trips/{id}/points",
     *     summary="Get all points of a trip",
     *     description="Retrieves all points (điểm dừng) of a specific trip, ordered by sequence",
     *     operationId="getTripPoints",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Points retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội", description="Địa chỉ điểm dừng"),
     *                 @OA\Property(property="type", type="integer", example=1, description="0 = Điểm phụ, 1 = Điểm dừng"),
     *                 @OA\Property(property="order", type="integer", example=1, description="Thứ tự điểm trong lộ trình")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getPoints($id): Response
    {
        $tripId = intval($id);
        $points = $this->service->getTripPoints($tripId);
        return $this->respond($points);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trips/{id}/students",
     *     summary="Get all students of a trip",
     *     description="Retrieves all students assigned to a specific trip",
     *     operationId="getTripStudents",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Students retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="student_id", type="integer", example=123),
     *                 @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                 @OA\Property(property="grade", type="integer", example=10, description="Khối lớp")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getStudents($id): Response
    {
        $tripId = intval($id);
        $students = $this->service->getTripStudents($tripId);
        return $this->respond($students);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trips/{trip_id}/points/{point_id}/students",
     *     summary="Get students pickup/dropoff at a specific point",
     *     description="Retrieves students who get on/off at a specific point of a trip",
     *     operationId="getPointStudents",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="trip_id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="point_id",
     *         in="path",
     *         required=true,
     *         description="ID of the point",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Point students data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="point_id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội"),
     *             @OA\Property(property="type", type="integer", example=1, description="0 = Điểm phụ, 1 = Điểm dừng"),
     *             @OA\Property(
     *                 property="students_pickup",
     *                 type="array",
     *                 description="Danh sách học sinh lên xe tại điểm này",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=123),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                     @OA\Property(property="grade", type="integer", example=10)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="students_dropoff",
     *                 type="array",
     *                 description="Danh sách học sinh xuống xe tại điểm này",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=124),
     *                     @OA\Property(property="full_name", type="string", example="Trần Thị Bình"),
     *                     @OA\Property(property="grade", type="integer", example=11)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip or Point not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getPointStudents($tripId, $pointId): Response
    {
        $tripId = intval($tripId);
        $pointId = intval($pointId);
        $data = $this->service->getPointStudents($tripId, $pointId);
        return $this->respond($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trips",
     *     summary="Get a paginated list of trips",
     *     description="Retrieves a paginated list of trips with optional filtering and relationships (driver, assistant, vehicle)",
     *     operationId="getTripsList",
     *     tags={"Trips"},
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
     *         description="Filter by trip name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="buổi sáng")
     *     ),
     *     @OA\Parameter(
     *         name="driver_id__equal",
     *         in="query",
     *         description="Filter by driver ID (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="assistant_id__equal",
     *         in="query",
     *         description="Filter by assistant ID (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_id__equal",
     *         in="query",
     *         description="Filter by vehicle ID (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="type__equal",
     *         in="query",
     *         description="Filter by type (0 = Đón, 1 = Trả)",
     *         required=false,
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Parameter(
     *         name="status__equal",
     *         in="query",
     *         description="Filter by status (0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="total_students__equal",
     *         in="query",
     *         description="Filter by total students (exact match)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="start_time__from",
     *         in="query",
     *         description="Filter trips with start_time from this date (format: Y-m-d H:i:s)",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time", example="2025-12-28 07:00:00")
     *     ),
     *     @OA\Parameter(
     *         name="start_time__to",
     *         in="query",
     *         description="Filter trips with start_time to this date (format: Y-m-d H:i:s)",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time", example="2025-12-28 08:00:00")
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
     *                     @OA\Property(property="name", type="string", nullable=true, example="Lộ trình đón buổi sáng"),
     *                     @OA\Property(property="driver_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="assistant_id", type="integer", nullable=true, example=2),
     *                     @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="total_students", type="integer", example=15),
     *                     @OA\Property(property="curr_students", type="integer", example=0),
     *                     @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *                     @OA\Property(property="status", type="integer", example=1, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *                     @OA\Property(property="start_time", type="string", format="date-time", nullable=true, example="2025-12-28T07:00:00.000000Z"),
     *                     @OA\Property(property="end_time", type="string", format="date-time", nullable=true, example="2025-12-28T08:30:00.000000Z"),
     *                     @OA\Property(
     *                         property="driver",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="Nguyễn Văn A")
     *                     ),
     *                     @OA\Property(
     *                         property="assistant",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="full_name", type="string", example="Trần Thị Bình")
     *                     ),
     *                     @OA\Property(
     *                         property="vehicle",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="plate_number", type="string", example="30A-12345")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z")
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
        $relation = ["driver:id,full_name", "assistant:id,full_name", "vehicle:id,plate_number"];
        return $this->respond($this->service->paginate($request->all(), $relation));
    }
}
