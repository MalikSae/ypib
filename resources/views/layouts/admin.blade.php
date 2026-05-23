<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin PMB YPIB')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #F1F4F7; font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ─────────────────────────────────────────
           SIDEBAR
        ───────────────────────────────────────── */
        #admin-sidebar {
            width: 260px;
            height: 100vh;
            background: #FFFFFF;
            border-right: 1px solid #DEE3E9;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            z-index: 50;
            transition: transform 0.28s cubic-bezier(0.4,0,0.2,1), width 0.28s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        /* Label teks sidebar (sembunyikan saat tablet collapsed) */
        .sidebar-label { transition: opacity 0.2s, width 0.2s; white-space: nowrap; overflow: hidden; }
        .sidebar-meta  { transition: opacity 0.2s; }

        /* ─────────────────────────────────────────
           OVERLAY (mobile)
        ───────────────────────────────────────── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,19,23,0.45);
            z-index: 45;
            opacity: 0;
            transition: opacity 0.28s;
        }
        #sidebar-overlay.visible {
            display: block;
            opacity: 1;
        }

        /* ─────────────────────────────────────────
           MAIN WRAPPER
        ───────────────────────────────────────── */
        #admin-main {
            margin-left: 260px;
            background: #F1F4F7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.28s cubic-bezier(0.4,0,0.2,1);
        }

        /* ─────────────────────────────────────────
           TOPBAR
        ───────────────────────────────────────── */
        #admin-topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #DEE3E9;
            height: 64px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        /* Hamburger — hidden di desktop */
        #btn-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #444950;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        #btn-hamburger:hover { background: #F1F4F7; }

        /* Topbar title + hamburger wrapper */
        .topbar-left { display: flex; align-items: center; gap: 12px; }

        /* ─────────────────────────────────────────
           NAV ITEMS
        ───────────────────────────────────────── */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #444950;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-item:hover { background: #F1F4F7; }
        .nav-item .nav-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: #5D6C7B;
            transition: color 0.15s;
        }
        .nav-item.active {
            background: #F2F5FD;
            color: #0B41CB;
            font-weight: 700;
        }
        .nav-item.active .nav-icon { color: #0B41CB; }

        /* ─────────────────────────────────────────
           CONTENT AREA
        ───────────────────────────────────────── */
        #admin-content { padding: 32px; flex: 1; }

        /* ─────────────────────────────────────────
           FLASH
        ───────────────────────────────────────── */
        .flash-success {
            background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32;
            border-radius: 12px; padding: 14px 16px; font-size: 14px;
            margin-bottom: 24px; display: flex; align-items: center; gap: 10px;
        }
        .flash-error {
            background: #FFEBEE; border: 1px solid #EF9A9A; color: #C62828;
            border-radius: 12px; padding: 14px 16px; font-size: 14px;
            margin-bottom: 24px; display: flex; align-items: center; gap: 10px;
        }

        /* ─────────────────────────────────────────
           SUMMERNOTE TAILWIND FIX
        ───────────────────────────────────────── */
        .note-editor .note-toolbar { z-index: 40 !important; }
        .note-editor .note-dropdown-menu { z-index: 9999 !important; box-sizing: content-box; min-width: 160px; }
        .note-editor .note-modal { z-index: 9999 !important; }
        .note-editor .note-modal-backdrop { z-index: 9998 !important; }
        .note-editor .note-btn { 
            color: #333 !important; 
            background-color: transparent; 
        }
        .note-editor .note-btn:hover { background-color: #e2e8f0 !important; }
        .note-editor .note-dropdown-menu a { color: #333 !important; text-decoration: none !important; }
        .note-editor .note-dropdown-menu a:hover { background-color: #f1f5f9 !important; }
        .note-editor .note-dropdown-menu ul { margin: 0; padding: 0; list-style: none; }
        .note-editor .note-color .dropdown-toggle { width: auto !important; padding-right: 10px; }
        .note-editor .note-dropdown-menu .note-btn { padding: 5px 10px; border-radius: 0; display: block; width: 100%; text-align: left; }

        /* ─────────────────────────────────────────
           SUMMERNOTE WYSIWYG FIX (Unreset Tailwind)
        ───────────────────────────────────────── */
        .note-editable h1 { font-size: 2em; font-weight: bold; margin-top: 0.67em; margin-bottom: 0.67em; }
        .note-editable h2 { font-size: 1.5em; font-weight: bold; margin-top: 0.83em; margin-bottom: 0.83em; }
        .note-editable h3 { font-size: 1.17em; font-weight: bold; margin-top: 1em; margin-bottom: 1em; }
        .note-editable h4 { font-size: 1em; font-weight: bold; margin-top: 1.33em; margin-bottom: 1.33em; }
        .note-editable h5 { font-size: 0.83em; font-weight: bold; margin-top: 1.67em; margin-bottom: 1.67em; }
        .note-editable h6 { font-size: 0.67em; font-weight: bold; margin-top: 2.33em; margin-bottom: 2.33em; }
        .note-editable ul { list-style-type: disc; padding-left: 40px; margin-top: 1em; margin-bottom: 1em; }
        .note-editable ol { list-style-type: decimal; padding-left: 40px; margin-top: 1em; margin-bottom: 1em; }
        .note-editable li { display: list-item; margin-bottom: 0.5em; }
        .note-editable p { margin-top: 1em; margin-bottom: 1em; }
        .note-editable strong, .note-editable b { font-weight: bolder; }
        .note-editable em, .note-editable i { font-style: italic; }
        .note-editable u { text-decoration: underline; }

        /* ─────────────────────────────────────────
           PAGINATION (shared)
        ───────────────────────────────────────── */
        nav[aria-label] span, nav[aria-label] a {
            display: inline-flex !important; align-items: center !important;
            justify-content: center !important; min-width: 36px !important;
            height: 36px !important; border-radius: 9999px !important;
            font-size: 14px !important; font-weight: 500 !important;
            padding: 0 8px !important; margin: 0 2px !important;
            text-decoration: none !important; color: #444950 !important;
            border: 1px solid #DEE3E9 !important; background: #FFFFFF !important;
        }
        nav[aria-label] span[aria-current] {
            background: #0B41CB !important; color: #FFFFFF !important;
            border-color: #0B41CB !important; font-weight: 700 !important;
        }
        nav[aria-label] a:hover { background: #F1F4F7 !important; }
        nav[aria-label] span.cursor-default { color: #CED0D4 !important; }

        /* ═══════════════════════════════════════
           DESKTOP COLLAPSED STATE (>= 1024px)
        ═══════════════════════════════════════ */
        #btn-collapse {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #444950;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        #btn-collapse:hover { background: #F1F4F7; }

        @media (min-width: 1024px) {
            #btn-collapse { display: flex; }
            #admin-sidebar.collapsed {
                width: 68px;
                padding: 24px 12px;
                overflow: visible;
            }
            #admin-main.collapsed { margin-left: 68px; }
            #admin-sidebar.collapsed .sidebar-label { opacity: 0; width: 0; pointer-events: none; }
            #admin-sidebar.collapsed .sidebar-meta  { opacity: 0; pointer-events: none; }
            #admin-sidebar.collapsed .nav-section-label { display: none; }
            
            #admin-sidebar.collapsed .sidebar-brand-full { display: none !important; }
            #admin-sidebar.collapsed .sidebar-brand-icon { display: block !important; }
            
            #admin-sidebar.collapsed .nav-item {
                justify-content: center;
                padding: 10px;
                gap: 0;
                position: relative;
            }
            
            #admin-sidebar.collapsed .nav-item::after {
                content: attr(data-label);
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background: #0A1317;
                color: #FFFFFF;
                font-size: 12px;
                font-weight: 600;
                padding: 6px 12px;
                border-radius: 8px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s;
                z-index: 100;
            }
            #admin-sidebar.collapsed .nav-item:hover::after { opacity: 1; }
        }

        /* ═══════════════════════════════════════
           TABLET  768px – 1023px
           Sidebar collapsed to icon-only (68px)
        ═══════════════════════════════════════ */
        @media (max-width: 1023px) and (min-width: 768px) {
            #admin-sidebar {
                width: 68px;
                padding: 24px 12px;
                overflow: visible;
            }
            #admin-main { margin-left: 68px; }
            .sidebar-label { opacity: 0; width: 0; pointer-events: none; }
            .sidebar-meta  { opacity: 0; pointer-events: none; }
            .nav-section-label { display: none; }

            /* Logo: hanya tampilkan kotak PMB */
            .sidebar-brand-full { display: none !important; }
            .sidebar-brand-icon { display: block !important; }

            /* User info bottom: avatar saja */
            .sidebar-user-name, .sidebar-user-role { display: none; }

            /* Nav items: center icon */
            .nav-item {
                justify-content: center;
                padding: 10px;
                gap: 0;
                position: relative;
            }

            /* Tooltip on hover untuk tablet */
            .nav-item::after {
                content: attr(data-label);
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background: #0A1317;
                color: #FFFFFF;
                font-size: 12px;
                font-weight: 600;
                padding: 6px 12px;
                border-radius: 8px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s;
                z-index: 100;
            }
            .nav-item:hover::after { opacity: 1; }
        }

        /* ═══════════════════════════════════════
           MOBILE  < 768px
           Sidebar sebagai off-canvas drawer
        ═══════════════════════════════════════ */
        @media (max-width: 767px) {
            #admin-sidebar {
                width: 260px;
                transform: translateX(-100%);
                box-shadow: 4px 0 24px rgba(10,19,23,0.12);
            }
            #admin-sidebar.open { transform: translateX(0); }

            #admin-main { margin-left: 0; }

            #btn-hamburger { display: flex; }

            #admin-topbar { padding: 0 16px; }
            #admin-content { padding: 16px; }

            /* Topbar title lebih kecil di mobile */
            .topbar-page-title { font-size: 16px !important; }

            /* Sembunyikan nama user di topbar mobile (hemat space) */
            .topbar-username { display: none; }
        }

        /* ═══════════════════════════════════════
           RESPONSIVE GRID HELPERS
        ═══════════════════════════════════════ */

        /* Stat cards: 4 col → 2 col → 1 col */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        @media (max-width: 1023px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        }

        /* 2-col detail layout → 1 col tablet+mobile */
        .detail-grid {
            display: grid;
            grid-template-columns: 65% 35%;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 1023px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .detail-sidebar-col {
                position: static !important;
            }
        }

        /* Card 2-col field grid → 1 col on mobile */
        .field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 480px) {
            .field-grid { grid-template-columns: 1fr; gap: 12px; }
            .field-full  { grid-column: 1 !important; }
        }
    </style>
