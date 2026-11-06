<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    private ForumCategory $category;

    protected function setUp(): void
    {

        parent::setUp();

        // Only seed if the global test setup has not already done it
        if (! Role::where('name', 'admin')->exists()) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }

        $this->category = ForumCategory::create([
            'name' => 'General',
            'slug' => 'general',
            'description' => 'General discussion',
            'order' => 1,
        ]);
    }

    //
    // Helpers
    //

    private function createUser(bool $approved = true): User
    {
        $user = User::factory()->create();

        // is_approved is not mass-assignable (Phase 0 hardening)
        $user->forceFill([
            'is_approved' => $approved,
            'email_verified_at' => now(),
        ])->save();

        return $user->refresh();
    }

    private function createThread(User $author, array $attributes = []): ForumThread
    {
        return ForumThread::create(array_merge([
            'forum_category_id' => $this->category->id,
            'user_id' => $author->id,
            'title' => 'A discussion worth having',
            'slug' => 'a-discussion-worth-having',
            'content' => 'The opening post of this thread.',
        ], $attributes));
    }

    private function subscriptionExists(ForumThread $thread, User $user): bool
    {
        return DB::table('forum_thread_subscriptions')
            ->where('forum_thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    //
    // Browsing
    //

    /** @test */
    public function category_page_lists_threads(): void
    {
        $author = $this->createUser();
        $thread = $this->createThread($author);
        $this->createThread($author, [
            'title' => 'Pinned topic',
            'slug' => 'pinned-topic',
            'is_pinned' => true,
        ]);

        $response = $this->actingAs($author)
            ->get(route('forum.categories.show', $this->category));

        $response->assertOk()
            ->assertSee($thread->title)
            ->assertSee('Pinned topic');
    }

    /** @test */
    public function replies_are_visible_on_the_thread_page(): void
    {
        $author = $this->createUser();
        $visitor = $this->createUser();
        $thread = $this->createThread($author);
        $thread->posts()->create([
            'user_id' => $author->id,
            'content' => 'A visible reply for testing.',
        ]);

        $this->actingAs($visitor)
            ->get(route('forum.threads.show', $thread))
            ->assertOk()
            ->assertSee('A visible reply for testing.');
    }

    //
    // Thread creation
    //

    /** @test */
    public function guests_cannot_create_threads(): void
    {
        $response = $this->post(route('forum.threads.store', $this->category), [
            'title' => 'Drive-by thread',
            'content' => 'Posted without an account.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('forum_threads', 0);
    }

    /** @test */
    public function unapproved_users_cannot_create_threads(): void
    {
        $user = $this->createUser(approved: false);

        $response = $this->actingAs($user)
            ->post(route('forum.threads.store', $this->category), [
                'title' => 'Not approved yet',
                'content' => 'This should not go through.',
            ]);

        // Either the approved middleware redirects to the pending page,
        // or the policy denies with 403 - either way nothing is created.
        $this->assertContains($response->status(), [302, 403]);
        $this->assertDatabaseCount('forum_threads', 0);
    }

    /** @test */
    public function approved_students_can_create_threads(): void
    {
        $student = $this->createUser();
        $student->assignRole('student');

        $response = $this->actingAs($student)
            ->post(route('forum.threads.store', $this->category), [
                'title' => 'Life after graduation',
                'content' => 'How do you all stay in touch with classmates?',
            ]);

        $response->assertRedirect();

        $thread = ForumThread::first();
        $this->assertNotNull($thread);
        $this->assertEquals('Life after graduation', $thread->title);
        $this->assertEquals($student->id, $thread->user_id);
        $this->assertEquals('life-after-graduation', $thread->slug);

        // Authors are auto-subscribed to their own thread
        $this->assertTrue($this->subscriptionExists($thread, $student));
    }

    //
    // Replies
    //

    /** @test */
    public function approved_users_can_reply_to_threads(): void
    {
        $author = $this->createUser();
        $replier = $this->createUser();
        $thread = $this->createThread($author);

        $response = $this->actingAs($replier)
            ->post(route('forum.posts.store', $thread), [
                'content' => 'This is my reply to your thread.',
            ]);

        $response->assertRedirect();

        $post = ForumPost::first();
        $this->assertNotNull($post);
        $this->assertEquals($replier->id, $post->user_id);
        $this->assertEquals($thread->id, $post->forum_thread_id);
    }

    /** @test */
    public function locked_threads_block_replies(): void
    {
        $author = $this->createUser();
        $thread = $this->createThread($author);
        $thread->update(['is_locked' => true]);

        $replier = $this->createUser();

        $response = $this->actingAs($replier)
            ->post(route('forum.posts.store', $thread), [
                'content' => 'Replying to a locked thread.',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('forum_posts', 0);
    }

    //
    // Thread edit permissions (author-only + admin)
    //

    /** @test */
    public function thread_authors_can_edit_their_own_threads(): void
    {
        $author = $this->createUser();
        $thread = $this->createThread($author);

        $this->actingAs($author)
            ->get(route('forum.threads.edit', $thread))
            ->assertOk();

        $this->actingAs($author)
            ->put(route('forum.threads.update', $thread), [
                'title' => 'A discussion worth having, updated',
                'content' => 'The updated opening post of this thread.',
            ])
            ->assertRedirect();

        $this->assertEquals('A discussion worth having, updated', $thread->fresh()->title);
        $this->assertEquals('a-discussion-worth-having-updated', $thread->fresh()->slug);
    }

    /** @test */
    public function other_users_cannot_edit_someone_elses_thread(): void
    {
        $author = $this->createUser();
        $other = $this->createUser();
        $thread = $this->createThread($author);

        $this->actingAs($other)
            ->get(route('forum.threads.edit', $thread))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('forum.threads.update', $thread), [
                'title' => 'Hijacked title',
                'content' => 'This update must be rejected.',
            ])
            ->assertForbidden();

        $this->assertEquals('A discussion worth having', $thread->fresh()->title);
    }

    /** @test */
    public function admins_can_edit_any_thread(): void
    {
        $author = $this->createUser();
        $admin = $this->createUser();
        $admin->assignRole('admin');

        $thread = $this->createThread($author);

        $this->actingAs($admin)
            ->put(route('forum.threads.update', $thread), [
                'title' => 'Cleaned up by moderation',
                'content' => 'Moderators may edit every thread.',
            ])
            ->assertRedirect();

        $this->assertEquals('Cleaned up by moderation', $thread->fresh()->title);
    }

    //
    // Post edit permissions (author-only)
    //

    /** @test */
    public function post_authors_can_edit_their_own_posts(): void
    {
        $author = $this->createUser();
        $replier = $this->createUser();
        $thread = $this->createThread($author);
        $post = $thread->posts()->create([
            'user_id' => $replier->id,
            'content' => 'Original reply content.',
        ]);

        $this->actingAs($replier)
            ->put(route('forum.posts.update', $post), [
                'content' => 'Edited reply content.',
            ])
            ->assertRedirect();

        $this->assertEquals('Edited reply content.', $post->fresh()->content);
    }

    /** @test */
    public function users_cannot_edit_someone_elses_post(): void
    {
        $author = $this->createUser();
        $other = $this->createUser();
        $thread = $this->createThread($author);
        $post = $thread->posts()->create([
            'user_id' => $author->id,
            'content' => 'A reply that belongs to the author.',
        ]);

        $this->actingAs($other)
            ->put(route('forum.posts.update', $post), [
                'content' => 'Tampered content.',
            ])
            ->assertForbidden();

        $this->assertEquals('A reply that belongs to the author.', $post->fresh()->content);
    }

    //
    // Subscriptions
    //

    /** @test */
    public function users_can_subscribe_and_unsubscribe(): void
    {
        
        $author = $this->createUser();
        $watcher = $this->createUser();
        $thread = $this->createThread($author);

        $this->actingAs($watcher)
            ->post(route('forum.threads.subscribe', $thread))
            ->assertRedirect();

        $this->assertTrue($this->subscriptionExists($thread, $watcher));

        $this->actingAs($watcher)
            ->post(route('forum.threads.unsubscribe', $thread))
            ->assertRedirect();

        $this->assertFalse($this->subscriptionExists($thread, $watcher));
    }

    //
    // Deletion behaviour
    //

    /** @test */
    public function deleting_the_last_post_deletes_the_thread(): void
    {
        $author = $this->createUser();
        $thread = $this->createThread($author);
        $post = $thread->posts()->create([
            'user_id' => $author->id,
            'content' => 'The only reply here.',
        ]);

        $this->actingAs($author)
            ->delete(route('forum.posts.destroy', $post))
            ->assertRedirect();

        // ForumPost uses SoftDeletes: the row stays in the table
        $this->assertSoftDeleted($post);
        // Soft-deleted threads must not resurface
        $this->assertNull(ForumThread::find($thread->id));
    }
}
