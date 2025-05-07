<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\JobPosting;

class FetchExternalJobs extends Command
{
    protected $signature = 'jobs:fetch-external';
    protected $description = 'Fetch external jobs from RSS feeds';

    public function handle()
    {
        $feed = Http::get('https://rss.indeed.com/rss?q=laravel')->body();
        
        // Parse RSS feed and store relevant jobs
        $jobs = simplexml_load_string($feed);
        
        foreach ($jobs->channel->item as $item) {
            JobPosting::updateOrCreate(
                ['external_id' => (string)$item->guid],
                [
                    'title' => (string)$item->title,
                    'description' => (string)$item->description,
                    'location' => $this->parseLocation($item),
                    'status' => 'external',
                    'deadline' => now()->addDays(30)
                ]
            );
        }
    }
    
    private function parseLocation($item) { /* ... */ }
}