<div class="container">
    <form wire:submit.prevent="save">
        <!-- Bio Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Bio</h5>
                <textarea wire:model="bio" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <!-- Skills Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Skills</h5>
                <div class="row">
                    @foreach($allSkills as $skill)
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input type="checkbox" 
                                    wire:model="skills"
                                    value="{{ $skill->id }}" 
                                    id="skill-{{ $skill->id }}" 
                                    class="form-check-input">
                                <label class="form-check-label" for="skill-{{ $skill->id }}">
                                    {{ $skill->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- File Upload -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Certificates</h5>
                <input type="file" wire:model="certificates" multiple class="form-control">
                
                @foreach($certificates as $cert)
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <a href="{{ asset('storage/'.$cert->path) }}" target="_blank">
                            {{ basename($cert->path) }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Privacy Settings</h5>
                <div class="form-check form-switch">
                    <input wire:model="privacy.show_email" 
                           type="checkbox" 
                           class="form-check-input"
                           id="privacyEmail">
                    <label class="form-check-label" for="privacyEmail">
                        Show Email Address
                    </label>
                </div>
                <!-- Add more privacy toggles -->
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>