<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        // This block queries the DB on EVERY artisan command — including
        // composer's package:discover — which crashed the whole toolchain
        // while MySQL was down. Roles are already ensured by
        // RolesAndPermissionsSeeder, so tolerate DB failures here.
        try {
            if (Schema::hasTable('roles')) {
                foreach (['admin', 'alumni', 'student'] as $roleName) {
                    Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                }
            }
        } catch (\Throwable $e) {
            // DB unreachable (MySQL down, fresh install, composer scripts) —
            // seeding creates the roles; never kill artisan/composer.
        }
    }
}
