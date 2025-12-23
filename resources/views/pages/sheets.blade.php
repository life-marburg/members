<x-app-layout :pageTitle="__('Sheets')">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6"
                 x-data="songSearch({{ Js::from($songs->map(fn($s) => ['id' => $s->id, 'title' => $s->title])) }})">

                <div class="mb-4">
                    <input
                        type="text"
                        x-model="query"
                        @input="search()"
                        placeholder="{{ __('Search songs...') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <template x-for="song in results" :key="song.id">
                    <a :href="'/sheets/' + song.id"
                       class="block py-2 hover:text-blue-600"
                       x-text="song.title">
                    </a>
                </template>

                <p x-show="results.length === 0 && query.trim() !== ''"
                   class="text-gray-500">
                    {{ __('No songs found.') }}
                </p>

                <p x-show="results.length === 0 && query.trim() === ''"
                   class="text-gray-500">
                    {{ __('No sheets available.') }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
