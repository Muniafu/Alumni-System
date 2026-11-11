<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Notifications\NewForumPostNotification;
use Illuminate\Support\Facades\Notification;

class ForumPostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ForumThread $thread)
    {
        $this->authorize('create', [ForumPost::class, $thread]);

        $request->validate([
            'content' => 'required|string|min:5',
        ]);

        $userId = auth()->id();

        $post = $thread->posts()->create([
            'user_id' => $userId,
            'content' => $request->content,
        ]);

        // Notify thread subscribers (excluding the post author)
        $subscribers = $thread->subscribers()
            ->where('user_id', '!=', $userId)
            ->get();

        Notification::send($subscribers, new NewForumPostNotification($post));

        // Also notify the thread author if they're not the one posting
        // and are not already covered as a subscriber
        if ($thread->user_id != $userId
            && $thread->author
            && !$subscribers->contains(fn ($subscriber) => $subscriber->id === $thread->user_id)) {
            $thread->author->notify(new NewForumPostNotification($post));
        }

        return redirect()->route('forum.threads.show', [
            'thread' => $thread,
            'page' => $thread->posts()->paginate(15)->lastPage(),
        ])->withFragment('post-' . $post->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ForumPost $post)
    {
        $this->authorize('update', $post);
        return view('forum.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ForumPost $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'content' => 'required|string|min:5',
        ]);

        $post->update([
            'content' => $request->content,
        ]);

        return redirect()->route('forum.threads.show', [
            'thread' => $post->thread,
            'page' => $post->thread->posts()->paginate(15)->lastPage(),
        ])->withFragment('post-' . $post->id)
          ->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ForumPost $post)
    {
        $this->authorize('delete', $post);

        $thread = $post->thread;
        $post->delete();

        return redirect()->route('forum.threads.show', $thread)
            ->with('success', 'Post deleted successfully');
    }
}
