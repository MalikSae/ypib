@extends('layouts.admin')
@section('title', 'Biaya & Kuota — Admin PMB YPIB')
@section('page-title', 'Biaya & Kuota per Periode')

@section('content')

<style>
    /* Tombol buka/tutup rincian biaya daftar ulang: tampak sebagai kontrol yang bisa diklik. */
    .fee-toggle {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        width: 100%; padding: 10px 14px; text-align: left; cursor: pointer;
        border: 1px solid #DEE3E9; border-radius: 10px; background: #F7F9FB;
        color: #0B41CB; font-size: 14px; font-weight: 600;
        transition: background 0.15s, border-color 0.15s;
    }
    .fee-toggle:hover { background: #F2F5FD; border-color: #B8C7F0; }
    .fee-toggle:focus-visible { outline: 2px solid #0B41CB; outline-offset: 2px; }
    .fee-toggle-left { display: flex; align-items: center; gap: 8px; min-width: 0; flex-wrap: wrap; }
    .fee-toggle-chevron { width: 16px; height: 16px; flex-shrink: 0; transition: transform 0.15s; }
    .fee-toggle-chevron.is-open { transform: rotate(180deg); }
    .fee-toggle-hint { color: #7A8794; font-size: 12px; font-weight: 500; }
    .fee-toggle-total { color: #3B404F; font-weight: 700; white-space: nowrap; }
</style>

<div class="mb-6 flex flex-col md:flex-row md:items-center gap-3">
    <a href="{{ route('admin.periods.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Biaya &amp; Kuota &mdash; {{ $period->name }}</h1>
        <p class="mt-0.5 text-sm text-neutral-400">
            {{ $period->open_date->isoFormat('D MMM Y') }} &ndash; {{ $period->close_date->isoFormat('D MMM Y') }}
            &middot; {{ $period->phaseLabel() }}.
            Pendaftar membaca nilai periode pendaftarannya sendiri, jadi perubahan di sini tidak mengubah periode lain.
        </p>
    </div>
</div>

@if($readOnly)
    <div class="mb-6 p-4 rounded-xl border border-warning-200 bg-warning-50 text-sm text-neutral-700">
        Periode ini diarsipkan sehingga biaya dan kuotanya hanya baca. Buka kembali periodenya bila perlu mengubah.
    </div>
@else
    <div class="mb-6 bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
        <form action="{{ route('admin.periods.pricing.copy', $period->id) }}" method="POST"
              class="flex flex-col sm:flex-row gap-3 sm:items-end"
              onsubmit="return confirm('Nilai yang ada pada periode ini akan ditimpa oleh nilai dari periode yang dipilih. Lanjutkan?')">
            @csrf
            <div class="flex-1">
                <x-input-label for="source_period_id" value="Salin biaya dan kuota dari periode" />
                <x-select id="source_period_id" name="source_period_id" required :error="$errors->has('source_period_id')">
                    <option value="">-- Pilih periode sumber --</option>
                    @foreach($otherPeriods as $other)
                        <option value="{{ $other->id }}">{{ $other->name }} ({{ $other->year }})</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('source_period_id')" />
            </div>
            <x-button type="submit" color="neutral" variant="outline">Salin Nilai</x-button>
        </form>
    </div>
@endif

<form action="{{ route('admin.periods.pricing.update', $period->id) }}" method="POST" id="pricing-form"
      x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    @method('PUT')

    <fieldset @if($readOnly) disabled @endif class="space-y-4 border-0 p-0 m-0 min-w-0">
        @foreach($programs as $program)
            @php
                $terms = $program->termsFor($period);
                $details = old("programs.{$program->id}.fee_names")
                    ? collect(old("programs.{$program->id}.fee_names"))->map(fn ($n, $i) => [
                        'name' => $n, 'amount' => (int) preg_replace('/\D/', '', (string) (old("programs.{$program->id}.fee_amounts")[$i] ?? 0)),
                    ])->values()->all()
                    : ($terms->re_registration_fee_details ?? []);
                $key = "programs.{$program->id}";
            @endphp

            {{-- div biasa (bukan x-card): @js tidak diproses di dalam atribut komponen Blade --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-neutral-200 p-5"
                 x-data="{ open: false, items: @js($details), get total() { return this.items.reduce((a, i) => a + (parseInt(i.amount) || 0), 0); } }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                    <div>
                        <div class="text-sm font-bold text-neutral-900">{{ $program->name }}</div>
                        <div class="text-xs text-neutral-400">
                            {{ $program->faculty?->name }} &middot; {{ $program->registration_track === 'non_reguler' ? 'Non-Reguler' : 'Reguler' }}
                            @unless($program->is_active)
                                &middot; <span class="text-warning-700 font-semibold">prodi nonaktif</span>
                            @endunless
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="programs[{{ $program->id }}][is_offered]" value="0">
                        <input type="checkbox" name="programs[{{ $program->id }}][is_offered]" value="1"
                               @checked(old("{$key}.is_offered", $terms->is_offered ? 1 : 0))
                               class="w-5 h-5 accent-primary-600">
                        <span class="text-sm text-neutral-600">Ditawarkan pada periode ini</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <x-input-label :for="'quota_'.$program->id" value="Kuota (0 = tanpa batas)" />
                        <x-text-input type="number" min="0" :id="'quota_'.$program->id" :name="'programs['.$program->id.'][quota]'"
                                      :value="old($key.'.quota', $terms->quota)" required :error="$errors->has($key.'.quota')" />
                        <x-input-error :messages="$errors->get($key.'.quota')" />
                    </div>
                    <div>
                        <x-input-label :for="'fee_'.$program->id" value="Biaya Pendaftaran (Rp)" />
                        <x-text-input type="number" min="0" step="1000" :id="'fee_'.$program->id" :name="'programs['.$program->id.'][registration_fee]'"
                                      :value="old($key.'.registration_fee', $terms->registration_fee)" required :error="$errors->has($key.'.registration_fee')" />
                        <x-input-error :messages="$errors->get($key.'.registration_fee')" />
                    </div>
                    <div>
                        <x-input-label :for="'dp_'.$program->id" value="Minimal DP Daftar Ulang (Rp)" />
                        <x-text-input type="number" min="0" step="1000" :id="'dp_'.$program->id" :name="'programs['.$program->id.'][re_registration_minimum_payment]'"
                                      :value="old($key.'.re_registration_minimum_payment', $terms->re_registration_minimum_payment)" required
                                      :error="$errors->has($key.'.re_registration_minimum_payment')" />
                        <x-input-error :messages="$errors->get($key.'.re_registration_minimum_payment')" />
                    </div>
                    <div>
                        <x-input-label :for="'ref_'.$program->id" value="Komisi Pendaftaran (Rp)" />
                        <x-text-input type="number" min="0" step="1000" :id="'ref_'.$program->id" :name="'programs['.$program->id.'][referral_reward_amount]'"
                                      :value="old($key.'.referral_reward_amount', $terms->referral_reward_amount)" required
                                      :error="$errors->has($key.'.referral_reward_amount')" />
                        <x-input-error :messages="$errors->get($key.'.referral_reward_amount')" />
                    </div>
                    <div>
                        <x-input-label :for="'rereg_'.$program->id" value="Komisi Daftar Ulang (Rp)" />
                        <x-text-input type="number" min="0" step="1000" :id="'rereg_'.$program->id" :name="'programs['.$program->id.'][re_registration_reward_amount]'"
                                      :value="old($key.'.re_registration_reward_amount', $terms->re_registration_reward_amount)" required
                                      :error="$errors->has($key.'.re_registration_reward_amount')" />
                        <x-input-error :messages="$errors->get($key.'.re_registration_reward_amount')" />
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-neutral-100">
                    <button type="button" class="fee-toggle" @click="open = !open" :aria-expanded="open.toString()">
                        <span class="fee-toggle-left">
                            <svg class="fee-toggle-chevron" :class="{ 'is-open': open }" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                            <span x-text="open ? 'Sembunyikan rincian biaya daftar ulang' : 'Atur rincian biaya daftar ulang'"></span>
                            <span class="fee-toggle-hint" x-text="'(' + items.length + ' komponen)'"></span>
                        </span>
                        <span class="fee-toggle-total">Total: Rp <span x-text="total.toLocaleString('id-ID')"></span></span>
                    </button>

                    <div x-show="open" x-cloak class="mt-3 space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex gap-2 items-start">
                                <input type="text" x-model="item.name" :name="'programs[{{ $program->id }}][fee_names][]'"
                                       placeholder="Contoh: Dana Pengembangan Pendidikan"
                                       class="flex-1 h-10 border border-neutral-300 rounded-lg px-3 text-sm text-neutral-900 focus:border-primary-600 outline-none">
                                <input type="number" min="0" x-model="item.amount" :name="'programs[{{ $program->id }}][fee_amounts][]'"
                                       placeholder="0"
                                       class="w-40 h-10 border border-neutral-300 rounded-lg px-3 text-sm text-neutral-900 focus:border-primary-600 outline-none">
                                <button type="button" @click="items.splice(index, 1)" aria-label="Hapus rincian"
                                        class="h-10 px-2 text-error hover:text-error-700 bg-transparent border-none cursor-pointer">&times;</button>
                            </div>
                        </template>
                        <button type="button" @click="items.push({ name: '', amount: 0 })"
                                class="text-sm font-semibold text-primary-600 hover:text-primary-700 bg-transparent border-none cursor-pointer">
                            + Tambah rincian
                        </button>
                        <p class="text-xs text-neutral-400">Total biaya daftar ulang dihitung otomatis dari rincian di atas.</p>
                    </div>
                </div>
            </div>
        @endforeach
    </fieldset>

    @unless($readOnly)
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-neutral-200">
            <a href="{{ route('admin.periods.index') }}" class="decoration-none">
                <x-button color="neutral" variant="ghost">Batal</x-button>
            </a>
            <x-button type="submit" color="primary" ::disabled="submitting">Simpan Biaya &amp; Kuota</x-button>
        </div>
    @endunless
</form>

@endsection
