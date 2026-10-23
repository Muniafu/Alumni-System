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
        if (Schema::hasTable('job_postings')) {
            return; // dev DB already has it — restored for fresh deploys
        }

        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description');
            $table->string('type')->default('job'); // job | internship | mentorship
            $table->string('company');
            $table->string('location');
            $table->boolean('is_remote')->default(false);
            $table->string('salary_range')->nullable();
            $table->string('employment_type')->nullable();
            $table->timestamp('application_deadline')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->text('skills_required')->nullable();
            $table->text('skills_preferred')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
