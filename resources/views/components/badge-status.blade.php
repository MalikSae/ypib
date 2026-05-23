@props(['active' => false, 'toggleAction' => null, 'activeText' => 'Aktif', 'inactiveText' => 'Nonaktif'])

@php
    $activeClass = 'inline-flex items-center px-2.5 py-1 bg-success-50 text-success-700 border border-success-200 rounded-full text-xs font-semibold';
    $inactiveClass = 'inline-flex items-center px-2.5 py-1 bg-neutral-100 text-neutral-600 border border-neutral-200 rounded-full text-xs font-semibold';
    $badgeClass = $active ? $activeClass : $inactiveClass;
    $text = $active ? $activeText : $inactiveText;
@endphp

@if($toggleAction)
    <form action="{{ $toggleAction }}" method="POST" class="m-0 inline-block">
        @csrf @method('PATCH')
        <button type="submit" aria-label="Toggle Status" class="bg-transparent border-none cursor-pointer p-0 hover:opacity-80 transition-opacity focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full">
            <span class="{{ $badgeClass }}">{{ $text }}</span>
        </button>
    </form>
@else
    <span class="{{ $badgeClass }}">{{ $text }}</span>
@endif
