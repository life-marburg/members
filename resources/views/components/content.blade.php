@props(['title' => '', 'isFullwidth' => false])

<x-app-layout :pageTitle="__($title)">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __($title) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="@if(!$isFullwidth) max-w-7xl @endif mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="border border-green-500 bg-green-100 p-3 rounded mb-4 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
