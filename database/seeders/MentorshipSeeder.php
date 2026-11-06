<?php

namespace Database\Seeders;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MentorshipSeeder extends Seeder
{
    public function run(): void
    {
        $alumni = User::where('email', 'alumni@alumni.edu')->first();
        $student = User::where('email', 'student@alumni.edu')->first();

        if (! $alumni || ! $student) {
            $this->command?->warn('MentorshipSeeder: demo users not found — run AdminUserSeeder first.');
            return;
        }

        // Match ONLY on the (mentor, mentee) pair — the old seeder put
        // start_date = now() inside the match array, which never matches
        // on a re-run and duplicated the mentorship every time.
        Mentorship::firstOrCreate(
            ['mentor_id' => $alumni->id, 'mentee_id' => $student->id],
            [
                'status' => 'active',
                'goal' => 'Career guidance in tech industry',
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ]
        );

        // Phase 7 moved mentor signup to mentor_profiles, and "Find
        // Mentors" lists only alumni with a mentor_profiles row —
        // without this, the demo showed no available mentors. Schema-
        // guarded: skipped cleanly if the table/columns differ.
        if (Schema::hasTable('mentor_profiles')) {
            $fk = Schema::hasColumn('mentor_profiles', 'user_id') ? 'user_id'
                : (Schema::hasColumn('mentor_profiles', 'mentor_id') ? 'mentor_id' : null);

            if ($fk) {
                $values = [
                    'expertise' => 'Software Engineering, Career Growth & Interview Prep',
                    'bio' => 'Backend developer with 5 years of industry experience. Happy to help students break into tech.',
                ];

                if (Schema::hasColumn('mentor_profiles', 'created_at')) {
                    $values['created_at'] = now();
                    $values['updated_at'] = now();
                }

                DB::table('mentor_profiles')->updateOrInsert([$fk => $alumni->id], $values);
            }
        }
    }
}
