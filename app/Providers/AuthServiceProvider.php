<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPostingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * NOTE: PHP array literals silently keep the LAST duplicate key. This
     * array previously contained "User::class => UserPolicy::class"
     * followed by "User::class => ReportPolicy::class", so every User
     * policy check was silently routed to ReportPolicy. ReportPolicy is
     * deliberately unmapped here — wire it to a real model in Phase 9
     * if reports need one.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Event::class => EventPolicy::class,
        JobPosting::class => JobPostingPolicy::class,
        JobApplication::class => JobApplicationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        // Role-based gates
        Gate::define('access-admin-dashboard', function ($user) {
            return $user->hasRole('admin');
        });
        Gate::define('access-alumni-dashboard', function ($user) {
            return $user->hasRole('alumni');
        });
        Gate::define('access-student-dashboard', function ($user) {
            return $user->hasRole('student');
        });
        Gate::define('manage-users', function ($user) {
            return $user->hasRole('admin');
        });
        Gate::define('approve-users', function ($user) {
            return $user->hasRole('admin');
        });
        Gate::define('edit-profile', function ($user, $targetUser = null) {
            return $user->id === $targetUser?->id || $user->hasRole('admin');
        });
        Gate::define('view-pending-approvals', function ($user) {
            return $user->hasRole('admin');
        });
        Gate::define('manage-permissions', function ($user) {
            return $user->hasRole('admin');
        });
        Gate::define('generate-reports', function ($user) {
            return $user->hasRole('admin');
        });

        // Job gates
        Gate::define('create-jobs', function ($user) {
            return $user->hasRole('alumni') || $user->hasRole('admin');
        });
        Gate::define('viewApplications', function ($user, $job) {
            return $user->id === $job->user_id || $user->hasRole('admin');
        });

        // Event gates
        Gate::define('view-events', function ($user) {
            return true; // Public view
        });
        Gate::define('create-events', function ($user) {
            return $user->hasRole('alumni') || $user->hasRole('admin');
        });
        Gate::define('rsvp-events', function ($user) {
            return $user->hasRole('student') || $user->hasRole('alumni');
        });

        // Forum/Mentorship (simple)
        Gate::define('create-forums', function ($user) {
            return $user->hasRole('alumni');
        });
        Gate::define('view-forums', function ($user) {
            return true;
        });
        Gate::define('create-mentorship', function ($user) {
            return $user->hasRole('alumni');
        });
        Gate::define('view-mentorship', function ($user) {
            return true;
        });
    }
}
