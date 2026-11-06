<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for thread subscriptions.
     *
     * The foreign key is forum_thread_id, matching the Laravel default
     * pivot key used by ForumThread::subscribers()
     * (belongsToMany(User::class, 'forum_thread_subscriptions')).
     */
    public function up(): void
    {
        Schema::create('forum_thread_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A user can only subscribe to a thread once
            $table->unique(['forum_thread_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_thread_subscriptions');
    }
};
