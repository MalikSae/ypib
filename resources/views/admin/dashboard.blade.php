@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Overview sistem penerimaan mahasiswa baru')

@section('content')
<div class="space-y-6">
    
    <!-- Video Banner -->
    <div class="relative bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-2xl overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative z-10 p-8 lg:p-12">
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div class="text-white space-y-4 fade-in">
                    <h2 class="text-4xl font-bold">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
                    <p class="text-lg text-blue-100">Kelola sistem penerimaan mahasiswa baru dengan mudah dan efisien.</p>
                    <div class="flex space-x-4">
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ now()->isoFormat('D MMMM YYYY') }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                            <i class="fas fa-clock mr-2"></i>
                            {{ now()->isoFormat('HH:mm') }} WIB
                        </span>
                    </div>
                </div>
                
                <!-- Video Player -->
                <div class="rounded-xl overflow-hidden shadow-2xl fade-in" style="animation-delay: 0.2s">
                    <iframe width="100%" height="315" 
                            src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                            title="Campus Tour" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            class="rounded-xl">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Mahasiswa</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $totalMahasiswa }}</h3>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-users text-3xl text-blue-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>
                <span>12% dari bulan lalu</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 fade-in" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pending Verifikasi</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $pendingVerifikasi }}</h3>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-clock text-3xl text-yellow-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-yellow-600">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <span>Perlu perhatian</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 fade-in" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Mahasiswa Diterima</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $totalMahasiswa }}</h3>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>
                <span>8% dari target</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 fade-in" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pending Pembayaran</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $pendingPembayaran }}</h3>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-money-bill-wave text-3xl text-purple-600"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-purple-600">
                <i class="fas fa-info-circle mr-1"></i>
                <span>Segera verifikasi</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Gallery -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 fade-in">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                Quick Actions
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.mahasiswa.index') }}" 
                   class="flex items-center space-x-3 p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition-all duration-200">
                    <i class="fas fa-user-check text-2xl text-blue-600"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Verifikasi Mahasiswa</p>
                        <p class="text-xs text-gray-600">{{ $pendingVerifikasi }} pending</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.pembayaran.index') }}" 
                   class="flex items-center space-x-3 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg hover:shadow-md transition-all duration-200">
                    <i class="fas fa-credit-card text-2xl text-green-600"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Cek Pembayaran</p>
                        <p class="text-xs text-gray-600">{{ $pendingPembayaran }} menunggu</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.pengumuman.create') }}" 
                   class="flex items-center space-x-3 p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition-all duration-200">
                    <i class="fas fa-bullhorn text-2xl text-purple-600"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Buat Pengumuman</p>
                        <p class="text-xs text-gray-600">Informasi baru</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.jurusan.index') }}" 
                   class="flex items-center space-x-3 p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg hover:shadow-md transition-all duration-200">
                    <i class="fas fa-graduation-cap text-2xl text-orange-600"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Kelola Jurusan</p>
                        <p class="text-xs text-gray-600">Edit data jurusan</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Campus Gallery -->
        <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay: 0.2s">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-images text-pink-500 mr-2"></i>
                Campus Gallery
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400&h=300&fit=crop" 
                     alt="Campus 1" 
                     class="rounded-lg shadow-md hover:scale-105 transition-transform duration-300 cursor-pointer">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=400&h=300&fit=crop" 
                     alt="Campus 2" 
                     class="rounded-lg shadow-md hover:scale-105 transition-transform duration-300 cursor-pointer">
                <img src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?w=400&h=300&fit=crop" 
                     alt="Campus 3" 
                     class="rounded-lg shadow-md hover:scale-105 transition-transform duration-300 cursor-pointer">
                <img src="https://images.unsplash.com/photo-1519406596751-0a3ccc4937fe?w=400&h=300&fit=crop" 
                     alt="Campus 4" 
                     class="rounded-lg shadow-md hover:scale-105 transition-transform duration-300 cursor-pointer">
            </div>
        </div>
    </div>

</div>
@endsection