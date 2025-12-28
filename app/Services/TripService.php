<?php

declare(strict_types=1);

namespace App\Services;

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
            ];
        })->toArray();
    }
}

