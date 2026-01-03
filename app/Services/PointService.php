<?php

namespace App\Services;

use App\Models\Point;

class PointService extends BaseService
{
    public function model()
    {
        return Point::class;
    }
}

