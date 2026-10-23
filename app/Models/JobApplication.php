<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'user_id',
        'cover_letter',
        'resume_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Canonical relation name — used by the controller and policies.
     */
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Legacy alias — kept for compatibility with code that uses job().
     * Prefer jobPosting() in new code.
     */
    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'submitted' => 'Submitted',
            'reviewed' => 'Under Review',
            'interviewed' => 'Interviewing',
            'rejected' => 'Not Selected',
            'hired' => 'Hired',
            'revoked' => 'Revoked',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Status-change notifications live HERE — the controller's
     * updateApplicationStatus() deliberately sends nothing, so every
     * recipient gets exactly one notification per status change.
     */
    protected static function booted()
    {
        static::updated(function ($application) {
            if (!$application->isDirty('status')) {
                return;
            }

            $oldStatus = $application->getOriginal('status');
            $newStatus = $application->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            $recipients = User::role('admin')->get();

            if ($application->applicant) {
                $recipients = $recipients->merge([$application->applicant]);
            }

            // Null-safe: a soft-deleted posting makes the job relation null.
            $job = $application->job;

            if ($job && $job->poster) {
                $recipients = $recipients->merge([$job->poster]);
            }

            Notification::send(
                $recipients->unique('id')->values(),
                new ApplicationStatusChangedNotification($application, $oldStatus)
            );
        });
    }
}
