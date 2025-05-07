<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'category_id',
        'pinned',
        'locked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}