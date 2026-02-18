{{-- Share folder dialog --}}
<div>
    @if($this->showShareDialog)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeShareDialog">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Share') }}: {{ $this->shareFolderName }}
                </h3>
                <button wire:click="closeShareDialog" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Inherited info --}}
            @if($inheritedInfo = $this->inheritedShareInfo)
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                    {{ __('Inherits access from') }} <strong>{{ $inheritedInfo['path'] }}</strong>:
                    {{ implode(', ', $inheritedInfo['groups']) }}
                </div>
            @endif

            {{-- Current shares --}}
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Current shares') }}</h4>
                @forelse($this->shares as $share)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $share['is_blocked'] ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-700' }}">
                        <span class="text-sm {{ $share['is_blocked'] ? 'text-red-700 dark:text-red-300 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $share['group_name'] }}
                        </span>
                        <button wire:click="removeShare({{ $share['id'] }})" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                            <x-heroicon-m-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Not shared') }}</p>
                @endforelse
            </div>

            {{-- Add group --}}
            <div class="mb-4">
                <div class="flex gap-2">
                    <select wire:model="selectedGroupId" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm">
                        <option value="">{{ __('Select group...') }}</option>
                        @foreach($this->availableGroups as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="addGroupShare" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700 disabled:opacity-50" @disabled(!$this->selectedGroupId)>
                        {{ __('Add') }}
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex gap-2">
                    <button wire:click="blockFolder" wire:confirm="{{ __('This will remove all existing shares and block access. Continue?') }}" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                        {{ __('Block access') }}
                    </button>
                    @if(count($this->shares) > 0)
                        <button wire:click="removeAllShares" wire:confirm="{{ __('Remove all share rules? The folder will inherit from its parent.') }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                            {{ __('Reset to inherit') }}
                        </button>
                    @endif
                </div>
                <button wire:click="closeShareDialog" class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
