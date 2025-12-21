<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Student
 * @mixin \Eloquent
 */
class Student extends Authenticatable
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'student_parent_id',
        'student_number',
        'email',
        'full_name',
        'phone',
        'gender',
        'dob',
        'grade',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gender' => 'boolean',
        'dob' => 'date',
        'grade' => 'integer',
        'status' => 'integer',
        'student_parent_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent that the student belongs to.
     */
    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'student_parent_id');
    }
}
