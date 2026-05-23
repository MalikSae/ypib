@props(['disabled' => false, 'error' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full bg-white border rounded-xl px-4 py-3 text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-primary-600 focus:ring-2 focus:ring-primary-200 hover:border-neutral-400 transition-colors resize-y min-h-[120px] disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'border-error focus:border-error focus:ring-error-light bg-error-light/20 text-error-dark' : 'border-neutral-200')]) !!}>{{ $slot }}</textarea>
