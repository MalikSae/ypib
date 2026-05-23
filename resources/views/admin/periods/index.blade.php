@extends('layouts.admin')
@section('title', 'Pengaturan PMB')
@section('page-title', 'Pengaturan PMB')

@section('content')
<div class="bg-white rounded-2xl border border-[#DEE3E9] p-6 max-w-4xl">
    <h2 class="text-lg font-bold text-[#0A1317] mb-6">Pengaturan Biaya & Komisi</h2>
    
    <form action="{{ route('admin.periods.update', $period->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Informasi Dasar --}}
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Nama Periode</label>
                <input type="text" name="name" value="{{ old('name', $period->name) }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Tahun</label>
                <input type="number" name="year" value="{{ old('year', $period->year) }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Tanggal Buka</label>
                <input type="date" name="open_date" value="{{ old('open_date', $period->open_date->format('Y-m-d')) }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Tanggal Tutup</label>
                <input type="date" name="close_date" value="{{ old('close_date', $period->close_date->format('Y-m-d')) }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" required>
            </div>

            {{-- Pengaturan Komisi --}}
            <div class="md:col-span-2 pt-4 border-t border-[#DEE3E9] mb-2">
                <h3 class="font-bold text-[#0A1317]">Pengaturan Komisi Afiliasi</h3>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Komisi Pendaftaran Awal</label>
                <div style="position:relative;">
                    <div style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-weight:600;color:#8595A4;">Rp</div>
                    <input type="text" id="referral_reward_display" value="{{ number_format(old('referral_reward_amount', $period->referral_reward_amount), 0, '', '.') }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" style="padding-left:48px;" required>
                    <input type="hidden" name="referral_reward_amount" id="referral_reward_amount" value="{{ old('referral_reward_amount', $period->referral_reward_amount) }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Komisi Daftar Ulang</label>
                <div style="position:relative;">
                    <div style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-weight:600;color:#8595A4;">Rp</div>
                    <input type="text" id="re_registration_reward_display" value="{{ number_format(old('re_registration_reward_amount', $period->re_registration_reward_amount), 0, '', '.') }}" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none" style="padding-left:48px;" required>
                    <input type="hidden" name="re_registration_reward_amount" id="re_registration_reward_amount" value="{{ old('re_registration_reward_amount', $period->re_registration_reward_amount) }}">
                </div>
            </div>

            {{-- Pengaturan Rekening Pembayaran --}}
            <div class="md:col-span-2 pt-4 border-t border-[#DEE3E9] mb-2">
                <h3 class="font-bold text-[#0A1317]">Informasi Rekening Pembayaran</h3>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Nama Bank</label>
                <input type="text" name="university_bank_name" value="{{ old('university_bank_name', $period->university_bank_name) }}" placeholder="Contoh: BRI, BCA, BNI" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#444950] mb-2">Nomor Rekening</label>
                <input type="text" name="university_bank_account" value="{{ old('university_bank_account', $period->university_bank_account) }}" placeholder="Contoh: 1234567890" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-[#444950] mb-2">Atas Nama (Pemilik Rekening)</label>
                <input type="text" name="university_bank_account_name" value="{{ old('university_bank_account_name', $period->university_bank_account_name) }}" placeholder="Contoh: YPIB Majalengka" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none">
            </div>

            {{-- Pengaturan WhatsApp Admin --}}
            <div class="md:col-span-2 pt-4 border-t border-[#DEE3E9] mb-2">
                <h3 class="font-bold text-[#0A1317]">Kontak WhatsApp Admin</h3>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-[#444950] mb-2">Nomor WhatsApp</label>
                <input type="text" name="admin_whatsapp" value="{{ old('admin_whatsapp', $period->admin_whatsapp) }}" placeholder="Contoh: 081234567890 atau 6281234567890" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full border border-[#CED0D4] rounded-lg px-4 py-2.5 focus:border-[#082e8f] focus:ring-1 focus:ring-[#082e8f] outline-none">
                <div class="text-[12px] text-[#8595A4] mt-1.5">Nomor ini akan dihubungi oleh pendaftar untuk konfirmasi pembayaran.</div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-[#DEE3E9]">
            <button type="submit" class="bg-[#082e8f] hover:bg-[#062472] text-white px-6 py-2.5 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75h1.5m9 0h-9" />
                </svg>
                Simpan Pengaturan
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

        setupCurrency('referral_reward_display', 'referral_reward_amount');
        setupCurrency('re_registration_reward_display', 're_registration_reward_amount');
    });
</script>
@endsection
