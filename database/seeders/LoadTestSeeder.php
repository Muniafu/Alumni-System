<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * DEV-ONLY load-test data. Never add this to DatabaseSeeder — it would
 * slow the test suite and pollute every fresh seed. Run it standalone:
 *
 *   php artisan db:seed --class=LoadTestSeeder
 *
 * Everything it creates is keyed so it can be removed surgically:
 *   - users:  *@loadtest.alumni.edu
 *   - events: title starts with "LT-"
 *   - jobs:   title starts with "LT-", company "LT Company N"
 *   - forum:  slug "lt-thread-N", title starts with "LT-"
 *   - mentorships: goal "Load-test mentorship goal"
 *
 * RESUMABLE: every step checks for its own data first, so a re-run after
 * a partial failure fills only the missing pieces.
 *
 * ENUM-SAFE: status values are read from the actual column definition
 * (SHOW COLUMNS) and filtered to what the schema allows — the first run
 * crashed because mentorships.status is an ENUM without 'pending'.
 *
 * To reset ALL data instead: php artisan migrate:fresh --seed
 */
class LoadTestSeeder extends Seeder
{
    // ---- Tunables: raise/lower to taste -------------------------------
    protected int $alumniCount        = 150;
    protected int $studentCount       = 200;
    protected int $pendingCount       = 25;   // fills the approval queue
    protected int $eventCount         = 20;
    protected int $jobCount           = 30;
    protected int $threadCount        = 40;
    protected int $maxPostsPerThread  = 8;
    protected int $mentorshipPairs    = 30;
    protected int $mentorProfileCount = 40;  // floods "Find Mentors" for pagination

    protected string $emailDomain = 'loadtest.alumni.edu';
    protected string $prefix      = 'LT-';

    protected const MENTORSHIP_GOAL = 'Load-test mentorship goal';

