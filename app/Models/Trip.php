<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'driver_id',
        'assistant_id',
        'vehicle_id',
        'total_students',
        'curr_students',
        'type',
        'status',
        'start_time',
        'end_time',
        'is_mon',
        'is_tue',
        'is_wed',
        'is_thu',
        'is_fri',
        'is_sat',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'driver_id' => 'integer',
        'assistant_id' => 'integer',
        'vehicle_id' => 'integer',
        'total_students' => 'integer',
        'curr_students' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
        'start_time' => 'string',
        'end_time' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_mon' => 'boolean',
        'is_tue' => 'boolean',
        'is_wed' => 'boolean',
        'is_thu' => 'boolean',
        'is_fri' => 'boolean',
        'is_sat' => 'boolean',
    ];

    /**
     * Get the driver (tài xế) that belongs to this trip.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Get the assistant (phụ xe) that belongs to this trip.
     */
    public function assistant(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assistant_id');
    }

    /**
     * Get the vehicle that belongs to this trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the trip points that belong to this trip.
     */
    public function tripPoints(): HasMany
    {
        return $this->hasMany(TripPoint::class);
    }

    /**
     * Get the trip students that belong to this trip.
     */
    public function tripStudents(): HasMany
    {
        return $this->hasMany(TripStudent::class);
    }

    /**
     * Get the point students that belong to this trip.
     */
    public function pointStudents(): HasMany
    {
        return $this->hasMany(PointStudent::class);
    }

    /**
     * Get the points that belong to this trip through trip_points.
     */
    public function points()
    {
        return $this->belongsToMany(Point::class, 'trip_points', 'trip_id', 'point_id')
            ->withPivot('order', 'status')
            ->withTimestamps()
            ->orderBy('trip_points.order');
    }

    /**
     * Get the students that belong to this trip through trip_students.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'trip_students', 'trip_id', 'student_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}

