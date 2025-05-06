<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Skill;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $skills = [
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'JavaScript', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'Project Management', 'category' => 'Soft Skills'],
        ];

        foreach ($skills as $skill) {
            Skill::create($skill);
        }

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'profile_completed' => true,
            'email_verified_at' => now(),
        ]);

        User::factory(10)->create(['role' => 'alumni'])
        ->each(function ($user) {
            $user->alumniProfile()->create([
                'graduation_year' => fake()->numberBetween(2010, 2023),
                'current_position' => fake()->jobTitle(),
                'industry' => fake()->word(),
                'bio' => fake()->paragraph(),
            ]);

            $skills = Skill::inRandomOrder()->limit(3)->pluck('id');
            $user->alumniProfile->skills()->attach($skills);
        });

        User::factory(5)->create(['role' => 'employer'])
        ->each(function ($user) {
            $user->employerProfile()->create([
                'company_name' => fake()->company(),
                'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
                'industry' => fake()->word(),
                'website' => fake()->url(),
                'contact_person' => fake()->name(),
                'contact_position' => fake()->jobTitle(),
                'phone' => fake()->phoneNumber(),
            ]);
        });

    }
}