<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function updated(User $user)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->log('User profile updated');
    }
}