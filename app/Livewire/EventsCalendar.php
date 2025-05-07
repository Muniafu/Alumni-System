<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;

class EventsCalendar extends Component
{
    public $events = [];

    protected $listeners = ['eventAdded' => 'refreshEvents'];

    public function mount()
    {
        $this->refreshEvents();
    }

    public function refreshEvents()
    {
        $this->events = Event::all()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_time->toIso8601String(),
                'end' => $event->end_time->toIso8601String(),
                'location' => $event->location,
                'url' => route('events.show', $event->id),
                'color' => $this->getEventColor($event->event_type),
                'extendedProps' => [
                    'description' => $event->description,
                    'is_online' => $event->is_online,
                ]
            ];
        });
    }

    protected function getEventColor($type)
    {
        return match($type) {
            'webinar' => '#3b82f6',
            'conference' => '#10b981',
            'meetup' => '#f59e0b',
            default => '#6b7280',
        };
    }

    public function render()
    {
        return view('livewire.events-calendar');
    }
}