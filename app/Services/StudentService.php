<?php

namespace App\Services;

use App\Models\Student;

class StudentService extends  BaseService
{

    public function model()
    {
        return Student::class;
    }
}
