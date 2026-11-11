<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Message;
use App\Models\Mentorship;
use App\Models\MentorshipRequest;
use App\Models\Conversation;
use Spatie\Permission\Models\Permission;
use App\Notifications\UserApprovedNotification;
use App\Notifications\UserRejectedNotification;
use Carbon\Carbon;

class AdminController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Every method in this controller requires an authenticated user
    | with the `access-admin-dashboard` permission (admin role).
    | Method-level gates below then require the specific action.
    |----------------------------------------------------------------------
    */
    public function __construct()
    {
        $this->middleware(['auth', 'can:access-admin-dashboard']);
    }

    /*
    |----------------------------------------------------------------------
    | Dashboard
    |----------------------------------------------------------------------
    */
    public function dashboard()
    {
        // -----------------------------
        // User Statistics
        // -----------------------------
        $userCounts = [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'alumni' => User::role('alumni')->count(),
            'students' => User::role('student')->count(),
            'new_last_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'pending_approvals' => User::pendingApproval()->count(),
        ];

        // User growth by month (last 12 months)
        $userGrowth = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month . '-01')->format('M Y'),
                    'count' => $item->count,
                ];
            });

        // -----------------------------
        // Event Statistics
        // -----------------------------

        $totalEvents = Event::count();

        $eventStats = [
            'total' => $totalEvents,
            'upcoming' => Event::where('start', '>=', now())->count(),
            'past' => Event::where('start', '<', now())->count(),
            'rsvps' => EventRsvp::count(),
            // Averaging a count = total ÷ number of rows — no need to
            // load every Event into memory with withCount()->get()->avg().
            'avg_attendance' => $totalEvents > 0
                ? round(EventRsvp::count() / $totalEvents, 1)
                : 0,
        ];

        // Last 10 past events with attendance
        $eventAttendance = Event::withCount('rsvps')
            ->where('start', '<', now())
            ->orderBy('start', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($e) => ['name' => $e->title, 'attendance' => $e->rsvps_count]);

        // -----------------------------
        // Job Statistics
        // -----------------------------
        $totalJobs = JobPosting::count();

        $jobStats = [
            'total' => $totalJobs,
            'active' => JobPosting::where('application_deadline', '>=', now())->count(),
            'applications' => JobApplication::count(),
            'avg_applications' => $totalJobs > 0
                ? round(JobApplication::count() / $totalJobs, 1)
                : 0,
        ];

        // -----------------------------
        // Forum Statistics
        // -----------------------------
        $forumStats = [
            'threads' => ForumThread::count(),
            'posts' => ForumPost::count(),
            'recent_posts' => ForumPost::where('created_at', '>=', now()->subWeek())->count(),
        ];

        // -----------------------------
        // Messaging Statistics
        // -----------------------------
        $messagingStats = [
            'conversations' => Conversation::count(),
            'messages' => Message::count(),
        ];

        // -----------------------------
        // Mentorship Statistics
        // -----------------------------
        $mentorshipStats = [
            'total' => Mentorship::count(),
            'active' => Mentorship::where('status', 'active')->count(),
            'requests' => MentorshipRequest::count(),
            'pending_requests' => MentorshipRequest::where('status', 'pending')->count(),
        ];

        // -----------------------------
        // Recent Activity feed
        // -----------------------------
        $recentActivities = collect()
            ->merge(Event::with('organizer')->latest()->take(5)->get())
            ->merge(JobPosting::with('poster')->latest()->take(5)->get())
            ->merge(ForumThread::with('author')->latest()->take(5)->get())
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return view('admin.dashboard', compact(
            'userCounts',
            'userGrowth',
            'eventStats',
            'eventAttendance',
            'jobStats',
            'forumStats',
            'messagingStats',
            'mentorshipStats',
            'recentActivities'
        ));
    }

    /*
    |----------------------------------------------------------------------
    | User view
    |----------------------------------------------------------------------
    */
    public function showUser(User $user)
    {
        // Eager-load everything the tabs render (avoids N+1 queries and
        // makes soft-deleted relations visible as null instead of crashing).
        $user->load(['roles.permissions', 'profile', 'events', 'jobApplications.jobPosting', 'forumPosts.thread']);

        return view('admin.user-show', compact('user'));
    }

    /*
    |----------------------------------------------------------------------
    | Approvals
    |----------------------------------------------------------------------
    */
    public function pendingApprovals()
    {
        Gate::authorize('view-pending-approvals');

        $users = User::pendingApproval()->with('roles')->get();

        return view('admin.pending-approvals', compact('users'));
    }

    public function approveUser(Request $request, User $user)
    {
        Gate::authorize('approve-users');

        // approve() uses forceFill — is_approved/approved_by/approved_at
        // are deliberately NOT mass-assignable on the User model.
        $user->approve($request->user());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'approve',
            'description' => 'Approved user: ' . $user->name,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $user->notify(new UserApprovedNotification());

        return redirect()->route('admin.pending-approvals')
            ->with('success', 'User approved successfully');
    }

    public function rejectUser(Request $request, User $user)
    {
        Gate::authorize('approve-users');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'reject',
            'description' => 'Rejected user: ' . $user->name,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        // Notify user about rejection before deleting
        $user->notify(new UserRejectedNotification());

        $user->delete();

        return redirect()->route('admin.pending-approvals')
            ->with('success', 'User rejected successfully');
    }

    /*
    |----------------------------------------------------------------------
    | User management
    |----------------------------------------------------------------------
    */
    public function userManagement(Request $request)
    {
        Gate::authorize('manage-users');

        // The search/role/status filter form previously did nothing —
        // the query always returned approved users only, so the Pending
        // and (invisible) Restore options were dead ends.
        $users = User::with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('roles', fn ($qr) => $qr->where('roles.id', $request->role));
            })
            ->when($request->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($request->status === 'pending', fn ($q) => $q->pendingApproval())
            ->when(in_array($request->status, [null, '', 'approved']), fn ($q) => $q->approved())
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::all();

        return view('admin.user-management', compact('users', 'roles'));
    }

    public function createUser()
    {
        Gate::authorize('manage-users');

        $roles = Role::all();
        $graduationYears = range(1980, now()->year + 5);

        $programs = [
            'Computer Science',
            'Information Systems',
            'Business Administration',
            'Engineering',
            'Education',
        ];

        return view('admin.user-create', compact('roles', 'graduationYears', 'programs'));
    }

    public function storeUser(Request $request)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => 'required|string|min:8|confirmed',
            'role'            => 'required|exists:roles,name',
            'program'         => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer',
            'department'      => 'nullable|string|max:255',
        ]);

        // Generate a unique student ID (same scheme as registration)
        do {
            $studentId = 'STU' . date('Y') . mt_rand(1000, 9999);
        } while (User::where('student_id', $studentId)->exists());

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'student_id'      => $studentId,
            'program'         => $validated['program'] ?? null,
            'graduation_year' => $validated['graduation_year'] ?? null,
        ]);

        // NOTE: `department` is intentionally NOT persisted — it is not in
        // User::$fillable and has no users-table column wired up. If you
        // need it, add a migration + put it in $fillable first.

        // An account created by an admin is approved by definition —
        // it must not sit in the pending-approval queue.
        $user->approve($request->user());

        // assignRole() keeps Spatie's permission cache consistent.
        // (roles()->attach() bypasses it.)
        $user->assignRole($validated['role']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'description' => 'Created user: ' . $user->name . ' (role: ' . $validated['role'] . ')',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return redirect()->route('admin.user-management')
            ->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        Gate::authorize('manage-users');

        $roles = Role::all();
        $graduationYears = range(1980, now()->year + 5);

        $programs = [
            'Computer Science',
            'Information Systems',
            'Business Administration',
            'Engineering',
            'Education',
        ];

        return view('admin.user-edit', compact('user', 'roles', 'graduationYears', 'programs'));
    }

    public function updateUser(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'role'            => 'required|exists:roles,name',
            'program'         => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer',
            'department'      => 'nullable|string|max:255',
        ]);

        // Self-lockout guard: an admin must not be able to demote
        // their own account and lose access to the admin area.
        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'program'         => $validated['program'] ?? null,
            'graduation_year' => $validated['graduation_year'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // syncRoles()/syncPermissions() keep Spatie's cache consistent
        // (roles()->sync() on the raw pivot bypasses it).
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.user-management')
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        Gate::authorize('manage-users');

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.user-management')
                ->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'description' => 'Deleted user: ' . $user->name,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return redirect()->route('admin.user-management')
            ->with('success', 'User deleted successfully');
    }

    public function restoreUser($id)
    {
        Gate::authorize('manage-users');

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User restored successfully.');
    }

    public function bulkAction(Request $request)
    {
        Gate::authorize('manage-users');

        $action = $request->input('action');
        $userIds = $request->input('selected_users', []);

        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        // Never delete your own account via a bulk action.
        if ($action === 'delete') {
            if (in_array(auth()->id(), $userIds)) {
                return back()->with('error', 'You cannot delete your own account.');
            }

            User::whereIn('id', $userIds)->delete();
            return back()->with('success', 'Selected users deleted successfully.');
        }

        if ($action === 'restore') {
            User::withTrashed()->whereIn('id', $userIds)->restore();
            return back()->with('success', 'Selected users restored successfully.');
        }

        return back()->with('error', 'Invalid bulk action selected.');
    }

    public function bulkApproval(Request $request)
    {
        Gate::authorize('approve-users');

        $action = $request->input('action');
        $userIds = $request->input('selected_users', []);

        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($action === 'approve') {
            foreach ($users as $user) {
                $user->approve($request->user());
                $user->notify(new UserApprovedNotification());
            }

            return back()->with('success', 'Selected users approved successfully.');
        }

        if ($action === 'reject') {
            foreach ($users as $user) {
                $user->notify(new UserRejectedNotification());
                $user->delete();
            }

            return back()->with('success', 'Selected users rejected successfully.');
        }

        return back()->with('error', 'Invalid bulk action.');
    }

    /*
    |----------------------------------------------------------------------
    | Role & permission management
    |----------------------------------------------------------------------
    */
    public function permissions()
    {
        Gate::authorize('manage-permissions');

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('admin.permissions', compact('roles', 'permissions'));
    }

    public function getRolePermissions(Role $role)
    {
        Gate::authorize('manage-permissions');

        return response()->json([
            'permissions' => $role->permissions->pluck('id'),
        ]);
    }

    public function updatePermissions(Request $request)
    {
        Gate::authorize('manage-permissions');

        $validated = $request->validate([
            'role_id'        => 'required|exists:roles,id',
            'permissions'    => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', 'Permissions updated successfully');
    }

    /*
    |----------------------------------------------------------------------
    | Reporting / oversight views
    | (gated by generate-reports; Phase 9 wires these to routes or
    | removes them)
    |----------------------------------------------------------------------
    */
    public function eventsIndex()
    {
        Gate::authorize('generate-reports');

        $events = Event::with('organizer')->latest()->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function jobsIndex()
    {
        Gate::authorize('generate-reports');

        $jobs = JobPosting::with('poster')->latest()->paginate(15);

        return view('admin.jobs.index', compact('jobs'));
    }

    public function forumsIndex()
    {
        Gate::authorize('generate-reports');

        $threads = ForumThread::with('author')->latest()->paginate(15);

        return view('admin.forums.index', compact('threads'));
    }

    public function mentorshipsIndex()
    {
        Gate::authorize('generate-reports');

        $mentorships = Mentorship::with(['mentor', 'mentee'])->latest()->paginate(15);

        return view('admin.mentorships.index', compact('mentorships'));
    }

    public function messagesIndex()
    {
        Gate::authorize('generate-reports');

        $conversations = Conversation::latest()->paginate(15);

        return view('admin.messages.index', compact('conversations'));
    }
}
