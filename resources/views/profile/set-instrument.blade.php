<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            Hi! Cool, dass du dabei bist. Bevor es losgehen kann, gib bitte an, in welcher Instrumentengruppe du spielst:
        </div>

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

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-jet-authentication-card>
</x-guest-layout>
