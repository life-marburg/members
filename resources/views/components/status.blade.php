@props(['status' => ''])
@php
    $statusColor = 'bg-orange-100 text-orange-800';
    $statusText = __('New');

    if($status === \App\Models\User::STATUS_UNLOCKED) {
        $statusColor = 'bg-green-100 text-green-800';
        $statusText = __('Active');
    }
    if($status === \App\Models\User::STATUS_LOCKED) {
        $statusColor = 'bg-red-100 text-red-800';
        $statusText = __('Locked');
    }
@endphp
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
        {{ $statusText }}
</span>
