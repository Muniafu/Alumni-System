<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    /** Roles have exactly the permissions the MATRIX says — no more, no less. */
    public function test_roles_have_exactly_the_matrixed_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        foreach (RolesAndPermissionsSeeder::MATRIX as $permission => $roles) {
            foreach (['admin', 'alumni', 'student'] as $roleName) {
                $granted = Role::findByName($roleName, 'web')
                    ->getAllPermissions()
                    ->pluck('name')
                    ->contains($permission);

                $expected = in_array($roleName, $roles, true);

                $this->assertSame($expected, $granted,
                    "Permission [{$permission}] is " . ($expected ? 'missing' : 'unexpectedly granted') . " on role [{$roleName}]."
                );
            }
        }
    }

    /** The old space-named / stale permissions can never come back. */
    public function test_no_stale_or_space_named_permissions_exist(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $names = Permission::where('guard_name', 'web')->pluck('name');

        $this->assertSame(count(RolesAndPermissionsSeeder::MATRIX), $names->count());
        $names->each(fn ($name) => $this->assertStringNotContainsString(' ', $name));
    }

    /** Gates actually resolve for real users with seeded roles. */
    public function test_gates_resolve_for_seeded_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $make = function (string $role): User {
            $user = User::factory()->create();
            $user->forceFill(['is_approved' => true])->save();
            $user->assignRole($role);

            return $user;
        };

        $admin = $make('admin');
        $alumni = $make('alumni');
        $student = $make('student');

        $this->assertTrue($admin->can('access-admin-dashboard'));
        $this->assertTrue($alumni->can('create-jobs'));
        $this->assertTrue($student->can('rsvp-events'));

        $this->assertFalse($student->can('create-jobs'));
        $this->assertFalse($alumni->can('approve-users'));
        $this->assertFalse($admin->can('no-such-ability'));
    }
}
