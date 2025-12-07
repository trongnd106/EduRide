<?php

namespace App\Services;

use App\Models\Vehicle;

class VehicleService extends BaseService
{
    public function model()
    {
        return Vehicle::class;
    }
}
