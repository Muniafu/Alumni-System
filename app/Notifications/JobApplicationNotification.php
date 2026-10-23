<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\JobApplication;

class JobApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The job application instance.
     *
     * @var JobApplication
     */
    public $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("New Application for {$this->application->jobPosting->title}")
                    ->line("{$this->application->applicant->name} has applied.")
                    ->action('View Applications', route('jobs.applications', $this->application->job_posting_id));
    }

    /**
     * Get the array representation of the notification (database + broadcast).
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'job_id' => $this->application->job_posting_id,
            'student_name' => $this->application->applicant->name,
            'message' => "{$this->application->applicant->name} applied to your job posting",
            'url' => route('jobs.applications', $this->application->job_posting_id),
            'type' => 'job_application'
        ];
    }

    /**
     * Broadcast in real-time (Laravel Echo / Pusher).
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
