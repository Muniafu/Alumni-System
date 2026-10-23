<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;

class EventCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Event Created: ' . $this->event->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("A new event has been created: **{$this->event->title}**")
            ->line("📅 Date: " . $this->event->start->format('d M, Y'))
            ->line("📍 Location: " . $this->event->location)
            ->action('View Event', route('events.show', $this->event->id))
            ->line('Stay connected and join in!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'date' => $this->event->start->format('d M, Y'),
            'location' => $this->event->location,
            'message' => "New event created: {$this->event->title}",
            'url' => route('events.show', $this->event->id),
            'type' => 'event_created'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
