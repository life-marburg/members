<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo/>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            <p>
                Hi!<br/>

                Ein Admin muss dich noch für den Zugang freischalten, damit du alles in vollem Umfang nutzen kannst.
            </p>
            <p class="mt-2">
                Du erhältst dann eine E-Mail.
            </p>
        </div>

        <x-help-email/>
        <x-logout-link/>
    </x-jet-authentication-card>
</x-guest-layout>
