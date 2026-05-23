@props([
    'type' => 'button',
    'color' => 'primary',
    'size' => 'md',
    'variant' => 'solid'
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClasses = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-3 text-base',
        'lg' => 'px-6 py-4 text-lg',
    ][$size] ?? 'px-4 py-3 text-base';

    $colorVariants = [
        'primary' => [
            'solid' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
            'outline' => 'border border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
            'ghost' => 'text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
        ],
        'secondary' => [
            'solid' => 'bg-secondary-500 text-white hover:bg-secondary-600 focus:ring-secondary-400',
            'outline' => 'border border-secondary-500 text-secondary-600 hover:bg-secondary-50 focus:ring-secondary-400',
            'ghost' => 'text-secondary-600 hover:bg-secondary-50 focus:ring-secondary-400',
        ],
        'success' => [
            'solid' => 'bg-success text-white hover:bg-success-dark focus:ring-success',
            'outline' => 'border border-success text-success hover:bg-success-light focus:ring-success',
            'ghost' => 'text-success hover:bg-success-light focus:ring-success',
        ],
        'danger' => [
            'solid' => 'bg-error text-white hover:bg-error-dark focus:ring-error',
            'outline' => 'border border-error text-error hover:bg-error-light focus:ring-error',
            'ghost' => 'text-error hover:bg-error-light focus:ring-error',
        ],
        'neutral' => [
            'solid' => 'bg-neutral-600 text-white hover:bg-neutral-700 focus:ring-neutral-500',
            'outline' => 'border border-neutral-300 text-neutral-700 hover:bg-neutral-50 focus:ring-neutral-500',
            'ghost' => 'text-neutral-600 hover:bg-neutral-100 focus:ring-neutral-500',
        ],
    ];

    $colorClasses = $colorVariants[$color][$variant] ?? $colorVariants['primary']['solid'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $colorClasses"]) }}>
    {{ $slot }}
</button>
