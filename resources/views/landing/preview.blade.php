<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMB Universitas YPIB Majalengka</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#F2F5FD', 100: '#E5EBFA', 500: '#0E4EF1', 600: '#0B41CB', 700: '#0936A9', 900: '#092367'
                        },
                        secondary: {
                            500: '#F1B10E', 600: '#CB950B'
                        },
                        neutral: {
                            50: '#F7F7F8', 100: '#F1F2F4', 200: '#E3E4E8', 400: '#9A9FAC', 500: '#646E87', 800: '#272B35', 900: '#181A20'
                        }
                    },
                    animation: {
                        'marquee': 'marquee 40s linear infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-100%)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans text-neutral-900 bg-neutral-50 antialiased selection:bg-primary-200 selection:text-primary-900 overflow-x-hidden">

    <!-- NAVBAR -->
    <nav class="fixed top-0 inset-x-0 z-50 transition-all duration-300" x-data="{ scrolled: false, mobileOpen: false }" @scroll.window="scrolled = window.scrollY > 20">
        <div :class="scrolled ? 'bg-white/95 backdrop-blur-xl shadow-[0_4px_30px_rgba(0,0,0,0.06)] border-b border-neutral-100' : 'bg-transparent'" class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-3">
                        <img :src="scrolled ? '{{ asset('images/logo-ypib.png') }}' : '{{ asset('images/logo-ypib-white.png') }}'" alt="Logo YPIB" class="h-12 w-auto transition-all duration-300">
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-1">
                        <a href="#" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-primary-600 bg-primary-50' : 'text-[#F1B10E] bg-white/10'">Beranda</a>
                        <a href="#keunggulan" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Keunggulan</a>
                        <a href="#prodi" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Program Studi</a>
                        <a href="#jalur" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Jalur Masuk</a>
                    </div>

                    <!-- Action / Mobile Toggle -->
                    <div class="flex items-center gap-4">
                        <div class="hidden md:block">
                            <a href="{{ route('registration.create') }}" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold rounded-xl bg-[#F1B10E] text-neutral-900 hover:bg-yellow-400 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                Daftar Sekarang
                            </a>
                        </div>
                        <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg transition-colors focus:outline-none"
                                :class="scrolled ? 'text-neutral-900 hover:bg-neutral-100' : 'text-white hover:bg-white/10'">
                            <i class="ti ti-menu-2 text-2xl" x-show="!mobileOpen"></i>
                            <i class="ti ti-x text-2xl" x-show="mobileOpen" x-cloak></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" x-transition x-cloak class="md:hidden absolute w-full shadow-2xl pb-6 border-t transition-colors duration-300"
             :class="scrolled ? 'bg-white/98 backdrop-blur-xl border-neutral-100' : 'bg-[#09183E]/98 backdrop-blur-xl border-white/10'">
            <div class="px-4 pt-4 space-y-1">
                <a href="#" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-primary-600 bg-primary-50' : 'text-[#F1B10E] bg-white/10'">Beranda</a>
                <a href="#keunggulan" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Keunggulan</a>
                <a href="#prodi" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Program Studi</a>
                <a href="#jalur" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Jalur Masuk</a>
                <div class="pt-4 px-2">
                    <a href="{{ route('registration.create') }}" class="block w-full text-center bg-[#F1B10E] text-neutral-900 px-6 py-3.5 rounded-xl font-bold">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </nav>


    <!-- SECTION 1: HERO -->
    <section class="relative pt-24 lg:pt-36 pb-0 overflow-hidden bg-gradient-to-br from-[#0B2B7F] to-[#09183E]">
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
                <div class="lg:col-span-7 pb-16 lg:pb-24">
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
                        YPIB Majalengka - pilihan cerdas untuk karir di kesehatan, farmasi, dan teknologi. Dua kampus, sembilan program studi, beasiswa tersedia.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-16 relative z-20">
                        <a href="{{ route('registration.create') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-black rounded-2xl bg-[#F1B10E] text-neutral-900 hover:bg-yellow-400 transition-all shadow-[0_10px_30px_rgba(241,177,14,0.35)] transform hover:-translate-y-1">
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
                <div class="lg:col-span-5 relative mt-16 lg:mt-0 pt-10 self-end">
                    
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
                        <img src="{{ asset('images/mahasiswa.png') }}" alt="Mahasiswa YPIB" class="w-[110%] -ml-[5%] max-w-[500px] lg:max-w-none lg:w-[135%] lg:-ml-[15%] h-auto object-contain object-bottom drop-shadow-[0_20px_30px_rgba(0,0,0,0.3)]">
                    </div>

                    <!-- Floating Card 1: Badge Unggul -->
                    <div class="absolute top-16 right-0 lg:-right-12 z-30 animate-float" style="animation-delay: 0s;">
                        <img src="{{ asset('images/badge-unggul.png') }}" alt="Akreditasi Unggul" class="w-24 h-24 lg:w-28 lg:h-28 object-cover rounded-2xl shadow-[0_20px_50px_-12px_rgba(11,65,203,0.4)] border-2 border-white/80">
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

    <!-- SECTION 2: STRIP STATISTIK -->
    <section class="py-12 bg-primary-950 relative z-20 shadow-2xl shadow-primary-900/50 border-t-4 border-secondary-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-x-12 gap-y-8 text-center md:text-left divide-x-0 md:divide-x divide-primary-800/50">
                <div class="md:px-8">
                    <span class="block text-4xl font-black text-white mb-1">9+</span>
                    <span class="text-sm font-bold text-primary-300 uppercase tracking-widest">Program Studi</span>
                </div>
                <div class="md:px-8">
                    <span class="block text-4xl font-black text-white mb-1">2</span>
                    <span class="text-sm font-bold text-primary-300 uppercase tracking-widest">Lokasi Kampus</span>
                </div>
                <div class="md:px-8">
                    <span class="block text-4xl font-black text-white mb-1">{{ isset($partners) ? $partners->count() : '20+' }}</span>
                    <span class="text-sm font-bold text-primary-300 uppercase tracking-widest">Mitra Institusi</span>
                </div>
                <div class="md:px-8 flex items-center gap-4 justify-center md:justify-start">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-secondary-400 to-secondary-500 text-neutral-900 flex items-center justify-center font-black text-3xl shadow-[0_10px_20px_-5px_rgba(241,177,14,0.4)]">B</div>
                    <span class="text-sm font-bold text-white leading-tight">Akreditasi<br><span class="text-secondary-400">Terjamin</span></span>
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
                    <a href="{{ route('prodi.show', $program->slug) }}" 
                       x-show="filter === 'all' || filter === '{{ $program->faculty_id }}'"
                       x-transition:enter="transition ease-out duration-300"
                       x-transition:enter-start="opacity-0 transform translate-y-8"
                       x-transition:enter-end="opacity-100 transform translate-y-0"
                       class="group bg-white/80 backdrop-blur-sm rounded-[2rem] border border-white shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden hover:shadow-[0_20px_40px_rgba(11,65,203,0.1)] hover:-translate-y-2 hover:border-primary-100 transition-all duration-500 flex flex-col">
                        
                        <div class="h-56 bg-neutral-100 relative overflow-hidden">
                            @if($program->galleries && $program->galleries->first())
                                <img src="{{ Storage::url($program->galleries->first()->image_path) }}" alt="{{ $program->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center">
                                    <span class="text-6xl font-black text-white/20">{{ substr($program->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <div class="bg-white/95 backdrop-blur-md text-neutral-900 text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm flex items-center gap-1.5">
                                    <i class="ti ti-star-filled text-secondary-500"></i> Akreditasi {{ $program->accreditation ?? '-' }}
                                </div>
                                <div class="bg-primary-600/95 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm flex items-center gap-1.5 w-max">
                                    <i class="ti ti-users"></i> Kuota {{ $program->quota }}
                                </div>
                            </div>
                        </div>

                        <div class="p-8 flex flex-col flex-grow">
                            <div class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-2">{{ $program->faculty ? $program->faculty->name : 'Fakultas' }}</div>
                            <h3 class="text-2xl font-bold text-neutral-900 mb-6 group-hover:text-primary-600 transition-colors leading-tight">{{ $program->name }}</h3>
                            
                            <div class="mt-auto pt-6 border-t border-neutral-100 flex items-end justify-between">
                                <div>
                                    <div class="text-xs text-neutral-400 font-bold uppercase tracking-wider mb-1">Biaya Pendaftaran</div>
                                    <div class="font-black text-lg text-neutral-900">Rp {{ number_format($program->registration_fee, 0, ',', '.') }}</div>
                                </div>
                                <div class="flex items-center gap-2 text-primary-600 font-bold text-sm group-hover:translate-x-1 transition-transform">
                                    Lihat Detail <i class="ti ti-arrow-right text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
        </div>
    </section>

    <!-- SECTION 4: CARA DAFTAR -->
    <section class="py-24 bg-white relative overflow-hidden">
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
    <section id="jalur" class="py-24 bg-neutral-50 border-t border-neutral-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight mb-4">Jalur Masuk</h2>
                <p class="text-lg text-neutral-500 font-medium">Pilih jalur pendaftaran yang paling sesuai dengan kualifikasimu.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto items-stretch">
                
                <!-- Kartu 1: Umum -->
                <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 border border-neutral-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(11,65,203,0.08)] hover:-translate-y-2 transition-all duration-300 flex flex-col">
                    <div class="w-14 h-14 bg-neutral-100 text-neutral-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="ti ti-clipboard-list text-3xl"></i>
                    </div>
                    <div class="inline-block px-3 py-1 mb-4 rounded-full bg-neutral-100 text-neutral-600 text-xs font-bold w-max">Terbuka untuk semua</div>
                    <h3 class="text-2xl font-black text-neutral-900 mb-4">Jalur Umum & Tes</h3>
                    <p class="text-neutral-500 font-medium leading-relaxed mb-8 flex-grow">Jalur masuk reguler dengan sistem seleksi melalui tes tertulis (CBT).</p>
                    <a href="{{ route('registration.create') }}" class="block w-full text-center py-4 rounded-xl bg-neutral-100 text-neutral-900 font-bold hover:bg-neutral-200 transition-colors">
                        Pilih Jalur Ini
                    </a>
                </div>

                <!-- Kartu 2: Prestasi (Highlighted) -->
                <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 border-2 border-secondary-400 shadow-[0_20px_50px_-12px_rgba(241,177,14,0.3)] relative transform md:-translate-y-4 z-10 flex flex-col">
                    <div class="absolute -top-4 inset-x-0 flex justify-center">
                        <span class="bg-gradient-to-r from-secondary-400 to-secondary-500 text-neutral-900 text-xs font-black uppercase tracking-widest px-6 py-2 rounded-full shadow-md">
                            Paling Diminati
                        </span>
                    </div>
                    <div class="w-14 h-14 bg-secondary-100 text-secondary-600 rounded-2xl flex items-center justify-center mb-6 mt-2">
                        <i class="ti ti-award text-3xl"></i>
                    </div>
                    <div class="inline-block px-3 py-1 mb-4 rounded-full bg-secondary-50 text-secondary-600 text-xs font-bold w-max">Tanpa Tes Tulis</div>
                    <h3 class="text-2xl font-black text-neutral-900 mb-4">Jalur Prestasi</h3>
                    <p class="text-neutral-500 font-medium leading-relaxed mb-8 flex-grow">Bebas tes tertulis khusus untuk siswa peraih prestasi akademik (rapor) maupun non-akademik.</p>
                    <a href="{{ route('registration.create') }}" class="block w-full text-center py-4 rounded-xl bg-primary-600 text-white font-bold hover:bg-primary-700 shadow-lg shadow-primary-600/30 transition-all">
                        Daftar Sekarang
                    </a>
                </div>

                <!-- Kartu 3: Tahfidz -->
                <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 border border-neutral-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(11,65,203,0.08)] hover:-translate-y-2 transition-all duration-300 flex flex-col">
                    <div class="w-14 h-14 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="ti ti-book text-3xl"></i>
                    </div>
                    <div class="inline-block px-3 py-1 mb-4 rounded-full bg-primary-50 text-primary-700 text-xs font-bold w-max">Beasiswa Tersedia</div>
                    <h3 class="text-2xl font-black text-neutral-900 mb-4">Jalur Tahfidz</h3>
                    <p class="text-neutral-500 font-medium leading-relaxed mb-8 flex-grow">Apresiasi khusus berupa beasiswa bagi calon mahasiswa penghafal Al-Quran minimal 1 Juz.</p>
                    <a href="{{ route('registration.create') }}" class="block w-full text-center py-4 rounded-xl bg-neutral-100 text-neutral-900 font-bold hover:bg-neutral-200 transition-colors">
                        Pilih Jalur Ini
                    </a>
                </div>

            </div>

            <div class="mt-16 text-center">
                <div class="inline-flex items-center gap-3 px-6 py-3 bg-white border border-neutral-200 rounded-2xl shadow-sm text-sm font-bold text-neutral-700">
                    <i class="ti ti-calendar-event text-primary-600 text-xl"></i>
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
                        <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}" class="h-12 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                    </div>
                @endforeach
                <!-- Duplicate for seamless loop -->
                @foreach($partners as $partner)
                    <div class="mx-10 flex-shrink-0">
                        <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}" class="h-12 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                    </div>
                @endforeach
                 @foreach($partners as $partner)
                    <div class="mx-10 flex-shrink-0">
                        <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}" class="h-12 w-auto object-contain grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
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

    <!-- SECTION 7: FASILITAS -->
    <section class="py-24 bg-neutral-50 border-t border-neutral-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-block px-3 py-1 mb-4 rounded-full bg-primary-100 text-primary-700 text-xs font-bold tracking-widest uppercase">Fasilitas</div>
                <h2 class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight mb-4">Lingkungan Belajar Terbaik</h2>
                <p class="text-lg text-neutral-500 font-medium">Infrastruktur kampus modern yang dirancang untuk mendukung praktik dan teori standar industri riil.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @if(isset($facilities))
                    @foreach($facilities->where('is_active', true) as $facility)
                    <div class="bg-white rounded-[2rem] p-8 border border-neutral-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] text-center group hover:bg-primary-600 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                        <div class="w-16 h-16 mx-auto bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-white/20 group-hover:text-white transition-colors duration-300">
                            <i class="ti {{ $facility->icon ?? 'ti-building' }} text-3xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-neutral-900 mb-2 group-hover:text-white transition-colors">{{ $facility->name }}</h4>
                        @if($facility->description)
                        <p class="text-sm text-neutral-500 group-hover:text-primary-100 transition-colors line-clamp-3">{{ $facility->description }}</p>
                        @endif
                    </div>
                    @endforeach
                @endif
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

            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-20">
                <a href="{{ route('registration.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-5 text-lg font-black rounded-2xl bg-secondary-500 text-neutral-900 hover:bg-secondary-400 transition-all shadow-[0_10px_30px_rgba(241,177,14,0.4)] transform hover:-translate-y-1">
                    Daftar Sekarang
                </a>
                @if(isset($period) && $period && $period->admin_whatsapp)
                <a href="https://wa.me/{{ $period->admin_whatsapp }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-5 text-lg font-bold rounded-2xl bg-white/10 text-white border border-white/20 hover:bg-white/20 transition-all backdrop-blur-md">
                    <i class="ti ti-brand-whatsapp text-2xl mr-2 text-green-400"></i> Hubungi via WhatsApp
                </a>
                @endif
            </div>

            <!-- Kontak Kampus -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto border-t border-white/10 pt-12 text-left">
                <div class="flex gap-4 items-start">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0"><i class="ti ti-map-pin text-2xl"></i></div>
                    <div>
                        <h5 class="text-white font-bold mb-1">Kampus Majalengka</h5>
                        <p class="text-primary-200 text-sm mb-2">Jl. Gerakan Koperasi No. 003, Kab. Majalengka</p>
                        <p class="text-white font-bold">0811 222 1913</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0"><i class="ti ti-map-pin text-2xl"></i></div>
                    <div>
                        <h5 class="text-white font-bold mb-1">Kampus Cirebon</h5>
                        <p class="text-primary-200 text-sm mb-2">Jl. Perjuangan No. 10a, Kesambi, Kota Cirebon</p>
                        <p class="text-white font-bold">0813 1323 0321</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-neutral-950 text-white pt-20 pb-10 border-t border-neutral-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Col 1 -->
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo-ypib-white.png') }}" alt="Logo YPIB" class="h-12 w-auto">
                    </div>
                    <p class="text-sm text-neutral-400 font-medium leading-relaxed mb-8">
                        Mencetak tenaga profesional unggul di bidang kesehatan dan teknologi yang siap bersaing di dunia kerja global.
                    </p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-neutral-400 hover:bg-primary-600 hover:text-white hover:border-primary-500 transition-colors"><i class="ti ti-brand-instagram text-xl"></i></a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-neutral-400 hover:bg-primary-600 hover:text-white hover:border-primary-500 transition-colors"><i class="ti ti-brand-facebook text-xl"></i></a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-neutral-400 hover:bg-primary-600 hover:text-white hover:border-primary-500 transition-colors"><i class="ti ti-brand-youtube text-xl"></i></a>
                    </div>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="font-bold text-lg mb-6 text-white">Tautan Cepat</h4>
                    <ul class="space-y-4 text-sm font-medium text-neutral-400">
                        <li><a href="#" class="hover:text-primary-400 transition-colors">Beranda</a></li>
                        <li><a href="#program-studi" class="hover:text-primary-400 transition-colors">Program Studi</a></li>
                        <li><a href="#jalur" class="hover:text-primary-400 transition-colors">Jalur Pendaftaran</a></li>
                        <li><a href="{{ route('registration.create') }}" class="hover:text-primary-400 transition-colors">Formulir Pendaftaran</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-primary-400 transition-colors">Login Pendaftar</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h4 class="font-bold text-lg mb-6 text-white">Kampus Majalengka</h4>
                    <ul class="space-y-4 text-sm font-medium text-neutral-400">
                        <li class="flex items-start gap-3">
                            <i class="ti ti-map-pin text-lg text-neutral-500 shrink-0"></i>
                            <span class="leading-relaxed">Jl. Gerakan Koperasi No. 003, Kab. Majalengka, Jawa Barat</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ti ti-phone text-lg text-neutral-500 shrink-0"></i>
                            <span>0811 222 1913</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ti ti-mail text-lg text-neutral-500 shrink-0"></i>
                            <span>info@ypib.ac.id</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4 -->
                <div>
                    <h4 class="font-bold text-lg mb-6 text-white">Kampus Cirebon</h4>
                    <ul class="space-y-4 text-sm font-medium text-neutral-400">
                        <li class="flex items-start gap-3">
                            <i class="ti ti-map-pin text-lg text-neutral-500 shrink-0"></i>
                            <span class="leading-relaxed">Jl. Perjuangan No. 10a, Kesambi, Kota Cirebon, Jawa Barat</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ti ti-phone text-lg text-neutral-500 shrink-0"></i>
                            <span>0813 1323 0321</span>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="border-t border-neutral-900 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm font-medium text-neutral-500">
                <p>&copy; {{ date('Y') }} PMB YPIB Majalengka. Seluruh hak dilindungi.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
