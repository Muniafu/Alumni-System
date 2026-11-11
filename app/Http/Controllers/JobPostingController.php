<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Notifications\JobApplicationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\UniqueConstraintViolationException;

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
     *
     * The NewJobPostedNotification is sent by JobPosting::booted()
     * (created event) — nothing is sent here on purpose.
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

        // An unchecked checkbox is simply absent from the request,
        // so normalise it explicitly instead of relying on the key being there.
        $validated['is_remote'] = $request->boolean('is_remote');

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        try {
            $job = JobPosting::create($validated);

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
     *
     * is_active notifications are sent by JobPosting::booted()
     * (updated event) — nothing is sent here on purpose.
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

        // Checkbox fix: an absent key used to leave is_active/is_remote
        // unchanged, so a posting could never be deactivated from the edit form.
        $validated['is_remote'] = $request->boolean('is_remote');
        $validated['is_active'] = $request->boolean('is_active');

        $job->update($validated);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job posting updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * The JobDeletedNotification is sent by JobPosting::booted()
     * (deleted event) — nothing is sent here on purpose. The posting
     * is soft-deleted (SoftDeletes), so no FK constraint can fire.
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
     *
     * Creation notifications stay here deliberately: this is the only
     * code path that creates applications. Status-change notifications
     * belong to JobApplication::booted().
     */
    public function apply(Request $request, JobPosting $job)
    {
        // The poster cannot apply to their own posting.
        if ($job->user_id === auth()->id()) {
            return redirect()->route('jobs.show', $job)
                ->with('error', 'You cannot apply to your own job posting.');
        }

        if (!$job->canApply()) {
            return redirect()->route('jobs.show', $job)
                ->with('error', 'This job posting is not accepting applications.');
        }

        // Friendly duplicate guard (canApply() also checks this, but an
        // explicit check gives the clearest error message). The real
        // guarantee is the unique (job_posting_id, user_id) index added
        // by the migration — it catches double-click/tab races.
        if ($job->applications()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('jobs.show', $job)
                ->with('error', 'You have already applied to this job posting.');
        }

        $validated = $request->validate([
            'cover_letter' => 'required|string|min:100|max:2000',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048', // 2MB
        ]);

        // store() creates the directory automatically — resumes stay on the private disk.
        $resumePath = $request->file('resume')->store('resumes', 'private');

        try {
            $application = JobApplication::create([
                'job_posting_id' => $job->id,
                'user_id' => auth()->id(),
                'cover_letter' => $validated['cover_letter'],
                'resume_path' => $resumePath,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Two requests raced past the friendly check — the unique index won.
            // Clean up the orphaned resume file.
            Storage::disk('private')->delete($resumePath);

            return redirect()->route('jobs.show', $job)
                ->with('error', 'You have already applied to this job posting.');
        }

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
     * No notification is sent here on purpose — JobApplication::booted()
     * (updated event) sends exactly one status-change notification to
     * the applicant, the poster, and the admins.
     */
    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'status' => 'required|in:submitted,reviewed,interviewed,rejected,hired',
            'notes' => 'nullable|string|max:500',
        ]);

        $application->update($validated);

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
            ($application->applicant?->name ?? 'applicant') . '_resume.' . pathinfo($application->resume_path, PATHINFO_EXTENSION)
        );
    }
}
