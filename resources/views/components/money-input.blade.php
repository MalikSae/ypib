@props([
    'name',
    'value' => null,
    'error' => false,
    'disabled' => false,
    'maxDigits' => 10,
])

@php
    // Nilai awal selalu angka bulat; kosong tetap kosong.
    $raw = ($value === null || $value === '') ? '' : (string) (int) preg_replace('/\D/', '', (string) $value);
    $shown = $raw === '' ? '' : number_format((int) $raw, 0, ',', '.');
@endphp

{{-- Input nominal: tampil dengan titik ribuan (1.500.000), tetapi yang dikirim ke server
     adalah angka polos lewat input tersembunyi, sehingga validasi dan penyimpanan tidak berubah. --}}
<div x-data="moneyInput(@js($raw), {{ (int) $maxDigits }})" class="w-full">
    <input type="text" inputmode="numeric" autocomplete="off"
           value="{{ $shown }}"
           x-bind:value="display"
           x-on:input="onInput($event)"
           {{ $disabled ? 'disabled' : '' }}
           {!! $attributes->merge(['class' => 'h-11 w-full border rounded-lg px-3.5 text-sm text-neutral-900 placeholder-neutral-400 bg-white outline-none focus:border-2 focus:border-primary-600 transition-colors disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed ' . ($error ? 'border-error focus:border-error' : 'border-neutral-300')]) !!}>
    <input type="hidden" name="{{ $name }}" value="{{ $raw }}" x-bind:value="raw" {{ $disabled ? 'disabled' : '' }}>
</div>

@once
<script>
    // Pemformat nominal rupiah: 1500000 -> "1.500.000" (titik ribuan, tanpa bergantung pada locale browser).
    window.rupiah = function (value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        return digits === '' ? '' : String(Number(digits)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    // Memformat isi sebuah <input> saat diketik dan menjaga posisi kursor. Mengembalikan angka polos.
    window.moneyField = function (el, maxDigits = 10) {
        const onlyDigits = (v) => String(v ?? '').replace(/\D/g, '');
        const caret = el.selectionStart ?? el.value.length;
        const digitsBeforeCaret = onlyDigits(el.value.slice(0, caret)).length;

        let digits = onlyDigits(el.value).slice(0, maxDigits);
        digits = digits === '' ? '' : String(Number(digits));
        el.value = window.rupiah(digits);

        let position = 0, seen = 0;
        while (position < el.value.length && seen < digitsBeforeCaret) {
            if (/\d/.test(el.value[position])) { seen++; }
            position++;
        }
        try { el.setSelectionRange(position, position); } catch (e) { /* tipe input tidak mendukung */ }

        return digits;
    };

    window.moneyInput = function (initial, maxDigits = 10) {
        const start = String(initial ?? '').replace(/\D/g, '');

        return {
            raw: start,
            display: window.rupiah(start),
            onInput(event) {
                this.raw = window.moneyField(event.target, maxDigits);
                this.display = event.target.value;
            },
        };
    };
</script>
@endonce
