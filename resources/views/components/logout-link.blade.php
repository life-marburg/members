<form method="POST" action="{{ route('logout') }}" class="mt-4">
    @csrf

    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
        {{ __('Log Out') }}
    </button>
</form>
