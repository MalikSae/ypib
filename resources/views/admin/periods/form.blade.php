@extends('layouts.admin')
@php $isEdit = $period->exists; @endphp
@section('title', ($isEdit ? 'Edit Periode PMB' : 'Tambah Periode PMB') . ' — Admin PMB YPIB')
@section('page-title', $isEdit ? 'Edit Periode PMB' : 'Tambah Periode PMB')

@section('content')

<div class="max-w-4xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.periods.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-neutral-900 tracking-tight">{{ $isEdit ? 'Edit Periode PMB' : 'Tambah Periode PMB' }}</h1>
            <p class="text-sm text-neutral-400">
                @if($isEdit)
                    Status: <strong>{{ $period->phaseLabel() }}</strong>. Periode baru berstatus draft sampai ditayangkan.
                @else
                    Periode baru berstatus draft. Tayangkan dari daftar periode setelah data lengkap.
                @endif
            </p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ $isEdit ? route('admin.periods.update', $period->id) : route('admin.periods.store') }}"
              method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Informasi Periode</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" value="Nama Periode" required="true" />
                            <x-text-input type="text" id="name" name="name" :value="old('name', $period->name)" required :error="$errors->has('name')" />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="year" value="Tahun Angkatan" required="true" />
                            <x-text-input type="number" id="year" name="year" :value="old('year', $period->year)" required
                                          :disabled="$isEdit && ($period->registrations_count ?? 0) > 0" :error="$errors->has('year')" />
                            @if($isEdit && ($period->registrations_count ?? 0) > 0)
                                <input type="hidden" name="year" value="{{ $period->year }}">
                                <p class="mt-1.5 text-xs text-neutral-400">Terkunci karena sudah ada pendaftar (dipakai pada nomor pendaftaran dan NIM).</p>
                            @endif
                            <x-input-error :messages="$errors->get('year')" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="open_date" value="Tanggal Buka Pendaftaran" required="true" />
                            <x-text-input type="date" id="open_date" name="open_date"
                                          :value="old('open_date', optional($period->open_date)->format('Y-m-d'))" required :error="$errors->has('open_date')" />
                            <x-input-error :messages="$errors->get('open_date')" />
                        </div>
                        <div>
                            <x-input-label for="close_date" value="Tanggal Tutup Pendaftaran" required="true" />
                            <x-text-input type="date" id="close_date" name="close_date"
                                          :value="old('close_date', optional($period->close_date)->format('Y-m-d'))" required :error="$errors->has('close_date')" />
                            <x-input-error :messages="$errors->get('close_date')" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="payment_grace_days" value="Masa Bayar Setelah Tutup (hari)" required="true" />
                            <x-text-input type="number" min="0" max="30" id="payment_grace_days" name="payment_grace_days"
                                          :value="old('payment_grace_days', $period->payment_grace_days)" required :error="$errors->has('payment_grace_days')" />
                            <p class="mt-1.5 text-xs text-neutral-400">Pendaftar yang mendaftar sampai tanggal tutup masih boleh mengunggah bukti bayar selama hari ini.</p>
                            <x-input-error :messages="$errors->get('payment_grace_days')" />
                        </div>
                        <div>
                            <x-input-label for="re_registration_deadline" value="Tenggat Daftar Ulang" />
                            <x-text-input type="date" id="re_registration_deadline" name="re_registration_deadline"
                                          :value="old('re_registration_deadline', optional($period->re_registration_deadline)->format('Y-m-d'))"
                                          :error="$errors->has('re_registration_deadline')" />
                            <p class="mt-1.5 text-xs text-neutral-400">Wajib diisi sebelum periode ditayangkan.</p>
                            <x-input-error :messages="$errors->get('re_registration_deadline')" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="nim_sequence_start" value="Nomor Urut NIM Pertama" required="true" />
                            <x-text-input type="number" min="1" max="999" id="nim_sequence_start" name="nim_sequence_start"
                                          :value="old('nim_sequence_start', $period->nim_sequence_start)" required :error="$errors->has('nim_sequence_start')" />
                            <p class="mt-1.5 text-xs text-neutral-400">Batas bawah urutan 3 digit terakhir NIM untuk angkatan ini.</p>
                            <x-input-error :messages="$errors->get('nim_sequence_start')" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Rekening Pembayaran Kampus</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="university_bank_name" value="Nama Bank" />
                            <x-text-input type="text" id="university_bank_name" name="university_bank_name"
                                          :value="old('university_bank_name', $period->university_bank_name)" placeholder="Cth: BRI, BCA, BNI"
                                          :error="$errors->has('university_bank_name')" />
                            <x-input-error :messages="$errors->get('university_bank_name')" />
                        </div>
                        <div>
                            <x-input-label for="university_bank_account" value="Nomor Rekening" />
                            <x-text-input type="text" id="university_bank_account" name="university_bank_account" class="font-mono"
                                          :value="old('university_bank_account', $period->university_bank_account)" placeholder="Cth: 1234567890"
                                          :error="$errors->has('university_bank_account')" />
                            <x-input-error :messages="$errors->get('university_bank_account')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="university_bank_account_name" value="Atas Nama (Pemilik Rekening)" />
                        <x-text-input type="text" id="university_bank_account_name" name="university_bank_account_name"
                                      :value="old('university_bank_account_name', $period->university_bank_account_name)" placeholder="Cth: YPIB Majalengka"
                                      :error="$errors->has('university_bank_account_name')" />
                        <x-input-error :messages="$errors->get('university_bank_account_name')" />
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Kontak WhatsApp Admin</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200">
                    <x-input-label for="admin_whatsapp" value="Nomor WhatsApp" />
                    <x-text-input type="text" id="admin_whatsapp" name="admin_whatsapp" class="font-mono md:w-1/2"
                                  :value="old('admin_whatsapp', $period->admin_whatsapp)" placeholder="Cth: 081234567890"
                                  oninput="this.value = this.value.replace(/[^0-9]/g, '')" :error="$errors->has('admin_whatsapp')" />
                    <x-input-error :messages="$errors->get('admin_whatsapp')" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.periods.index') }}" class="decoration-none">
                    <x-button color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">Simpan Periode</x-button>
            </div>
        </form>
    </x-card>
</div>

@endsection
