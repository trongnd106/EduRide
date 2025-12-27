<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Point extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the trip points that belong to this point.
     */
    public function tripPoints(): HasMany
    {
        return $this->hasMany(TripPoint::class);
    }

    /**
     * Get the point students that belong to this point.
     */
    public function pointStudents(): HasMany
    {
        return $this->hasMany(PointStudent::class);
    }

    /**
     * Get the trips that include this point through trip_points.
     */
    public function trips()
    {
        return $this->belongsToMany(Trip::class, 'trip_points', 'point_id', 'trip_id')
            ->withPivot('order')
            ->withTimestamps();
    }
}

