<?php

namespace App\Services;

use App\Constants\AppConst;
use App\Helpers\CommonHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;


class UserService extends BaseService
{
    public function model(): string
    {
        return User::class;
    }
}
