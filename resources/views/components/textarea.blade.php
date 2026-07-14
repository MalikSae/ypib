@props(['disabled' => false, 'error' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full border rounded-lg px-3.5 py-3 text-sm text-neutral-900 placeholder-neutral-400 bg-white outline-none focus:border-2 focus:border-primary-600 transition-colors resize-y min-h-[120px] disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'border-error focus:border-error' : 'border-neutral-300')]) !!}>{{ $slot }}</textarea>
