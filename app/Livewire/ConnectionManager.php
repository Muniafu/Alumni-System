<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ConnectionRequest;
use App\Models\User;

class ConnectionManager extends Component
{
    public $search = '';
    public $message = '';
    public $selectedUserId;

    protected $rules = [
        'message' => 'required|string|max:500',
        'selectedUserId' => 'required|exists:users,id'
    ];

    public function sendRequest()
    {
        $this->validate();

        ConnectionRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUserId,
            'message' => $this->message,
            'status' => 'pending'
        ]);

        $this->reset(['message', 'selectedUserId']);
        session()->flash('message', 'Connection request sent!');
    }

    public function acceptRequest($requestId)
    {
        $request = ConnectionRequest::findOrFail($requestId);
        $request->update(['status' => 'accepted']);
    }

    public function rejectRequest($requestId)
    {
        $request = ConnectionRequest::findOrFail($requestId);
        $request->update(['status' => 'rejected']);
    }

    public function render()
    {
        $user = auth()->user();

        $connections = $user ? $user->connections()->paginate(10) : collect();
        $pendingRequests = $user ? $user->pendingConnectionRequests()->paginate(10) : collect();

        $users = User::where('id', '!=', auth()->id())
            ->when($user, function ($query) use ($user) {
                return $query->whereNotIn('id', $user->connections->pluck('id'));
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->paginate(5);

        return view('livewire.connection-manager', [
            'users' => $users,
            'connections' => $connections,
            'pendingRequests' => $pendingRequests
        ]);
    }
}