<x-jet-form-section submit="update">
    <x-slot name="title">
        {{ __('Address Data') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your address data here.') }}
    </x-slot>

    <x-slot name="form">

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('First and lastname') }}"/>
            <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name"
                         autocomplete="name"/>
            <x-jet-input-error for="name" class="mt-2"/>
        </div>

        <!-- Phone -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="phone" value="{{ __('Phone') }}"/>
            <x-jet-input id="phone" type="text" class="mt-1 block w-full" wire:model.defer="state.phone"/>
            <x-jet-input-error for="phone" class="mt-2"/>
        </div>

        <!-- Mobile Phone -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="mobile_phone" value="{{ __('Mobile Phone') }}"/>
            <x-jet-input id="mobile_phone" type="text" class="mt-1 block w-full" wire:model.defer="state.mobile_phone"/>
            <x-jet-input-error for="mobile_phone" class="mt-2"/>
        </div>

        <!-- Address -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="street" value="{{ __('Street and Housenumber') }}"/>
            <x-jet-input id="street" type="text" class="mt-1 block w-full" wire:model.defer="state.street"/>
            <x-jet-input-error for="street" class="mt-2"/>
        </div>
        <div class="flex col-span-6 sm:col-span-4">
            <div class="w-3/4">
                <x-jet-label for="city" value="{{ __('City') }}"/>
                <x-jet-input id="city" type="text" class="mt-1 block w-full" wire:model.defer="state.city"/>
                <x-jet-input-error for="city" class="mt-2"/>
            </div>
            <div class="w-1/4 ml-4">
                <x-jet-label for="zip" value="{{ __('Zip Code') }}"/>
                <x-jet-input id="zip" type="text" class="mt-1 block w-full" wire:model.defer="state.zip"/>
                <x-jet-input-error for="zip" class="mt-2"/>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
