<x-app-layout :pageTitle="__('Edit :user', ['user' => $member->name])">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ $member->name }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <livewire:update-personal-data-form :user="$member"/>
            <x-section-border/>
            <livewire:user-update-meta :user="$member"/>
            <x-section-border/>
            <livewire:admin-delete-user-form :user="$member"/>
        </div>
    </div>
</x-app-layout>
