@extends('layouts.mahasiswa')

@section('title', 'Status Pendaftaran')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Status Card dengan Gambar -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden fade-in-up">
        <div class="relative h-48 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
            <div class="absolute inset-0 opacity-20">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200&h=400&fit=crop" 
                     alt="Background" 
                     class="w-full h-full object-cover">
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-white">
                    <i class="fas fa-id-card text-6xl mb-4"></i>
                    <h1 class="text-3xl font-bold">Status Pendaftaran</h1>
                </div>
            </div>
        </div>

        <div class="p-8">
            <!-- Status Badge -->
            <div class="text-center mb-8">
                @php
                    $statusConfig = [
                        'pending' => [
                            'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'icon' => 'fa-clock',
                            'title' => 'Menunggu Verifikasi',
                            'desc' => 'Pendaftaran Anda sedang dalam proses verifikasi oleh admin'
                        ],
                        'diverifikasi' => [
                            'color' => 'bg-blue-100 text-blue-800 border-blue-300',
                            'icon' => 'fa-check',
                            'title' => 'Sudah Diverifikasi',
                            'desc' => 'Pendaftaran Anda telah diverifikasi. Silakan lakukan pembayaran'
                        ],
                        'ditolak' => [
                            'color' => 'bg-red-100 text-red-800 border-red-300',
                            'icon' => 'fa-times-circle',
                            'title' => 'Ditolak',
                            'desc' => 'Mohon maaf, pendaftaran Anda ditolak'
                        ],
                        'diterima' => [
                            'color' => 'bg-green-100 text-green-800 border-green-300',
                            'icon' => 'fa-check-circle',
                            'title' => 'Diterima',
                            'desc' => 'Selamat! Anda diterima sebagai mahasiswa baru'
                        ]
                    ];
                    $status = $statusConfig[$mahasiswa->status_pendaftaran] ?? $statusConfig['pending'];
                @endphp

                <div class="inline-flex items-center space-x-3 px-8 py-4 rounded-full border-2 {{ $status['color'] }} text-2xl font-bold">
                    <i class="fas {{ $status['icon'] }}"></i>
                    <span>{{ $status['title'] }}</span>
                </div>
                <p class="text-gray-600 mt-4">{{ $status['desc'] }}</p>
            </div>

            <!-- Data Pendaftaran -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-barcode text-indigo-600 text-2xl"></i>
                        <div>
                            <p class="text-sm text-indigo-600 font-medium">Nomor Pendaftaran</p>
                            <p class="text-2xl font-bold text-indigo-900">{{ $mahasiswa->no_pendaftaran }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-user text-purple-600 text-2xl"></i>
                        <div>
                            <p class="text-sm text-purple-600 font-medium">Nama Lengkap</p>
                            <p class="text-xl font-bold text-purple-900">{{ $mahasiswa->nama_lengkap }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-graduation-cap text-pink-600 text-2xl"></i>
                        <div>
                            <p class="text-sm text-pink-600 font-medium">Jurusan Pilihan</p>
                            <p class="text-xl font-bold text-pink-900">{{ $mahasiswa->jurusan->nama_jurusan }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-calendar text-orange-600 text-2xl"></i>
                        <div>
                            <p class="text-sm text-orange-600 font-medium">Tanggal Pendaftaran</p>
                            <p class="text-xl font-bold text-orange-900">{{ $mahasiswa->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foto & Detail -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="text-center">
                    <img src="{{ asset('storage/' . $mahasiswa->foto) }}" 
                         alt="Foto" 
                         class="w-full h-64 object-cover rounded-xl shadow-lg mx-auto">
                    <p class="text-sm text-gray-600 mt-2">Foto Pendaftaran</p>
                </div>

                <div class="md:col-span-2 space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Tempat, Tanggal Lahir</p>
                        <p class="font-semibold text-gray-800">{{ $mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($mahasiswa->tanggal_lahir)->format('d M Y') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Jenis Kelamin</p>
                        <p class="font-semibold text-gray-800">{{ $mahasiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">No. Handphone</p>
                        <p class="font-semibold text-gray-800">{{ $mahasiswa->no_hp }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Asal Sekolah</p>
                        <p class="font-semibold text-gray-800">{{ $mahasiswa->asal_sekolah }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Alamat</p>
                        <p class="font-semibold text-gray-800">{{ $mahasiswa->alamat }}</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Admin -->
            @if($mahasiswa->catatan_admin)
            <div class="p-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg mb-8">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-comment-dots text-blue-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-blue-900 mb-2">Catatan dari Admin:</p>
                        <p class="text-gray-700">{{ $mahasiswa->catatan_admin }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
                <a href="{{ route('mahasiswa.dashboard') }}" 
                   class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Dashboard
                </a>
                
                @if($mahasiswa->status_pendaftaran == 'diverifikasi')
                <a href="{{ route('mahasiswa.pembayaran.create') }}" 
                   class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Lakukan Pembayaran
                </a>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection