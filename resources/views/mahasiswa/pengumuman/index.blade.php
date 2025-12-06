@extends('layouts.mahasiswa')

@section('title', 'Pengumuman')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Pengumuman
                </h1>
                <p class="text-gray-600">Informasi terbaru seputar PMB 2025</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-bullhorn text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Kategori -->
    <div x-data="{ kategori: 'all' }" class="mb-6">
        <div class="bg-white rounded-xl shadow-lg p-4">
            <div class="flex flex-wrap gap-2">
                <button @click="kategori = 'all'" 
                        :class="kategori === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-medium transition-all duration-200">
                    <i class="fas fa-th-large mr-2"></i>Semua
                </button>
                <button @click="kategori = 'penting'" 
                        :class="kategori === 'penting' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-medium transition-all duration-200">
                    <i class="fas fa-exclamation-circle mr-2"></i>Penting
                </button>
                <button @click="kategori = 'informasi'" 
                        :class="kategori === 'informasi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-medium transition-all duration-200">
                    <i class="fas fa-info-circle mr-2"></i>Informasi
                </button>
                <button @click="kategori = 'jadwal'" 
                        :class="kategori === 'jadwal' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-medium transition-all duration-200">
                    <i class="fas fa-calendar-alt mr-2"></i>Jadwal
                </button>
            </div>
        </div>
    </div>

    <!-- Pengumuman List -->
    @if($pengumumans->count() > 0)
        <div class="space-y-4">
            @foreach($pengumumans as $pengumuman)
            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <!-- Kategori Badge -->
                            <div class="mb-3">
                                @if($pengumuman->kategori === 'penting')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Penting
                                    </span>
                                @elseif($pengumuman->kategori === 'informasi')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <i class="fas fa-info-circle mr-1"></i>Informasi
                                    </span>
                                @elseif($pengumuman->kategori === 'jadwal')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <i class="fas fa-calendar-alt mr-1"></i>Jadwal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        <i class="fas fa-tag mr-1"></i>Umum
                                    </span>
                                @endif
                            </div>

                            <!-- Judul -->
                            <h3 class="text-xl font-bold text-gray-800 mb-2 hover:text-indigo-600 transition-colors">
                                {{ $pengumuman->judul }}
                            </h3>

                            <!-- Isi (Preview) -->
                            <p class="text-gray-600 mb-4 line-clamp-2">
                                {{ Str::limit(strip_tags($pengumuman->isi), 150) }}
                            </p>

                            <!-- Meta Info -->
                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                <span class="flex items-center">
                                    <i class="far fa-calendar mr-2"></i>
                                    {{ $pengumuman->tanggal_publish->format('d M Y') }}
                                </span>
                                <span class="flex items-center">
                                    <i class="far fa-clock mr-2"></i>
                                    {{ $pengumuman->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="ml-4">
                            <a href="{{ route('mahasiswa.pengumuman.show', $pengumuman->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <span class="hidden sm:inline mr-2">Baca</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Highlight Bar -->
                @if($pengumuman->kategori === 'penting')
                    <div class="h-1 bg-gradient-to-r from-red-500 to-red-600"></div>
                @elseif($pengumuman->kategori === 'informasi')
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                @elseif($pengumuman->kategori === 'jadwal')
                    <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
                @else
                    <div class="h-1 bg-gradient-to-r from-gray-400 to-gray-500"></div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $pengumumans->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Pengumuman</h3>
            <p class="text-gray-600">Pengumuman akan ditampilkan di sini ketika tersedia</p>
        </div>
    @endif
</div>

<!-- Custom Pagination Styling -->
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection