<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ForumCategoryController;
use App\Http\Controllers\ForumThreadController;
use App\Http\Controllers\ForumPostController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\MentorshipController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

// Public directory & events (limited info)
Route::get('/public/directory', [UserSearchController::class, 'publicIndex'])->name('public.directory');
Route::get('/public/events', [EventController::class, 'publicIndex'])->name('public.events');

/*
|--------------------------------------------------------------------------
| Pending Approval
| (auth required — the controller reads Auth::user(),
|  so guests get redirected to login instead of a crash)
|--------------------------------------------------------------------------
*/
Route::get('/pending-approval', [ApprovalController::class, 'pending'])
    ->middleware('auth')
    ->name('pending-approval');

/*
|--------------------------------------------------------------------------
| Authenticated & Approved Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'approved'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |-----------------------------
    | Profile Management
    |-----------------------------
    */
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.details.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |-----------------------------
    | Events
    | (static calendar routes MUST come before /events/{event}
    |  or Laravel treats "calendar" as the {event} parameter → 404)
    |-----------------------------
    */
    Route::get('/events/calendar', [EventController::class, 'calendar'])->name('events.calendar');
    Route::get('/events/calendar/data', [EventController::class, 'getCalendarEvents'])->name('events.calendar.data');

    Route::resource('events', EventController::class)->except(['show']);
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
    Route::delete('/events/{event}/rsvp', [EventController::class, 'cancelRsvp'])->name('events.rsvp.cancel');

    /*
    |-----------------------------
    | Jobs
    |-----------------------------
    */
    Route::middleware(['can:create-jobs'])->group(function () {
        Route::resource('jobs', JobPostingController::class)->except(['index', 'show']);
    });

    Route::get('/jobs', [JobPostingController::class, 'index'])->name('jobs.index');

    // my-postings MUST come before /jobs/{job}
    Route::get('/jobs/my-postings', [JobPostingController::class, 'myPostings'])
        ->middleware('role:alumni')
        ->name('jobs.my-postings');

    Route::get('/jobs/{job}', [JobPostingController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/apply', [JobPostingController::class, 'apply'])->name('jobs.apply');
    Route::get('/jobs/{job}/applications', [JobPostingController::class, 'applications'])
        ->middleware('role:admin|alumni')
        ->name('jobs.applications');

    // Application viewing + resume download (policy-authorized in the controller)
    Route::get('/applications/{application}', [JobPostingController::class, 'showApplication'])->name('applications.show');
    Route::get('/applications/{application}/resume', [JobPostingController::class, 'downloadResume'])->name('applications.resume.download');
    Route::get('/jobs/{application}/download-resume', [JobPostingController::class, 'downloadResume'])->name('jobs.downloadResume');

    // Single, correct status-update route (controller signature matches)
    Route::put('/jobs/{job}/applications/{application}', [JobPostingController::class, 'updateApplicationStatus'])->name('jobs.applications.update');

    /*
    |-----------------------------
    | Messaging
    |-----------------------------
    */
    Route::resource('conversations', ConversationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::resource('conversations.messages', MessageController::class)->only(['store', 'destroy']);

    /*
    |-----------------------------
    | Forum
    |-----------------------------
    */
    Route::prefix('forum')->name('forum.')->group(function () {
        // Forum home = category listing (ForumThreadController::index was
        // an empty stub, removed during the Phase 5 cleanup)
        Route::get('/', [ForumCategoryController::class, 'index'])->name('index');

        Route::get('/categories', [ForumCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [ForumCategoryController::class, 'show'])->name('categories.show');

        // Thread create/store are nested under {category}: implicit binding
        // must inject a real ForumCategory, otherwise the thread insert
        // crashes with a NULL forum_category_id
        Route::get('/categories/{category}/threads/create', [ForumThreadController::class, 'create'])->name('threads.create');
        Route::post('/categories/{category}/threads', [ForumThreadController::class, 'store'])->name('threads.store');

        Route::resource('threads', ForumThreadController::class)->except(['index', 'create', 'store']);
        Route::post('/threads/{thread}/posts', [ForumPostController::class, 'store'])->name('posts.store');
        Route::resource('posts', ForumPostController::class)->only(['edit', 'update', 'destroy']);
        Route::post('/threads/{thread}/subscribe', [ForumThreadController::class, 'subscribe'])->name('threads.subscribe');
        Route::post('/threads/{thread}/unsubscribe', [ForumThreadController::class, 'unsubscribe'])->name('threads.unsubscribe');
    });

    /*
    |----------------------------
    | Notifications
    |----------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.all');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    /*
    |-----------------------------
    | Mentorship – Mentor Signup
    | (restricted to alumni with
    |  "create-mentorship" permission)
    |-----------------------------
    */
    Route::middleware(['auth', 'role:alumni', 'can:create-mentorship'])->group(function () {
        Route::get('/mentorship/create', [MentorshipController::class, 'create'])->name('mentorship.create');
        Route::post('/mentorship', [MentorshipController::class, 'store'])->name('mentorship.store');
    });

    /*
    |-----------------------------
    | Mentorship
    |-----------------------------
    */
    Route::prefix('mentorship')->name('mentorship.')->group(function () {
        Route::get('/', [MentorshipController::class, 'index'])->name('index');
        Route::get('/find', [MentorshipController::class, 'findMentors'])->name('find');

        // Static segments MUST be registered BEFORE GET /{mentorship},
        // otherwise "requests" is treated as the {mentorship} binding -> 404
        Route::get('/requests', [MentorshipController::class, 'myRequests'])->name('requests');
        Route::post('/requests/{mentorshipRequest}/respond', [MentorshipController::class, 'respondToRequest'])->name('respond-to-request');

        Route::get('/mentor/{mentor}', [MentorshipController::class, 'showMentor'])->name('show-mentor');
        Route::post('/request/{mentor}', [MentorshipController::class, 'sendRequest'])->name('send-request');

        Route::get('/{mentorship}', [MentorshipController::class, 'show'])->name('show');
        Route::put('/{mentorship}', [MentorshipController::class, 'update'])->name('update');
    });

    /*
    |-----------------------------
    | Directory
    |-----------------------------
    */
    Route::get('/directory', [UserSearchController::class, 'index'])->name('directory.index');
    Route::get('/directory/{user}', [UserSearchController::class, 'show'])->name('directory.show');

    /*
    |-----------------------------
    | Admin Routes
    |-----------------------------
    */
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals'])->name('pending-approvals');

        // Single approve/reject pair (POST) — the pending-approvals
        // view posts to both of these route names.
        Route::post('/approve-user/{user}', [AdminController::class, 'approveUser'])->name('approve-user');
        Route::post('/reject-user/{user}', [AdminController::class, 'rejectUser'])->name('reject-user');

        // Permissions
        Route::get('/permissions', [AdminController::class, 'permissions'])->name('permissions');
        Route::get('/permissions/{role}', [AdminController::class, 'getRolePermissions'])->name('permissions.get');
        Route::post('/permissions/update', [AdminController::class, 'updatePermissions'])->name('permissions.update');

        // User Management
        Route::get('/user-management', [AdminController::class, 'userManagement'])->name('user-management');
        Route::get('/user/create', [AdminController::class, 'createUser'])->name('user.create');
        Route::post('/user/store', [AdminController::class, 'storeUser'])->name('user.store');
        Route::post('/bulk-approval', [AdminController::class, 'bulkApproval'])->name('users.bulk-approval');
        Route::post('/users/bulk-action', [AdminController::class, 'bulkAction'])->name('users.bulk-action');
        Route::post('/user/{id}/restore', [AdminController::class, 'restoreUser'])->name('user.restore');
        Route::get('/user/{user}', [AdminController::class, 'showUser'])->name('user.show');
        Route::get('/user/{user}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::put('/user/{user}/update', [AdminController::class, 'updateUser'])->name('user.update');
        Route::delete('/user/{user}/delete', [AdminController::class, 'deleteUser'])->name('user.delete');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
        });
    });

    /*
    |-----------------------------
    | Alumni Routes
    |-----------------------------
    */
    Route::middleware(['role:alumni'])->prefix('alumni')->name('alumni.')->group(function () {
        Route::get('/dashboard', [AlumniController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AlumniController::class, 'editProfile'])->name('profile');
        Route::post('/profile/update', [AlumniController::class, 'updateProfile'])->name('profile.update');
    });

    /*
    |-----------------------------
    | Student Routes
    |-----------------------------
    */
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentController::class, 'editProfile'])->name('profile');
        Route::post('/profile/update', [StudentController::class, 'updateProfile'])->name('profile.update');
    });
});

// Breeze auth routes (login, register, logout, password reset,
// email verification, password confirmation — all of these live
// in auth.php and MUST NOT be duplicated inside the approved group)
require __DIR__.'/auth.php';