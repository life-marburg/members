<x-app-layout :pageTitle="__('Sheets')">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Sheets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6"
                 x-data="songSearch({{ \Illuminate\Support\Js::from($songs) }}, {{ \Illuminate\Support\Js::from($songSets->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_new' => $s->is_new])) }})">

                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            x-model="query"
                            @input="filter()"
                            placeholder="{{ __('Search songs...') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    @if($songSets->isNotEmpty())
                        <div class="sm:w-64">
                            <select
                                x-model="selectedSet"
                                @change="filter()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('All Songs') }}</option>
                                <template x-for="set in songSets" :key="set.id">
                                    <option :value="set.id" x-text="set.title + (set.is_new ? ' ✱' : '')"></option>
                                </template>
                            </select>
                        </div>
                    @endif
                </div>

                <template x-for="song in results" :key="song.id">
                    <a :href="'/sheets/' + song.id"
                       class="block py-2 hover:text-blue-600">
                        <span x-text="song.title"></span>
                        <span x-show="song.is_new"
                              class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            {{ __('NEW') }}
                        </span>
                    </a>
                </template>

                <p x-show="results.length === 0 && (query.trim() !== '' || selectedSet)"
                   class="text-gray-500">
                    {{ __('No songs found.') }}
                </p>

                <p x-show="results.length === 0 && query.trim() === '' && !selectedSet"
                   class="text-gray-500">
                    {{ __('No sheets available.') }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
