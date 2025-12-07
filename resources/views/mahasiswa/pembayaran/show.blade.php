@extends('layouts.mahasiswa')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Detail Pembayaran
                </h1>
                <p class="text-gray-600">Informasi lengkap pembayaran Anda</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-receipt text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-6">
        <div class="space-y-6">
            <div>
                <p class="font-bold text-gray-700">{{ __('Nomor Pembayaran') }}</p>
                <p class="mt-1 text-gray-600 text-lg">{{ $pembayaran->no_pembayaran }}</p>
            </div>

            <div>
                <p class="font-bold text-gray-700">{{ __('Nama Mahasiswa') }}</p>
                <p class="mt-1 text-gray-600 text-lg">{{ $pembayaran->mahasiswa->nama_lengkap }}</p>
            </div>

            <div>
                <p class="font-bold text-gray-700">{{ __('Jumlah Pembayaran') }}</p>
                <p class="mt-1 text-gray-600 text-lg">Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
            </div>

            <div>
                <p class="font-bold text-gray-700">{{ __('Tanggal Bayar') }}</p>
                <p class="mt-1 text-gray-600 text-lg">{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d F Y') }}</p>
            </div>

            <div>
                <p class="font-bold text-gray-700">{{ __('Status') }}</p>
                @if($pembayaran->status === 'pending')
                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                        <i class="fas fa-clock mr-1"></i>Pending
                    </span>
                @elseif($pembayaran->status === 'terverifikasi')
                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                        <i class="fas fa-check-circle mr-1"></i>Terverifikasi
                    </span>
                @elseif($pembayaran->status === 'ditolak')
                    <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-700">
                        <i class="fas fa-times-circle mr-1"></i>Ditolak
                    </span>
                @endif
            </div>

            @if ($pembayaran->catatan)
                <div>
                    <p class="font-bold text-gray-700">{{ __('Catatan') }}</p>
                    <div class="mt-1 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-sticky-note mr-2"></i>
                            {{ $pembayaran->catatan }}
                        </p>
                    </div>
                </div>
            @endif

            <div>
                <p class="font-bold text-gray-700">{{ __('Bukti Pembayaran') }}</p>
                @if ($pembayaran->bukti_pembayaran)
                    <a href="{{ Storage::url($pembayaran->bukti_pembayaran) }}" target="_blank" class="mt-1 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>Lihat Bukti
                    </a>
                @else
                    <p class="mt-1 text-gray-600 text-lg">Tidak ada bukti pembayaran.</p>
                @endif
            </div>

            <div class="flex justify-end mt-8">
                <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection