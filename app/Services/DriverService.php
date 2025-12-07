<?php

namespace App\Services;

use App\Models\Driver;

class DriverService extends BaseService
{
    public function model()
    {
        return Driver::class;
    }
}
