<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PMB YPIB Majalengka 2025/2026')</title>
    <meta name="description" content="@yield('meta-description', 'Penerimaan Mahasiswa Baru YPIB Majalengka 2025/2026. Daftarkan dirimu sekarang dan raih masa depan bersama kami.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
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
        /* Hilangkan native select, ganti tampilan lebih bersih */
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
    </style>
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; padding: 0; background: #F1F4F7; color: #1C1E21; }

        /* ── Navbar ── */
        #pub-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid #DEE3E9;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .pub-nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pub-nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .pub-nav-logo-box {
            width: 36px;
            height: 36px;
            background: #082e8f;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pub-nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .pub-nav-links a {
            font-size: 14px;
            font-weight: 500;
            color: #444950;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 9999px;
            transition: background 0.15s, color 0.15s;
        }
        .pub-nav-links a:hover { background: #F1F4F7; color: #0A1317; }

        .pub-nav-cta {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-nav-login {
            font-size: 14px; font-weight: 500; color: #444950;
            text-decoration: none; padding: 8px 16px; border-radius: 9999px;
            transition: background 0.15s;
        }
        .btn-nav-login:hover { background: #F1F4F7; color: #0A1317; }
        .btn-nav-register {
            font-size: 14px; font-weight: 700; color: #FFFFFF;
            text-decoration: none; padding: 10px 24px; border-radius: 9999px;
            background: #000000; transition: background 0.15s;
            border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-nav-register:hover { background: #1C1E21; }

        /* ── User Dropdown ── */
        .nav-user-trigger {
            display: flex; align-items: center; gap: 8px;
            background: transparent; border: none; cursor: pointer;
            padding: 4px 8px 4px 4px; border-radius: 9999px;
            transition: background 0.15s;
        }
        .nav-user-trigger:hover { background: #F1F4F7; }
        .nav-user-avatar {
            width: 36px; height: 36px; border-radius: 9999px;
            background: #082e8f; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0;
        }
        .nav-user-name {
            font-size: 14px; font-weight: 500; color: #1C1E21;
            white-space: nowrap;
        }
        .nav-dropdown {
            position: absolute; top: calc(100% + 8px); right: 0;
            background: #FFFFFF; border: 1px solid #DEE3E9;
            border-radius: 12px; min-width: 240px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 8px; z-index: 100;
        }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            font-size: 14px; color: #444950; text-decoration: none;
            background: transparent; border: none; cursor: pointer;
            width: 100%; font-family: inherit; font-weight: 400;
            transition: background 0.12s;
            white-space: nowrap;
        }
        .nav-dropdown-item:hover { background: #F1F4F7; }
        .nav-dropdown-item:hover.danger { color: #E41E3F; }
        .nav-dropdown-divider {
            height: 1px; background: #DEE3E9; margin: 4px 0;
        }

        /* Hamburger */
        #pub-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border: none; background: transparent;
            border-radius: 9999px; cursor: pointer;
            color: #444950; transition: background 0.15s;
        }
        #pub-hamburger:hover { background: #F1F4F7; }

        /* Mobile menu drawer */
        #pub-mobile-menu {
            display: none;
            position: fixed;
            top: 64px; left: 0; right: 0;
            background: #FFFFFF;
            border-bottom: 1px solid #DEE3E9;
            padding: 16px;
            z-index: 49;
            flex-direction: column;
            gap: 4px;
        }
        #pub-mobile-menu.open { display: flex; }
        #pub-mobile-menu a, #pub-mobile-menu button {
            display: block; width: 100%;
            font-size: 15px; font-weight: 500;
            color: #444950; text-decoration: none;
            padding: 12px 16px; border-radius: 10px;
            text-align: left; transition: background 0.12s;
        }
        #pub-mobile-menu a:hover { background: #F1F4F7; }
        #pub-mobile-menu .mobile-cta {
            background: #082e8f; color: #FFFFFF;
            font-weight: 700; text-align: center;
            border: none; cursor: pointer; margin-top: 4px;
        }
        #pub-mobile-menu .mobile-cta:hover { background: #052066; }
        #pub-mobile-menu .mobile-danger { color: #E41E3F; background: transparent; border: none; cursor: pointer; }
        #pub-mobile-menu .mobile-danger:hover { background: #FFEBEE; }

        /* ── Footer ── */
        #pub-footer {
            background: #0A1317;
            color: #FFFFFF;
            padding: 56px 32px 32px;
            margin-top: 0;
        }
        .pub-footer-inner {
            max-width: 1200px;
            margin: 0 auto;
        }
        .pub-footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1.25fr 1.25fr;
            gap: 32px;
            margin-bottom: 40px;
        }
        .pub-footer-heading {
            font-size: 14px; font-weight: 700; color: #FFFFFF;
            margin: 0 0 14px 0;
        }
        .pub-footer-text {
            font-size: 14px; color: #8595A4; line-height: 1.6; margin: 0;
        }
        .pub-footer-links { list-style: none; margin: 0; padding: 0; }
        .pub-footer-links li { margin-bottom: 8px; }
        .pub-footer-links a {
            font-size: 14px; color: #8595A4; text-decoration: none;
            transition: color 0.15s;
        }
        .pub-footer-links a:hover { color: #FFFFFF; }
        .pub-footer-divider {
            height: 1px; background: #1C1E21; margin-bottom: 24px;
        }
        .pub-footer-bottom {
            font-size: 13px; color: #5D6C7B; text-align: center;
        }
        .pub-footer-contact-item {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 10px;
        }
        .pub-footer-contact-icon {
            width: 20px; height: 20px; color: #5D6C7B; flex-shrink: 0;
        }

        /* ── Responsive ── */
        @media (max-width: 767px) {
            .pub-nav-inner { padding: 0 16px; }
            .pub-nav-links { display: none; }
            #pub-hamburger { display: flex; }
            .pub-nav-cta .btn-nav-login,
            .pub-nav-cta .btn-nav-register,
            .desktop-user-menu { display: none !important; }

            #pub-footer { padding: 40px 16px 24px; margin-top: 0; }
            .pub-footer-grid { grid-template-columns: 1fr; gap: 32px; }
        }
        @media (min-width: 768px) and (max-width: 1023px) {
            .pub-nav-inner { padding: 0 24px; }
            .pub-footer-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
        }

        /* ── Shared content helpers ── */
        .pub-container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
        @media (max-width: 767px) { .pub-container { padding: 0 16px; } }
        @media (min-width: 768px) and (max-width: 1023px) { .pub-container { padding: 0 24px; } }

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
    </style>
</head>
<body>

{{-- ══ NAVBAR ══ --}}
<header id="pub-navbar">
    <div class="pub-nav-inner">
        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="pub-nav-logo">
            <img src="{{ asset('images/logo-ypib.png') }}" alt="PMB YPIB" style="height: 40px; width: auto;">
        </a>

        {{-- Desktop nav links --}}
        <nav class="pub-nav-links">
            <a href="{{ route('landing') }}">Beranda</a>
            <a href="{{ route('landing') }}#prodi">Program Studi</a>
            <a href="{{ route('landing') }}#cara-daftar">Cara Daftar</a>
            <a href="{{ route('landing') }}#biaya">Biaya</a>
        </nav>

        {{-- Desktop CTA --}}
        <div class="pub-nav-cta">
            @guest
                <a href="{{ route('login') }}" class="btn-nav-login">Masuk</a>
                <a href="{{ route('landing') }}#prodi" class="btn-nav-register">Daftar Sekarang</a>
            @else
                {{-- User Dropdown (Alpine.js) --}}
                <div class="desktop-user-menu" style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
                    <button class="nav-user-trigger" @click="open = !open" type="button" aria-haspopup="true" :aria-expanded="open">
                        <div class="nav-user-avatar">
                            <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <span class="nav-user-name">Hi, {{ auth()->user()->name }}</span>
                        <svg style="width:16px;height:16px;flex-shrink:0;transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''"
                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-neutral-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div class="nav-dropdown" x-show="open" x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         style="display:none;">

                        @if(in_array(auth()->user()->role, ['admin','operator']))
                            {{-- ADMIN / OPERATOR: hanya Panel Admin + Keluar --}}
                            <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item">
                                <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                Panel Admin
                            </a>
                        @else
                            {{-- MAHASISWA / REFERRER --}}
                            @if(auth()->user()->registrations()->exists())
                                <a href="{{ route('registration.status') }}" class="nav-dropdown-item">
                                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Status Pendaftaran
                                </a>
                            @endif

                            {{-- Dashboard Afiliasi (selalu muncul) --}}
                            @if(auth()->user()->is_referrer)
                                <a href="{{ route('referrer.dashboard') }}" class="nav-dropdown-item">
                                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                    </svg>
                                    Dashboard Afiliasi
                                </a>
                            @else
                                <a href="{{ route('referrer.index') }}" class="nav-dropdown-item">
                                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                    </svg>
                                    Dashboard Afiliasi
                                    <span style="background:#e6edfc;font-size:11px;font-weight:700;border-radius:9999px;padding:2px 8px;flex-shrink:0;margin-left:auto;" class="text-primary-600">Daftar</span>
                                </a>
                            @endif
                        @endif

                        <div class="nav-dropdown-divider"></div>

                        <a href="{{ route('profile.edit') }}" class="nav-dropdown-item">
                            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71-.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Pengaturan Akun
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-dropdown-item text-neutral-500"
                                    onmouseover="this.style.background='#F1F4F7';this.style.color='#E41E3F';"
                                    onmouseout="this.style.background='transparent';this.style.color='#5D6C7B';">
                                <svg style="width:16px;height:16px;color:inherit;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endguest

            {{-- Hamburger --}}
            <button id="pub-hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
                <svg id="icon-hamburger" style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg id="icon-close" style="width:22px;height:22px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="pub-mobile-menu">
        @auth
            <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;margin-bottom:8px;" class="bg-neutral-100">
                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;" class="bg-primary-600 text-white">
                    <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <div style="font-weight:700;font-size:15px;" class="text-neutral-900">Hi, {{ auth()->user()->name }}</div>
            </div>
            <div style="height:1px;margin:4px 0 8px 0;" class="bg-neutral-200"></div>
        @endauth
        <a href="{{ route('landing') }}">Beranda</a>
        <a href="{{ route('landing') }}#prodi" onclick="closeMobileMenu()">Program Studi</a>
        <a href="{{ route('landing') }}#cara-daftar" onclick="closeMobileMenu()">Cara Daftar</a>
        <a href="{{ route('landing') }}#biaya" onclick="closeMobileMenu()">Biaya</a>
        <div style="height:1px;margin:8px 0;" class="bg-neutral-200"></div>
        @guest
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('landing') }}#prodi" class="mobile-cta">Daftar Sekarang</a>
        @else
            @if(in_array(auth()->user()->role, ['admin','operator']))
                <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
            @else
                @if(auth()->user()->registrations()->exists())
                    <a href="{{ route('registration.status') }}">Status Pendaftaran</a>
                @endif
                @if(auth()->user()->is_referrer)
                    <a href="{{ route('referrer.dashboard') }}">Dashboard Afiliasi</a>
                @else
                    <a href="{{ route('referrer.index') }}">Daftar Afiliasi</a>
                @endif
            @endif
            <div style="height:1px;margin:8px 0;" class="bg-neutral-200"></div>
            <a href="{{ route('profile.edit') }}">Pengaturan Akun</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mobile-danger">Keluar</button>
            </form>
        @endguest
    </div>
