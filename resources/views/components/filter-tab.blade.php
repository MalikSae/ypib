{{-- Tab pilihan untuk filter (misal status). Tidak aktif: timbul seperti tombol. Aktif: tampak tertekan. --}}
@props(['active' => false, 'count' => null])

@php
    $base = 'inline-flex items-center gap-2 whitespace-nowrap h-11 md:h-9 rounded-md border px-3 text-sm font-semibold transition duration-200 ease-in-out motion-reduce:transition-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:cursor-wait';
    $state = $active
        ? 'x-filter-tab-active bg-primary-50 border-primary-200 text-primary-700'
        : 'x-button-raised bg-white border-neutral-200 text-neutral-600 hover:bg-neutral-50';
@endphp

<button type="button" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->merge(['class' => "$base $state"]) }}>
    <span>{{ $slot }}</span>
    @if($count !== null)
        <span class="{{ $active ? 'text-primary-700' : 'text-neutral-400' }}">{{ $count }}</span>
    @endif
</button>
