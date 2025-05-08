<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Endorsement;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;

class EndorsementModal extends Component
{
    public $alumni;
    public $message;
    public $selectedSkills = [];
    public $showModal = false;

    protected $rules = [
        'selectedSkills' => 'required|array|min:1',
        'message' => 'required|string|max:500'
    ];

    public function mount($alumni)
    {
        $this->alumni = $alumni;
    }

    public function submit()
    {
        $this->validate();

        foreach ($this->selectedSkills as $skillId) {
            Endorsement::create([
                'endorser_id' => Auth::id(),
                'skill_id' => $skillId,
                'message' => $this->message
            ]);
        }

        $this->reset(['selectedSkills', 'message', 'showModal']);
        $this->emit('endorsementAdded');
    }

    public function render()
    {
        return view('livewire.endorsement-modal', [
            'skills' => Skill::all()
        ]);
    }
}