<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProfileCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine the notification delivery channels.
     */
    public function via($notifiable): array
    {
        // Send via email and save to database
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Profile Completed!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Congratulations! You have completed your profile.')
            ->line('A complete profile increases your chances to connect with alumni and students.')
            ->action('View Profile', url(route('profile.edit')))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'message' => 'Your profile is now 100% complete!',
            'action_url' => route('profile.edit'),
            'completed_at' => now(),
        ];
    }
}
