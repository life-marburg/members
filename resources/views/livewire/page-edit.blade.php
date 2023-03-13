    <form wire:submit.prevent="update">
        <x-textarea class="w-full" rows="10" wire:model="content"></x-textarea>

        <div class="flex justify-end mt-4 items-center">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button wire:loading.attr="disabled" wire:target="photo">
                {{ __('Save') }}
            </x-button>
        </div>
    </form>
