@props(['submit' => null, 'hasFormShell' => true])

<div {{ $attributes->merge(['class' => $hasFormShell ? 'md:grid md:grid-cols-3 md:gap-6' : '']) }}>
    @if($hasFormShell)
        <x-jet-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-jet-section-title>
    @endif

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit.prevent="{{ $submit }}">
            @if($hasFormShell)
                <div
                    class="px-4 py-5 bg-white sm:p-6 shadow {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                    <div class="grid grid-cols-6 gap-6">
                        {{ $form }}
                    </div>
                </div>
            @else
                <div class="grid gap-6">
                    {{ $form }}
                </div>
            @endif

            @if (isset($actions))
                <div
                    class="flex items-center justify-end text-right py-3 {{ $hasFormShell ? 'bg-gray-50 shadow sm:rounded-bl-md sm:rounded-br-md px-4 sm:px-6' : '' }}">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