</header>

{{-- ══ MAIN CONTENT ══ --}}
<main>
    @yield('content')
</main>

{{-- ══ FOOTER ══ --}}
<footer id="pub-footer">
    <div class="pub-footer-inner">
        <div class="pub-footer-grid">
            {{-- Brand --}}
            <div>
                <div style="margin-bottom:20px;">
                    <div style="display:inline-flex;border-radius:9999px;padding:8px 16px;align-items:center;" class="bg-white">
                        <img src="{{ asset('images/logo-ypib.png') }}" alt="Logo YPIB" style="height:36px;width:auto;object-fit:contain;">
                    </div>
                </div>
                <p class="pub-footer-text" style="text-align:justify; line-height:1.7;">
                    Universitas YPIB Majalengka berkomitmen mencetak lulusan profesional, islami, dan mandiri melalui 3 Fakultas dan 9 Program Studi unggulan di bidang Kesehatan, Farmasi, Psikologi, Bisnis, dan Teknologi.
                </p>
            </div>

            {{-- Links --}}
            <div>
                <h3 class="pub-footer-heading">Tautan</h3>
                <ul class="pub-footer-links">
                    <li><a href="{{ route('landing') }}">Beranda</a></li>
                    <li><a href="{{ route('landing') }}#prodi">Program Studi</a></li>
                    <li><a href="{{ route('landing') }}#cara-daftar">Cara Daftar</a></li>
                    <li><a href="{{ route('landing') }}#prodi">Daftar Sekarang</a></li>
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                </ul>
            </div>

            {{-- Kampus Majalengka --}}
            <div>
                <h3 class="pub-footer-heading">Kampus Majalengka</h3>
                <div class="pub-footer-contact-item" style="align-items:flex-start;">
                    <svg class="pub-footer-contact-icon" style="margin-top:2px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <span style="font-size:14px;line-height:1.5;" class="text-neutral-400">Jl. Gerakan Koperasi No. 003,<br>Majalengka 45411</span>
                </div>
                <div class="pub-footer-contact-item">
                    <svg class="pub-footer-contact-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <span style="font-size:14px;" class="text-neutral-400">pmb@ypib.ac.id</span>
                </div>
            </div>

            {{-- Kampus Cirebon --}}
            <div>
                <h3 class="pub-footer-heading">Kampus Cirebon</h3>
                <div class="pub-footer-contact-item" style="align-items:flex-start;">
                    <svg class="pub-footer-contact-icon" style="margin-top:2px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <span style="font-size:14px;line-height:1.5;" class="text-neutral-400">Jl. Perjuangan Majasem No.10a,<br>Karyamulya, Kesambi,<br>Kota Cirebon, 45131</span>
                </div>
            </div>
        </div>

        <div class="pub-footer-divider"></div>
        <p class="pub-footer-bottom">
            &copy; {{ date('Y') }} PMB YPIB Majalengka. Semua hak dilindungi.
        </p>
    </div>
</footer>

<script>
    var mobileMenuOpen = false;
    function toggleMobileMenu() {
        mobileMenuOpen = !mobileMenuOpen;
        var menu = document.getElementById('pub-mobile-menu');
        var iconH = document.getElementById('icon-hamburger');
        var iconC = document.getElementById('icon-close');
        menu.classList.toggle('open', mobileMenuOpen);
        iconH.style.display = mobileMenuOpen ? 'none' : 'block';
        iconC.style.display = mobileMenuOpen ? 'block' : 'none';
    }
    function closeMobileMenu() {
        mobileMenuOpen = false;
        document.getElementById('pub-mobile-menu').classList.remove('open');
        document.getElementById('icon-hamburger').style.display = 'block';
        document.getElementById('icon-close').style.display = 'none';
    }
    // Close on scroll anchor click
    document.querySelectorAll('#pub-mobile-menu a[href*="#"]').forEach(function(el) {
        el.addEventListener('click', closeMobileMenu);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@livewireScripts
</body>
</html>
