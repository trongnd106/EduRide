<?php

namespace App\Observers;

use App\Constants\AppConst;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    protected array $trackedFields = [
        'full_name',
        'email',
        'status',
    ];

    public function updating(User $user): void
    {
        // implement auto update here
    }
}
