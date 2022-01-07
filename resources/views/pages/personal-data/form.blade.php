<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo/>
        </x-slot>

        <h2 class="text-xl font-display mb-2">Eine Sache noch...</h2>
        <p class="mb-4 text-sm text-gray-600">
            Bitte gib deine Persönlichen Daten an, damit wir wissen, wer du bist:
        </p>

        <livewire:update-personal-data-form
            :user="Auth::user()"
            :has-form-shell="false"
            redirect-after-save="dashboard"/>

        <p class="my-4 text-sm text-gray-600">
            Du kannst diese Daten später jederzeit ändern.
        </p>

        <x-help-email/>
        <x-logout-link/>
    </x-jet-authentication-card>
</x-guest-layout>
