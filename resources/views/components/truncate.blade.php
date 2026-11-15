{{--
    Teks satu baris untuk sel tabel (aturan "data tabel maksimal dua baris"): dipotong dengan elipsis,
    teks lengkap muncul di title.

    <x-truncate :value="$reg->full_name" class="text-sm font-medium text-neutral-900" />
    <x-truncate :value="$reg->user?->email" class="text-xs text-neutral-500" />

    - max: lebar maksimal (kelas Tailwind), bawaan max-w-xs.
    - fallback: teks bila nilai kosong, bawaan "—".
--}}
@props(['value' => null, 'max' => 'max-w-xs', 'fallback' => '—'])

<span {{ $attributes->merge(['class' => "block truncate $max"]) }} @if(filled($value)) title="{{ $value }}" @endif>{{ filled($value) ? $value : $fallback }}</span>
