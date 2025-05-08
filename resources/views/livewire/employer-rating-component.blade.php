<div class="card">
    <div class="card-body">
        <h5 class="card-title">Rate {{ $employer->name }}</h5>
        
        <div class="rating-stars mb-3">
            @for($i = 1; $i <= 5; $i++)
                <button wire:click="$set('rating', {{ $i }})" 
                        class="star {{ $i <= $rating ? 'text-warning' : '' }}">
                    ★
                </button>
            @endfor
        </div>

        <textarea wire:model="comment" 
                  class="form-control mb-3" 
                  placeholder="Optional comments..."></textarea>

        <button wire:click="submit" 
                class="btn btn-primary"
                @if(!$rating) disabled @endif>
            Submit Rating
        </button>
    </div>
</div>