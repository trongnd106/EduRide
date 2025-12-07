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
        'school_id',
        'student_number',
        'email',
        'full_name',
        'phone',
        'gender',
        'dob',
        'grade',
        'address',
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
        'school_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the school that the student belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
