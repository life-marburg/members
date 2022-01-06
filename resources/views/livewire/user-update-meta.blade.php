<x-jet-form-section submit="update">
    <x-slot name="title">
        {{ __('Admin Settings') }}
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
            <x-jet-label for="instrument_groups" value="{{ __('Instrument Groups') }}"/>
            <x-select id="instrument_groups" type="text" class="mt-1 block w-full" wire:model.defer="state.instrument_groups" multiple>
                @foreach(\App\Models\InstrumentGroup::with('instruments')->get() as $group)
                    <option value="{{ $group->id }}">
                        {{ $group->title }} ({{ $group->instruments->implode('title', ', ') }})
                    </option>
                @endforeach
            </x-select>
            <x-jet-input-error for="instrument_groups" class="mt-2"/>
        </div>

        <!-- Disable after days -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="disable-after-days" value="{{ __('Disable after inactivity') }}"/>
            <x-select id="disable-after-days" type="text" class="mt-1 block w-full"
                      wire:model.defer="state.disable_after">
                <option value="null">{{ __('Never') }}</option>
                <option value="14">{{ __('After :n days', ['n' => 14]) }}</option>
                <option value="90">{{ __('After :n days', ['n' => 90]) }}</option>
            </x-select>
            <x-jet-input-error for="disable-after-days" class="mt-2"/>
        </div>

        <!-- Admin -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label>
                <x-jet-checkbox id="is_admin" wire:model="state.is_admin" class="mr-2"/>
                {{ __('Is Admin') }}
            </x-jet-label>
            <x-jet-input-error for="is_admin" class="mt-2"/>
        </div>

        <!-- All Instruments -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label>
                <x-jet-checkbox id="can_view_all_instruments" wire:model="state.can_view_all_instruments" class="mr-2"
                                :disabled="$state['is_admin']"/>
                {{ __('Can view sheets for all instruments') }}
                @if($state['is_admin'])
                    <br/>
                    <span class="text-xs text-gray-500">
                        {{ __('Already implied since this user has admin permissions.') }}
                    </span>
                @endif
            </x-jet-label>
            <x-jet-input-error for="can_view_all_instruments" class="mt-2"/>
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