</head>
<body>

{{-- ══════ OVERLAY (mobile) ══════ --}}
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- ══════════════ SIDEBAR ══════════════ --}}
<aside id="admin-sidebar">

    {{-- Logo + Brand --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;min-width:0;justify-content:center;">
        <img src="{{ asset('images/logo-ypib.png') }}" alt="PMB YPIB" class="sidebar-brand-full" style="height:36px;width:auto;object-fit:contain;flex-shrink:0;">
        <img src="{{ asset('images/favicon.png') }}" alt="PMB YPIB" class="sidebar-brand-icon" style="height:36px;width:auto;object-fit:contain;flex-shrink:0;display:none;">
    </div>

    {{-- Divider --}}
    <div style="height:1px;margin:0 0 16px 0;" class="bg-neutral-200"></div>

    {{-- Navigation --}}
    <nav style="flex:1;">
        <div class="nav-section-label text-neutral-400" style="font-size:11px;font-weight:700;letter-spacing:0.08em;margin-bottom:8px;padding:0 12px;">MENU UTAMA</div>

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           data-label="Dashboard"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            <span class="sidebar-label">Dashboard</span>
        </a>

        {{-- Pengaturan PMB --}}
        <a href="{{ route('admin.periods.index') }}"
           data-label="Pengaturan PMB"
           class="nav-item {{ request()->routeIs('admin.periods.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688 0-1.37-.043-2.05-.13m0 0A13.984 13.984 0 0 1 3 6.969V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v1.719c0 2.61-1.04 5.096-2.89 6.924m-6.17 1.847L12 18l-.34-2.16m-2.81 2.81L7.5 21m6-2.35L15 21M9.75 9h4.5" />
            </svg>
            <span class="sidebar-label">Pengaturan PMB</span>
        </a>

        {{-- Kelola Fakultas --}}
        <a href="{{ route('admin.faculties.index') }}"
           data-label="Fakultas"
           class="nav-item {{ request()->routeIs('admin.faculties.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            <span class="sidebar-label">Kelola Fakultas</span>
        </a>

        {{-- Kelola Prodi --}}
        <a href="{{ route('admin.programs.index') }}"
           data-label="Kelola Program Studi"
           class="nav-item {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
            <span class="sidebar-label">Kelola Program Studi</span>
        </a>

        {{-- Kelola Fasilitas --}}
        <a href="{{ route('admin.facilities.index') }}"
           data-label="Fasilitas"
           class="nav-item {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M15 6.75h.75m-.75 3h.75m-.75 3h.75" />
            </svg>
            <span class="sidebar-label">Kelola Fasilitas</span>
        </a>

        {{-- Kelola Partner --}}
        <a href="{{ route('admin.partners.index') }}"
           data-label="Partner"
           class="nav-item {{ request()->routeIs('admin.partners.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            <span class="sidebar-label">Kelola Partner</span>
        </a>

        {{-- Data Pendaftar --}}
        <a href="{{ route('admin.registrations.index') }}"
           data-label="Data Pendaftar"
           class="nav-item {{ request()->routeIs('admin.registrations.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span class="sidebar-label">Data Pendaftar</span>
        </a>

        {{-- Data Afiliasi --}}
        <a href="{{ route('admin.referrers.index') }}"
           data-label="Data Afiliasi"
           class="nav-item {{ request()->routeIs('admin.referrers.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
            </svg>
            <span class="sidebar-label">Data Afiliasi</span>
        </a>

        {{-- Kelola Komisi --}}
        <a href="{{ route('admin.rewards.index') }}"
           data-label="Kelola Komisi"
           class="nav-item {{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
            </svg>
            <span class="sidebar-label">Kelola Komisi</span>
        </a>
    </nav>

    {{-- Bottom: Logout --}}
    <div>
        <div style="height:1px;margin:16px 0;" class="bg-neutral-200"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item text-error" style="width:100%;border:none;cursor:pointer;background:transparent;" data-label="Logout">
                <svg class="nav-icon text-error" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                </svg>
                <span class="sidebar-label text-error">Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- ══════════════ MAIN ══════════════ --}}
