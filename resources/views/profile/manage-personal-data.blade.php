<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Personal Data') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <livewire:profile.update-profile-information-form/>
            <x-jet-section-border/>
            <livewire:update-personal-data-form/>
            <x-jet-section-border/>
        </div>
    </div>
</x-app-layout>
