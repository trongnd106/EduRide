<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignStudentsRequest;
use App\Http\Requests\AssignTripPointStudentsRequest;
use App\Http\Requests\CheckInStudentRequest;
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
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-28 08:30:00", description="Thời gian kết thúc (phải sau start_time, optional)"),
     *             @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2 (optional, default: 0)"),
     *             @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3 (optional, default: 0)"),
     *             @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4 (optional, default: 0)"),
     *             @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5 (optional, default: 0)"),
     *             @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6 (optional, default: 0)"),
     *             @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7 (optional, default: 0)")
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
     *             @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2"),
     *             @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3"),
     *             @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4"),
     *             @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5"),
     *             @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6"),
     *             @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7"),
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
     *                     @OA\Property(property="status", type="integer", example=-1, description="-1=Chưa lên xe, 0=Đang trên xe, 1=Đã xuống xe"),
     *                     @OA\Property(property="check_in", type="integer", example=0, description="0=chưa điểm danh, 1=đã điểm danh"),
     *                     @OA\Property(property="method", type="integer", nullable=true, example=0, description="0=thủ công, 1=qr"),
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
     * @OA\Post(
     *     path="/api/v1/trips/{id}/assign-point-students",
     *     summary="Assign students to multiple points in a trip",
     *     description="Assigns students to multiple points (điểm dừng) in a specific trip. Each point can have multiple students assigned.",
     *     operationId="assignTripPointStudents",
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
     *             required={"points"},
     *             @OA\Property(
     *                 property="points",
     *                 type="array",
     *                 description="Danh sách các điểm và học sinh được gán",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "students"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID của điểm dừng"),
     *                     @OA\Property(
     *                         property="students",
     *                         type="array",
     *                         description="Danh sách ID học sinh được gán cho điểm này",
     *                         @OA\Items(type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Students assigned to points successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Lộ trình đón buổi sáng"),
     *             @OA\Property(property="driver_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="assistant_id", type="integer", nullable=true, example=2),
     *             @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="total_students", type="integer", example=15),
     *             @OA\Property(property="curr_students", type="integer", example=0),
     *             @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *             @OA\Property(property="status", type="integer", example=0, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *             @OA\Property(
     *                 property="point_students",
     *                 type="array",
     *                 description="Danh sách học sinh được gán cho các điểm",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid-string"),
     *                     @OA\Property(property="trip_id", type="integer", example=1),
     *                     @OA\Property(property="point_id", type="integer", example=1),
     *                     @OA\Property(property="student_id", type="integer", example=1),
     *                     @OA\Property(property="type", type="integer", example=0, description="0 = Lên xe, 1 = Xuống xe"),
     *                     @OA\Property(property="status", type="integer", nullable=true, example=null, description="Không dùng nữa, luôn null"),
     *                     @OA\Property(
     *                         property="student",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                         @OA\Property(property="grade", type="integer", example=4)
     *                     ),
     *                     @OA\Property(
     *                         property="point",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="address", type="string", example="123 Đường Láng, Hà Nội")
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
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="points", type="array",
     *                     @OA\Items(type="string", example="Danh sách điểm là bắt buộc")
     *                 ),
     *                 @OA\Property(property="points.0.id", type="array",
     *                     @OA\Items(type="string", example="ID điểm là bắt buộc")
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
    public function assignPointStudents(AssignTripPointStudentsRequest $request, $id): Response
    {
        $tripId = intval($id);
        return DB::transaction(function () use ($request, $tripId) {
            $points = $request->validated()['points'];

            // Convert format from {id, students} to {point_id, student_ids}
            $pointsData = array_map(function ($point) {
                return [
                    'point_id' => $point['id'],
                    'student_ids' => $point['students'],
                ];
            }, $points);

            return $this->respond($this->service->assignPointStudents($tripId, $pointsData));
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
     *                 @OA\Property(property="order", type="integer", example=1, description="Thứ tự điểm trong lộ trình"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=21.028511, description="Vĩ độ GPS"),
     *                 @OA\Property(property="longitude", type="number", format="float", example=105.804817, description="Kinh độ GPS")
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
     *     summary="Get students at a specific point",
     *     description="Retrieves all students at a specific point of a trip with their status",
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
     *                 property="students",
     *                 type="array",
     *                 description="Danh sách học sinh tại điểm này",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="integer", example=123),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn An"),
     *                     @OA\Property(property="grade", type="integer", example=10, description="Khối lớp"),
     *                     @OA\Property(property="type", type="integer", example=0, description="0 = Lên xe, 1 = Xuống xe"),
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
     *         description="Filter trips with start_time from this time (format: HH:mm)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="07:00")
     *     ),
     *     @OA\Parameter(
     *         name="start_time__to",
     *         in="query",
     *         description="Filter trips with start_time to this time (format: HH:mm)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="08:00")
     *     ),
     *     @OA\Parameter(
     *         name="is_mon__equal",
     *         in="query",
     *         description="Filter by Monday (0 = false, 1 = true)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="is_tue__equal",
     *         in="query",
     *         description="Filter by Tuesday (0 = false, 1 = true)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="is_wed__equal",
     *         in="query",
     *         description="Filter by Wednesday (0 = false, 1 = true)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="is_thu__equal",
     *         in="query",
     *         description="Filter by Thursday (0 = false, 1 = true)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="is_fri__equal",
     *         in="query",
     *         description="Filter by Friday (0 = false, 1 = true)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="is_sat__equal",
     *         in="query",
     *         description="Filter by Saturday (0 = false, 1 = true)",
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
     *                     @OA\Property(property="name", type="string", nullable=true, example="Lộ trình đón buổi sáng"),
     *                     @OA\Property(property="driver_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="assistant_id", type="integer", nullable=true, example=2),
     *                     @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="total_students", type="integer", example=15),
     *                     @OA\Property(property="curr_students", type="integer", example=0),
     *                     @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *                     @OA\Property(property="status", type="integer", example=1, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *                     @OA\Property(property="start_time", type="string", format="time", nullable=true, example="07:00", description="Thời gian bắt đầu (format: HH:mm)"),
     *                     @OA\Property(property="end_time", type="string", format="time", nullable=true, example="08:30", description="Thời gian kết thúc (format: HH:mm)"),
     *                     @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2"),
     *                     @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3"),
     *                     @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4"),
     *                     @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5"),
     *                     @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6"),
     *                     @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7"),
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

    /**
     * @OA\Get(
     *     path="/api/v1/trips/{id}",
     *     summary="Get trip by ID",
     *     description="Retrieves detailed information about a trip by its ID, including driver, assistant, and vehicle information",
     *     operationId="getTripById",
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
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", nullable=true, example="Lộ trình đón buổi sáng"),
     *             @OA\Property(property="driver_id", type="integer", nullable=true, example=1),
     *             @OA\Property(
     *                 property="driver",
     *                 type="object",
     *                 nullable=true,
     *                 description="Thông tin tài xế",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="full_name", type="string", example="Nguyễn Văn A")
     *             ),
     *             @OA\Property(property="assistant_id", type="integer", nullable=true, example=2),
     *             @OA\Property(
     *                 property="assistant",
     *                 type="object",
     *                 nullable=true,
     *                 description="Thông tin phụ xe",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="full_name", type="string", example="Trần Thị Bình")
     *             ),
     *             @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1),
     *             @OA\Property(
     *                 property="vehicle",
     *                 type="object",
     *                 nullable=true,
     *                 description="Thông tin phương tiện",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="plate_number", type="string", example="30A-12345")
     *             ),
     *             @OA\Property(property="total_students", type="integer", example=25),
     *             @OA\Property(property="curr_students", type="integer", example=20),
     *             @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *             @OA\Property(property="status", type="integer", example=1, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *             @OA\Property(property="start_time", type="string", nullable=true, example="07:00", description="Thời gian bắt đầu (format: HH:mm)"),
     *             @OA\Property(property="end_time", type="string", nullable=true, example="08:30", description="Thời gian kết thúc (format: HH:mm)"),
     *             @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2"),
     *             @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3"),
     *             @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4"),
     *             @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5"),
     *             @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6"),
     *             @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Trip not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($id): Response
    {
$id = intval($id);
        $relation = ["driver:id,full_name", "assistant:id,full_name", "vehicle:id,plate_number"];
        return $this->respond($this->service->show($id, $relation));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/trips/{id}",
     *     summary="Update a trip",
     *     description="Updates an existing trip's information by ID",
     *     operationId="updateTrip",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Lộ trình đón buổi sáng", description="Tên lộ trình (optional)"),
     *             @OA\Property(property="driver_id", type="integer", example=1, description="ID tài xế (drivers.position = 1, optional)"),
     *             @OA\Property(property="assistant_id", type="integer", example=2, description="ID phụ xe (drivers.position = 2, phải khác driver_id, optional)"),
     *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID phương tiện (optional)"),
     *             @OA\Property(property="total_students", type="integer", example=25, description="Tổng số học sinh (optional)"),
     *             @OA\Property(property="curr_students", type="integer", example=20, description="Số học sinh hiện tại (optional)"),
     *             @OA\Property(property="type", type="integer", example=0, description="Loại chuyến: 0 = Đón, 1 = Trả (optional)"),
     *             @OA\Property(property="status", type="integer", example=1, description="Trạng thái: 0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành (optional)"),
     *             @OA\Property(property="start_time", type="string", example="07:00", description="Thời gian bắt đầu (format: HH:mm, optional)"),
     *             @OA\Property(property="end_time", type="string", example="08:30", description="Thời gian kết thúc (format: HH:mm, phải sau start_time, optional)"),
     *             @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2 (optional, default: 0)"),
     *             @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3 (optional, default: 0)"),
     *             @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4 (optional, default: 0)"),
     *             @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5 (optional, default: 0)"),
     *             @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6 (optional, default: 0)"),
     *             @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7 (optional, default: 0)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", nullable=true, example="Lộ trình đón buổi sáng"),
     *             @OA\Property(property="driver_id", type="integer", nullable=true, example=1, description="ID tài xế"),
     *             @OA\Property(property="assistant_id", type="integer", nullable=true, example=2, description="ID phụ xe"),
     *             @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1, description="ID phương tiện"),
     *             @OA\Property(property="total_students", type="integer", example=25),
     *             @OA\Property(property="curr_students", type="integer", example=20),
     *             @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *             @OA\Property(property="status", type="integer", example=1, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *             @OA\Property(property="start_time", type="string", nullable=true, example="07:00", description="Thời gian bắt đầu (format: HH:mm)"),
     *             @OA\Property(property="end_time", type="string", nullable=true, example="08:30", description="Thời gian kết thúc (format: HH:mm)"),
     *             @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2"),
     *             @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3"),
     *             @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4"),
     *             @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5"),
     *             @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6"),
     *             @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-28T10:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Trip not found")
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
    public function update(CreateTripRequest $request, $id): Response
    {
        $tripId = intval($id);
        $attributes = $request->validated();
        return DB::transaction(function () use ($attributes, $tripId) {
            $trip = $this->service->update($tripId, $attributes);
            return $this->respond($trip);
        }, 3);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trips/user-trips",
     *     summary="Get trips for authenticated user",
     *     description="Retrieves all trips for the authenticated user based on their role. For assistants (phụ xe), returns trips where they are assigned as assistant. For parents (phụ huynh), returns trips where their children are participating. Returns empty array if user has no role or no trips.",
     *     operationId="getUserTrips",
     *     tags={"Trips"},
     *     security={{"Authorization":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Trips retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", nullable=true, example="Lộ trình đón buổi sáng"),
     *                 @OA\Property(property="driver_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="driver", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="phone", type="string", example="0987654321")
     *                 ),
     *                 @OA\Property(property="assistant_id", type="integer", nullable=true, example=2),
     *                 @OA\Property(property="assistant", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="full_name", type="string", example="Trần Thị B"),
     *                     @OA\Property(property="phone", type="string", example="0987654322")
     *                 ),
     *                 @OA\Property(property="vehicle_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="vehicle", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="plate_number", type="string", example="30A-12345"),
     *                     @OA\Property(property="brand", type="string", example="Toyota"),
     *                     @OA\Property(property="model", type="string", example="Hiace")
     *                 ),
     *                 @OA\Property(property="total_students", type="integer", example=25),
     *                 @OA\Property(property="curr_students", type="integer", example=20),
     *                 @OA\Property(property="type", type="integer", example=0, description="0 = Đón, 1 = Trả"),
     *                 @OA\Property(property="status", type="integer", example=1, description="0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành"),
     *                 @OA\Property(property="start_time", type="string", nullable=true, example="07:00", description="Thời gian bắt đầu (format: HH:mm)"),
     *                 @OA\Property(property="end_time", type="string", nullable=true, example="08:30", description="Thời gian kết thúc (format: HH:mm)"),
     *                 @OA\Property(property="is_mon", type="boolean", example=false, description="Thứ 2"),
     *                 @OA\Property(property="is_tue", type="boolean", example=false, description="Thứ 3"),
     *                 @OA\Property(property="is_wed", type="boolean", example=false, description="Thứ 4"),
     *                 @OA\Property(property="is_thu", type="boolean", example=false, description="Thứ 5"),
     *                 @OA\Property(property="is_fri", type="boolean", example=false, description="Thứ 6"),
     *                 @OA\Property(property="is_sat", type="boolean", example=false, description="Thứ 7"),
     *                 @OA\Property(property="created_at", type="string", nullable=true, example="2025-12-28 10:30:00"),
     *                 @OA\Property(property="updated_at", type="string", nullable=true, example="2025-12-28 10:30:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getUserTrips(): Response
    {
        $trips = $this->service->getUserTrip();
        return $this->respond($trips);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trips/check-in",
     *     summary="Check in a student using QR code",
     *     description="Điểm danh học sinh bằng QR code. Cập nhật check_in trong trip_students, status trong trip_students và point_students, type trong point_students, và status trong trip_points.",
     *     operationId="checkInStudent",
     *     tags={"Trips"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"trip_id", "qr_code", "flag", "point_id"},
     *             @OA\Property(property="trip_id", type="integer", example=1, description="ID của chuyến đi"),
     *             @OA\Property(property="qr_code", type="string", example="STUDENT_123", description="Mã QR code của học sinh (format: STUDENT_{student_id})"),
     *             @OA\Property(property="flag", type="integer", example=0, description="0 = Lên xe, 1 = Xuống xe"),
     *             @OA\Property(property="point_id", type="integer", example=1, description="ID của điểm dừng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check in successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Điểm danh thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="trip_id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=123),
     *                 @OA\Property(property="student_name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="point_id", type="integer", example=1),
     *                 @OA\Property(property="point_address", type="string", example="Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội"),
     *                 @OA\Property(property="flag", type="integer", example=0, description="0 = Lên xe, 1 = Xuống xe"),
     *                 @OA\Property(property="flag_description", type="string", example="Lên xe")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip, Student, Point, TripPoint, TripStudent, or PointStudent not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Trip not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="qr_code", type="array",
     *                     @OA\Items(type="string", example="QR code không hợp lệ. Format: STUDENT_{student_id}")
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
    public function checkIn(CheckInStudentRequest $request): Response
    {
        $validated = $request->validated();
        
        return DB::transaction(function () use ($validated) {
            $result = $this->service->checkInStudent(
                $validated['trip_id'],
                $validated['qr_code'],
                $validated['flag'],
                $validated['point_id']
            );
            return $this->respond($result);
        }, 3);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trips/{id}",
     *     summary="Delete a trip",
     *     description="Deletes a trip by ID. This will also delete related records in trip_points, trip_students, and point_students tables.",
     *     operationId="deleteTrip",
     *     tags={"Trips"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the trip to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="boolean", example=true, description="Indicates if the deletion was successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Trip not found")
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
        $tripId = intval($id);
        return DB::transaction(function () use ($tripId) {
            return $this->respond($this->service->destroy($tripId));
        }, 3);
    }
}
