<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserSearchController extends Controller
{
    /**
     * Full directory (authenticated, approved members only).
     * Only alumni and students are listed — admins are staff, not
     * directory entries. Soft-deleted users are excluded by the
     * SoftDeletes global scope.
     */
    public function index(Request $request)
    {
        $query = User::with('profile')
            ->approved()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['alumni', 'student']);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('student_id', 'like', "%$search%")
                    ->orWhere('program', 'like', "%$search%")
                    ->orWhereHas('profile', function ($q) use ($search) {
                        // profiles.skills is a comma-separated TEXT column —
                        // the old orWhere('skills') hit the USERS table
                        // (no such column) and orWhereJsonContains treated
                        // TEXT as JSON; both crashed the search.
                        $q->where('current_job', 'like', "%$search%")
                            ->orWhere('company', 'like', "%$search%")
                            ->orWhere('skills', 'like', "%$search%");
                    });
            });
        }

        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        if ($request->filled('program')) {
            $query->where('program', 'like', "%{$request->program}%");
        }

        if ($request->filled('role') && in_array($request->role, ['alumni', 'student'], true)) {
            $query->role($request->role);
        }

        $users = $query->paginate(15)->appends($request->all());

        $graduationYears = User::approved()
            ->select('graduation_year')
            ->distinct()
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

        $programs = User::approved()
            ->select('program')
            ->distinct()
            ->whereNotNull('program')
            ->orderBy('program')
            ->pluck('program');

        return view('directory.index', compact('users', 'graduationYears', 'programs'));
    }

    /**
     * Public (guest) directory — limited info only: no emails, no phone
     * numbers, no student IDs. This method was referenced by the
     * /public/directory route but never existed, so the page 500'd.
     */
    public function publicIndex(Request $request)
    {
        $query = User::with('profile')
            ->approved()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['alumni', 'student']);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('program', 'like', "%$search%")
                    ->orWhereHas('profile', function ($q) use ($search) {
                        $q->where('current_job', 'like', "%$search%")
                            ->orWhere('company', 'like', "%$search%");
                    });
            });
        }

        $users = $query->paginate(15)->appends($request->all());

        return view('directory.public', compact('users'));
    }

    /**
     * Directory member detail. Unapproved users must never be reachable
     * by direct URL, and admins are not directory members.
     */
    public function show(User $user)
    {
        abort_unless(
            $user->is_approved && $user->hasAnyRole(['alumni', 'student']),
            404
        );

        $user->load('profile');
        return view('directory.show', compact('user'));
    }
}
