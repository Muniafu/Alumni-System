<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CompleteProfileRequest;
use App\Models\AlumniProfile;
use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileCompletionController extends Controller
{
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($user->profile_completed) {
            return redirect()->route('dashboard');
        }

        return view('profile.complete', [
            'user' => $user,
            'role' => $user->role
        ]);
    }

    public function store(CompleteProfileRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $data = $request->validated();

        if ($user->isAlumni()) {
            $profile = AlumniProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        } elseif ($user->isEmployer()) {
            $profile = EmployerProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        }

        $user->update(['profile_completed' => true]);

        return redirect()->route('dashboard')->with('status', 'Profile completed successfully!');
    }
}