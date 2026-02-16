<x-app-layout :pageTitle="__('Files')">
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Files') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">

                {{-- Breadcrumbs --}}
                <nav class="mb-4 text-sm text-gray-500">
                    @foreach ($breadcrumbs as $i => $crumb)
                        @if ($i > 0)
                            <span class="mx-1">/</span>
                        @endif
                        @if ($loop->last)
                            <span class="text-gray-800 font-medium">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ $crumb['path'] === null ? route('files.index') : route('files.index', $crumb['path']) }}"
                               class="text-blue-600 hover:underline">
                                {{ $crumb['name'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                {{-- Folders --}}
                @foreach ($folders as $folder)
                    <a href="{{ route('files.index', $folder['path']) }}"
                       class="flex items-center gap-2 py-2 hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                        </svg>
                        {{ $folder['name'] }}
                    </a>
                @endforeach

                {{-- Files --}}
                @foreach ($files as $file)
                    <a href="{{ route('files.download', $file['path']) }}"
                       class="flex items-center justify-between py-2 hover:text-blue-600">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            {{ $file['name'] }}
                        </span>
                        <span class="text-sm text-gray-400">
                            {{ \Illuminate\Support\Number::fileSize($file['size']) }}
                        </span>
                    </a>
                @endforeach

                @if ($folders->isEmpty() && $files->isEmpty())
                    <p class="text-gray-500">{{ __('No files available.') }}</p>
                @endif

                @if ($currentPath !== '')
                    <div class="mt-6">
                        <x-link href="{{ count($breadcrumbs) > 2 ? route('files.index', $breadcrumbs[count($breadcrumbs) - 2]['path']) : route('files.index') }}">
                            {{ __('Back') }}
                        </x-link>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
