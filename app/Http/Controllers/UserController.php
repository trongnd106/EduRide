<?php

namespace App\Http\Controllers;

use App\Constants\AppConst;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use function App\Http\Controllers\Admin\config;

class UserController extends Controller
{
    public function __construct(UserService $userService)
    {
        parent::__construct($userService);
    }
}
