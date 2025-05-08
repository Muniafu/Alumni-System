<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('users');
            $table->foreignId('endorser_id')->constrained('users');
            $table->foreignId('skill_id')->constrained();
            $table->text('message');
            $table->timestamps();
            
            $table->index(['alumni_id', 'endorser_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
