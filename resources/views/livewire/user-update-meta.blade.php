<x-jet-form-section submit="update">
    <x-slot name="title">
        {{ __('Various Settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Set the user\'s state, admin status or instrument here.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Status -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="status" value="{{ __('Status') }}"/>
            <x-select id="status" type="text" class="mt-1 block w-full" wire:model.defer="state.status">
                <option value="{{ \App\Models\User::STATUS_NEW  }}">{{ __('New') }}</option>
                <option value="{{ \App\Models\User::STATUS_UNLOCKED  }}">{{ __('Active') }}</option>
                <option value="{{ \App\Models\User::STATUS_LOCKED  }}">{{ __('Locked') }}</option>
            </x-select>
            <x-jet-input-error for="status" class="mt-2"/>
        </div>

        <!-- Instrument -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="instrument" value="{{ __('Instrument') }}"/>
            <x-select id="instrument" type="text" class="mt-1 block w-full" wire:model.defer="state.instrument">
                <option></option>
                @foreach(\App\Instruments::INSTRUMENT_GROUPS as $key => $instrument)
                    <option value="{{ $key }}">{{ $instrument['name'] }} ({{ implode(', ', $instrument['instruments']) }})</option>
                @endforeach
            </x-select>
            <x-jet-input-error for="instrument" class="mt-2"/>
        </div>

        <!-- Admin -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label>
                <x-jet-checkbox id="is_admin" wire:model.defer="state.is_admin" class="mr-2"/>
                {{ __('Is Admin') }}
            </x-jet-label>
            <x-jet-input-error for="is_admin" class="mt-2"/>
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
