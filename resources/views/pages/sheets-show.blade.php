<x-app-layout :pageTitle="__('Sheets For :instrument', ['instrument' => $instrument->title])">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets For :instrument', ['instrument' => $instrument->title]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                @forelse($sheets as $songTitle => $songSheets)
                    <h2 class="font-display mb-2 mt-4 text-xl">{{ $songTitle }}</h2>
                    @foreach($songSheets as $sheet)
                        <x-link href="{{ route('sheets.download', $sheet) }}">
                            {{ $sheet->display_title }}
                        </x-link><br/>
                    @endforeach
                @empty
                    <p class="text-gray-500">{{ __('No sheets available for this instrument.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
