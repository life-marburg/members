<x-app-layout :pageTitle="__('Sheets For :instrument', ['instrument' => $instrument->title])">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets For :instrument', ['instrument' => $instrument->title]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                @foreach($sheets as $sheet => $variants)
                    <h2 class="font-display mb-2 mt-4 text-xl">{{ preg_replace('/([A-Z]|[0-9])/', ' $1', $sheet) }}</h2>
                    @foreach($variants as $variant)
                        <x-link href="{{ route('sheets.download', ['sheet' => $sheet, 'instrument' => $instrument, 'variant' => $variant['path']]) }}">
                            {{ $variant['title'] }}
                        </x-link><br/>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
