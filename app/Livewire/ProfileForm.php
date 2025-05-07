<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\AlumniProfile;
use App\Models\User;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProfileForm extends Component
{
    use WithFileUploads;

    public $bio;
    public $skills = [];
    public $certificates = [];
    public $privacy = [];

    /** @var User */
    protected $user;

    protected $rules = [
        'bio' => 'required|string|max:500',
        'skills' => 'array|min:3',
        'certificates.*' => 'file|mimes:pdf,doc,docx|max:2048',
        'privacy' => 'array'
    ];

    public function mount()
    {
        /** @var User $user */
        $user = Auth::user();
        $this->user = $user;

        if ($user->alumniProfile) {
            $this->bio = $user->alumniProfile->bio;
            $this->skills = $user->alumniProfile->skills->pluck('id')->toArray();
            $this->privacy = $user->alumniProfile->privacy_settings ?? [];
        }
    }

    public function save()
    {
        $this->validate();

        /** @var AlumniProfile $profile */
        $profile = $this->user->alumniProfile()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'bio' => $this->bio,
                'privacy_settings' => $this->privacy
            ]
        );

        $profile->skills()->sync($this->skills);

        if ($this->certificates) {
            foreach ($this->certificates as $file) {
                $path = $file->store('certificates', 'public');
                $profile->certificates()->create(['path' => $path]);
            }
        }

        session()->flash('message', 'Profile updated successfully');
    }

    public function render()
    {
        return view('livewire.profile-form', [
            'allSkills' => \App\Models\Skill::all(),
            'certificates' => $this->user->alumniProfile->certificates ?? [],
            'experiences' => $this->user->alumniProfile->experiences ?? []
        ]);
    }
}