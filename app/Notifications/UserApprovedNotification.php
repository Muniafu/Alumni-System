<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Mail + in-app: the user sees the approval in the notification
     * center even if mail delivery is slow or unconfigured.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We are pleased to inform you that your account has been approved.')
            ->action('Login Now', url('/login'))
            ->line('Welcome aboard!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval',
            'message' => 'Your account has been approved. Welcome aboard!',
            'url' => url('/dashboard'),
        ];
    }
}
