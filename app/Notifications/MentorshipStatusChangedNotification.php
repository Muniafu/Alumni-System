<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Mentorship;
use App\Models\MentorshipRequest;

class MentorshipStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $model;
    public $status;

    /**
     * Accept either a Mentorship or MentorshipRequest model.
     */
    public function __construct($model, string $status)
    {
        if (!($model instanceof Mentorship || $model instanceof MentorshipRequest)) {
            throw new \InvalidArgumentException('Model must be Mentorship or MentorshipRequest');
        }

        $this->model = $model;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {

        $counterpart = $notifiable->id === $this->model->mentor_id ? $this->model->mentee->name : $this->model->mentor->name;

        // ✅ Handle MentorshipRequest (rejected case)
        if ($this->model instanceof MentorshipRequest) {
            return [
                'request_id' => $this->model->id,
                'status' => $this->status,
                'message' => "Your mentorship request to {$this->model->mentor->name} was {$this->status}.",
                'url' => route('mentorship.requests'),
                'type' => 'mentorship_request_status_changed'
            ];
        }

        // ✅ Handle Mentorship (active, completed, cancelled)
        if ($this->model instanceof Mentorship) {
            $message = $this->status === 'completed'
                ? "Your mentorship with {$this->model->mentor->name} has been completed"
                : "Your mentorship with {$this->model->mentor->name} has been {$this->status}";

            if ($notifiable->id === $this->model->mentor_id) {
                $message = $this->status === 'completed'
                    ? "Your mentorship with {$this->model->mentee->name} has been completed"
                    : "Your mentorship with {$this->model->mentee->name} has been {$this->status}";
            }

            return [
                'mentorship_id' => $this->model->id,
                'status' => $this->status,
                'message' => "Mentorship status changed: {$this->status}",
                'url' => route('mentorship.show', $this->model),
                'type' => 'mentorship_status_changed'
            ];
        }
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
