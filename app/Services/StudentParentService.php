<?php

namespace App\Services;

use App\Models\StudentParent;

class StudentParentService extends BaseService
{
    public function model()
    {
        return StudentParent::class;
    }
}

