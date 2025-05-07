<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Find Alumni</h5>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name...">
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($users as $user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $user->name }}
                                <button wire:click="selectUser({{ $user->id }})" 
                                        class="btn btn-sm btn-outline-primary">
                                    Connect
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @if($selectedUserId)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Send Connection Request</h5>
                        <textarea wire:model="message" class="form-control mb-2" 
                                  placeholder="Add a personal message..."></textarea>
                        <button wire:click="sendRequest" class="btn btn-primary">
                            Send Request
                        </button>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5>Pending Requests</h5>
                </div>
                <div class="card-body">
                    @forelse($pendingRequests as $request)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>{{ $request->sender->name }}</strong>
                                <p class="mb-0">{{ $request->message }}</p>
                            </div>
                            <div>
                                <button wire:click="acceptRequest({{ $request->id }})" 
                                        class="btn btn-sm btn-success me-2">
                                    Accept
                                </button>
                                <button wire:click="rejectRequest({{ $request->id }})" 
                                        class="btn btn-sm btn-danger">
                                    Reject
                                </button>
                            </div>
                        </div>
                    @empty
                        <p>No pending connection requests</p>
                    @endforelse
                    {{ $pendingRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>