<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Registration Request')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your registration has been rejected.')
            ->line('If you believe this is an error, please contact support.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rejection',
            'message' => 'Your registration request was not approved.',
            'url' => url('/'),
        ];
    }
}
