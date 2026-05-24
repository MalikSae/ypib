@extends('layouts.admin')
@section('title', 'Pengaturan PMB — Admin PMB YPIB')
@section('page-title', 'Pengaturan PMB')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Pengaturan PMB</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola periode pendaftaran, biaya, dan komisi afiliasi.</p>
    </div>
</div>

<div class="max-w-4xl">
    <form action="{{ route('admin.periods.update', $period->id) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-6">
        @csrf
        @method('PUT')
        
        {{-- SECTION 1: Informasi Periode --}}
        <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
                <h2 class="text-sm font-bold text-neutral-900">Informasi Periode</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Tentukan nama dan batas waktu pendaftaran mahasiswa baru.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Nama Periode <span class="text-error-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $period->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('name') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('name')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="year" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Tahun <span class="text-error-500">*</span></label>
                    <input type="number" id="year" name="year" value="{{ old('year', $period->year) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('year') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('year')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="open_date" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Tanggal Buka <span class="text-error-500">*</span></label>
                    <input type="date" id="open_date" name="open_date" value="{{ old('open_date', $period->open_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('open_date') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('open_date')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="close_date" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Tanggal Tutup <span class="text-error-500">*</span></label>
                    <input type="date" id="close_date" name="close_date" value="{{ old('close_date', $period->close_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('close_date') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('close_date')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- SECTION 3: Rekening Pembayaran --}}
        <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
                <h2 class="text-sm font-bold text-neutral-900">Informasi Rekening Kampus</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Rekening tujuan untuk transfer biaya pendaftaran dari mahasiswa.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="university_bank_name" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Nama Bank</label>
                    <input type="text" id="university_bank_name" name="university_bank_name" value="{{ old('university_bank_name', $period->university_bank_name) }}" placeholder="Cth: BRI, BCA, BNI"
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('university_bank_name') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('university_bank_name')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="university_bank_account" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Nomor Rekening</label>
                    <input type="text" id="university_bank_account" name="university_bank_account" value="{{ old('university_bank_account', $period->university_bank_account) }}" placeholder="Cth: 1234567890"
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm font-mono text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('university_bank_account') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('university_bank_account')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="university_bank_account_name" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Atas Nama (Pemilik Rekening)</label>
                    <input type="text" id="university_bank_account_name" name="university_bank_account_name" value="{{ old('university_bank_account_name', $period->university_bank_account_name) }}" placeholder="Cth: YPIB Majalengka"
                           class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('university_bank_account_name') ? 'border-error-500 focus:ring-error-500' : '' }}">
                    @error('university_bank_account_name')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- SECTION 4: Kontak --}}
        <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
                <h2 class="text-sm font-bold text-neutral-900">Kontak WhatsApp Admin</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Nomor kontak resmi yang dihubungi pendaftar untuk konfirmasi.</p>
            </div>
            <div class="p-6">
                <label for="admin_whatsapp" class="block text-xs font-bold text-neutral-700 uppercase tracking-wide mb-2">Nomor WhatsApp</label>
                <input type="text" id="admin_whatsapp" name="admin_whatsapp" value="{{ old('admin_whatsapp', $period->admin_whatsapp) }}" placeholder="Cth: 081234567890" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       class="w-full md:w-1/2 px-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm font-mono text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors {{ $errors->has('admin_whatsapp') ? 'border-error-500 focus:ring-error-500' : '' }}">
                @error('admin_whatsapp')<p class="mt-1.5 text-xs text-error-600 font-medium">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all duration-200 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!submitting">Simpan Pengaturan</span>
                <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const formatNumber = (n) => {
            return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        function setupCurrency(displayId, hiddenId) {
            $('#' + displayId).on('input', function() {
                let val = $(this).val();
                let cleanVal = val.replace(/\D/g, "");
                $(this).val(formatNumber(val));
                $('#' + hiddenId).val(cleanVal);
            });
        }


    });
</script>
@endsection
