<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ProfileService
{
    public function getProfileMetrics($userId)
    {
        return Cache::remember("profile_metrics_{$userId}", 3600, function() use ($userId) {
            return [
                'rating' => EmployerRating::where('employer_id', $userId)->avg('rating'),
                'endorsements' => Endorsement::where('alumni_id', $userId)
                    ->with('skill')
                    ->get()
                    ->groupBy('skill_id')
            ];
        });
    }
}