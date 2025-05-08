<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPrivacySettings
{
    public function handle(Request $request, Closure $next, $field)
    {
        $user = $request->user();
        
        if ($user->alumniProfile->privacy_settings[$field] === 'private' && 
            !$request->user()->isAdmin()) {
            abort(403, 'This information is private');
        }

        return $next($request);
    }
}