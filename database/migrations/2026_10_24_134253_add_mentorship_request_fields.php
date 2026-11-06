<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mentor's rejection/response message — kept separate from the
        // mentee's original request message, which was previously
        // overwritten on rejection.
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->text('response_message')->nullable()->after('message');
        });

        // Links a Mentorship to the request that created it, so
        // MentorshipRequest->mentorship() works.
        Schema::table('mentorships', function (Blueprint $table) {
            $table->foreignId('mentorship_request_id')
                ->nullable()
                ->after('mentee_id')
                ->constrained('mentorship_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mentorship_request_id');
        });

        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->dropColumn('response_message');
        });
    }
};
