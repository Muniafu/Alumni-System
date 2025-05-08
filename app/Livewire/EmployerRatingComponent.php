<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EmployerRating;
use Illuminate\Support\Facades\Auth;

class EmployerRatingComponent extends Component
{
    public $employer;
    public $rating;
    public $comment;

    protected $rules = [
        'rating' => 'required|integer|between:1,5',
        'comment' => 'nullable|string|max:500'
    ];

    public function mount($employer)
    {
        $this->employer = $employer;
        $existingRating = EmployerRating::where('employer_id', $this->employer->id)
            ->where('alumni_id', Auth::user()->id)
            ->first();

        if ($existingRating) {
            $this->rating = $existingRating->rating;
            $this->comment = $existingRating->comment;
        }
    }

    public function submit()
    {
        $this->validate();
        EmployerRating::updateOrCreate(
            [
                'employer_id' => $this->employer->id,
                'alumni_id' => Auth::user()->id
            ],
            [
                'rating' => $this->rating,
                'comment' => $this->comment
            ]
        );

        $this->emit('ratingUpdated');
    }

    public function render()
    {
        return view('livewire.employer-rating-component');
    }
}