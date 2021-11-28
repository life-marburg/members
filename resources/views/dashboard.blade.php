<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-4">
                <div class="prose">
                    {!! $page->content !!}
                </div>
                @if($canEdit)
                    <x-link href="{{ route('pages.edit', ['page' => $page]) }}" class="block mt-2 text-gray-500 hover:underline">
                        {{ __('Edit') }}
                    </x-link>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
