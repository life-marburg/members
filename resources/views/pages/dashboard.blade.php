<x-content title="Dashboard">
    <div class="prose">
        {!! $page->content !!}
    </div>
    @if($canEdit)
        <x-link href="{{ route('pages.edit', ['page' => $page]) }}" class="block mt-2 text-gray-500 hover:underline">
            {{ __('Edit') }}
        </x-link>
    @endif
</x-content>
