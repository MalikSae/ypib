@props(['disabled' => false, 'error' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'h-11 w-full border rounded-lg px-3.5 text-sm text-neutral-900 placeholder-neutral-400 bg-white outline-none focus:border-2 focus:border-primary-600 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'border-error focus:border-error' : 'border-neutral-300')]) !!}>
