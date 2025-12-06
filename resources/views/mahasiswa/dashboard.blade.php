@extends('layouts.mahasiswa')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    
    <!-- Hero Section dengan Video Background -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        
        <div class="relative z-10 p-8 lg:p-12">
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <!-- Text Content -->
                <div class="text-white space-y-6 fade-in-up">
                    <div class="inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                        <i class="fas fa-star text-yellow-300 mr-2"></i>
                        Selamat Datang!
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-bold leading-tight">
                        Hai, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-xl text-indigo-100">
                        Selamat datang di Portal Mahasiswa. Lengkapi proses pendaftaran Anda untuk memulai perjalanan akademik!
                    </p>
                    
                    @if(!$mahasiswa)
                    <a href="{{ route('mahasiswa.pendaftaran.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-rocket mr-2"></i>
                        Mulai Pendaftaran
                    </a>
                    @endif
                </div>
                
                <!-- Illustration/Image -->
                <div class="fade-in-up float" style="animation-delay: 0.2s">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600&h=400&fit=crop" 
                         alt="Student" 
                         class="rounded-2xl shadow-2xl">
                </div>
            </div>
        </div>
    </div>

    @if($mahasiswa)
    <!-- Status Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-id-card text-indigo-600 mr-3"></i>
                Status Pendaftaran
            </h2>
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    'diverifikasi' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'ditolak' => 'bg-red-100 text-red-800 border-red-300',
                    'diterima' => 'bg-green-100 text-green-800 border-green-300'
                ];
                $statusIcons = [
                    'pending' => 'fa-clock',
                    'diverifikasi' => 'fa-check',
                    'ditolak' => 'fa-times',
                    'diterima' => 'fa-check-circle'
                ];
            @endphp
            <span class="px-4 py-2 rounded-full border-2 font-semibold {{ $statusColors[$mahasiswa->status_pendaftaran] ?? 'bg-gray-100 text-gray-800 border-gray-300' }}">
                <i class="fas {{ $statusIcons[$mahasiswa->status_pendaftaran] ?? 'fa-question' }} mr-2"></i>
                {{ ucfirst($mahasiswa->status_pendaftaran) }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl">
                <p class="text-sm text-indigo-600 font-medium mb-1">Nomor Pendaftaran</p>
                <p class="text-2xl font-bold text-indigo-900">{{ $mahasiswa->no_pendaftaran }}</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                <p class="text-sm text-purple-600 font-medium mb-1">Jurusan</p>
                <p class="text-lg font-bold text-purple-900">{{ $mahasiswa->jurusan->nama_jurusan }}</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl">
                <p class="text-sm text-pink-600 font-medium mb-1">Tanggal Daftar</p>
                <p class="text-lg font-bold text-pink-900">{{ $mahasiswa->created_at->format('d M Y') }}</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl">
                <p class="text-sm text-orange-600 font-medium mb-1">Status</p>
                <p class="text-lg font-bold text-orange-900">{{ ucfirst($mahasiswa->status_pendaftaran) }}</p>
            </div>
        </div>

        @if($mahasiswa->catatan_admin)
        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
            <p class="text-sm font-medium text-blue-800 mb-1">
                <i class="fas fa-comment-dots mr-2"></i>Catatan Admin:
            </p>
            <p class="text-gray-700">{{ $mahasiswa->catatan_admin }}</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Video Tutorial & Quick Links -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Video Tutorial -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-6 fade-in-up">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-play-circle text-red-500 mr-3"></i>
                Panduan Pendaftaran
            </h3>
            <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
                <iframe width="100%" height="100%" 
                        src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                        title="Tutorial" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
            <p class="mt-4 text-gray-600 text-sm">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Tonton video ini untuk memahami proses pendaftaran lengkap
            </p>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-2xl shadow-xl p-6 fade-in-up" style="animation-delay: 0.2s">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                Quick Links
            </h3>
            <div class="space-y-3">
                <a href="{{ route('mahasiswa.pendaftaran.status') }}" 
                   class="block p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file-alt text-blue-600 text-xl group-hover:scale-110 transition-transform"></i>
                            <span class="font-medium text-gray-800">Cek Status</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                    </div>
                </a>

                <a href="{{ route('mahasiswa.pembayaran.index') }}" 
                   class="block p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg hover:shadow-md transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-wallet text-green-600 text-xl group-hover:scale-110 transition-transform"></i>
                            <span class="font-medium text-gray-800">Pembayaran</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 transition-colors"></i>
                    </div>
                </a>

                <a href="{{ route('mahasiswa.pengumuman.index') }}" 
                   class="block p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-bell text-purple-600 text-xl group-hover:scale-110 transition-transform"></i>
                            <span class="font-medium text-gray-800">Pengumuman</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-600 transition-colors"></i>
                    </div>
                </a>

                <a href="{{ route('profile.edit') }}" 
                   class="block p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg hover:shadow-md transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user-edit text-orange-600 text-xl group-hover:scale-110 transition-transform"></i>
                            <span class="font-medium text-gray-800">Edit Profile</span>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-600 transition-colors"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Latest Announcements -->
    @if($pengumumans->count() > 0)
    <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-bullhorn text-indigo-600 mr-3"></i>
            Pengumuman Terbaru
        </h3>
        <div class="space-y-4">
            @foreach($pengumumans as $pengumuman)
            <div class="flex items-start space-x-4 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all duration-200">
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-megaphone text-indigo-600"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800">{{ $pengumuman->judul }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($pengumuman->isi, 150) }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $pengumuman->tanggal_publish->format('d M Y') }}
                    </p>
                </div>
                <a href="{{ route('mahasiswa.pengumuman.show', $pengumuman->id) }}" 
                   class="flex-shrink-0 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Baca
                </a>
            </div>
            @endforeach
        </div>
        <div class="mt-6 text-center">
            <a href="{{ route('mahasiswa.pengumuman.index') }}" 
               class="inline-flex items-center text-indigo-600 font-medium hover:text-indigo-700">
                Lihat Semua Pengumuman
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
    @endif

    <!-- Campus Gallery -->
    <div class="bg-white rounded-2xl shadow-xl p-8 fade-in-up">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-images text-pink-600 mr-3"></i>
            Galeri Kampus
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&h=400&fit=crop" 
                     alt="Campus" 
                     class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                    <p class="text-white font-semibold p-4">Perpustakaan</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400&h=400&fit=crop" 
                     alt="Campus" 
                     class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                    <p class="text-white font-semibold p-4">Ruang Kelas</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?w=400&h=400&fit=crop" 
                     alt="Campus" 
                     class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                    <p class="text-white font-semibold p-4">Laboratorium</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1519406596751-0a3ccc4937fe?w=400&h=400&fit=crop" 
                     alt="Campus" 
                     class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                    <p class="text-white font-semibold p-4">Auditorium</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection