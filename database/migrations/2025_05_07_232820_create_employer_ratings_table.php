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
        Schema::create('employer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users');
            $table->foreignId('alumni_id')->constrained('users');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
            
            $table->unique(['employer_id', 'alumni_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_ratings');
    }
};
