<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: roles before users (assignRole), demo users before
     * module data (every module seeder references them by email).
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            EventsSeeder::class,
            ForumSeeder::class,
            JobsSeeder::class,
            MentorshipSeeder::class,
        ]);
    }
}
