<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PMB YPIB Majalengka 2025/2026')</title>
    <meta name="description" content="@yield('meta-description', 'Penerimaan Mahasiswa Baru YPIB Majalengka 2025/2026. Daftarkan dirimu sekarang dan raih masa depan bersama kami.')">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    @livewireStyles
    <style>
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* ── Flatpickr Custom Theme ── */
        .flatpickr-calendar {
            font-family: 'Inter', sans-serif !important;
            border: 1px solid #DEE3E9 !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
            padding: 4px 8px 8px !important;
        }
        .flatpickr-calendar.arrowTop:before,
        .flatpickr-calendar.arrowTop:after { display: none; }

        /* Header bulan & tahun */
        .flatpickr-month {
            background: #fff !important;
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            margin-bottom: 4px;
        }
        .flatpickr-current-month {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0A1317 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        .flatpickr-monthDropdown-months {
            font-family: 'Inter', sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0A1317 !important;
            background: transparent !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 4px 24px 4px 8px !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235D6C7B' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 4px center !important;
        }
        .flatpickr-monthDropdown-months:hover {
            background-color: #F1F4F7 !important;
        }
        .numInputWrapper {
            border-radius: 8px !important;
        }
        .numInputWrapper:hover { background: #F1F4F7 !important; }
        .numInput.cur-year {
            font-family: 'Inter', sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0A1317 !important;
            padding: 4px 4px 4px 4px !important;
            width: 60px !important;
        }
        .numInputWrapper span {
            border-color: #DEE3E9 !important;
        }
        .numInputWrapper span:hover { background: #e6edfc !important; }
        .numInputWrapper span svg { fill: #5D6C7B !important; }

        /* Nav arrows */
        .flatpickr-prev-month, .flatpickr-next-month {
            fill: #5D6C7B !important;
            padding: 6px !important;
            border-radius: 8px !important;
        }
        .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg {
            fill: #082e8f !important;
        }
        .flatpickr-prev-month:hover, .flatpickr-next-month:hover {
            background: #e6edfc !important;
        }

        /* Weekdays header */
        .flatpickr-weekdays { background: #fff !important; }
        .flatpickr-weekday {
            background: #fff !important;
            color: #8595A4 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }

        /* Days */
        .flatpickr-days { border: none !important; }
        .dayContainer { border: none !important; }
        .flatpickr-day {
            font-family: 'Inter', sans-serif !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            color: #0A1317 !important;
            border: none !important;
            height: 36px !important;
            line-height: 36px !important;
            max-width: 36px !important;
        }
        .flatpickr-day:hover {
            background: #e6edfc !important;
            color: #082e8f !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: #082e8f !important;
            color: #fff !important;
            border-radius: 8px !important;
        }
        .flatpickr-day.today {
            border: 2px solid #082e8f !important;
            color: #082e8f !important;
            font-weight: 700 !important;
        }
        .flatpickr-day.today.selected { color: #fff !important; }
        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.flatpickr-disabled:hover,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: #C0C9D0 !important;
            background: transparent !important;
        }
        
        /* Buttons */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #082e8f; color: #FFFFFF;
            font-size: 15px; font-weight: 700; border-radius: 9999px;
            padding: 14px 28px; text-decoration: none; border: none;
            cursor: pointer; transition: background 0.15s;
        }
        .btn-primary:hover { background: #052066; }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: #0A1317;
            font-size: 15px; font-weight: 700; border-radius: 9999px;
            padding: 12px 28px; text-decoration: none;
            border: 2px solid #0A1317; cursor: pointer;
            transition: background 0.15s;
        }
        .btn-secondary:hover { background: rgba(10,19,23,0.06); }
        .btn-white {
            display: inline-flex; align-items: center; gap: 8px;
            background: #FFFFFF; color: #082e8f;
            font-size: 15px; font-weight: 700; border-radius: 9999px;
            padding: 14px 28px; text-decoration: none; border: none;
            cursor: pointer; transition: background 0.15s;
        }
        .btn-white:hover { background: #F1F4F7; }
        .btn-white-outline {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: #FFFFFF;
            font-size: 15px; font-weight: 700; border-radius: 9999px;
            padding: 12px 28px; text-decoration: none;
            border: 2px solid rgba(255,255,255,0.5);
            cursor: pointer; transition: background 0.15s;
        }
        .btn-white-outline:hover { background: rgba(255,255,255,0.1); }

        /* Cards */
        .pub-card {
            background: #FFFFFF;
            border: 1px solid #DEE3E9;
            border-radius: 16px;
            padding: 24px;
        }

        /* Flash alerts */
        .pub-flash-success {
            background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32;
            border-radius: 12px; padding: 14px 16px; font-size: 14px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .pub-flash-error {
            background: #FFEBEE; border: 1px solid #EF9A9A; color: #C62828;
            border-radius: 12px; padding: 14px 16px; font-size: 14px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }

        /* Status badge (reused on public pages) */
        .status-badge {
            font-size: 12px; font-weight: 700;
            border-radius: 9999px; padding: 4px 12px;
            display: inline-block; white-space: nowrap;
        }

        /* Container for legacy pages */
        .pub-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 16px;
        }
        @media (min-width: 640px) {
            .pub-container { padding: 0 24px; }
        }
        @media (min-width: 1024px) {
            .pub-container { padding: 0 32px; }
        }
    </style>
</head>
<body class="font-sans text-neutral-900 bg-neutral-50 antialiased selection:bg-primary-200 selection:text-primary-900 overflow-x-hidden flex flex-col min-h-screen {{ !request()->routeIs('landing') ? 'pt-20' : '' }}">

    <!-- NAVBAR -->
    <nav class="fixed top-0 inset-x-0 z-50 transition-all duration-300" x-data="{ isHome: {{ request()->routeIs('landing') ? 'true' : 'false' }}, scrolled: {{ request()->routeIs('landing') ? 'false' : 'true' }}, mobileOpen: false }" @scroll.window="scrolled = !isHome || window.scrollY > 20">
        <div :class="scrolled ? 'bg-white/95 backdrop-blur-xl shadow-[0_4px_30px_rgba(0,0,0,0.06)] border-b border-neutral-100' : 'bg-transparent'" class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <a href="{{ route('landing') }}" class="flex items-center gap-3">
                        <img :src="scrolled ? '{{ asset('images/logo-ypib.png') }}' : '{{ asset('images/logo-ypib-white.png') }}'" alt="Logo YPIB" class="h-12 w-auto transition-all duration-300">
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('landing') }}" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-primary-600 bg-primary-50' : 'text-[#F1B10E] bg-white/10'">Beranda</a>
                        <a href="{{ route('landing') }}#program-studi" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Program Studi</a>
                        <a href="{{ route('landing') }}#cara-daftar" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Cara Daftar</a>
                        <a href="{{ route('landing') }}#jalur" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors"
                           :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Jalur Masuk</a>
                    </div>

                    <!-- Action / Mobile Toggle -->
                    <div class="flex items-center gap-4">
                        <div class="hidden md:block">
                            @auth
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-semibold rounded-full transition-all border" :class="scrolled ? 'border-neutral-200 text-neutral-600 hover:border-primary-600 hover:text-primary-600 hover:bg-primary-50' : 'border-white/30 text-white hover:bg-white/10 hover:border-white/50'">
                                        <i class="ti ti-user text-lg"></i> Hi, {{ strtok(Auth::user()->name, ' ') }}!
                                        <i class="ti ti-chevron-down text-sm transition-transform" :class="open ? 'rotate-180' : ''"></i>
                                    </button>
                                    <div x-show="open" x-transition x-cloak class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-neutral-100 overflow-hidden z-50 text-left">
                                        <div class="py-2">
                                            @if(in_array(Auth::user()->role, ['admin', 'operator']))
                                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-600 font-semibold transition-colors">Dashboard Admin</a>
                                            @elseif(Auth::user()->role === 'referrer')
                                                <a href="{{ Auth::user()->is_referrer ? route('referrer.dashboard') : route('referrer.index') }}" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-600 font-semibold transition-colors">Dashboard Affiliator</a>
                                            @else
                                                <a href="{{ route('registration.status') }}" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-600 font-semibold transition-colors">Status Pendaftaran</a>
                                                <a href="{{ Auth::user()->is_referrer ? route('referrer.dashboard') : route('referrer.index') }}" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-600 font-semibold transition-colors">Dashboard Affiliator</a>
                                            @endif
                                            <div class="h-px bg-neutral-100 my-2"></div>
                                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-neutral-700 hover:bg-primary-50 hover:text-primary-600 font-semibold transition-colors">Pengaturan Akun</a>
                                            <div class="h-px bg-neutral-100 my-2"></div>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors">Logout</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-semibold rounded-full transition-all border" :class="scrolled ? 'border-neutral-200 text-neutral-600 hover:border-primary-600 hover:text-primary-600 hover:bg-primary-50' : 'border-white/30 text-white hover:bg-white/10 hover:border-white/50'">
                                    <i class="ti ti-user text-lg"></i> Login
                                </a>
                            @endauth
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
             :class="scrolled ? 'bg-white/95 backdrop-blur-xl border-neutral-100' : 'bg-[#09183E]/95 backdrop-blur-xl border-white/10'">
            <div class="px-4 pt-4 space-y-1">
                <a href="{{ route('landing') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-primary-600 bg-primary-50' : 'text-[#F1B10E] bg-white/10'">Beranda</a>
                <a href="{{ route('landing') }}#program-studi" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Program Studi</a>
                <a href="{{ route('landing') }}#cara-daftar" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Cara Daftar</a>
                <a href="{{ route('landing') }}#jalur" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-base font-bold"
                   :class="scrolled ? 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/70 hover:text-white hover:bg-white/10'">Jalur Masuk</a>
                <div class="pt-4 px-2">
                    @auth
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center justify-center gap-2 w-full bg-[#F1B10E] text-neutral-900 px-6 py-3.5 rounded-xl font-bold transition-all">
                                <i class="ti ti-user text-lg"></i> Hi, {{ strtok(Auth::user()->name, ' ') }}!
                                <i class="ti ti-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition x-cloak class="mt-2 bg-neutral-50 rounded-xl overflow-hidden border border-neutral-100 text-left">
                                <div class="py-2">
                                    @if(in_array(Auth::user()->role, ['admin', 'operator']))
                                        <a href="{{ route('admin.dashboard') }}" class="block px-5 py-3 text-sm text-neutral-700 hover:text-primary-600 font-semibold border-b border-neutral-100 last:border-0">Dashboard Admin</a>
                                    @elseif(Auth::user()->role === 'referrer')
                                        <a href="{{ Auth::user()->is_referrer ? route('referrer.dashboard') : route('referrer.index') }}" class="block px-5 py-3 text-sm text-neutral-700 hover:text-primary-600 font-semibold border-b border-neutral-100 last:border-0">Dashboard Affiliator</a>
                                    @else
                                        <a href="{{ route('registration.status') }}" class="block px-5 py-3 text-sm text-neutral-700 hover:text-primary-600 font-semibold border-b border-neutral-100">Dashboard Mahasiswa</a>
                                        <a href="{{ Auth::user()->is_referrer ? route('referrer.dashboard') : route('referrer.index') }}" class="block px-5 py-3 text-sm text-neutral-700 hover:text-primary-600 font-semibold border-b border-neutral-100 last:border-0">Dashboard Affiliator</a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="block px-5 py-3 text-sm text-neutral-700 hover:text-primary-600 font-semibold border-b border-neutral-100">Pengaturan Akun</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-5 py-3 text-sm text-red-600 font-bold">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full bg-[#F1B10E] text-neutral-900 px-6 py-3.5 rounded-xl font-bold">
                            <i class="ti ti-user text-lg"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-neutral-950 pt-20 pb-8 mt-auto border-t border-neutral-900">
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
                        <li><a href="{{ route('landing') }}" class="hover:text-primary-400 transition-colors">Beranda</a></li>
                        <li><a href="{{ route('landing') }}#program-studi" class="hover:text-primary-400 transition-colors">Program Studi</a></li>
                        <li><a href="{{ route('landing') }}#jalur" class="hover:text-primary-400 transition-colors">Jalur Pendaftaran</a></li>
                        <li><a href="{{ url('/afiliasi') }}" class="hover:text-primary-400 transition-colors">Affiliator YPIB</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @livewireScripts
</body>
</html>
