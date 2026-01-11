<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Role;
use App\Http\Requests\CreateTripRequest;
use App\Models\Trip;
use App\Models\TripStudent;

class TripService extends BaseService
{
    public function model()
    {
        return Trip::class;
    }

    /**
     * Create a new trip with validated data.
     *
     * @param CreateTripRequest $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(CreateTripRequest $request)
    {
        $attributes = $request->validated();
        return $this->store($attributes);
    }

    /**
     * Assign students to a trip.
     * Removes existing assignments and creates new ones.
     *
     * @param int $tripId
     * @param array $studentIds
     * @return Trip
     */
    public function assignStudents(int $tripId, array $studentIds): Trip
    {
        // Lấy trip
        $trip = $this->show($tripId);

        // Xóa các assignments cũ
        $trip->tripStudents()->delete();

        // Tạo mới assignments với UUID tự sinh
        $tripStudents = [];
        $now = now();
        foreach ($studentIds as $studentId) {
            $tripStudents[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'trip_id' => $trip->id,
                'student_id' => $studentId,
                'status' => 0, // Chưa điểm danh
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert
        if (!empty($tripStudents)) {
            TripStudent::insert($tripStudents);
        }

        // Cập nhật tổng số học sinh trong trip
        $trip->update([
            'total_students' => count($studentIds),
            'curr_students' => 0, // Reset về 0 vì chưa ai điểm danh
        ]);

        // Reload relationships
        $trip->load('tripStudents.student');

        return $trip;
    }

    /**
     * Get all points (ordered) for a specific trip.
     *
     * @param int $tripId
     * @return array
     */
    public function getTripPoints(int $tripId): array
    {
        $trip = $this->show($tripId, ['points']);

        return $trip->points->map(function ($point) {
            return [
                'id' => $point->id,
                'address' => $point->address,
                'type' => $point->type,
                'order' => $point->pivot->order,
                'latitude' => $point->latitude,
                'longitude' => $point->longitude,
            ];
        })->toArray();
    }

    /**
     * Get all students assigned to a specific trip.
     *
     * @param int $tripId
     * @return array
     */
    public function getTripStudents(int $tripId): array
    {
        $trip = $this->show($tripId, ['students']);

        return $trip->students->map(function ($student) {
            return [
                'student_id' => $student->id,
                'full_name' => $student->full_name,
                'grade' => $student->grade,
            ];
        })->toArray();
    }

    /**
     * Get students pickup/dropoff at a specific point of a trip.
     *
     * @param int $tripId
     * @param int $pointId
     * @return array
     */
    public function getPointStudents(int $tripId, int $pointId): array
    {
        // Verify trip and point exist
        $trip = $this->show($tripId);
        $point = \App\Models\Point::findOrFail($pointId);

        // Get all point_students for this trip and point
        $pointStudents = \App\Models\PointStudent::where('trip_id', $tripId)
            ->where('point_id', $pointId)
            ->with('student')
            ->get();

        // Separate by type (0 = pickup, 1 = dropoff)
        $studentsPickup = $pointStudents->where('type', 0)->map(function ($ps) {
            return [
                'student_id' => $ps->student_id,
                'full_name' => $ps->student->full_name,
                'grade' => $ps->student->grade,
            ];
        })->values()->toArray();

        $studentsDropoff = $pointStudents->where('type', 1)->map(function ($ps) {
            return [
                'student_id' => $ps->student_id,
                'full_name' => $ps->student->full_name,
                'grade' => $ps->student->grade,
            ];
        })->values()->toArray();

        return [
            'point_id' => $point->id,
            'address' => $point->address,
            'type' => $point->type,
            'students_pickup' => $studentsPickup,
            'students_dropoff' => $studentsDropoff,
        ];
    }

    /**
     * Assign students to multiple points in a trip.
     * Removes existing assignments for the specified points and creates new ones.
     * Also ensures trip_points records exist for the trip and points.
     *
     * @param int $tripId
     * @param array $pointsData Array of ['point_id' => int, 'student_ids' => array]
     * @return Trip
     */
    public function assignPointStudents(int $tripId, array $pointsData): Trip
    {
        $trip = $this->show($tripId);

        $pointIds = array_column($pointsData, 'point_id');
        if (empty($pointIds)) {
            // Reload relationships and return
            $trip->load(['pointStudents.student', 'pointStudents.point', 'points']);
            return $trip;
        }

        $tripPoints = [];
        $now = now();
        $order = 1;

        foreach ($pointIds as $pointId) {
            $existingTripPoint = \App\Models\TripPoint::where('trip_id', $tripId)
                ->where('point_id', $pointId)
                ->first();

            if (!$existingTripPoint) {
                $tripPoints[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'trip_id' => $trip->id,
                    'point_id' => $pointId,
                    'order' => $order++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert trip_points
        if (!empty($tripPoints)) {
            \App\Models\TripPoint::insert($tripPoints);
        }

        // Delete old assignments for selected points in point_students
        \App\Models\PointStudent::where('trip_id', $tripId)
            ->whereIn('point_id', $pointIds)
            ->delete();

        // Save point_id, student_id into point_students
        $pointStudents = [];
        foreach ($pointsData as $pointData) {
            $pointId = $pointData['point_id'];
            $studentIds = $pointData['student_ids'] ?? [];

            foreach ($studentIds as $studentId) {
                $pointStudents[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'trip_id' => $trip->id,
                    'point_id' => $pointId,
                    'student_id' => $studentId,
                    'type' => 0,  // len xe
                    'status' => 0,  // chua diem danh
                    'method' => 0,  // thu cong
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert point_students
        if (!empty($pointStudents)) {
            \App\Models\PointStudent::insert($pointStudents);
        }

        // Reload relationships
        $trip->load(['pointStudents.student', 'pointStudents.point', 'points']);

        return $trip;
    }

    /**
     * Get trips for the authenticated user based on their role.
     *
     * @return array List of trips (without students and points details)
     */
    public function getUserTrip(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $trips = collect();

        if ($user->hasRole(Role::ROLE_ASSISTANT)) {
            $driver = $user->driver;
            if ($driver) {
                $trips = Trip::where('assistant_id', $driver->id)
                    ->with(['driver', 'assistant', 'vehicle'])
                    ->get();
            }
        } elseif ($user->hasRole(Role::ROLE_PARENT)) {
            $studentParent = $user->studentParent;
            if ($studentParent) {
                $studentIds = $studentParent->students()->pluck('id');

                if ($studentIds->isNotEmpty()) {
                    $tripIds = \App\Models\TripStudent::whereIn('student_id', $studentIds)
                        ->pluck('trip_id')
                        ->unique();

                    if ($tripIds->isNotEmpty()) {
                        $trips = Trip::whereIn('id', $tripIds)
                            ->with(['driver', 'assistant', 'vehicle'])
                            ->get();
                    }
                }
            }
        }

        // Format response
        return $trips->map(function ($trip) {
            return [
                'id' => $trip->id,
                'name' => $trip->name,
                'driver_id' => $trip->driver_id,
                'driver' => $trip->driver ? [
                    'id' => $trip->driver->id,
                    'full_name' => $trip->driver->full_name,
                    'phone' => $trip->driver->phone,
                ] : null,
                'assistant_id' => $trip->assistant_id,
                'assistant' => $trip->assistant ? [
                    'id' => $trip->assistant->id,
                    'full_name' => $trip->assistant->full_name,
                    'phone' => $trip->assistant->phone,
                ] : null,
                'vehicle_id' => $trip->vehicle_id,
                'vehicle' => $trip->vehicle ? [
                    'id' => $trip->vehicle->id,
                    'plate_number' => $trip->vehicle->plate_number,
                    'brand' => $trip->vehicle->brand,
                    'model' => $trip->vehicle->model,
                ] : null,
                'total_students' => $trip->total_students,
                'curr_students' => $trip->curr_students,
                'type' => $trip->type,
                'status' => $trip->status,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
                'created_at' => $trip->created_at?->toDateTimeString(),
                'updated_at' => $trip->updated_at?->toDateTimeString(),
            ];
        })->toArray();
    }
}

