<div class="card mb-4">
    <div class="card-header">
        <h5>Social Links</h5>
    </div>
    <div class="card-body">
        <form wire:submit.prevent="save">
            <div class="mb-3">
                <label class="form-label">LinkedIn</label>
                <input type="url" wire:model="linkedin" class="form-control" placeholder="https://linkedin.com/in/yourprofile">
            </div>
            <div class="mb-3">
                <label class="form-label">GitHub</label>
                <input type="url" wire:model="github" class="form-control" placeholder="https://github.com/yourusername">
            </div>
            <div class="mb-3">
                <label class="form-label">Twitter</label>
                <input type="url" wire:model="twitter" class="form-control" placeholder="https://twitter.com/yourhandle">
            </div>
            <div class="mb-3">
                <label class="form-label">Portfolio</label>
                <input type="url" wire:model="portfolio" class="form-control" placeholder="https://yourportfolio.com">
            </div>
            <button type="submit" class="btn btn-primary">Save Links</button>
        </form>
    </div>
</div>