<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewJobPostedNotification;
use App\Notifications\JobApplicationNotification;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class JobPostingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = JobPosting::active()->with('poster')->latest();

        if ($type && in_array($type, ['job', 'internship', 'mentorship'])) {
            $query->where('type', $type);
            $title = ucfirst($type) . ' Opportunities';
        } else {
            $title = 'Job Board';
        }

        $jobs = $query->paginate(10);

        return view('jobs.index', compact('jobs', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', JobPosting::class);

        return view('jobs.create');
    }

    public function show(JobPosting $job)
    {
        $userApplication = auth()->check()
            ? $job->applications()->where('user_id', auth()->id())->first()
            : null;

        return view('jobs.show', [
            'job' => $job,
            'userApplication' => $userApplication
        ]);
    }

    public function myPostings()
    {
        $jobs = JobPosting::where('user_id', auth()->id())
            ->withCount('applications')
            ->latest()
            ->paginate(10);

        return view('jobs.my-postings', [
            'jobs' => $jobs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', JobPosting::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:job,internship,mentorship',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'is_remote' => 'boolean',
            'salary_range' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:100',
            'application_deadline' => 'nullable|date',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'skills_required' => 'nullable|string',
            'skills_preferred' => 'nullable|string',
        ]);

        // Process skills
        $validated['skills_required'] = $request->skills_required
            ? array_map('trim', explode(',', $request->skills_required))
            : null;

        $validated['skills_preferred'] = $request->skills_preferred
            ? array_map('trim', explode(',', $request->skills_preferred))
            : null;

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        try {
            $job = JobPosting::create($validated);

            if (!$job->exists) {
                throw new \Exception('Failed to save job posting');
            }

            // ✅ Send notifications here (queued — see NewJobPostedNotification implements ShouldQueue)
            $recipients = User::query()
                ->where('is_approved', true)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['alumni', 'student', 'admin']);
                })
                ->get();

            Notification::send($recipients, new NewJobPostedNotification($job));

            return redirect()->route('jobs.show', $job)
                ->with('success', 'Job posting created successfully');

        } catch (\Exception $e) {
            Log::error('Job posting creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create job posting. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosting $job)
    {
        Gate::authorize('update', $job);

        return view('jobs.edit', compact('job'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosting $job)
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:job,internship,mentorship',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'is_remote' => 'boolean',
            'salary_range' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:100',
            'application_deadline' => 'nullable|date',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'skills_required' => 'nullable|string',
            'skills_preferred' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['skills_required'] = $request->skills_required
            ? array_map('trim', explode(',', $request->skills_required))
            : null;

        $validated['skills_preferred'] = $request->skills_preferred
            ? array_map('trim', explode(',', $request->skills_preferred))
            : null;

        $job->update($validated);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job posting updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosting $job)
    {
        Gate::authorize('delete', $job);

        $job->delete();

        return redirect()->route('jobs.index')
            ->with('success', 'Job posting deleted successfully');
    }

    /**
     * Apply to the specified job posting.
     */
    public function apply(Request $request, JobPosting $job)
    {
        // Check if user can apply
        if (!$job->canApply()) {
            return back()->with('error', 'You cannot apply to this job posting.');
        }

        // Validate request
        $validated = $request->validate([
            'cover_letter' => 'required|string|min:100|max:2000',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048', // 2MB
        ]);

        // Ensure the private folder exists
        $resumeFolder = 'resumes';
        $privateDisk = 'private';
        if (!Storage::disk($privateDisk)->exists($resumeFolder)) {
            Storage::disk($privateDisk)->makeDirectory($resumeFolder);
        }

        // Store resume in private storage
        $resumePath = $request->file('resume')->store($resumeFolder, $privateDisk);

        // Create job application
        $application = JobApplication::create([
            'job_posting_id' => $job->id,
            'user_id' => auth()->id(),
            'cover_letter' => $validated['cover_letter'],
            'resume_path' => $resumePath,
        ]);

        // Notify job poster
        if ($job->poster) {
            $job->poster->notify(new JobApplicationNotification($application));
        }

        // Notify all admins
        $admins = User::role('admin')->get();
        Notification::send($admins, new JobApplicationNotification($application));

        return redirect()->route('jobs.show', $job)
                        ->with('success', 'Your application has been submitted successfully.');
    }

    /**
     * List applications for the specified job posting.
     */
    public function applications(JobPosting $job)
    {
        Gate::authorize('viewApplications', $job);

        $applications = $job->applications()->with(['applicant' => function ($query) {
            $query->with('profile');
        }])->latest()->get();

        return view('jobs.applications', compact('job', 'applications'));
    }

    /**
     * Show a single application.
     */
    public function showApplication(JobApplication $application)
    {
        // Authorize access to view this application
        $this->authorize('view', $application);

        // Load relationships
        $application->load(['jobPosting', 'applicant.profile']);

        $job = $application->jobPosting;

        return view('jobs.application-show', compact('application', 'job'));
    }

    /**
     * Update application status.
     *
     * Called via PUT /jobs/{job}/applications/{application} — the {job}
     * route parameter is deliberately not bound here since only the
     * application is needed.
     */
    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'status' => 'required|in:submitted,reviewed,interviewed,rejected,hired',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $application->status;
        $application->update($validated);

        if ($oldStatus !== $application->status && in_array($application->status, ['interviewed', 'hired', 'rejected'])) {
            $application->applicant->notify(new ApplicationStatusChangedNotification($application, $oldStatus));
        }

        return redirect()->back()->with('success', 'Application status updated');
    }

    /**
     * Download the resume attached to an application.
     */
    public function downloadResume(JobApplication $application)
    {
        $this->authorize('downloadResume', $application);

        $disk = Storage::disk('private');

        if (!$disk->exists($application->resume_path)) {
            abort(404, 'Resume file not found.');
        }

        return $disk->download(
            $application->resume_path,
            $application->applicant->name . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
        );
    }
}