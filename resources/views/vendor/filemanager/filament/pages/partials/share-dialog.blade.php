{{-- Share folder modal --}}
<x-filament::modal id="share-folder-modal" width="md">
    <x-slot name="heading">
        {{ __('Share') }}: {{ $this->shareFolderName }}
    </x-slot>

    @if($this->shareFolderPath)
        {{-- Inherited info (only for subfolders) --}}
        @if(! $this->isRootFolder && $inheritedInfo = $this->inheritedShareInfo)
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                {{ __('Inherits access from') }} <strong>{{ $inheritedInfo['path'] }}</strong>:
                {{ implode(', ', $inheritedInfo['groups']) }}
            </div>
        @endif

        {{-- Current shares --}}
        <div>
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
        @if(count($this->availableGroups) > 0)
            <div x-data="{ groupId: '' }">
                <div class="flex gap-2 items-end">
                    <x-filament::input.wrapper class="flex-1">
                        <x-filament::input.select
                            wire:model.live="selectedGroupId"
                            x-model="groupId"
                        >
                            <option value="">{{ __('Select group...') }}</option>
                            @foreach($this->availableGroups as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    <x-filament::button
                        wire:click="addGroupShare"
                        size="sm"
                        x-bind:disabled="!groupId"
                    >
                        {{ __('Add') }}
                    </x-filament::button>
                </div>
            </div>
        @endif
    @endif

    <x-slot name="footerActions">
        @if(! $this->isRootFolder)
            <x-filament::button
                wire:click="blockFolder"
                wire:confirm="{{ __('This will remove all existing shares and block access. Continue?') }}"
                color="danger"
            >
                {{ __('Block access') }}
            </x-filament::button>
        @endif
        @if($this->shareFolderPath && count($this->shares) > 0)
            @if($this->isRootFolder)
                <x-filament::button
                    wire:click="removeAllShares"
                    wire:confirm="{{ __('Remove all shares? The folder will no longer be accessible.') }}"
                    color="danger"
                >
                    {{ __('Remove all shares') }}
                </x-filament::button>
            @else
                <x-filament::button
                    wire:click="removeAllShares"
                    wire:confirm="{{ __('Remove all share rules? The folder will inherit from its parent.') }}"
                    color="gray"
                >
                    {{ __('Reset to inherit') }}
                </x-filament::button>
            @endif
        @endif
        <x-filament::button
            x-on:click="$dispatch('close-modal', { id: 'share-folder-modal' })"
            color="gray"
        >
            {{ __('Close') }}
        </x-filament::button>
    </x-slot>
</x-filament::modal>
