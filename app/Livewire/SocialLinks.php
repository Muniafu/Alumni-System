<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SocialLinks extends Component
{
    public $linkedin;
    public $github;
    public $twitter;
    public $portfolio;

    protected $rules = [
        'linkedin' => 'nullable|url',
        'github' => 'nullable|url',
        'twitter' => 'nullable|url',
        'portfolio' => 'nullable|url'
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->linkedin = $user->linkedin_url;
        $this->github = $user->github_url;
        $this->twitter = $user->twitter_url;
        $this->portfolio = $user->portfolio_url;
    }
    public function save()
    {
        $this->validate();
        $user = User::find(Auth::id());
        $user->linkedin_url = $this->linkedin;
        $user->github_url = $this->github;
        $user->twitter_url = $this->twitter;
        $user->portfolio_url = $this->portfolio;
        $user->save();
        $user->save();

        session()->flash('message', 'Social links updated!');
    }


    public function render()
    {
        return view('livewire.social-links');
    }
}