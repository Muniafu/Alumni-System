<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['subject'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function markAsReadForUser($userId)
    {
        $this->users()->updateExistingPivot($userId, [
            'last_read_at' => now()
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('users', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Other participants: everyone except the current user.
     * Reads the (eager-loaded) users collection.
     */
    public function getOtherUsersAttribute()
    {
        return $this->users->where('id', '!=', auth()->id());
    }

    /**
     * Whether the current user has unread messages in this conversation.
     * Null-guarded: never crashes if the pivot row is somehow missing.
     */
    public function getIsUnreadAttribute()
    {
        $participant = $this->users->find(auth()->id());

        if (! $participant) {
            return false;
        }

        $lastRead = $participant->pivot->last_read_at;

        if ($lastRead) {
            return (bool) ($this->latestMessage && $this->latestMessage->created_at->gt($lastRead));
        }

        return (bool) $this->latestMessage;
    }
}
