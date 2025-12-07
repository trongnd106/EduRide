<?php
namespace App\Models;

class Role extends BaseModel
{
    protected $fillable = [
        'name',
        'guard_name',
    ];
}
