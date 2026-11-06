<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\ForumThread;

class DashboardController extends Controller
{
    /**
     * /dashboard — dispatch each user to the dashboard for their role.
     */
    public function index()
    {
        $user = Auth::user();

        // The route should sit behind auth middleware; this guard keeps a
        // direct guest hit from being a fatal error.
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('alumni')) {
            return redirect()->route('alumni.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // Redirecting a role-less user to /login loops forever (the guest
        // middleware bounces authenticated users straight back). Fail
        // honestly instead.
        abort(403, 'No role is assigned to this account. Contact an administrator.');
    }

    /**
     * Admin Dashboard — same variable contract as before, but every query
     * now targets columns/tables that actually exist: users.is_approved
     * (not status), events.start/end (not date), event_rsvps (not
     * event_user), the active() scope over application_deadline (not
     * deadline), and approvals read from users.approved_at (there is no
     * user_approvals or profile_updates table).
     */
    public function adminDashboard()
    {
        $userCounts = [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'alumni' => User::role('alumni')->count(),
            'students' => User::role('student')->count(),
            'pending_approvals' => User::where('is_approved', false)->count(),
            'new_last_week' => User::where('created_at', '>=', now()->subWeek())->count(),
        ];

        $eventStats = [
            'total' => Event::count(),
            'upcoming' => Event::where('start', '>=', now())->count(),
            'past' => Event::where('end', '<', now())->count(),
            'rsvps' => EventRsvp::count(),
            'avg_attendance' => round(EventRsvp::count() / max(1, Event::count()), 1),
        ];

        $jobStats = [
            'total' => JobPosting::count(),
            'active' => JobPosting::active()->count(),
            'applications' => JobApplication::count(),
            'avg_applications' => round(JobApplication::count() / max(1, JobPosting::count()), 1),
        ];

        $forumStats = [
            'threads' => ForumThread::count(),
            'posts' => DB::table('forum_posts')->count(),
            'recent_posts' => DB::table('forum_posts')
                ->where('created_at', '>=', now()->subWeek())
                ->count(),
        ];

        // Recent activity — all Eloquent models, so the merged collection is
        // uniform (the old version mixed in stdClass rows from two tables
        // that don't even exist).
        $recentActivities = collect()
            ->merge(Event::latest()->take(5)->get())
            ->merge(JobPosting::latest()->take(5)->get())
            ->merge(ForumThread::latest()->take(5)->get())
            ->merge(User::latest()->take(5)->get())
            ->merge(User::whereNotNull('approved_at')->latest('approved_at')->take(5)->get())
            ->sortByDesc('created_at')
            ->take(10);

        return view('admin.dashboard', compact(
            'userCounts',
            'eventStats',
            'jobStats',
            'forumStats',
            'recentActivities'
        ));
    }
}
