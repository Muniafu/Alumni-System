<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\JobPosting;

class NewJobPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $job;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobPosting $job)
    {
        $this->job = $job;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast']; // add 'mail' once the mailer is configured
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Job Posted: {$this->job->title}")
            ->line("{$this->job->poster->name} has posted a new {$this->job->type}.")
            ->line("Company: {$this->job->company}")
            ->line("Location: {$this->job->location}")
            ->action('View Job Posting', route('jobs.show', $this->job->id));
    }

    /**
     * Get the array representation of the notification
     * (used by both the database and broadcast channels).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_id'  => $this->job->id,
            'title'   => $this->job->title,
            'message' => "New job posted: {$this->job->title} by {$this->job->poster->name}",
            'url'     => route('jobs.show', $this->job->id),
            'type'    => 'new_job_posted'
        ];
    }

    /**
     * Broadcast in real-time (Laravel Echo / Pusher).
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
