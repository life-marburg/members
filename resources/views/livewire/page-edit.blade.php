    <form wire:submit.prevent="update">
        <x-textarea class="w-full" rows="10" wire:model="content"></x-textarea>

        <div class="flex justify-end mt-4 items-center">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button wire:loading.attr="disabled" wire:target="photo">
                {{ __('Save') }}
            </x-jet-button>
        </div>
    </form>
