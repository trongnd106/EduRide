<?php
namespace App\Models;

class Role extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the guard_name attribute (always return 'api' for User roles)
     * This is needed because Spatie Permission package requires guard_name
     * but we removed it from the database table
     */
    public function getGuardNameAttribute(): string
    {
        return 'api';
    }
}