<div id="admin-main">

    {{-- Topbar --}}
    <header id="admin-topbar">
        <div class="topbar-left">
            {{-- Hamburger button (mobile only) --}}
            <button id="btn-hamburger" onclick="openSidebar()" aria-label="Buka menu">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            {{-- Collapse button (desktop only) --}}
            <button id="btn-collapse" onclick="toggleDesktopSidebar()" aria-label="Minimize sidebar">
                <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <h1 class="topbar-page-title text-neutral-900" style="font-size:20px;font-weight:700;margin:0;">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="topbar-username text-neutral-600" style="font-size:14px;font-weight:500;">{{ auth()->user()->name }}</span>
            <span style="background:#F2F5FD;font-size:12px;font-weight:700;border-radius:9999px;padding:4px 12px;white-space:nowrap;" class="text-primary-600">{{ ucfirst(auth()->user()->role) }}</span>
        </div>
    </header>

    {{-- Content --}}
    <div id="admin-content">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flash-success">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flash-error">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script>
    /* ── Desktop sidebar toggle ── */
    function toggleDesktopSidebar() {
        document.getElementById('admin-sidebar').classList.toggle('collapsed');
        document.getElementById('admin-main').classList.toggle('collapsed');
        
        // Save state to localStorage
        const isCollapsed = document.getElementById('admin-sidebar').classList.contains('collapsed');
        localStorage.setItem('adminSidebarCollapsed', isCollapsed ? 'true' : 'false');
    }

    // Load state on mount
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth >= 1024) {
            const isCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.getElementById('admin-sidebar').classList.add('collapsed');
                document.getElementById('admin-main').classList.add('collapsed');
            }
        }
    });

    /* ── Mobile sidebar toggle ── */
    function openSidebar() {
        document.getElementById('admin-sidebar').classList.add('open');
        var ov = document.getElementById('sidebar-overlay');
        ov.style.display = 'block';
        requestAnimationFrame(function() { ov.classList.add('visible'); });
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('admin-sidebar').classList.remove('open');
        var ov = document.getElementById('sidebar-overlay');
        ov.classList.remove('visible');
        setTimeout(function() { ov.style.display = 'none'; }, 280);
        document.body.style.overflow = '';
    }

    /* ── Close sidebar on nav link click (mobile) ── */
    document.querySelectorAll('#admin-sidebar .nav-item').forEach(function(el) {
        el.addEventListener('click', function() {
            if (window.innerWidth < 768) closeSidebar();
        });
    });

    /* ── Swipe-to-close (mobile touch) ── */
    var touchStartX = 0;
    document.getElementById('admin-sidebar').addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });
    document.getElementById('admin-sidebar').addEventListener('touchend', function(e) {
        if (e.changedTouches[0].clientX - touchStartX < -60) closeSidebar();
    }, { passive: true });
</script>
</body>
</html>
