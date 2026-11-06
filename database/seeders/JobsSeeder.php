<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JobsSeeder extends Seeder
{
    public function run(): void
    {
        $alumni = User::where('email', 'alumni@alumni.edu')->first();

        if (! $alumni) {
            $this->command?->warn('JobsSeeder: demo users not found — run AdminUserSeeder first.');
            return;
        }

        // skills MUST be arrays: JobPosting casts them as 'array'. Seeding
        // comma strings stores JSON-encoded scalars that read back as PHP
        // strings and crash every blade that counts/loops them.
        $jobs = [
            [
                'title' => 'Software Engineer',
                'company' => 'TechCorp',
                'location' => 'Remote',
                'description' => 'We are looking for an experienced Laravel developer.',
                'type' => 'job',
                'is_remote' => true,
                'is_active' => true,
                'contact_email' => 'hr@techcorp.com',
                'application_deadline' => Carbon::now()->addWeeks(3),
                'user_id' => $alumni->id,
                'skills_required' => ['Laravel', 'PHP', 'MySQL'],
            ],
            [
                'title' => 'Business Analyst Intern',
                'company' => 'BizSolutions Ltd.',
                'location' => 'New York, NY',
                'description' => 'Internship opportunity for final-year business students.',
                'type' => 'internship',
                'is_remote' => false,
                'is_active' => true,
                'contact_email' => 'careers@bizsolutions.com',
                'application_deadline' => Carbon::now()->addWeeks(6),
                'user_id' => $alumni->id,
                'skills_required' => ['Excel', 'Data Analysis', 'Communication'],
            ],
        ];

        foreach ($jobs as $job) {
            JobPosting::firstOrCreate(
                ['title' => $job['title'], 'company' => $job['company']],
                $job
            );
        }
    }
}
