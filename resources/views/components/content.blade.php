@props(['title' => ''])

<x-app-layout :pageTitle="__($title)">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __($title) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
