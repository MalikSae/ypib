@extends('layouts.landing')
@section('title', 'PMB Universitas YPIB Majalengka')
@section('content')
    <!-- SECTION 1: HERO -->
    <section class="relative pt-32 lg:pt-36 pb-0 overflow-hidden bg-gradient-to-br from-[#0B2B7F] to-[#09183E]">
        <!-- Glow Orbs -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-[#0B41CB] rounded-full mix-blend-screen filter blur-[120px] opacity-40"></div>
            <div class="absolute top-1/2 right-0 w-[400px] h-[400px] bg-[#F1B10E] rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>
            <!-- Dot Grid -->
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 28px 28px;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Content (7 columns) -->
                <div class="lg:col-span-7 pb-0 lg:pb-24">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-3 mb-8 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md">
                        <div class="w-6 h-6 rounded-full bg-[#F1B10E] flex items-center justify-center shrink-0">
                            <i class="ti ti-bolt text-xs text-neutral-900"></i>
                        </div>
                        <span class="text-white/90 font-bold text-sm">
                            Pendaftaran dibuka hingga
                            <span class="text-[#F1B10E]">
                                {{ isset($period) && $period->close_date ? \Carbon\Carbon::parse($period->close_date)->translatedFormat('d F Y') : 'N/A' }}
                            </span>
                        </span>
                    </div>

                    <style>
                        @keyframes blob-morph {
                            0% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
                            50% { border-radius: 40% 60% 70% 30% / 50% 60% 30% 60%; }
                            100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
                        }
                        .animate-blob { animation: blob-morph 12s ease-in-out infinite; }
                    </style>

                    <!-- Headline -->
                    <h1 class="text-[42px] md:text-[54px] lg:text-[64px] font-black tracking-tight text-white mb-6 leading-[1.1]">
                        Wujudkan <span class="text-[#F1B10E]">Karirmu</span><br>di Bidang Kesehatan & Teknologi.
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-lg text-white/65 mb-12 leading-relaxed font-medium max-w-xl">
                        Universitas YPIB Majalengka - pilihan cerdas untuk karir di kesehatan, farmasi, dan teknologi.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4 lg:mb-16 relative z-20">
                        <a href="#program-studi" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-black rounded-2xl bg-[#F1B10E] text-neutral-900 hover:bg-yellow-400 transition-all shadow-[0_10px_30px_rgba(241,177,14,0.35)] transform hover:-translate-y-1">
                            Daftar Sekarang
                        </a>
                        <div class="flex items-center gap-4 px-2">
                            <div class="w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0">
                                <i class="ti ti-wallet text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-[11px] font-bold text-white/40 uppercase tracking-wider mb-0.5">Biaya Kuliah</div>
                                <div class="font-bold text-white whitespace-nowrap">Mulai Rp 400.000</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Image & Floating Cards (5 columns) -->
                <div class="lg:col-span-5 relative mt-0 lg:mt-0 lg:pt-10 self-end">
                    
                    <!-- Organic Blob Background -->
                    <div class="absolute top-4 bottom-4 left-0 right-0 lg:-right-12 bg-gradient-to-br from-[#0B41CB] to-[#092367] z-0 overflow-hidden shadow-[0_30px_60px_-15px_rgba(11,65,203,0.5)] animate-blob">
                        <!-- Soft Grid overlay -->
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>
                    </div>

                    <!-- Modern Floating Ornaments -->
                    <div class="absolute top-1/4 -left-12 z-10 opacity-70 pointer-events-none hidden sm:block animate-float">
                        <svg width="100" height="100" fill="none" viewBox="0 0 100 100">
                            <pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <circle cx="2" cy="2" r="2" fill="#F1B10E" />
                            </pattern>
                            <rect width="100" height="100" fill="url(#dots)" />
                        </svg>
                    </div>
                    <div class="absolute bottom-32 -left-8 w-12 h-12 rounded-full border-[3px] border-primary-300 z-10 opacity-60 pointer-events-none hidden sm:block animate-float" style="animation-delay: 1.5s;"></div>
                    
                    <!-- Main Image (Solid Cutout, sits at bottom) -->
                    <div class="relative z-20 flex justify-center pb-0">
                        <img src="{{ asset('images/mahasiswa.png') }}" fetchpriority="high" loading="eager" alt="Mahasiswa YPIB" class="w-[110%] -ml-[5%] max-w-[500px] lg:max-w-none lg:w-[135%] lg:-ml-[15%] h-auto object-contain object-bottom drop-shadow-[0_20px_30px_rgba(0,0,0,0.3)]">
                    </div>

                    <!-- Floating Card 1: Badge Unggul -->
                    <div class="absolute top-16 right-0 lg:-right-12 z-30 animate-float" style="animation-delay: 0s;">
                        <img src="{{ asset('images/badge-unggul.png') }}" fetchpriority="high" loading="eager" alt="Akreditasi Unggul" class="w-24 h-24 lg:w-28 lg:h-28 object-cover rounded-2xl shadow-[0_20px_50px_-12px_rgba(11,65,203,0.4)] border-2 border-white/80">
                    </div>

                    <!-- Floating Card 2: Job available style (Beasiswa) -->
                    <div class="absolute bottom-24 -right-4 lg:-right-8 bg-white/70 backdrop-blur-xl pl-2 pr-6 py-2 rounded-full shadow-[0_20px_50px_-12px_rgba(241,177,14,0.3)] border border-white/60 flex items-center gap-3 z-30 animate-float" style="animation-delay: 1.5s;">
                        <div class="w-10 h-10 bg-gradient-to-br from-secondary-100 to-secondary-200 text-secondary-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="ti ti-gift text-xl"></i>
                        </div>
                        <div class="font-bold text-neutral-900 text-[15px]">Tersedia Beasiswa</div>
                    </div>


                </div>

            </div>
        </div>
    </section>



    <!-- SECTION 3: PROGRAM STUDI -->
    <section id="program-studi" class="py-24 bg-neutral-50 border-t border-neutral-200/60 relative overflow-hidden">
        <!-- Decor -->
        <div class="absolute top-0 right-0 w-1/2 h-96 bg-primary-50 rounded-full blur-[120px] opacity-60 pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" x-data="{ filter: 'all' }">
            
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 mb-16">
                <div class="max-w-2xl">
                    <div class="inline-block px-3 py-1 mb-4 rounded-full bg-primary-100 text-primary-700 text-xs font-bold tracking-widest uppercase">Akademik</div>
                    <h2 class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight mb-4">Pilih Program Studimu</h2>
                    <p class="text-lg text-neutral-500 font-medium">Temukan program yang sesuai minat dan passionmu untuk masa depan gemilang.</p>
                </div>
                
                <!-- Filter Pills -->
                <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl shadow-sm border border-neutral-100">
                    <button @click="filter = 'all'" 
                            :class="filter === 'all' ? 'bg-primary-600 text-white shadow-md' : 'bg-transparent text-neutral-600 hover:bg-neutral-100'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all">
                        Semua
                    </button>
                    @foreach($faculties as $f)
                        <button @click="filter = '{{ $f->id }}'" 
                                :class="filter === '{{ $f->id }}' ? 'bg-primary-600 text-white shadow-md' : 'bg-transparent text-neutral-600 hover:bg-neutral-100'"
                                class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all">
                            {{ $f->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Grid Kartu Prodi -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($programs as $program)
                    <div x-show="filter === 'all' || filter === '{{ $program->faculty_id }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-8"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="bg-white rounded-[2rem] p-6 lg:p-8 flex flex-col hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] transition-all duration-300 border border-neutral-100 group">
                        
                        <!-- Top Header: Logo/Icon & Decorative Icon -->
                        <div class="flex items-start justify-between mb-8">
                            @if($program->icon)
                                <img src="{{ asset('images/icons/' . $program->icon) }}" loading="lazy" alt="{{ $program->name }}" class="w-16 h-16 object-contain shrink-0 drop-shadow-sm">
                            @else
                                <div class="w-16 h-16 rounded-full flex items-center justify-center bg-primary-50 text-primary-600 font-black text-2xl shrink-0">
                                    {{ substr($program->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="px-3 py-1.5 rounded-full border border-neutral-200 text-xs font-bold text-neutral-500 flex items-center gap-1.5">
                                Terakreditasi <i class="ti ti-trophy text-neutral-400"></i>
                            </div>
                        </div>

                        <!-- Main Content: Subtitle, Title, Badges -->
                        <div class="flex-grow">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm font-bold text-neutral-900">{{ $program->faculty ? $program->faculty->name : 'Fakultas' }}</span>
                            </div>
                            <h3 class="text-2xl font-black text-neutral-900 mb-4 leading-tight group-hover:text-primary-600 transition-colors">
                                {{ $program->name }}
                            </h3>
                            
                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mb-8">
                                <span class="px-3 py-1.5 rounded-lg bg-neutral-100 text-neutral-600 text-xs font-bold">
                                    Kuota {{ $program->quota }}
                                </span>
                                <span class="px-3 py-1.5 rounded-lg bg-neutral-100 text-neutral-600 text-xs font-bold">
                                    Akreditasi {{ $program->accreditation ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Footer: Price & Apply Button -->
                        <div class="flex items-end justify-between pt-6 mt-auto">
                            <div>
                                <div class="font-black text-lg text-neutral-900 leading-none mb-1">
                                    Rp {{ number_format($program->registration_fee, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wide">
                                    Biaya Pendaftaran
                                </div>
                            </div>
                            <a href="{{ route('prodi.show', $program->slug) }}" class="px-6 py-2.5 rounded-xl bg-neutral-900 text-white text-sm font-bold hover:bg-primary-600 transition-colors flex items-center gap-2">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
        </div>
    </section>

    <!-- SECTION 4: CARA DAFTAR -->
    <section id="cara-daftar" class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <div class="inline-block px-3 py-1 mb-4 rounded-full bg-secondary-100 text-secondary-700 text-xs font-bold tracking-widest uppercase">Panduan</div>
                <h2 class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight mb-4">Cara Mendaftar</h2>
                <p class="text-lg text-neutral-500 font-medium">5 langkah mudah untuk bergabung menjadi mahasiswa YPIB.</p>
            </div>

            <!-- Horizontal Stepper -->
            <div class="relative max-w-6xl mx-auto">
                <!-- Desktop connecting line -->
                <div class="hidden lg:block absolute top-10 left-[10%] right-[10%] h-0.5 bg-neutral-200 border-t-2 border-dashed border-neutral-200 z-0"></div>

                <div class="flex flex-col lg:flex-row justify-between gap-12 lg:gap-4 relative z-10">
                    
                    @php
                        $steps = [
                            ['num' => '01', 'title' => 'PMB Online', 'desc' => 'Kunjungi website PMB atau datang ke kampus'],
                            ['num' => '02', 'title' => 'Biodata Diri', 'desc' => 'Isi form biodata dengan lengkap dan benar'],
                            ['num' => '03', 'title' => 'Lengkapi Berkas', 'desc' => 'Upload dokumen persyaratan yang diminta'],
                            ['num' => '04', 'title' => 'Seleksi', 'desc' => 'Ikuti seleksi tertulis (One Day Service)'],
                            ['num' => '05', 'title' => 'Pengumuman', 'desc' => 'Cek hasil kelulusan & lanjut daftar ulang']
                        ];
                    @endphp

                    @foreach($steps as $index => $step)
                    <div class="flex lg:flex-col items-start lg:items-center text-left lg:text-center gap-6 flex-1 group">
                        <div class="w-20 h-20 rounded-[1.5rem] bg-white border border-neutral-200 text-neutral-400 group-hover:border-primary-200 group-hover:bg-primary-50 group-hover:text-primary-600 flex items-center justify-center text-3xl font-black shadow-[0_8px_30px_rgba(0,0,0,0.04)] transition-all duration-300 shrink-0">
                            {{ $step['num'] }}
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-neutral-900 mb-2 group-hover:text-primary-600 transition-colors">{{ $step['title'] }}</h4>
                            <p class="text-neutral-500 text-sm font-medium leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: JALUR PENDAFTARAN -->
    <section id="jalur" class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #09183E 0%, #0B2B7F 50%, #09183E 100%);">
        <!-- Background Glow Orbs -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-40 left-1/4 w-96 h-96 rounded-full" style="background: #0B41CB; filter: blur(120px); opacity: 0.3;"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full" style="background: #F1B10E; filter: blur(140px); opacity: 0.12;"></div>
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 28px 28px;"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-6 text-xs font-bold uppercase tracking-widest" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.8);">
                    Jalur Penerimaan
                </div>
                <h2 class="text-4xl md:text-5xl font-black tracking-tight mb-4" style="color: #fff;">Pilih <span style="color: #F1B10E;">Jalurmu</span> Masuk Universitas YPIB</h2>
                <p class="text-lg font-medium max-w-xl mx-auto" style="color: rgba(255,255,255,0.55);">Tiga jalur pendaftaran yang fleksibel, dirancang untuk mengakomodasi berbagai latar belakang calon mahasiswa.</p>
            </div>

            <!-- 3-Column Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">

                <!-- Kartu 1: Jalur Umum -->
                <div class="rounded-[2rem] p-8 flex flex-col h-full transition-all duration-300 hover:-translate-y-1" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(20px);">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5" style="background: rgba(255,255,255,0.1);">
                        <i class="ti ti-clipboard-list text-3xl" style="color: rgba(255,255,255,0.75);"></i>
                    </div>
                    <div class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4 w-max" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.55);">Terbuka untuk semua</div>
                    <h3 class="text-2xl font-black mb-3" style="color: #fff;">Jalur Reguler</h3>
                    <p class="text-sm font-medium leading-relaxed flex-grow" style="color: rgba(255,255,255,0.5);">Jalur masuk reguler dengan sistem seleksi melalui tes tertulis (CBT). Tersedia untuk seluruh lulusan SMA/SMK/MA.</p>
                    <a href="#program-studi" class="mt-8 flex items-center justify-center gap-2 w-full py-3.5 rounded-xl font-bold text-sm" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.8);">
                        Pilih Jalur Ini <i class="ti ti-arrow-right"></i>
                    </a>
                </div>

                <!-- Kartu 2: Jalur Prestasi (FEATURED - elevated) -->
                <div class="rounded-[2rem] p-8 flex flex-col relative overflow-hidden md:-mt-4 md:-mb-4 transition-all duration-300 hover:scale-[1.02]" style="background: linear-gradient(160deg, #F1B10E 0%, #C98C00 100%); box-shadow: 0 30px 80px -10px rgba(241,177,14,0.5);">
                    <!-- Inner glow -->
                    <div class="absolute top-0 right-0 w-52 h-52 rounded-full pointer-events-none" style="background: rgba(255,255,255,0.2); filter: blur(50px); transform: translate(30%,-30%);"></div>
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 w-max px-3 py-1.5 rounded-full mb-5 text-xs font-black uppercase tracking-wider" style="background: rgba(0,0,0,0.18); color: #09183E;">
                        <span class="w-1.5 h-1.5 rounded-full inline-block" style="background: #09183E; animation: pulse 2s infinite;"></span>
                        Paling Diminati
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5" style="background: rgba(0,0,0,0.15);">
                        <i class="ti ti-award text-3xl" style="color: #09183E;"></i>
                    </div>
                    <div class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4 w-max" style="background: rgba(0,0,0,0.12); color: #09183E;">Tanpa Tes Tulis</div>
                    <h3 class="text-2xl font-black mb-3" style="color: #09183E;">Jalur Prestasi</h3>
                    <p class="text-sm font-medium leading-relaxed flex-grow" style="color: rgba(9,24,62,0.7);">Bebas tes tertulis khusus untuk siswa peraih prestasi akademik (rapor) maupun non-akademik.</p>
                    <a href="#program-studi" class="mt-8 flex items-center justify-center gap-2 w-full py-4 rounded-xl font-black text-base relative z-10" style="background: #09183E; color: #F1B10E; box-shadow: 0 10px 30px rgba(9,24,62,0.35);">
                        Daftar Sekarang <i class="ti ti-arrow-right"></i>
                    </a>
                </div>

                <!-- Kartu 3: Jalur Tahfidz -->
                <div class="rounded-[2rem] p-8 flex flex-col h-full relative overflow-hidden transition-all duration-300 hover:-translate-y-1" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(20px);">
                    <!-- Inner glow -->
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full pointer-events-none" style="background: #0B41CB; filter: blur(50px); opacity: 0.4;"></div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 relative z-10" style="background: rgba(11,65,203,0.3); border: 1px solid rgba(11,65,203,0.4);">
                        <i class="ti ti-book text-3xl" style="color: #7EB3FF;"></i>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-4 w-max relative z-10" style="background: rgba(11,65,203,0.25); color: #7EB3FF; border: 1px solid rgba(11,65,203,0.3);">
                        <i class="ti ti-star-filled text-[10px]"></i> Beasiswa Tersedia
                    </div>
                    <h3 class="text-2xl font-black mb-3 relative z-10" style="color: #fff;">Jalur Tahfidz</h3>
                    <p class="text-sm font-medium leading-relaxed flex-grow relative z-10" style="color: rgba(255,255,255,0.5);">Apresiasi khusus berupa beasiswa bagi calon mahasiswa penghafal Al-Quran minimal 1 Juz.</p>
                    <a href="#program-studi" class="mt-8 flex items-center justify-center gap-2 w-full py-3.5 rounded-xl font-bold text-sm relative z-10" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.8);">
                        Pilih Jalur Ini <i class="ti ti-arrow-right"></i>
                    </a>
                </div>

            </div>

            <!-- Period Footer -->
            <div class="mt-10 text-center">
                <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl text-sm font-bold" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.7);">
                    <i class="ti ti-calendar-event" style="color: #F1B10E;"></i>
                    Pendaftaran: {{ isset($period) && $period ? \Carbon\Carbon::parse($period->open_date)->translatedFormat('d M Y') . ' — ' . \Carbon\Carbon::parse($period->close_date)->translatedFormat('d M Y') : 'Dibuka Segera' }}
                </div>
            </div>

        </div>
    </section>


    <!-- SECTION 6: MITRA KERJA SAMA -->
    <section class="py-20 bg-white border-t border-neutral-200 overflow-hidden">
        <div class="text-center mb-12">
            <div class="inline-block px-3 py-1 mb-4 rounded-full bg-neutral-100 text-neutral-600 text-xs font-bold tracking-widest uppercase">Kerja Sama</div>
            <h2 class="text-3xl md:text-4xl font-black text-neutral-900 mb-4">Dipercaya Institusi Terkemuka</h2>
            <p class="text-neutral-500 font-medium">Jaringan mitra strategis kami membuka peluang karir yang lebih luas untuk lulusan.</p>
        </div>
        
        @if(isset($partners) && $partners->count() > 0)
        <div class="relative flex overflow-x-hidden group">
            <!-- Fade gradients -->
            <div class="absolute top-0 left-0 w-32 h-full bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-32 h-full bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
            
            <div class="animate-marquee whitespace-nowrap flex items-center group-hover:[animation-play-state:paused]">
                @foreach($partners as $partner)
                    <div class="mx-10 flex-shrink-0">
                        <img src="{{ Storage::url($partner->logo_path) }}" loading="lazy" alt="{{ $partner->name }}" class="h-20 md:h-24 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                    </div>
                @endforeach
                <!-- Duplicate for seamless loop -->
                @foreach($partners as $partner)
                    <div class="mx-10 flex-shrink-0">
                        <img src="{{ Storage::url($partner->logo_path) }}" loading="lazy" alt="{{ $partner->name }}" class="h-20 md:h-24 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                    </div>
                @endforeach
                 @foreach($partners as $partner)
                    <div class="mx-10 flex-shrink-0">
                        <img src="{{ Storage::url($partner->logo_path) }}" loading="lazy" alt="{{ $partner->name }}" class="h-20 md:h-24 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="text-center py-10">
            <p class="text-neutral-400 font-medium">Logo mitra akan ditampilkan di sini.</p>
        </div>
        @endif
    </section>

    <!-- SECTION 7: GALLERY IMAGE -->
    <section class="py-24 bg-white border-t border-neutral-200/60" x-data="{ limit: 6, total: {{ isset($facilities) ? $facilities->where('is_active', true)->count() : 0 }} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-block px-3 py-1 mb-4 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-widest uppercase border border-primary-100">Gallery Kampus</div>
                <h2 class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight mb-4">Momen & Aktivitas</h2>
                <p class="text-lg text-neutral-500 font-medium">Jelajahi potret kegiatan mahasiswa, fasilitas, dan kehidupan kampus di YPIB Majalengka.</p>
            </div>

            <div class="columns-2 lg:columns-3 gap-3 sm:gap-6 space-y-3 sm:space-y-6">
                @if(isset($facilities) && $facilities->where('is_active', true)->count() > 0)
                    @foreach($facilities->where('is_active', true)->values() as $index => $gallery)
                    <div class="break-inside-avoid relative group rounded-[2rem] overflow-hidden bg-neutral-100 mb-6"
                         x-show="{{ $index }} < limit"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         {{-- Assign varied heights to placeholders to simulate masonry --}}
                         style="{{ !$gallery->image_path ? 'min-height: ' . rand(250, 450) . 'px;' : '' }}">
                        
                        @if($gallery->image_path)
                            <img src="{{ Storage::url($gallery->image_path) }}" loading="lazy" alt="{{ $gallery->name }}" class="w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        @else
                            <div class="w-full h-full absolute inset-0 flex flex-col items-center justify-center bg-neutral-100 text-neutral-300">
                                <i class="ti ti-photo text-5xl mb-2"></i>
                                <span class="text-xs font-medium">{{ rand(600, 800) }} x {{ rand(400, 900) }}</span>
                            </div>
                        @endif
                        
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6 md:p-8">
                            <h4 class="text-xl font-bold text-white mb-1 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">{{ $gallery->name }}</h4>
                            @if($gallery->description)
                            <p class="text-sm text-white/80 line-clamp-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 delay-75">{{ $gallery->description }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-span-full text-center py-16">
                        <p class="text-neutral-400 font-medium">Gallery foto belum tersedia.</p>
                    </div>
                @endif
            </div>

            <!-- Load More Button -->
            <div class="mt-16 text-center" x-show="limit < total" x-cloak>
                <button @click="limit += 6" class="inline-flex items-center justify-center px-8 py-3.5 text-sm font-bold rounded-2xl bg-white border border-neutral-200 text-neutral-700 hover:bg-neutral-50 hover:text-primary-600 transition-all duration-300 shadow-sm hover:shadow-md group">
                    <span class="mr-2">Tampilkan Lebih Banyak</span>
                    <i class="ti ti-loader group-hover:rotate-180 transition-transform duration-500"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION 8: FINAL CTA -->
    <section class="py-24 bg-gradient-to-br from-[#0B2B7F] to-[#09183E] relative overflow-hidden">
        <!-- Decor -->
        <div class="absolute inset-0 z-0">
            <div class="absolute -top-24 -left-24 w-[500px] h-[500px] bg-[#0B41CB] rounded-full mix-blend-overlay filter blur-[100px] opacity-50"></div>
            <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-[#F1B10E] rounded-full mix-blend-overlay filter blur-[120px] opacity-30"></div>
            <!-- Grid Pattern -->
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 32px 32px;"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
            <div class="inline-block px-4 py-1.5 mb-6 rounded-full bg-white/10 backdrop-blur-md text-white border border-white/20 text-xs font-bold tracking-widest uppercase">
                PMB UNIVERSITAS YPIB {{ isset($period) ? $period->year : '2026/2027' }}
            </div>
            
            <h2 class="text-4xl md:text-5xl lg:text-7xl font-black text-white tracking-tight mb-6 leading-tight">
                Siap Mulai<br><span class="text-secondary-400">Perjalananmu?</span>
            </h2>
            <p class="text-xl text-primary-100 font-medium mb-12 max-w-2xl mx-auto">
                Daftarkan dirimu sekarang sebelum kuota penuh. Pendaftaran ditutup pada <strong class="text-white">{{ isset($period) && $period ? \Carbon\Carbon::parse($period->close_date)->translatedFormat('d F Y') : 'N/A' }}</strong>.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#program-studi" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-5 text-lg font-black rounded-2xl bg-secondary-500 text-neutral-900 hover:bg-secondary-400 transition-all shadow-[0_10px_30px_rgba(241,177,14,0.4)] transform hover:-translate-y-1">
                    Daftar Sekarang
                </a>

            </div>


        </div>
    </section>

@endsection
