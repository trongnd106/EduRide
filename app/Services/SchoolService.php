<?php

namespace App\Services;

use App\Models\School;

class SchoolService extends BaseService
{
    public function model()
    {
        return School::class;
    }
}
