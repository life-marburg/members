<x-content title="Dashboard">
    <div class="prose">
        <h3>{{ __('Welcome, :user', ['user' => \Illuminate\Support\Facades\Auth::user()->name]) }}</h3>
        {!! $page->content !!}
    </div>
    @role(\App\Rights::R_ADMIN)
        <a href="{{ route('filament.admin.resources.pages.edit', ['record' => $page]) }}" class="block mt-2 text-gray-500 hover:underline">
            {{ __('Edit') }}
        </a>
    @endrole
</x-content>
