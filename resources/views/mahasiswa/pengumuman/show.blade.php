@extends('layouts.mahasiswa')

@section('title', 'Detail Pengumuman')

@section('content')
<div class="fade-in-up">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('mahasiswa.pengumuman.index') }}" 
                       class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Detail Pengumuman
                    </h1>
                </div>
                <p class="text-gray-600 ml-13">Informasi lengkap pengumuman</p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center float">
                    <i class="fas fa-bullhorn text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Konten Pengumuman -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header Kategori -->
                <div class="p-6 bg-gradient-to-r 
                    @if($pengumuman->kategori === 'umum')
                        from-purple-500 to-purple-600
                    @elseif($pengumuman->kategori === 'penerimaan')
                        from-blue-500 to-blue-600
                    @elseif($pengumuman->kategori === 'pembayaran')
                        from-green-500 to-green-600
                    @else
                        from-indigo-500 to-indigo-600
                    @endif
                ">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            @if($pengumuman->kategori === 'umum')
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-tag text-2xl"></i>
                                </div>
                                <span class="text-lg font-semibold">Pengumuman Umum</span>
                            @elseif($pengumuman->kategori === 'penerimaan')
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-check text-2xl"></i>
                                </div>
                                <span class="text-lg font-semibold">Penerimaan</span>
                            @elseif($pengumuman->kategori === 'pembayaran')
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-2xl"></i>
                                </div>
                                <span class="text-lg font-semibold">Pembayaran</span>
                            @else
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-bullhorn text-2xl"></i>
                                </div>
                                <span class="text-lg font-semibold">Pengumuman</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Judul & Meta Info -->
                <div class="p-8 border-b border-gray-200">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        {{ $pengumuman->judul }}
                    </h2>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="far fa-calendar text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Publish</p>
                                <p class="font-semibold text-gray-800">{{ $pengumuman->tanggal_publish->format('d F Y') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="far fa-clock text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Diposting</p>
                                <p class="font-semibold text-gray-800">{{ $pengumuman->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if($pengumuman->kategori)
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 
                                @if($pengumuman->kategori === 'umum')
                                    bg-purple-100
                                @elseif($pengumuman->kategori === 'penerimaan')
                                    bg-blue-100
                                @elseif($pengumuman->kategori === 'pembayaran')
                                    bg-green-100
                                @else
                                    bg-gray-100
                                @endif
                                rounded-lg flex items-center justify-center">
                                <i class="fas 
                                    @if($pengumuman->kategori === 'umum')
                                        fa-tag text-purple-600
                                    @elseif($pengumuman->kategori === 'penerimaan')
                                        fa-user-check text-blue-600
                                    @elseif($pengumuman->kategori === 'pembayaran')
                                        fa-money-bill-wave text-green-600
                                    @else
                                        fa-tag text-gray-600
                                    @endif
                                "></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kategori</p>
                                <p class="font-semibold text-gray-800 capitalize">{{ $pengumuman->kategori }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($pengumuman->gambar)
                <div class="w-full h-64 md:h-96 bg-slate-100">
                    <img src="{{ Storage::url($pengumuman->gambar) }}" 
                         alt="{{ $pengumuman->judul }}" 
                         class="w-full h-full object-cover">
                </div>
                @endif

                <!-- Isi Pengumuman -->
                <div class="p-8">
                    <div class="prose prose-lg max-w-none">
                        <div class="text-gray-700 leading-relaxed space-y-4">
                            {!! nl2br(e($pengumuman->isi)) !!}
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('mahasiswa.pengumuman.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all duration-200 font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Daftar
                        </a>

                        <button onclick="window.print()" 
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-print mr-2"></i>
                            Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Badge -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Status</h3>
                        <p class="text-sm text-gray-600">Pengumuman Aktif</p>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    <p class="flex items-center mb-2">
                        <i class="fas fa-eye text-indigo-600 mr-2"></i>
                        Pengumuman ini sedang aktif dan dapat dilihat oleh semua mahasiswa
                    </p>
                </div>
            </div>

            <!-- Info Kategori -->
            <div class="bg-gradient-to-br 
                @if($pengumuman->kategori === 'umum')
                    from-purple-500 to-purple-600
                @elseif($pengumuman->kategori === 'penerimaan')
                    from-blue-500 to-blue-600
                @elseif($pengumuman->kategori === 'pembayaran')
                    from-green-500 to-green-600
                @else
                    from-indigo-500 to-indigo-600
                @endif
                rounded-2xl shadow-xl p-6 text-white">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        @if($pengumuman->kategori === 'umum')
                            <i class="fas fa-tag text-2xl"></i>
                        @elseif($pengumuman->kategori === 'penerimaan')
                            <i class="fas fa-user-check text-2xl"></i>
                        @elseif($pengumuman->kategori === 'pembayaran')
                            <i class="fas fa-money-bill-wave text-2xl"></i>
                        @else
                            <i class="fas fa-bullhorn text-2xl"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">
                            @if($pengumuman->kategori === 'umum')
                                Umum
                            @elseif($pengumuman->kategori === 'penerimaan')
                                Penerimaan
                            @elseif($pengumuman->kategori === 'pembayaran')
                                Pembayaran
                            @else
                                Pengumuman
                            @endif
                        </h3>
                        <p class="text-sm opacity-90">Kategori Pengumuman</p>
                    </div>
                </div>
                <p class="text-sm opacity-90">
                    @if($pengumuman->kategori === 'umum')
                        Pengumuman umum untuk mahasiswa PMB 2025
                    @elseif($pengumuman->kategori === 'penerimaan')
                        Informasi terkait proses penerimaan mahasiswa baru
                    @elseif($pengumuman->kategori === 'pembayaran')
                        Informasi terkait pembayaran dan biaya pendaftaran
                    @else
                        Pengumuman untuk mahasiswa
                    @endif
                </p>
            </div>

            <!-- Pengumuman Terkait -->
            @php
                $relatedPengumumans = App\Models\Pengumuman::where('is_active', true)
                    ->where('id', '!=', $pengumuman->id)
                    ->where('kategori', $pengumuman->kategori)
                    ->latest()
                    ->take(3)
                    ->get();
            @endphp

            @if($relatedPengumumans->count() > 0)
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list text-indigo-600 mr-2"></i>
                    Pengumuman Terkait
                </h3>
                <div class="space-y-3">
                    @foreach($relatedPengumumans as $related)
                    <a href="{{ route('mahasiswa.pengumuman.show', $related->id) }}" 
                       class="block p-3 bg-gray-50 rounded-lg hover:bg-indigo-50 transition-all group">
                        <h4 class="font-semibold text-sm text-gray-800 group-hover:text-indigo-600 mb-1 line-clamp-2">
                            {{ $related->judul }}
                        </h4>
                        <p class="text-xs text-gray-500 flex items-center">
                            <i class="far fa-calendar mr-1"></i>
                            {{ $related->tanggal_publish->format('d M Y') }}
                        </p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Bantuan -->
            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-5">
                <div class="flex items-start">
                    <i class="fas fa-question-circle text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Butuh Bantuan?</h4>
                        <p class="text-sm text-gray-600 mb-3">Hubungi kami jika ada pertanyaan</p>
                        <div class="space-y-2 text-sm">
                            <a href="tel:+621234567890" class="flex items-center text-gray-700 hover:text-indigo-600">
                                <i class="fas fa-phone mr-2"></i>+62 123 4567 890
                            </a>
                            <a href="mailto:pmb@university.ac.id" class="flex items-center text-gray-700 hover:text-indigo-600">
                                <i class="fas fa-envelope mr-2"></i>pmb@university.ac.id
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        nav, footer, .no-print {
            display: none !important;
        }
        .bg-gradient-to-r, .bg-gradient-to-br {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .prose {
        max-width: 100%;
    }
</style>
@endsection