<div>
    <button wire:click="$set('showModal', true)" class="btn btn-primary">
        Add Endorsement
    </button>

    <x-modal wire:model="showModal">
        <x-slot name="title">
            Endorse {{ $alumni->name }}
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="block font-medium">Skills</label>
                @foreach($skills as $skill)
                    <label class="inline-flex items-center mr-4">
                        <input type="checkbox" 
                               wire:model="selectedSkills" 
                               value="{{ $skill->id }}" 
                               class="form-checkbox">
                        <span class="ml-2">{{ $skill->name }}</span>
                    </label>
                @endforeach
            </div>

            <div>
                <label class="block font-medium">Message</label>
                <textarea wire:model="message" 
                          class="form-textarea w-full"></textarea>
                @error('message') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button wire:click="submit" class="btn btn-primary">
                Submit Endorsement
            </button>
        </x-slot>
    </x-modal>
</div>