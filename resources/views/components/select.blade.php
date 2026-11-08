@props(['disabled' => false, 'error' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'h-11 w-full border rounded-md px-3.5 text-base md:text-sm text-neutral-900 bg-white outline-none focus:border-2 focus:border-primary-600 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed appearance-none ' . ($error ? 'border-error focus:border-error' : 'border-neutral-300')]) !!}>
    {{ $slot }}
</select>
