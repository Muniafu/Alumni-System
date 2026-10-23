<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_the_first_registered_user_becomes_the_bootstrap_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'graduation_year' => (string) (date('Y') - 1),
            'program' => 'Computer Science',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue((bool) $user->is_approved);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_subsequent_users_register_unapproved_and_wait_for_approval(): void
    {
        User::factory()->create(); // ensure this is NOT the first user

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'graduation_year' => (string) (date('Y') - 1),
            'program' => 'Computer Science',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/pending-approval');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertFalse((bool) $user->is_approved);
        $this->assertTrue($user->hasRole('alumni'));
    }

    public function test_future_graduates_are_assigned_the_student_role(): void
    {
        User::factory()->create();

        $this->post('/register', [
            'name' => 'Future Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'graduation_year' => (string) (date('Y') + 4),
            'program' => 'Computer Science',
        ]);

        $user = User::where('email', 'student@example.com')->first();
        $this->assertTrue($user->hasRole('student'));
    }
}
