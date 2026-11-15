{{--
    Saklar on/off (role="switch") untuk status yang berlaku sebagai keadaan, misal "Aktif" atau "Tampilkan di publik".
    Untuk memilih baris, menyetujui pernyataan, atau memilih beberapa item, pakai <x-checkbox>.
    <x-toggle name="is_active" value="1" :checked="(bool) old('is_active', $x->is_active)" label="Aktif" />
    Sertakan <input type="hidden" name="is_active" value="0"> sebelumnya bila nilai mati harus ikut terkirim.
--}}
@props(['label' => null])

<label class="inline-flex items-center gap-3 min-h-11 md:min-h-9 cursor-pointer has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
    <span class="relative inline-flex shrink-0">
        <input {{ $attributes->merge(['type' => 'checkbox', 'role' => 'switch', 'class' => 'peer sr-only']) }}>
        <span aria-hidden="true" class="h-6 w-11 rounded-full bg-neutral-300 transition-colors motion-reduce:transition-none peer-checked:bg-primary-600 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500/30 peer-focus-visible:ring-offset-2"></span>
        <span aria-hidden="true" class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform motion-reduce:transition-none peer-checked:translate-x-5"></span>
    </span>
    @if($label !== null || ! $slot->isEmpty())
        <span class="text-sm text-neutral-700">{{ $label ?? $slot }}</span>
    @endif
</label>
