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
