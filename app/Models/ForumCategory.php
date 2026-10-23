<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'order'];

    public function threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    // Posts through threads
    public function posts()
    {
        return $this->hasManyThrough(
            ForumPost::class,
            ForumThread::class,
            'forum_category_id', // Foreign key on threads table
            'forum_thread_id',   // Foreign key on posts table
            'id',                // Local key on categories table
            'id'                 // Local key on threads table
        );
    }

    public function latestThread()
    {
        return $this->hasOne(ForumThread::class)->latestOfMany();
    }

    public function getThreadCountAttribute()
    {
        return $this->threads()->count();
    }

    public function getPostCountAttribute()
    {
        return $this->posts()->count();
    }
}
