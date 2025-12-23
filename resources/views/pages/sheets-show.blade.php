<x-app-layout :pageTitle="__('Sheets for :song', ['song' => $song->title])">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets for :song', ['song' => $song->title]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                @forelse ($sheetsByGroup as $groupName => $instruments)
                    <h3 class="font-display mb-2 mt-4 text-lg text-gray-800">{{ $groupName }}</h3>

                    @foreach ($instruments as $instrumentName => $sheets)
                        <h4 class="font-medium mt-2 mb-1">{{ $instrumentName }}</h4>
                        <ul class="ml-4">
                            @foreach ($sheets->sortBy('part_number') as $sheet)
                                <li>
                                    <x-link href="{{ route('sheets.download', $sheet) }}">
                                        {{ $sheet->display_title }}
                                    </x-link>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                @empty
                    <p class="text-gray-500">{{ __('No sheets available for your instruments.') }}</p>
                @endforelse

                <div class="mt-6">
                    <x-link href="{{ route('sheets.index') }}">
                        {{ __('Back to song list') }}
                    </x-link>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
