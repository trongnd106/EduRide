<?php
namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
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

    /**
     * Override to handle guard_name since we removed the column from database
     */
    protected static function boot()
    {
        parent::boot();

        // Remove guard_name from attributes before saving since column doesn't exist
        static::creating(function ($role) {
            // Unset guard_name from attributes to prevent database error
            // Accessor will still return 'api' when accessed
            unset($role->attributes['guard_name']);
        });

        static::updating(function ($role) {
            // Unset guard_name from attributes to prevent database error
            // Accessor will still return 'api' when accessed
            unset($role->attributes['guard_name']);
        });
    }

    /**
     * Override setAttribute to prevent setting guard_name into attributes
     * but still allow accessor to work
     */
    public function setAttribute($key, $value)
    {
        // Ignore guard_name when setting attributes since column doesn't exist
        if ($key === 'guard_name') {
            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}
