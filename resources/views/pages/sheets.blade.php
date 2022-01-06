<x-app-layout :pageTitle="__('Sheets Per Instrument')">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets Per Instrument') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6 pt-2">
                @foreach($groups as $group)
                    <h3 class="text-lg text-gray-800 font-display mt-4 mb-2">{{ $group->title }}</h3>
                    @foreach($group->instruments as $instrument)
                        <a href="{{ route('sheets.show', ['instrument' => $instrument]) }}">
                            {{ $instrument->title }}
                        </a><br/>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
