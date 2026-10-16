<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    /*
    public function created(User $user)
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'User account created',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

    }
    */

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user)
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']); // Exclude timestamp from changes

        if (!empty($changes)) {
            return;
        }

        $actorId = auth()->id();

        if ($actorId || !user::where('id', $actorId)->exists()) {
            $actorId = $user->id;
        }
            ActivityLog::create([
                'user_id' => $actorId, // Use the user's ID if no authenticated user
                'action' => 'updated',
                'description' => 'User account updated: ' . json_encode($changes),
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'description' => 'User account deleted',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'restored',
            'description' => 'User account restored',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }
}
