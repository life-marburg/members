<div>
    <x-slot name="header">
        <h2 class="text-xl text-gray-800 leading-tight font-display">
            {{ __('Files') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">

                {{-- Breadcrumbs --}}
                @if (count($this->breadcrumbs) > 1)
                <nav class="mb-4 text-sm text-gray-500 flex items-center flex-wrap gap-1">
                    @foreach ($this->breadcrumbs as $i => $crumb)
                        @if ($i > 0)
                            <span class="text-gray-400">/</span>
                        @endif
                        @if ($loop->last)
                            <span class="text-gray-800 font-medium">{{ $crumb['name'] }}</span>
                        @else
                            <button wire:click="navigateTo('{{ $crumb['id'] }}')"
                                    class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $crumb['name'] }}
                            </button>
                        @endif
                    @endforeach
                </nav>
                @endif

                {{-- Items --}}
                <div class="divide-y divide-gray-100">
                    @forelse ($this->items as $item)
                        @if ($item->isFolder())
                            <button wire:click="navigateTo('{{ $item->getIdentifier() }}')"
                                    class="flex items-center gap-3 py-3 px-2 w-full text-left hover:bg-gray-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                </svg>
                                <span class="font-medium text-gray-900">{{ $item->getName() }}</span>
                            </button>
                        @else
                            <button wire:click="download('{{ $item->getIdentifier() }}')"
                                    class="flex items-center justify-between py-3 px-2 w-full text-left hover:bg-gray-50 rounded-lg transition-colors">
                                <span class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">{{ $item->getName() }}</span>
                                </span>
                                <span class="text-sm text-gray-400">
                                    {{ $item->getFormattedSize() }}
                                </span>
                            </button>
                        @endif
                    @empty
                        <p class="py-4 text-gray-500">{{ __('No files available.') }}</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>
