<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'full_name',
        'cccd',
        'phone',
        'email',
        'gender',
        'license_number',
        'age',
        'address',
        'image_url',
        'school_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gender' => 'integer',
        'age' => 'integer',
        'school_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the school that the driver belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
