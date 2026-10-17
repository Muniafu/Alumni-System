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
    public function __construct()
    {
        $this->middleware(['auth', 'can:access-admin-dashboard']);
    }

    public function dashboard()
    {
        Gate::authorize('access-admin-dashboard');

        // Ensure roles exist
        foreach (['admin', 'alumni', 'student'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

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
        $eventStats = [
            'total' => Event::count(),
            'upcoming' => Event::where('start', '>=', now())->count(),
            'past' => Event::where('start', '<', now())->count(),
            'rsvps' => EventRsvp::count(),
            'avg_attendance' => Event::withCount('rsvps')->get()->avg('rsvps_count') ?? 0,
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
        $jobStats = [
            'total' => JobPosting::count(),
            'active' => JobPosting::where('application_deadline', '>=', now())->count(),
            'applications' => JobApplication::count(),
            'avg_applications' => JobPosting::withCount('applications')->get()->avg('applications_count') ?? 0,
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
        // Recent Platform Activities
        // -----------------------------
        $events = Event::with('organizer')->latest()->limit(3)->get();
        $jobs = JobPosting::with('poster')->latest()->limit(3)->get();
        $threads = ForumThread::with('author')->latest()->limit(3)->get();

        $recentActivities = $events->concat($jobs)->concat($threads)
            ->sortByDesc('created_at')
            ->take(5);

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

    public function showUser(User $user)
    {
        return view('admin.user-show', compact('user'));
    }

    public function pendingApprovals()
    {
        Gate::authorize('view-pending-approvals');

        $users = User::pendingApproval()->with('roles')->get();

        return view('admin.pending-approvals', compact('users'));
    }

    public function approveUser(Request $request, User $user)
    {
        Gate::authorize('approve-users');

        // Uses forceFill internally — is_approved* fields are not fillable
        $user->approve(auth()->user());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'approve',
            'description' => 'Approved user: ' . $user->name,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        // Notify user about approval
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

        // Delete user after notification
        $user->delete();

        return redirect()->route('admin.pending-approvals')
            ->with('success', 'User rejected successfully');
    }

    public function userManagement()
    {
        Gate::authorize('manage-users');

        $users = User::with('roles')->approved()->get();
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

    public function bulkAction(Request $request)
    {
        Gate::authorize('manage-users');

        $action = $request->input('action');
        $userIds = $request->input('selected_users', []);

        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        if ($action === 'delete') {
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
                $user->approve(auth()->user());
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

    public function storeUser(Request $request)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|exists:roles,name',
            'program'    => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer',
            'department' => 'nullable|string|max:255',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'program'    => $validated['program'] ?? null,
            'graduation_year' => $validated['graduation_year'] ?? null,
            'department' => $validated['role'] === 'admin' ? $validated['department'] : null,
        ]);

        // Assign role
        $role = Role::where('name', $validated['role'])->first();
        $user->roles()->attach($role);

        // Assign permissions (if using spatie/laravel-permission or pivot table)
        if ($validated['role'] === 'admin' && !empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.user-management')->with('success', 'User created successfully.');
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
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => 'required|exists:roles,name',
            'program'    => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer',
            'department' => 'nullable|string|max:255',
            'permissions' => 'array',
        ]);

        $user->update([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'program'    => $validated['program'] ?? null,
            'graduation_year' => $validated['graduation_year'] ?? null,
            'department' => $validated['role'] === 'admin' ? $validated['department'] : null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync role
        $role = Role::where('name', $validated['role'])->first();
        $user->roles()->sync([$role->id]);

        // Sync permissions (only for admins)
        if ($validated['role'] === 'admin') {
            $user->syncPermissions($validated['permissions'] ?? []);
        } else {
            $user->syncPermissions([]); // clear permissions if not admin
        }

        return redirect()->route('admin.user-management')->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        Gate::authorize('manage-users');

        // Prevent deleting yourself
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
            'permissions' => $role->permissions->pluck('id')
        ]);
    }

    public function updatePermissions(Request $request)
    {
        Gate::authorize('manage-permissions');

        $validate = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', 'Permissions updated successfully');
    }

    /*
    |----------------------------------------------------------------
    | Reporting / oversight views (not yet wired to routes — Phase 9
    | will either route these or remove them)
    |----------------------------------------------------------------
    */

    public function eventsIndex()
    {
        Gate::authorize('generate-reports');

        $events = Event::with('organizer')->latest()->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function eventsCreate()
    {
        Gate::authorize('create-events');

        return view('admin.events.create');
    }

    public function eventsEdit(Event $event)
    {
        Gate::authorize('update', $event);

        return view('admin.events.edit', compact('event'));
    }

    public function eventsDestroy(Event $event)
    {
        Gate::authorize('delete', $event);

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted');
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