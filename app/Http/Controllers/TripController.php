<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignStudentsRequest;
use App\Http\Requests\CreateTripRequest;
use App\Services\TripService;
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
     *     path="trips",
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
     *     path="trips/{id}/assign-students",
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
}