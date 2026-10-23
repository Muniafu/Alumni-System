<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function approvedUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_approved' => true])->save();

        return $user;
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->approvedUser();

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->approvedUser();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile/edit');

        $this->assertSame('Test User', $user->refresh()->name);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = $this->approvedUser();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile/edit');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $this->withoutExceptionHandling();
        
        $user = $this->approvedUser();

        $response = $this->actingAs($user)
            ->from('/profile/edit')
            ->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->approvedUser();

        $response = $this->actingAs($user)
            ->from('/profile/edit')
            ->delete('/profile', ['password' => 'wrong-password']);

        $response->assertSessionHasErrorsIn('userDeletion', 'password')->assertRedirect('/profile/edit');

        $this->assertNotNull($user->fresh());
    }
}
