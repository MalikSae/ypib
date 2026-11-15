{{-- active: isian penyaring yang sedang terisi (misal kotak cari). Tampil dengan border dan latar primary tipis. --}}
@props(['disabled' => false, 'error' => false, 'active' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'h-11 w-full border rounded-md px-3.5 text-base md:text-sm text-neutral-900 placeholder-neutral-400 outline-none focus:ring-2 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'bg-white border-error focus:border-error focus:ring-error-500/20' : 'focus:border-primary-500 focus:ring-primary-500/20 ' . ($active ? 'border-primary-300 bg-primary-50' : 'border-neutral-300 bg-white'))]) !!}>
