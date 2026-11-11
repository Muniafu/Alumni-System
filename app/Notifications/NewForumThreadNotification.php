<?php

namespace App\Notifications;

use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewForumThreadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $thread;

    public function __construct(ForumThread $thread)
    {
        $this->thread = $thread;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // database + email
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Forum Thread: ' . $this->thread->title)
            ->line($this->thread->author->name . ' started a new thread.')
            ->action('View Thread', route('forum.threads.show', $this->thread));
    }

    public function toArray($notifiable)
    {
        return [
            'thread_id' => $this->thread->id,
            'title' => $this->thread->title,
            'author' => $this->thread->author->name,
        ];
    }
}
