@props(['disabled' => false, 'error' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'h-11 w-full border rounded-md pl-3.5 pr-10 text-base md:text-sm text-neutral-900 bg-white outline-none focus:ring-2 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed appearance-none ' . ($error ? 'border-error focus:border-error focus:ring-error-500/20' : 'border-neutral-300 focus:border-primary-500 focus:ring-primary-500/20')]) !!}>
    {{ $slot }}
</select>
