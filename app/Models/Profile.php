<?php

namespace App\Models;

use App\Notifications\ProfileCompletedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'current_job',
        'company',
        'bio',
        'social_links',
        'skills',
        'interests',
        'profile_completion',
        'education',
        'certifications',
    ];

    /**
     * social_links: JSON object, e.g. {"linkedin":"https://...","twitter":"https://..."}
     * profile_completion: MySQL returns strings; cast keeps === comparisons sane.
     */
    protected $casts = [
        'social_links' => 'array',
        'profile_completion' => 'integer',
    ];

    protected $attributes = [
        'skills' => '',
        'interests' => '',
        'certifications' => '',
        'profile_completion' => 0,
    ];

    /**
     * Relationship to the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Skills / interests / certifications (plain comma-separated strings)
    |--------------------------------------------------------------------------
    */

    public function getSkillsArrayAttribute(): array
    {
        return $this->skills ? array_values(array_filter(array_map('trim', explode(',', $this->skills)))) : [];
    }

    public function getInterestsArrayAttribute(): array
    {
        return $this->interests ? array_values(array_filter(array_map('trim', explode(',', $this->interests)))) : [];
    }

    public function getCertificationsArrayAttribute(): array
    {
        return $this->certifications ? array_values(array_filter(array_map('trim', explode(',', $this->certifications)))) : [];
    }

    public function setSkillsAttribute($value)
    {
        $this->attributes['skills'] = is_array($value) ? implode(',', $value) : (string) $value;
    }

    public function setInterestsAttribute($value)
    {
        $this->attributes['interests'] = is_array($value) ? implode(',', $value) : (string) $value;
    }

    public function setCertificationsAttribute($value)
    {
        $this->attributes['certifications'] = is_array($value) ? implode(',', $value) : (string) $value;
    }

    public function getSocialLinksArrayAttribute(): array
    {
        return array_values(array_filter($this->social_links ?? []));
    }

    /*
    |--------------------------------------------------------------------------
    | Social links (JSON object via cast)
    |--------------------------------------------------------------------------
    */

    /**
     * Get a single social link by key, e.g. $profile->getSocialLink('linkedin')
     */
    public function getSocialLink(string $key): ?string
    {
        return $this->social_links[$key] ?? null;
    }

    /**
     * Set a single social link by key. Passing null or '' removes it.
     * Does NOT save — persist with $profile->save() from the caller.
     */
    public function setSocialLink(string $key, ?string $value): void
    {
        $links = $this->social_links ?? [];

        if ($value === null || $value === '') {
            unset($links[$key]);
        } else {
            $links[$key] = $value;
        }

        $this->social_links = $links;
    }

    /*
    |--------------------------------------------------------------------------
    | Completion
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate profile completion percentage (0–100).
     *
     * Pass $save = false if the caller saves the model itself
     * (recommended: one save per request).
     */
    public function calculateCompletion(bool $save = true): int
    {
        $completedFields = 0;

        $userFields    = ['name', 'email'];
        $profileFields = ['phone', 'address', 'current_job', 'company', 'bio', 'education'];
        $arrayFields   = ['skills', 'interests', 'social_links', 'certifications'];

        foreach ($userFields as $field) {
            if (!empty($this->user->{$field})) {
                $completedFields++;
            }
        }

        foreach ($profileFields as $field) {
            if (!empty($this->{$field})) {
                $completedFields++;
            }
        }

        foreach ($arrayFields as $field) {
            if (!empty($this->{$field})) {
                $completedFields++;
            }
        }

        $totalFields = count($userFields) + count($profileFields) + count($arrayFields);
        $completionPercentage = min(100, (int) round(($completedFields / $totalFields) * 100));

        $this->profile_completion = $completionPercentage;

        // One-time notification at 100% (never duplicates).
        if (
            $completionPercentage === 100
            && !$this->user->notifications()
                ->where('type', ProfileCompletedNotification::class)
                ->exists()
        ) {
            $this->user->notify(new ProfileCompletedNotification());
        }

        if ($save) {
            $this->save();
        }

        return $completionPercentage;
    }
}
