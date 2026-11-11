<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\JobPosting;
use App\Models\User;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('generate-reports')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:users,events,jobs',
            'format' => 'required|in:csv,xlsx',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        switch ($request->report_type) {
            case 'users':
                return $this->generateUserReport($request->format, $startDate, $endDate);
            case 'events':
                return $this->generateEventReport($request->format, $startDate, $endDate);
            case 'jobs':
                return $this->generateJobReport($request->format, $startDate, $endDate);
        }
    }

    protected function generateUserReport($format, $startDate, $endDate)
    {
        $query = User::with(['profile', 'roles'])
            ->when($startDate, function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function($q) use ($endDate) {
                $q->where('created_at', '<=', $endDate->endOfDay());
            })
            ->orderBy('created_at');

        $users = $query->get()->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Student ID' => $user->student_id,
                'Graduation Year' => $user->graduation_year,
                'Program' => $user->program,
                'Role' => optional($user->roles->first())->name ?? 'N/A',
                'Approved' => $user->is_approved ? 'Yes' : 'No',
                'Approved At' => $user->approved_at?->format('Y-m-d H:i:s'),
                'Current Job' => optional($user->profile)->current_job ?? '',
                'Company' => optional($user->profile)->company ?? '',
                'Skills' => $user->profile ? implode(', ', $user->profile->skills) : '',
                'Registered At' => $user->created_at->format('Y-m-d H:i:s'),
                'Last Login' => $user->last_login_at?->format('Y-m-d H:i:s'),
            ];
        });

        $filename = 'users-report-' . now()->format('Y-m-d');

        return (new FastExcel($users))->download("{$filename}.{$format}");
    }

    protected function generateEventReport($format, $startDate, $endDate)
    {
        $query = Event::with(['organizer', 'rsvps'])
            ->when($startDate, function($q) use ($startDate) {
                $q->where('start', '>=', $startDate);
            })
            ->when($endDate, function($q) use ($endDate) {
                $q->where('end', '<=', $endDate->endOfDay());
            })
            ->orderBy('start');

        $events = $query->get()->map(function ($event) {
            return [
                'ID' => $event->id,
                'Title' => $event->title,
                'Description' => $event->description,
                'Start' => $event->start->format('Y-m-d H:i:s'),
                'End' => $event->end->format('Y-m-d H:i:s'),
                'Location' => $event->location,
                'Organizer' => $event->organizer?->name ?? 'Former member',
                'Organizer Email' => $event->organizer?->email ?? 'N/A',
                'Capacity' => $event->capacity,
                'Attendees' => $event->rsvps->where('status', 'going')->count(),
                'Interested' => $event->rsvps->where('status', 'interested')->count(),
                'Is Online' => $event->is_online ? 'Yes' : 'No',
                'Meeting URL' => $event->meeting_url,
                'Created At' => $event->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $filename = 'events-report-' . now()->format('Y-m-d');

        return (new FastExcel($events))->download("{$filename}.{$format}");
    }

    protected function generateJobReport($format, $startDate, $endDate)
    {
        $query = JobPosting::with(['poster', 'applications'])
            ->when($startDate, function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function($q) use ($endDate) {
                $q->where('created_at', '<=', $endDate->endOfDay());
            })
            ->orderBy('created_at');

        $jobs = $query->get()->map(function ($job) {
            return [
                'ID' => $job->id,
                'Title' => $job->title,
                'Type' => $job->type,
                'Company' => $job->company,
                'Location' => $job->location,
                'Is Remote' => $job->is_remote ? 'Yes' : 'No',
                'Salary Range' => $job->salary_range,
                'Employment Type' => $job->employment_type,
                'Application Deadline' => $job->application_deadline?->format('Y-m-d'),
                'Posted By' => $job->poster?->name ?? 'Former member',
                'Posted By Email' => $job->poster?->email ?? 'N/A',
                'Contact Email' => $job->contact_email,
                'Contact Phone' => $job->contact_phone,
                'Website' => $job->website,
                'Skills Required' => $job->skills_required ? implode(', ', $job->skills_required) : '',
                'Skills Preferred' => $job->skills_preferred ? implode(', ', $job->skills_preferred) : '',
                'Applications' => $job->applications->count(),
                'Active' => $job->is_active ? 'Yes' : 'No',
                'Created At' => $job->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $filename = 'jobs-report-' . now()->format('Y-m-d');

        return (new FastExcel($jobs))->download("{$filename}.{$format}");
    }

}
