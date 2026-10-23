<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Single source of truth for the role x permission matrix.
     *
     * RULES:
     * 1. Permission names are HYPHENATED and must match every
     *    Gate::authorize() / can() call in the controllers EXACTLY.
     *    (The old seeder used space names — every gate silently denied.)
     * 2. Re-running this seeder is safe: syncPermissions() makes each
     *    role end up with exactly this matrix, no more, no less.
     * 3. Legacy space-named permissions are deleted so stale grants
     *    cannot linger in the DB.
     */
    public const MATRIX = [
        // ---- Access ----
        'access-admin-dashboard'    => ['admin'],
        'access-alumni-dashboard'   => ['alumni'],
        'access-student-dashboard'  => ['student'],
        'edit-profile'             => ['admin', 'alumni', 'student'],

        // ---- Admin only ----
        'view-pending-approvals'    => ['admin'],
        'approve-users'            => ['admin'],
        'manage-users'             => ['admin'],
        'manage-permissions'       => ['admin'],
        'generate-reports'         => ['admin'],

        // ---- Jobs ----
        'view-jobs'                => ['admin', 'alumni', 'student'],
        'create-jobs'              => ['admin', 'alumni'],
        'edit-jobs'                => ['admin', 'alumni'],
        'delete-jobs'              => ['admin', 'alumni'],
        'apply-jobs'               => ['admin', 'alumni', 'student'],
        'view-job-applications'    => ['admin'],
        'manage-job-applications'  => ['admin'],

        // ---- Events ----
        'view-events'              => ['admin', 'alumni', 'student'],
        'create-events'            => ['admin', 'alumni'],
        'edit-events'              => ['admin', 'alumni'],
        'delete-events'            => ['admin', 'alumni'],
        'rsvp-events'              => ['admin', 'alumni', 'student'],

        // ---- Forums ----
        'view-forums'              => ['admin', 'alumni', 'student'],
        'create-forums'            => ['admin', 'alumni', 'student'],

        // ---- Mentorship ----
        'view-mentorship'          => ['admin', 'alumni', 'student'],
        'create-mentorship'        => ['admin', 'alumni'],
        'request-mentorship'       => ['admin', 'alumni', 'student'],
    ];

    public function run(): void
    {
        $registrar = app()[PermissionRegistrar::class];
        $registrar->forgetCachedPermissions();

        foreach (['admin', 'alumni', 'student'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Delete any permission not in the matrix (removes the old
        // space-named set: "view admin dashboard", "approve users", ...).
        Permission::where('guard_name', 'web')
            ->whereNotIn('name', array_keys(self::MATRIX))
            ->delete();

        $byRole = [];

        foreach (self::MATRIX as $permission => $roles) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

            foreach ($roles as $role) {
                $byRole[$role][] = $permission;
            }
        }

        // Deterministic: re-running always converges to exactly MATRIX.
        foreach ($byRole as $roleName => $permissions) {
            Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first()
                ->syncPermissions($permissions);
        }

        $registrar->forgetCachedPermissions();
    }
}
