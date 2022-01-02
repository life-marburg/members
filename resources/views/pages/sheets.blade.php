<x-app-layout :pageTitle="__('Sheets Per Instrument')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                @foreach($instruments as $instrument)
                    <a href="{{ route('sheets.show', ['instrument' => $instrument]) }}">
                        {{ $instrument }}
                    </a><br/>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
