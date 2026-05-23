@props(['disabled' => false, 'error' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full bg-white border rounded-xl px-4 py-3 text-neutral-900 focus:outline-none focus:border-primary-600 focus:ring-2 focus:ring-primary-200 hover:border-neutral-400 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed appearance-none ' . ($error ? 'border-error focus:border-error focus:ring-error-light bg-error-light/20 text-error-dark' : 'border-neutral-200')]) !!}>
    {{ $slot }}
</select>
