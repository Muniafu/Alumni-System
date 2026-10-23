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
        if (!Schema::hasTable('job_applications')) {
            return;
        }

        $indexExists = collect(DB::select('SHOW INDEX FROM job_applications'))
            ->pluck('Key_name')
            ->contains('job_applications_job_user_unique');

        if ($indexExists) {
            return;
        }

        // Keep only the oldest application per (job_posting, user) pair —
        // otherwise existing duplicates would make the index creation fail.
        $duplicates = DB::table('job_applications')
            ->selectRaw('MIN(id) as keep_id, job_posting_id, user_id')
            ->groupBy('job_posting_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('job_applications')
                ->where('job_posting_id', $dup->job_posting_id)
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['job_posting_id', 'user_id'], 'job_applications_job_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_job_user_unique');
        });
    }
};
