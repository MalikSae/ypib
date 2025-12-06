@extends('layouts.mahasiswa')

@section('title', 'Pembayaran')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Pembayaran
                </h1>
                <p class="text-gray-600">Kelola pembayaran dan riwayat transaksi Anda</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-wallet text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <!-- Total Pembayaran -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <span class="text-sm font-medium bg-white/20 px-3 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-2xl font-bold mb-1">Rp {{ number_format($pembayarans->sum('jumlah'), 0, ',', '.') }}</h3>
            <p class="text-blue-100 text-sm">Total Pembayaran</p>
        </div>

        <!-- Pending -->
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <span class="text-sm font-medium bg-white/20 px-3 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-2xl font-bold mb-1">{{ $pembayarans->where('status', 'pending')->count() }}</h3>
            <p class="text-yellow-100 text-sm">Menunggu Verifikasi</p>
        </div>

        <!-- Verified -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <span class="text-sm font-medium bg-white/20 px-3 py-1 rounded-full">Verified</span>
            </div>
            <h3 class="text-2xl font-bold mb-1">{{ $pembayarans->where('status', 'terverifikasi')->count() }}</h3>
            <p class="text-green-100 text-sm">Pembayaran Terverifikasi</p>
        </div>
    </div>

    <!-- Action Button -->
    @if($mahasiswa->status_pendaftaran === 'diverifikasi')
    <div class="mb-6">
        <a href="{{ route('mahasiswa.pembayaran.create') }}" 
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
            <i class="fas fa-plus-circle mr-2"></i>
            Upload Pembayaran Baru
        </a>
    </div>
    @else
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-yellow-500 text-xl mr-3"></i>
            <p class="text-yellow-700 font-medium">Pendaftaran Anda masih dalam proses verifikasi. Harap tunggu hingga diverifikasi untuk melakukan pembayaran.</p>
        </div>
    </div>
    @endif

    <!-- Riwayat Pembayaran -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
            <h2 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-history mr-3"></i>
                Riwayat Pembayaran
            </h2>
        </div>

        @if($pembayarans->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($pembayarans as $pembayaran)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Info Pembayaran -->
                    <div class="flex-1">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-receipt text-indigo-600 text-xl"></i>
                            </div>

                            <!-- Details -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-gray-800">{{ $pembayaran->no_pembayaran }}</h3>
                                    
                                    <!-- Status Badge -->
                                    @if($pembayaran->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    @elseif($pembayaran->status === 'terverifikasi')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            <i class="fas fa-check-circle mr-1"></i>Terverifikasi
                                        </span>
                                    @elseif($pembayaran->status === 'ditolak')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                            <i class="fas fa-times-circle mr-1"></i>Ditolak
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave w-5 mr-2"></i>
                                        <span class="font-semibold text-gray-800">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar w-5 mr-2"></i>
                                        <span>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="far fa-clock w-5 mr-2"></i>
                                        <span>Diupload {{ $pembayaran->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                @if($pembayaran->catatan)
                                <div class="mt-3 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                    <p class="text-sm text-blue-800">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        <strong>Catatan:</strong> {{ $pembayaran->catatan }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('mahasiswa.pembayaran.show', $pembayaran->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-eye mr-2"></i>
                            <span class="hidden sm:inline">Detail</span>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Pembayaran</h3>
            <p class="text-gray-600 mb-6">Anda belum melakukan pembayaran apapun</p>
            @if($mahasiswa->status_pendaftaran === 'diverifikasi')
            <a href="{{ route('mahasiswa.pembayaran.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus-circle mr-2"></i>
                Upload Pembayaran
            </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle text-white text-xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 mb-2">Informasi Pembayaran</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-indigo-600 mr-2 mt-1"></i>
                        <span>Upload bukti pembayaran dalam format JPG, PNG, atau PDF (max 2MB)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-indigo-600 mr-2 mt-1"></i>
                        <span>Pembayaran akan diverifikasi dalam 1x24 jam</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-indigo-600 mr-2 mt-1"></i>
                        <span>Pastikan data pembayaran yang diupload sudah benar</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-indigo-600 mr-2 mt-1"></i>
                        <span>Hubungi admin jika ada kendala dalam proses pembayaran</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection