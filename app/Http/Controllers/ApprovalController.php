<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * The "Account pending approval: page.
     * 
     * The pending-approval route may sit outside of the auth middleware
     * group, so guard against gusts insted of calling ->is_approved
     * on null (which was a 500 for anyone hitting the URL logged-out).
     */

    public function pending()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->is_approved) {
            return redirect()->route('dashboard');            
        }

        return view('auth.pending-approval');
    }

}
