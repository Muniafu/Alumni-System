<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Message;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Database-only for now. The broadcast channel was dropped until a
     * websockets driver (Pusher/Reverb) is actually enabled — BROADCAST_DRIVER
     * is null/log, so broadcasting went nowhere and only added noise.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_name' => $this->message->sender->name,
            'message' => "New message from {$this->message->sender->name}",
            'body' => Str::limit($this->message->body, 50),
            'url' => route('conversations.show', $this->message->conversation_id),
            'type' => 'new_message'
        ];
    }
}
