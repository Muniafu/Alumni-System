<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        // Allow all users to view events
        return $user->can('view-events');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event)
    {
        // Allow all users to view events
        return $user->can('view-events');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create-events');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event)
    {
        // Allow only the organizer or admin to update the event
        return $user->hasPermissionTo('edit-events') && ($user->hasRole('admin') || $event->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event)
    {
        // Allow only the organizer or admin to delete the event
        return $user->hasPermissionTo('delete-events') && ($user->hasRole('admin') || $event->user_id === $user->id);
    }

    /**
     * Determine whether the user can rsvp the model.
     */
    public function rsvp(User $user, Event $event)
    {
        return $user->hasPermissionTo('rsvp-events');
    }
}
