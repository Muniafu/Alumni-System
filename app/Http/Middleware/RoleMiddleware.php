<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Defence in depth: role-guarded routes are for approved users.
        // (Keeps behaviour consisstent even if a route is missing the
        // `approved` middleware.)
        if (!$user->is_approved) {
            return redirect()->route('pending-approval');
        }

        // Laravel only splits middleware parameters on commas
        // (role:admin, alumni), but this app's routes use pipe syntax
        // (role:admin|alumni), which arrives here as ONE parameter:
        // the literal string "admin|alumni". Normalize both syntaxes
        // so hasAnyRole() receives individual role names.
        $roles = collect($roles)
            ->flatMap(fn (string $role): array => explode('|', $role))
            ->map(fn (string $role): string => trim($role))
            ->filter()
            ->values()
            ->all();

        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
