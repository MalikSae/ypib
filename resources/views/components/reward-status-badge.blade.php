@props(['status'])

@php
    $reward = new \App\Models\Reward(['status' => $status]);
    $color = $reward->status_color;
    $label = $reward->status_label;

    $colorClasses = match($color) {
        'blue'  => 'bg-info/10 text-info-700 border-info-200',
        'green' => 'bg-success/10 text-success-700 border-success-200',
        'red'   => 'bg-error/10 text-error-700 border-error-200',
        'gray'  => 'bg-neutral-100 text-neutral-700 border-neutral-200',
        default => 'bg-neutral-100 text-neutral-700 border-neutral-200',
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs font-bold capitalize {{ $colorClasses }}">
    {{ $label }}
</span>
