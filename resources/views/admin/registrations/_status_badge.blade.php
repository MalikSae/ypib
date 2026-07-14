@php
$colorMap = [
    'warning' => ['bg' => 'bg-warning-50', 'text' => 'text-warning-700', 'border' => 'border-warning-200'],
    'info'    => ['bg' => 'bg-info-50', 'text' => 'text-info-700', 'border' => 'border-info-200'],
    'success' => ['bg' => 'bg-success-50', 'text' => 'text-success-700', 'border' => 'border-success-200'],
    'error'   => ['bg' => 'bg-error-50', 'text' => 'text-error-700', 'border' => 'border-error-200'],
    'primary' => ['bg' => 'bg-primary-50', 'text' => 'text-primary-700', 'border' => 'border-primary-200'],
    'neutral' => ['bg' => 'bg-neutral-100', 'text' => 'text-neutral-700', 'border' => 'border-neutral-200'],
];

$regStatus = $registration ?? new \App\Models\Registration(['status' => $status ?? '']);
$c = $colorMap[$regStatus->getStatusColor()] ?? $colorMap['neutral'];
@endphp
<span class="inline-flex items-center px-2.5 py-1 {{ $c['bg'] }} {{ $c['text'] }} border {{ $c['border'] }} rounded-full text-xs font-semibold whitespace-nowrap">
    {{ $regStatus->getStatusLabel() }}
</span>
