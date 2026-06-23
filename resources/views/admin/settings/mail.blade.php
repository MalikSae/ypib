@extends('layouts.admin')

@section('title', 'Pengaturan Email')
@section('page-title', 'Pengaturan Email (Resend)')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 p-6">
        
        <div class="mb-6">
            <h2 class="text-lg font-bold text-neutral-900">Konfigurasi Pengiriman Email</h2>
            <p class="text-sm text-neutral-500 mt-1">Pengaturan ini akan menimpa konfigurasi `.env` bawaan. Sangat disarankan untuk menggunakan API Key dari <a href="https://resend.com" target="_blank" class="text-primary-600 font-semibold hover:underline">Resend.com</a> untuk pengiriman yang cepat.</p>
        </div>

        <form action="{{ route('admin.settings.mail.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="resend_api_key" class="block text-sm font-semibold text-neutral-700 mb-2">Resend API Key</label>
                <input type="password" name="resend_api_key" id="resend_api_key" 
                    class="w-full px-4 py-2 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all" 
                    value="{{ old('resend_api_key', $resendKey) }}" 
                    placeholder="re_123456789...">
                @error('resend_api_key')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-neutral-500 text-xs mt-2">Dapatkan API Key ini dari dashboard Resend. Kosongkan jika tidak ingin mengubah/menggunakan layanan bawaan.</p>
            </div>

            <div class="mb-5">
                <label for="mail_from_address" class="block text-sm font-semibold text-neutral-700 mb-2">Alamat Email Pengirim (From Address)</label>
                <input type="email" name="mail_from_address" id="mail_from_address" 
                    class="w-full px-4 py-2 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all" 
                    value="{{ old('mail_from_address', $fromAddress) }}" 
                    required 
                    placeholder="no-reply@domainkampus.ac.id">
                @error('mail_from_address')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-neutral-500 text-xs mt-2">Gunakan alamat email dengan domain yang telah diverifikasi di Resend.</p>
            </div>

            <div class="mb-8">
                <label for="mail_from_name" class="block text-sm font-semibold text-neutral-700 mb-2">Nama Pengirim (From Name)</label>
                <input type="text" name="mail_from_name" id="mail_from_name" 
                    class="w-full px-4 py-2 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all" 
                    value="{{ old('mail_from_name', $fromName) }}" 
                    required 
                    placeholder="PMB YPIB">
                @error('mail_from_name')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 text-white font-semibold text-sm rounded-xl hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
