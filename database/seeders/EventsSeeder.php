<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@alumni.edu')->first();
        $alumni = User::where('email', 'alumni@alumni.edu')->first();
        $student = User::where('email', 'student@alumni.edu')->first();

        // Standalone run (db:seed --class=EventsSeeder) must not crash
        // on "->id of null".
        if (! $admin || ! $alumni) {
            $this->command?->warn('EventsSeeder: demo users not found — run AdminUserSeeder first.');
            return;
        }

        $events = [
            [
                'title' => 'Annual Alumni Meetup',
                'description' => 'Join us for networking, fun, and career discussions with fellow alumni.',
                'location' => 'University Hall',
                'capacity' => 100,
                'start' => Carbon::now()->addDays(15),
                'end' => Carbon::now()->addDays(15)->addHours(4),
                'user_id' => $admin->id,
            ],
            [
                'title' => 'Web Development Workshop',
                'description' => 'Hands-on Laravel and ReactJS session by industry experts.',
                'location' => 'Tech Innovation Lab',
                'capacity' => 40,
                'start' => Carbon::now()->addDays(30),
                'end' => Carbon::now()->addDays(30)->addHours(5),
                'user_id' => $alumni->id,
            ],
            [
                'title' => 'Past Business Networking Event',
                'description' => 'Recap and gallery from our successful networking event last year.',
                'location' => 'Business School Auditorium',
                'capacity' => 120,
                'start' => Carbon::now()->subMonths(3),
                'end' => Carbon::now()->subMonths(3)->addHours(3),
                'user_id' => $admin->id,
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(['title' => $event['title']], $event);
        }

        // Seed RSVPs so the admin dashboard's attendance average and the
        // user-show "Events Attended" tab have real data. Schema-guarded:
        // skipped cleanly if the pivot or its columns differ.
        if (Schema::hasTable('event_rsvps')) {
            $statusCol = Schema::hasColumn('event_rsvps', 'status') ? 'status'
                : (Schema::hasColumn('event_rsvps', 'rsvp_status') ? 'rsvp_status' : null);

            if ($statusCol) {
                $meetup = Event::where('title', 'Annual Alumni Meetup')->first();
                $past = Event::where('title', 'Past Business Networking Event')->first();

                $rsvps = [
                    [$meetup, $alumni, 'going'],
                    [$meetup, $student, 'going'],
                    [$past, $admin, 'going'],
                    [$past, $alumni, 'going'],
                    [$past, $student, 'interested'],
                ];

                $hasTimestamps = Schema::hasColumn('event_rsvps', 'created_at');

                foreach ($rsvps as [$event, $user, $status]) {
                    if (! $event || ! $user) {
                        continue;
                    }

                    $values = [$statusCol => $status];
                    if ($hasTimestamps) {
                        $values['created_at'] = now();
                        $values['updated_at'] = now();
                    }

                    DB::table('event_rsvps')->updateOrInsert(
                        ['event_id' => $event->id, 'user_id' => $user->id],
                        $values
                    );
                }
            }
        }
    }
}
