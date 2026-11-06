<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Demo accounts for every state the platform must handle:
     * approved admin / alumni / student, plus one PENDING user so the
     * admin approval flow (Phase 1) is demoable right after seeding.
     *
     * Every account is keyed on email (firstOrCreate), so re-running
     * `php artisan db:seed` never crashes on duplicates and never
     * resets passwords that were changed during testing... it DOES
     * re-force approval, which is what we want for demo data.
     */
    public function run(): void
    {
        // assignRole() throws if the roles table is empty (standalone run)
        if (! Role::where('name', 'admin')->where('guard_name', 'web')->exists()) {
            $this->call(RolesAndPermissionsSeeder::class);
        }

        // is_approved / approved_at / email_verified_at are NOT mass-
        // assignable (Phase 0 hardening). The old seeder passed
        // is_approved to User::create() and mass assignment silently
        // dropped it — every demo user was created UNAPPROVED. Approve
        // via forceFill, exactly like the real approval flow does.
        $approved = function (string $email, array $attributes): User {
            $user = User::firstOrCreate(['email' => $email], $attributes);

            $user->forceFill([
                'is_approved' => true,
                'approved_at' => now(),
                'email_verified_at' => now(),
            ])->save();

            return $user->refresh();
        };

        $admin = $approved('admin@alumni.edu', [
            'name' => 'Admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $alumni = $approved('alumni@alumni.edu', [
            'name' => 'Alumni User',
            'password' => Hash::make('password'),
            'student_id' => 'AL12345',
            'graduation_year' => '2020',
            'program' => 'Computer Science',
        ]);
        $alumni->assignRole('alumni');

        $student = $approved('student@alumni.edu', [
            'name' => 'Student User',
            'password' => Hash::make('password'),
            'student_id' => 'ST67890',
            'graduation_year' => '2025',
            'program' => 'Business Administration',
        ]);
        $student->assignRole('student');

        // One PENDING user: approve (or reject) her from the admin panel
        // to demo the whole approval pipeline end to end.
        $pending = User::firstOrCreate(['email' => 'pending@alumni.edu'], [
            'name' => 'Pending User',
            'password' => Hash::make('password'),
            'student_id' => 'ST24680',
            'graduation_year' => '2026',
            'program' => 'Information Technology',
        ]);
        $pending->forceFill([
            'is_approved' => false,
            'email_verified_at' => now(),
        ])->save();
        $pending->assignRole('student');
    }
}
