@props(['disabled' => false, 'error' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full border rounded-md px-3.5 py-3 text-base md:text-sm text-neutral-900 placeholder-neutral-400 bg-white outline-none focus:ring-2 transition-colors resize-y min-h-[120px] disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'border-error focus:border-error focus:ring-error-500/20' : 'border-neutral-300 focus:border-primary-500 focus:ring-primary-500/20')]) !!}>{{ $slot }}</textarea>
