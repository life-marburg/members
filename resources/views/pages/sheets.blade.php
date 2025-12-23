<x-app-layout :pageTitle="__('Sheets')">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                @forelse ($songs as $song)
                    <a href="{{ route('sheets.show', $song) }}" class="block py-2 hover:text-blue-600">
                        {{ $song->title }}
                    </a>
                @empty
                    <p class="text-gray-500">{{ __('No sheets available.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
