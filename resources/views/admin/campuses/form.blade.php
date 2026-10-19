@extends('layouts.admin')
@section('title', (isset($campus) ? 'Edit Kampus' : 'Tambah Kampus') . ' — Admin PMB YPIB')
@section('page-title', isset($campus) ? 'Edit Kampus' : 'Tambah Kampus')

@section('content')

<div class="max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.campuses.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-neutral-900 tracking-tight">{{ isset($campus) ? 'Edit Kampus' : 'Tambah Kampus' }}</h1>
            <p class="mt-0.5 text-sm text-neutral-400">Alamat, WhatsApp, dan rekening dipakai pada SKL, instruksi interview, dan daftar ulang pendaftar kampus ini.</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ isset($campus) ? route('admin.campuses.update', $campus->id) : route('admin.campuses.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if(isset($campus)) @method('PUT') @endif

            <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                <div>
                    <x-input-label for="name" value="Nama Kampus" required="true" />
                    <x-text-input type="text" id="name" name="name" :value="old('name', $campus->name ?? '')" required
                                  placeholder="Contoh: Kampus Cirebon" :error="$errors->has('name')" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="slug" value="Alamat Halaman Landing" />
                    <x-text-input type="text" id="slug" name="slug" :value="old('slug', $campus->slug ?? '')"
                                  placeholder="cirebon" :error="$errors->has('slug')" />
                    <p class="mt-1 text-xs text-neutral-400">Halaman publik kampus: {{ url('/') }}/<strong>{{ old('slug', $campus->slug ?? 'alamat') }}</strong>. Kosongkan untuk dibuat otomatis dari kota. Mengubahnya memutus tautan lama.</p>
                    <x-input-error :messages="$errors->get('slug')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="code" value="Kode Kampus" required="true" />
                        <x-text-input type="text" id="code" name="code" :value="old('code', $campus->code ?? '')" required
                                      placeholder="CRB" :error="$errors->has('code')" />
                        <x-input-error :messages="$errors->get('code')" />
                    </div>
                    <div>
                        <x-input-label for="city" value="Kota" />
                        <x-text-input type="text" id="city" name="city" :value="old('city', $campus->city ?? '')"
                                      placeholder="Cirebon" :error="$errors->has('city')" />
                        <x-input-error :messages="$errors->get('city')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" value="Alamat Kampus" />
                    <x-textarea name="address" id="address" :error="$errors->has('address')">{{ old('address', $campus->address ?? '') }}</x-textarea>
                    <x-input-error :messages="$errors->get('address')" />
                </div>

                <div>
                    <x-input-label for="admin_whatsapp" value="WhatsApp Admin Kampus" />
                    <x-text-input type="text" id="admin_whatsapp" name="admin_whatsapp" :value="old('admin_whatsapp', $campus->admin_whatsapp ?? '')"
                                  placeholder="08xxxxxxxxxx" :error="$errors->has('admin_whatsapp')" />
                    <p class="mt-1 text-xs text-neutral-400">Kosongkan untuk memakai WhatsApp admin periode.</p>
                    <x-input-error :messages="$errors->get('admin_whatsapp')" />
                </div>
            </div>

            {{-- Teks hero landing kampus (/slug). Kampus bawaan memakai landing utama, jadi tidak perlu. --}}
            @if(isset($campus) && $campus->is_default)
                <div class="mt-6 p-4 rounded-xl border border-neutral-200 text-sm text-neutral-500">
                    Kampus bawaan memakai landing utama, jadi tidak memerlukan teks hero khusus.
                </div>
            @else
                <div class="mt-6 p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5"
                     x-data="{
                        h: @js(old('hero_headline', $campus->hero_headline ?? '')),
                        k: @js(old('hero_highlight', $campus->hero_highlight ?? '')),
                        s: @js(old('hero_subtitle', $campus->hero_subtitle ?? '')),
                        get i() { return this.k && this.h.includes(this.k) ? this.h.indexOf(this.k) : -1; }
                     }">
                    <div>
                        <div class="text-sm font-bold text-neutral-900">Teks Hero Landing <span class="text-error-600">*</span></div>
                        <p class="mt-0.5 text-xs text-neutral-400">Tampil di bagian atas halaman kampus ini. Tulis sesuai prodi yang benar-benar dibuka di kampus ini, bukan klaim umum.</p>
                    </div>

                    <div>
                        <x-input-label for="hero_headline" value="Judul Hero" required="true" />
                        <x-text-input type="text" id="hero_headline" name="hero_headline" x-model="h" maxlength="90"
                                      :value="old('hero_headline', $campus->hero_headline ?? '')"
                                      placeholder="Wujudkan Karirmu di Bidang Farmasi." :error="$errors->has('hero_headline')" />
                        <div class="mt-1 flex justify-between text-xs text-neutral-400">
                            <span>10 sampai 90 karakter, tanpa tanda &lt; atau &gt;.</span>
                            <span><span x-text="h.length"></span>/90</span>
                        </div>
                        <x-input-error :messages="$errors->get('hero_headline')" />
                    </div>

                    <div>
                        <x-input-label for="hero_highlight" value="Kata Sorotan (opsional)" />
                        <x-text-input type="text" id="hero_highlight" name="hero_highlight" x-model="k" maxlength="40"
                                      :value="old('hero_highlight', $campus->hero_highlight ?? '')"
                                      placeholder="Karirmu" :error="$errors->has('hero_highlight')" />
                        <p class="mt-1 text-xs text-neutral-400">Bagian judul yang diberi warna kuning. Harus sama persis dengan teks di judul (huruf besar/kecil dibedakan).</p>
                        <p class="mt-1 text-xs text-error-600" x-show="k && i < 0" x-cloak>Kata sorotan tidak ditemukan di judul.</p>
                        <x-input-error :messages="$errors->get('hero_highlight')" />
                    </div>

                    <div>
                        <x-input-label for="hero_subtitle" value="Deskripsi Hero" required="true" />
                        <x-textarea id="hero_subtitle" name="hero_subtitle" x-model="s" rows="3" maxlength="180"
                                    :error="$errors->has('hero_subtitle')">{{ old('hero_subtitle', $campus->hero_subtitle ?? '') }}</x-textarea>
                        <div class="mt-1 flex justify-between text-xs text-neutral-400">
                            <span>20 sampai 180 karakter, sebutkan prodi yang dibuka.</span>
                            <span><span x-text="s.length"></span>/180</span>
                        </div>
                        <x-input-error :messages="$errors->get('hero_subtitle')" />
                    </div>

                    {{-- Pratinjau --}}
                    <div class="rounded-xl p-5 text-white" style="background:linear-gradient(135deg,#0B2B7F,#09183E);">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-white/40 mb-2">Pratinjau</div>
                        <div class="text-2xl font-black leading-tight">
                            <span x-text="i < 0 ? (h || 'Judul hero') : h.slice(0, i)"></span><span x-show="i >= 0" class="text-[#F1B10E]" x-text="k"></span><span x-text="i < 0 ? '' : h.slice(i + k.length)"></span>
                        </div>
                        <p class="mt-3 text-sm text-white/70" x-text="s || 'Deskripsi hero'"></p>
                    </div>
                </div>
            @endif
            <div class="mt-6 p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                <div>
                    <div class="text-sm font-bold text-neutral-900">Rekening Pembayaran</div>
                    <p class="mt-0.5 text-xs text-neutral-400">Kosongkan semua untuk memakai rekening periode.</p>
                </div>
                <div>
                    <x-input-label for="university_bank_name" value="Nama Bank" />
                    <x-text-input type="text" id="university_bank_name" name="university_bank_name" :value="old('university_bank_name', $campus->university_bank_name ?? '')" :error="$errors->has('university_bank_name')" />
                    <x-input-error :messages="$errors->get('university_bank_name')" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="university_bank_account" value="Nomor Rekening" />
                        <x-text-input type="text" id="university_bank_account" name="university_bank_account" :value="old('university_bank_account', $campus->university_bank_account ?? '')" :error="$errors->has('university_bank_account')" />
                        <x-input-error :messages="$errors->get('university_bank_account')" />
                    </div>
                    <div>
                        <x-input-label for="university_bank_account_name" value="Atas Nama" />
                        <x-text-input type="text" id="university_bank_account_name" name="university_bank_account_name" :value="old('university_bank_account_name', $campus->university_bank_account_name ?? '')" :error="$errors->has('university_bank_account_name')" />
                        <x-input-error :messages="$errors->get('university_bank_account_name')" />
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center gap-3 text-sm text-neutral-700 {{ isset($campus) && $campus->is_default ? 'opacity-60' : 'cursor-pointer' }}">
                    <input type="checkbox" name="is_active" value="1"
                           class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                           @checked(old('is_active', $campus->is_active ?? true))
                           @disabled(isset($campus) && $campus->is_default)>
                    Kampus aktif (tampil di pilihan pendaftar)
                </label>
                @if(isset($campus) && $campus->is_default)
                    <p class="mt-1 text-xs text-neutral-400">Kampus bawaan selalu aktif.</p>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.campuses.index') }}" class="decoration-none">
                    <x-button type="button" color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">Simpan</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
