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
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->text('goal')->nullable()->after('message');
        });

        Schema::table('mentorships', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->dropColumn('goal');
        });
    }
};