    public function run(): void
    {
        if (! app()->environment(['local', 'development'])) {
            $this->command?->warn('LoadTestSeeder: dev-only, aborting.');
            return;
        }

        $t0  = microtime(true);
        $now = now();

        if (User::where('email', 'like', '%@' . $this->emailDomain)->exists()) {
            $this->command?->info('LoadTestSeeder: load-test users found — resuming missing steps only.');
            [$alumniIds, $studentIds, $participantIds] = $this->loadIds();
            $this->ensureRoles();
        } else {
            $hash = Hash::make('password'); // ONE bcrypt per run, reused by all accounts
            [$alumniIds, $studentIds, $participantIds] = $this->seedUsers($hash, $now);
        }

        $this->seedEvents($participantIds, $now);
        $this->seedRsvps($now);
        $this->seedJobs($alumniIds, $now);
        $this->seedApplications($participantIds, $now);
        $this->seedForum($participantIds, $now);
        $this->seedMentorship($alumniIds, $studentIds, $now);

        // Bulk pivot inserts bypass Spatie's cache — flush it.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(sprintf(
            'LoadTestSeeder done in %.1fs: %d alumni, %d students (%d pending), %d events, %d jobs, %d threads.',
            microtime(true) - $t0,
            $this->alumniCount, $this->studentCount, $this->pendingCount,
            $this->eventCount, $this->jobCount, $this->threadCount
        ));
    }

    // -----------------------------------------------------------------
    // Enum helpers — never guess status values again.
    // -----------------------------------------------------------------

    /** Allowed values of a MySQL ENUM column, or null if it is not an enum. */
    protected function enumValues(string $table, string $column): ?array
    {
        $row = DB::selectOne("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);
        $type = $row->Type ?? null;

        if ($type && preg_match('/^enum\((.*)\)$/i', $type, $m)) {
            return array_map(fn ($v) => trim($v, "'"), str_getcsv($m[1]));
        }

        return null; // plain varchar — any string is accepted
    }

    /**
     * Pick the usable statuses for a table: the preferred list filtered
     * down to what the enum actually allows. Falls back to the first
     * preferred value for non-enum columns.
     */
    protected function pickStatuses(string $table, array $preferred): array
    {
        $values = $this->enumValues($table, 'status');

        if ($values === null) {
            return [$preferred[0]];
        }

        $usable = array_values(array_intersect($preferred, $values));

        return $usable ?: [$values[0]];
    }

    // -----------------------------------------------------------------

    protected function loadIds(): array
    {
        $participantIds = User::where('email', 'like', '%@' . $this->emailDomain)
            ->where('is_approved', 1)->pluck('id')->all();

        $alumniIds = User::where('email', 'like', 'lt.alumni.%')
            ->where('is_approved', 1)->pluck('id')->all();

        $studentIds = User::where('email', 'like', 'lt.student.%')
            ->where('is_approved', 1)->pluck('id')->all();

        return [$alumniIds, $studentIds, $participantIds];
    }

    protected function seedUsers(string $hash, $now): array
    {
        $first = ['Amina','Baraka','Chebet','David','Esther','Faith','Gideon','Halima','Ian','Joy',
                  'Kamau','Lucy','Mwangi','Naomi','Otieno','Peter','Ruth','Samuel','Teresia','Wanjiku'];
        $last = ['Ochieng','Kamau','Njeri','Mutua','Wanjiru','Kiprop','Achieng','Baraka','Mwangi',
                 'Otieno','Chebet','Maina','Kariuki','Wafula','Juma','Korir','Nduta','Omondi','Chelimo','Abdi'];
        $programs = ['Computer Science','Business Administration','Information Technology','Economics',
                     'Civil Engineering','Law','Medicine','Mass Communication','Statistics','Education'];

        $groups = [
            ['alumni',  $this->alumniCount,  true],
            ['student', $this->studentCount, true],
            ['student', $this->pendingCount, false], // approval-queue filler
        ];

        $rows = [];
        $seq  = 0;

        foreach ($groups as [$role, $count, $approved]) {
            for ($i = 1; $i <= $count; $i++) {
                $seq++;
                $createdAt = $now->copy()->subDays(random_int(0, 400)); // spreads the user-growth chart

                $rows[] = [
                    'name'              => $first[array_rand($first)] . ' ' . $last[array_rand($last)],
                    'email'             => sprintf('lt.%s.%04d@%s', $role, $seq, $this->emailDomain),
                    'email_verified_at' => $now,
                    'password'          => $hash,
                    'student_id'        => sprintf('LT-%s-%05d', strtoupper(substr($role, 0, 1)), $seq),
                    'graduation_year'   => (string) random_int(1995, 2026),
                    'program'           => $programs[array_rand($programs)],
                    'is_approved'       => $approved ? 1 : 0,
                    'approved_at'       => $approved ? $createdAt : null,
                    'remember_token'    => Str::random(10),
                    'created_at'        => $createdAt,
                    'updated_at'        => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $this->ensureRoles();

        return $this->loadIds();
    }

    /**
     * Bulk role assignment through the Spatie pivot. Safe to call again:
     * it only touches load-test users that have NO role yet (e.g. after a
     * crash between the user insert and the pivot insert).
     */
    protected function ensureRoles(): void
    {
        $roleIds = Role::whereIn('name', ['alumni', 'student'])
            ->where('guard_name', 'web')->pluck('id', 'name');

        if ($roleIds->isEmpty()) {
            $this->command?->warn('LoadTestSeeder: roles missing — run RolesAndPermissionsSeeder first.');
            return;
        }

        $pivot = [];
        User::where('email', 'like', '%@' . $this->emailDomain)
            ->whereDoesntHave('roles')
            ->get(['id', 'email'])
            ->each(function (User $u) use (&$pivot, $roleIds) {
                $roleName = str_starts_with($u->email, 'lt.alumni.') ? 'alumni' : 'student';
                $pivot[] = [
                    'model_type' => User::class,
                    'model_id'   => $u->id,
                    'role_id'    => $roleIds[$roleName],
                ];
            });

        foreach (array_chunk($pivot, 500) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }
    }

    protected function seedEvents(array $actorIds, $now): void
    {
        if (DB::table('events')->where('title', 'like', $this->prefix . '%')->exists()) {
            return; // resume guard
        }

        $titles = ['Career Fair','Tech Talk','Networking Night','Alumni Gala','Startup Pitch Day',
                   'Resume Clinic','Industry Panel','Homecoming','Mentorship Kickoff','Research Symposium'];
        $locations = ['Nairobi Campus Hall', 'Innovation Lab', 'Auditorium A', 'Virtual (Zoom)'];

        $rows = [];
        for ($i = 1; $i <= $this->eventCount; $i++) {
            $past  = $i % 3 === 0; // every third event is in the past
            $start = $past
                ? $now->copy()->subDays(random_int(10, 300))
                : $now->copy()->addDays(random_int(3, 120));

            $rows[] = [
                'title'       => sprintf('%s%s %d', $this->prefix, $titles[$i % count($titles)], $i),
                'description' => 'Load-test event generated by LoadTestSeeder.',
                'location'    => $locations[$i % count($locations)],
                'capacity'    => random_int(30, 300),
                'start'       => $start,
                'end'         => $start->copy()->addHours(random_int(2, 6)),
                'user_id'     => $actorIds[array_rand($actorIds)],
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('events')->insert($rows);
    }

    protected function seedRsvps($now): void
    {
        if (! Schema::hasTable('event_rsvps')) {
            return;
        }

        $statusCol = Schema::hasColumn('event_rsvps', 'status') ? 'status'
            : (Schema::hasColumn('event_rsvps', 'rsvp_status') ? 'rsvp_status' : null);
        if (! $statusCol) {
            return;
        }

        $eventIds = DB::table('events')->where('title', 'like', $this->prefix . '%')->pluck('id')->all();
        if (empty($eventIds)) {
            return;
        }

        if (DB::table('event_rsvps')->whereIn('event_id', $eventIds)->exists()) {
            return; // resume guard
        }

        $participantIds = User::where('email', 'like', '%@' . $this->emailDomain)
            ->where('is_approved', 1)->pluck('id')->all();

        $hasTs = Schema::hasColumn('event_rsvps', 'created_at');
        $rows  = [];

        foreach ($eventIds as $eventId) {
            $n = min(count($participantIds), max(5, (int) round(count($participantIds) * 0.2)));
            foreach (array_rand(array_flip($participantIds), $n) as $userId) {
                $row = [
                    'event_id' => $eventId,
                    'user_id'  => $userId,
                    $statusCol => random_int(0, 1) ? 'going' : 'interested',
                ];
                if ($hasTs) {
                    $row['created_at'] = $now;
                    $row['updated_at'] = $now;
                }
                $rows[] = $row;
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('event_rsvps')->insert($chunk);
        }
    }

    protected function seedJobs(array $alumniIds, $now): void
    {
        if (DB::table('job_postings')->where('title', 'like', $this->prefix . '%')->exists()) {
            return; // resume guard
        }

        $demoAlumniId = User::where('email', 'alumni@alumni.edu')->value('id');
        $posterIds = array_values(array_filter(array_merge([$demoAlumniId], $alumniIds)));

        $titles = ['Software Engineer','Data Analyst','Product Manager','Marketing Lead',
                   'Finance Officer','UX Designer','DevOps Engineer','Research Assistant'];
        $locations = ['Remote', 'Nairobi', 'Mombasa', 'Kisumu'];

        $rows = [];
        for ($i = 1; $i <= $this->jobCount; $i++) {
            $rows[] = [
                'title'                => sprintf('%s%s %d', $this->prefix, $titles[$i % count($titles)], $i),
                'company'              => sprintf('LT Company %d', $i),
                'location'             => $locations[$i % count($locations)],
                'description'          => 'Load-test job posting generated by LoadTestSeeder.',
                'type'                 => $i % 3 === 0 ? 'internship' : 'job',
                'is_remote'            => $i % 2,
                'is_active'            => $i % 10 === 0 ? 0 : 1, // a few inactive
                'contact_email'        => sprintf('hr%d@ltcompany.example', $i),
                'application_deadline' => $now->copy()->addDays(random_int(7, 90)),
                'user_id'              => $posterIds[array_rand($posterIds)],
                'skills_required'      => json_encode(['PHP', 'Communication', 'Teamwork', 'SQL', 'Leadership']),
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        DB::table('job_postings')->insert($rows);
    }

    protected function seedApplications(array $userIds, $now): void
    {
        if (! Schema::hasTable('job_applications')) {
            $this->command?->warn('LoadTestSeeder: job_applications table not found — applications skipped.');
            return;
        }

        $fkCol = Schema::hasColumn('job_applications', 'job_posting_id') ? 'job_posting_id'
            : (Schema::hasColumn('job_applications', 'job_id') ? 'job_id' : null);
        if (! $fkCol) {
            $this->command?->warn('LoadTestSeeder: unrecognized job_applications schema — applications skipped.');
            return;
        }

        if (DB::table('job_applications')->whereIn('user_id', $userIds)->exists()) {
            return; // resume guard
        }

        $jobIds = DB::table('job_postings')
            ->where('title', 'like', $this->prefix . '%')
            ->where('is_active', 1)
            ->pluck('id')->all();

        if (empty($jobIds)) {
            return;
        }

        // Read what the status ENUM actually allows instead of guessing.
        $statuses = $this->pickStatuses('job_applications', ['pending', 'reviewed', 'accepted', 'rejected']);

        $rows = [];
        foreach ($userIds as $userId) {
            if (random_int(1, 100) > 60) {
                continue; // ~60% of approved users apply once
            }

            $row = [
                $fkCol       => $jobIds[array_rand($jobIds)],
                'user_id'    => $userId,
                'status'     => $statuses[array_rand($statuses)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('job_applications', 'cover_letter')) {
                $row['cover_letter'] = 'Load-test application generated by LoadTestSeeder.';
            }

            // resume_path is NOT NULL with no default — the crash cause.
            // A dummy path satisfies the constraint; downloads of this
            // file will 404, which is acceptable for load-test rows.
            if (Schema::hasColumn('job_applications', 'resume_path')) {
                $row['resume_path'] = 'resumes/loadtest-dummy.pdf';
            }
            $rows[] = $row;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('job_applications')->insert($chunk);
        }
    }

    protected function seedForum(array $userIds, $now): void
    {
        if (DB::table('forum_threads')->where('slug', 'like', 'lt-thread-%')->exists()) {
            return; // resume guard
        }

        $catIds = DB::table('forum_categories')->pluck('id')->all();
        if (empty($catIds)) {
            $this->command?->warn('LoadTestSeeder: no forum categories — forum skipped. Run ForumSeeder first.');
            return;
        }

        $threads = [];
        for ($i = 1; $i <= $this->threadCount; $i++) {
            $threads[] = [
                'title'             => sprintf('%sDiscussion topic %d', $this->prefix, $i),
                'slug'              => sprintf('lt-thread-%d', $i),
                'forum_category_id' => $catIds[$i % count($catIds)],
                'user_id'           => $userIds[array_rand($userIds)],
                'content'           => 'Load-test thread starter content.',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }
        DB::table('forum_threads')->insert($threads);

        $threadIds = DB::table('forum_threads')->where('slug', 'like', 'lt-thread-%')->pluck('id')->all();

        $posts = [];
        foreach ($threadIds as $threadId) {
            $n = random_int(2, $this->maxPostsPerThread);
            for ($p = 0; $p < $n; $p++) {
                $posts[] = [
                    'forum_thread_id' => $threadId,
                    'user_id'         => $userIds[array_rand($userIds)],
                    'content'         => 'Load-test reply: sharing experience, asking a follow-up, or adding resources.',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }
        }

        foreach (array_chunk($posts, 500) as $chunk) {
            DB::table('forum_posts')->insert($chunk);
        }
    }

    protected function seedMentorship(array $alumniIds, array $studentIds, $now): void
    {
        $table = Schema::hasTable('mentorships') ? 'mentorships'
            : (Schema::hasTable('mentorship_requests') ? 'mentorship_requests' : null);

        if ($table) {
            if (! DB::table($table)->where('goal', self::MENTORSHIP_GOAL)->exists()) { // resume guard
                // Read the status ENUM instead of guessing — 'pending' is
                // NOT in your mentorships.status enum, which crashed run #1.
                $statuses = $this->pickStatuses($table, ['active', 'completed']);

                $pairs = min($this->mentorshipPairs, count($alumniIds), count($studentIds));
                $rows = [];
                for ($i = 0; $i < $pairs; $i++) {
                    $rows[] = [
                        'mentor_id'  => $alumniIds[$i],
                        'mentee_id'  => $studentIds[$i],
                        'status'     => $statuses[array_rand($statuses)],
                        'goal'       => self::MENTORSHIP_GOAL,
                        'start_date' => $now->copy()->subDays(random_int(0, 60)),
                        'end_date'   => $now->copy()->addMonths(3),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table($table)->insert($rows);
            }
        }

        // Floods "Find Mentors" so its listing paginates. Own resume guard,
        // separate from the mentorships one.
        if (Schema::hasTable('mentor_profiles')) {
            $fk = Schema::hasColumn('mentor_profiles', 'user_id') ? 'user_id'
                : (Schema::hasColumn('mentor_profiles', 'mentor_id') ? 'mentor_id' : null);

            if ($fk && ! DB::table('mentor_profiles')->whereIn($fk, $alumniIds)->exists()) {
                $rows = [];
                $n = min($this->mentorProfileCount, count($alumniIds));
                for ($i = 0; $i < $n; $i++) {
                    $row = [
                        $fk         => $alumniIds[$i],
                        'expertise' => 'Career Growth, Interview Prep, Software Engineering',
                        'bio'       => 'Load-test mentor bio generated by LoadTestSeeder.',
                    ];
                    if (Schema::hasColumn('mentor_profiles', 'created_at')) {
                        $row['created_at'] = $now;
                        $row['updated_at'] = $now;
                    }
                    $rows[] = $row;
                }
                DB::table('mentor_profiles')->insert($rows);
            }
        }
    }
}