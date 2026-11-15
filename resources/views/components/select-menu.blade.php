{{--
    Dropdown kustom (pengganti <select> bawaan browser). Pola ARIA "select-only combobox".

    Form biasa:  <x-select-menu id="jenis" name="jenis" :options="['' => 'Semua jenis'] + $kinds" :value="request('jenis')" />
    Livewire:    <x-select-menu id="event" wire:model.live="event" :value="$event" :options="['' => 'Semua event'] + $eventOptions" />
    Alpine:      <x-select-menu id="kind" name="kind" x-model="kind" :options="$kinds" :value="old('kind', 'event')" />
    Kirim form saat berubah:  onchange="this.closest('form').submit()"  (event "change" naik dari input tersembunyi)

    - options: array nilai => label. Nilai dibandingkan sebagai teks.
    - Seperti <select> bawaan: bila nilai kosong/tidak dikenal dan tidak ada pilihan bernilai '', pilihan pertama dipakai.
    - Daftar pilihan selebar tombol. Label panjang dipotong, teks lengkap di title.
--}}
@props([
    'options' => [],
    'value' => null,
    'name' => null,
    'id' => null,
    'placeholder' => 'Pilih',
    'error' => false,
    'disabled' => false,
    'required' => false,
])

@php
    $id ??= 'select-' . \Illuminate\Support\Str::random(6);
    $items = collect($options)->map(fn ($label, $val) => ['value' => (string) $val, 'label' => trim((string) $label)])->values();
    $current = (string) ($value ?? '');
    if (! $items->contains('value', $current) && ! $items->contains('value', '') && $items->isNotEmpty()) {
        $current = $items->first()['value'];
    }
    $currentLabel = $items->firstWhere('value', $current)['label'] ?? $placeholder;
    $ariaLabel = $attributes->get('aria-label');
    $rootAttributes = $attributes->whereDoesntStartWith('wire:model')->except(['aria-label']);
@endphp

<div x-data="{
        open: false,
        value: @js($current),
        options: @js($items),
        active: -1,
        typed: '',
        typedTimer: null,
        get selected() { return this.options.find(o => o.value === String(this.value ?? '')) },
        get label() { return this.selected ? this.selected.label : @js($placeholder) },
        show() {
            if (this.open || this.$refs.button.disabled) return;
            this.open = true;
            this.active = Math.max(this.options.indexOf(this.selected), 0);
            this.$nextTick(() => this.scrollToActive());
        },
        hide(focus = true) { this.open = false; if (focus) this.$refs.button.focus(); },
        choose(index) {
            const option = this.options[index];
            if (!option) return;
            const changed = option.value !== String(this.value ?? '');
            this.value = option.value;
            this.hide();
            if (changed) this.$nextTick(() => this.$refs.input.dispatchEvent(new Event('change', { bubbles: true })));
        },
        move(step) {
            if (!this.open) { this.show(); return; }
            this.active = Math.min(Math.max(this.active + step, 0), this.options.length - 1);
            this.scrollToActive();
        },
        edge(toEnd) { if (!this.open) this.show(); this.active = toEnd ? this.options.length - 1 : 0; this.scrollToActive(); },
        scrollToActive() { const el = this.$refs.list?.querySelectorAll('[role=option]')[this.active]; if (el) el.scrollIntoView({ block: 'nearest' }); },
        typeahead(key) {
            this.typed += key.toLowerCase();
            clearTimeout(this.typedTimer);
            this.typedTimer = setTimeout(() => this.typed = '', 600);
            const index = this.options.findIndex(o => o.label.toLowerCase().startsWith(this.typed));
            if (index < 0) return;
            if (this.open) { this.active = index; this.scrollToActive(); } else { this.choose(index); }
        },
        onKey(e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); this.move(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); this.move(-1); }
            else if (e.key === 'Home') { e.preventDefault(); this.edge(false); }
            else if (e.key === 'End') { e.preventDefault(); this.edge(true); }
            else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.open ? this.choose(this.active) : this.show(); }
            else if (e.key === 'Escape') { if (this.open) { e.preventDefault(); this.hide(); } }
            else if (e.key === 'Tab') { this.open = false; }
            else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) { this.typeahead(e.key); }
        },
     }"
     x-modelable="value"
     {{ $attributes->whereStartsWith('wire:model') }}
     x-on:click.outside="open = false"
     {{ $rootAttributes->merge(['class' => 'relative']) }}>

    {{-- Selalu ada (sumber event "change"); hanya dikirim bila punya name dan tidak nonaktif. --}}
    <input type="hidden" x-ref="input" :value="value" @if($name) name="{{ $name }}" @endif @disabled($disabled)>

    <button type="button" id="{{ $id }}" x-ref="button" role="combobox"
            aria-haspopup="listbox" aria-controls="{{ $id }}-list"
            @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
            @if($required) aria-required="true" @endif
            @if($error) aria-invalid="true" @endif
            :aria-expanded="open.toString()"
            :aria-activedescendant="open && active >= 0 ? '{{ $id }}-option-' + active : null"
            @disabled($disabled)
            x-on:click="open ? hide() : show()" x-on:keydown="onKey($event)"
            class="h-11 w-full flex items-center justify-between gap-2 rounded-md border bg-white pl-3.5 pr-3 text-left text-base md:text-sm text-neutral-900 outline-none transition-colors focus:border-2 focus:border-primary-600 focus-visible:ring-2 focus-visible:ring-primary-500/30 disabled:opacity-50 disabled:bg-neutral-50 disabled:cursor-not-allowed {{ $error ? 'border-error' : 'border-neutral-300' }}">
        <span class="truncate" x-text="label" :title="label">{{ $currentLabel }}</span>
        <svg class="w-4 h-4 shrink-0 text-neutral-500 transition-transform motion-reduce:transition-none" :class="open ? 'rotate-180' : ''"
             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Selebar tombol: tidak boleh melebar keluar wadah atau layar. --}}
    <ul id="{{ $id }}-list" x-ref="list" role="listbox" x-show="open" x-cloak
        x-transition.opacity.duration.100ms
        class="absolute left-0 right-0 z-30 mt-1 max-h-64 overflow-y-auto overflow-x-hidden rounded-lg border border-neutral-200 bg-white py-1 shadow-lg">
        <template x-for="(option, index) in options" :key="option.value">
            <li role="option" :id="'{{ $id }}-option-' + index" :title="option.label"
                :aria-selected="(option.value === String(value ?? '')).toString()"
                x-on:click="choose(index)" x-on:mouseenter="active = index"
                :class="index === active ? 'bg-neutral-50' : ''"
                class="flex items-center justify-between gap-3 min-h-11 md:min-h-9 px-3.5 text-base md:text-sm cursor-pointer select-none">
                <span class="truncate min-w-0" :class="option.value === String(value ?? '') ? 'font-semibold text-primary-700' : 'text-neutral-800'" x-text="option.label"></span>
                <svg x-show="option.value === String(value ?? '')" class="w-4 h-4 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </li>
        </template>
    </ul>
</div>
