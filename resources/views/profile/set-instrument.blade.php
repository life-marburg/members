<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        <h2 class="text-xl font-display mb-2">Hi!</h2>
        <p class="mb-4 text-sm text-gray-600">
            Cool, dass du dabei bist. Bevor es losgehen kann, gib bitte an, in welcher Instrumentengruppe du spielst:
        </p>

        <div class="mt-4">
            <form method="POST" action="{{ route('set-instrument.save') }}">
                @csrf

                <x-select name="instrument" class="mb-4">
                    @foreach($instruments as $group)
                        <option value="{{ $group->id }}">{{ $group->title }} ({{ $group->instruments->implode('title', ', ') }})</option>
                    @endforeach
                </x-select>

                <div>
                    <x-jet-button type="submit">
                        Instrument Speichern
                    </x-jet-button>
                </div>
            </form>

            <x-help-email/>
            <x-logout-link/>
        </div>
    </x-jet-authentication-card>
</x-guest-layout>
