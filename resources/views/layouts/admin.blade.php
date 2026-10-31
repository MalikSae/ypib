<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    @include('partials.pwa-head', ['manifestPath' => 'manifest-admin.json'])
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin PMB YPIB')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.11.0/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #F1F4F7; font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

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

        /* Tooltip nama menu saat sidebar hanya ikon. Dipasang lewat JS (position: fixed)
           karena .sidebar-nav ber-overflow sehingga ::after akan terpotong. */
        #nav-tooltip {
            position: fixed;
            z-index: 1000;
            background: #0A1317;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-50%);
            transition: opacity 0.15s;
        }
        #nav-tooltip.visible { opacity: 1; }

        /* Pemilih periode di topbar (dropdown kustom) */
        .period-picker { position: relative; margin: 0; display: flex; align-items: center; gap: 6px; }
        .period-picker-button {
            display: inline-flex; align-items: center; gap: 8px;
            height: 34px; max-width: 190px; padding: 0 10px;
            background: #FFFFFF; border: 1px solid #DEE3E9; border-radius: 8px;
            font-size: 13px; font-weight: 600; color: #3B404F; cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }
        .period-picker-button:hover { background: #F7F9FB; border-color: #CED0D4; }
        .period-picker-button:focus-visible { outline: 2px solid #0B41CB; outline-offset: 2px; }
        .period-picker-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .period-picker-chevron { width: 14px; height: 14px; flex-shrink: 0; color: #5D6C7B; transition: transform 0.15s; }
        .period-picker-chevron.is-open { transform: rotate(180deg); }
        .period-picker-panel {
            position: absolute; right: 0; top: calc(100% + 8px); z-index: 60;
            width: 320px; max-width: calc(100vw - 32px); max-height: 360px; overflow-y: auto;
            background: #FFFFFF; border: 1px solid #DEE3E9; border-radius: 12px;
            box-shadow: 0 12px 32px rgba(10, 19, 23, 0.14); padding: 6px;
        }
        .period-picker-item {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            width: 100%; padding: 9px 10px; border: 0; border-radius: 8px;
            background: transparent; text-align: left; cursor: pointer; color: #3B404F;
        }
        .period-picker-item:hover { background: #F1F4F7; }
        .period-picker-item.is-selected { background: #F2F5FD; color: #0B41CB; }
        .period-picker-item-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .period-picker-item-name { font-size: 13px; font-weight: 600; line-height: 1.3; overflow-wrap: anywhere; }
        .period-picker-item-meta { font-size: 11px; font-weight: 500; color: #7A8794; }
        .period-picker-item.is-selected .period-picker-item-meta { color: #5C78C9; }
        .period-picker-check { width: 16px; height: 16px; flex-shrink: 0; }
        /* Custom Scrollbar for Sidebar Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            margin-right: -8px;
            padding-right: 8px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: #DEE3E9; border-radius: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: #CED0D4; }

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
            #admin-sidebar.collapsed .sidebar-nav { overflow: visible; }
            
            #admin-sidebar.collapsed .sidebar-brand-full { display: none !important; }
            #admin-sidebar.collapsed .sidebar-brand-icon { display: block !important; }
            
            #admin-sidebar.collapsed .nav-item {
                justify-content: center;
                padding: 10px;
                gap: 0;
                position: relative;
            }
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
    <nav class="sidebar-nav">
        @php
            // Struktur menu per alur kerja. 'global' = hanya admin tanpa batasan kampus.
            $icons = [
                'dashboard' => ['M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
                'people'    => ['M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                'chart'     => ['M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                'book'      => ['M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                'link'      => ['M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244'],
                'cash'      => ['M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                'pin'       => ['M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z', 'M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                'building'  => ['M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
                'image'     => ['M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
                'calendar'  => ['M10.34 15.84c-.688 0-1.37-.043-2.05-.13m0 0A13.984 13.984 0 0 1 3 6.969V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v1.719c0 2.61-1.04 5.096-2.89 6.924m-6.17 1.847L12 18l-.34-2.16m-2.81 2.81L7.5 21m6-2.35L15 21M9.75 9h4.5'],
                'mail'      => ['M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
            ];

            $menu = [
                ['label' => 'MENU UTAMA', 'items' => [
                    ['Dashboard', 'admin.dashboard', 'dashboard', request()->routeIs('admin.dashboard')],
                ]],
                ['label' => 'PENDAFTARAN', 'items' => [
                    ['Data Pendaftar', 'admin.registrations.index', 'people',
                        request()->routeIs('admin.registrations.*') && !request()->routeIs('admin.registrations.recap*')],
                    ['Rekap Pendaftaran', 'admin.registrations.recap', 'chart', request()->routeIs('admin.registrations.recap*')],
                ]],
                ['label' => 'SELEKSI', 'items' => [
                    ['Bank Soal', 'admin.bank-soal.index', 'book', request()->routeIs('admin.bank-soal.*'), 'global' => true],
                ]],
                ['label' => 'AFILIASI', 'items' => [
                    ['Data Afiliasi', 'admin.referrers.index', 'link', request()->routeIs('admin.referrers.*'), 'global' => true],
                    ['Komisi', 'admin.rewards.index', 'cash', request()->routeIs('admin.rewards.*'), 'global' => true],
                ]],
                ['label' => 'MASTER DATA', 'items' => [
                    ['Kampus', 'admin.campuses.index', 'pin', request()->routeIs('admin.campuses.*'), 'global' => true],
                    ['Fakultas', 'admin.faculties.index', 'building', request()->routeIs('admin.faculties.*'), 'global' => true],
                    ['Program Studi', 'admin.programs.index', 'book', request()->routeIs('admin.programs.*'), 'global' => true],
                    ['Galeri', 'admin.facilities.index', 'image', request()->routeIs('admin.facilities.*'), 'global' => true],
                    ['Partner', 'admin.partners.index', 'building', request()->routeIs('admin.partners.*'), 'global' => true],
                ]],
                ['label' => 'PENGATURAN', 'items' => [
                    ['Periode PMB', 'admin.periods.index', 'calendar', request()->routeIs('admin.periods.*'), 'global' => true],
                    ['Akun Staf', 'admin.staff.index', 'people', request()->routeIs('admin.staff.*'), 'super' => true],
                    ['Integrasi Email', 'admin.settings.mail', 'mail', request()->routeIs('admin.settings.mail'), 'global' => true],
                ]],
            ];

            $campusScoped = (bool) auth()->user()?->campus_id;
            // 'super' = hanya admin semua kampus (akun staf).
            $superAdmin = auth()->user()?->role === 'admin' && !$campusScoped;
        @endphp

        @foreach($menu as $group)
            @php
                // Item lintas kampus disembunyikan untuk admin kampus; grup kosong ikut hilang.
                $visible = array_values(array_filter($group['items'], fn ($item) => (!($item['global'] ?? false) || !$campusScoped) && (!($item['super'] ?? false) || $superAdmin)));
            @endphp
            @continue(empty($visible))

            <div class="nav-section-label text-neutral-400" style="font-size:11px;font-weight:700;letter-spacing:0.08em;margin-top:{{ $loop->first ? '0' : '20px' }};margin-bottom:8px;padding:0 12px;">{{ $group['label'] }}</div>

            @foreach($visible as [$label, $route, $icon, $active])
                <a href="{{ route($route) }}"
                   data-label="{{ $label }}"
                   class="nav-item {{ $active ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        @foreach($icons[$icon] as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                    <span class="sidebar-label">{{ $label }}</span>
                </a>
            @endforeach
        @endforeach
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
        <div class="topbar-left" style="min-width:0;">
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
            <h1 class="topbar-page-title text-neutral-900" style="font-size:20px;font-weight:700;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0;">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            @php
                $adminPeriodOptions = \App\Support\AdminPeriod::options();
                $adminPeriodId = \App\Support\AdminPeriod::id();
            @endphp
            @if($adminPeriodOptions->isNotEmpty())
                @php
                    $adminPeriodCurrent = $adminPeriodOptions->firstWhere('id', $adminPeriodId);
                    // Label ringkas: "2027/2028" bila nama memuatnya, selain itu nama dipotong.
                    $adminPeriodShort = function ($period) {
                        if (preg_match('/\d{4}\s*\/\s*\d{4}/', $period->name, $match)) {
                            return preg_replace('/\s+/', '', $match[0]);
                        }

                        return \Illuminate\Support\Str::limit($period->name, 22);
                    };
                    $adminPeriodButton = $adminPeriodCurrent ? $adminPeriodShort($adminPeriodCurrent) : 'Semua periode';
                    $adminPeriodButtonTitle = $adminPeriodCurrent ? $adminPeriodCurrent->name : 'Semua periode';
                @endphp
                <form method="POST" action="{{ route('admin.periods.select') }}" class="period-picker"
                      x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    @csrf
                    <span class="topbar-username text-neutral-500" style="font-size:12px;font-weight:600;">Periode</span>
                    <button type="button" id="admin-period-select" class="period-picker-button"
                            @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox"
                            aria-label="Pilih periode" title="{{ $adminPeriodButtonTitle }}">
                        <span class="period-picker-label">{{ $adminPeriodButton }}</span>
                        <svg class="period-picker-chevron" :class="{ 'is-open': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div class="period-picker-panel" x-show="open" x-cloak x-transition.opacity.duration.100ms role="listbox" aria-label="Periode PMB">
                        <button type="submit" name="period" value="all" role="option"
                                class="period-picker-item {{ $adminPeriodId === null ? 'is-selected' : '' }}"
                                aria-selected="{{ $adminPeriodId === null ? 'true' : 'false' }}">
                            <span class="period-picker-item-text">
                                <span class="period-picker-item-name">Semua periode</span>
                                <span class="period-picker-item-meta">Gabungan seluruh tahun ajaran</span>
                            </span>
                            @if($adminPeriodId === null)
                                <svg class="period-picker-check" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            @endif
                        </button>

                        @foreach($adminPeriodOptions as $adminPeriodOption)
                            @php $isSelected = $adminPeriodId === $adminPeriodOption->id; @endphp
                            <button type="submit" name="period" value="{{ $adminPeriodOption->id }}" role="option"
                                    class="period-picker-item {{ $isSelected ? 'is-selected' : '' }}"
                                    aria-selected="{{ $isSelected ? 'true' : 'false' }}">
                                <span class="period-picker-item-text">
                                    <span class="period-picker-item-name">{{ $adminPeriodOption->name }}{{ $adminPeriodOption->isArchived() ? ' (arsip)' : '' }}</span>
                                    <span class="period-picker-item-meta">{{ $adminPeriodOption->phaseLabel() }} &middot; {{ $adminPeriodOption->open_date->isoFormat('D MMM Y') }} &ndash; {{ $adminPeriodOption->close_date->isoFormat('D MMM Y') }}</span>
                                </span>
                                @if($isSelected)
                                    <svg class="period-picker-check" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </form>
            @endif
            <span class="topbar-username text-neutral-600" style="font-size:14px;font-weight:500;">{{ auth()->user()->name }}</span>
            <span class="topbar-username text-primary-600" style="background:#F2F5FD;font-size:12px;font-weight:700;border-radius:9999px;padding:4px 12px;white-space:nowrap;">{{ ucfirst(auth()->user()->role) }}</span>
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
@include('partials.pwa-register')
@include('partials.pwa-install-banner', ['section' => 'admin'])
    <div id="nav-tooltip" role="tooltip" aria-hidden="true"></div>
    <script>
        // Tooltip nama menu saat sidebar hanya ikon (desktop collapsed dan tablet).
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var tip = document.getElementById('nav-tooltip');
            if (!sidebar || !tip) return;

            function iconOnly() {
                return window.innerWidth >= 768 && sidebar.getBoundingClientRect().width <= 80;
            }

            function show(el) {
                if (!iconOnly()) return;
                var label = el.getAttribute('data-label');
                if (!label) return;
                var rect = el.getBoundingClientRect();
                tip.textContent = label;
                tip.style.left = (rect.right + 12) + 'px';
                tip.style.top = (rect.top + rect.height / 2) + 'px';
                tip.classList.add('visible');
            }

            function hide() { tip.classList.remove('visible'); }

            sidebar.querySelectorAll('.nav-item[data-label]').forEach(function (el) {
                el.addEventListener('mouseenter', function () { show(el); });
                el.addEventListener('mouseleave', hide);
                el.addEventListener('focus', function () { show(el); });
                el.addEventListener('blur', hide);
            });

            sidebar.addEventListener('scroll', hide, true);
            window.addEventListener('resize', hide);
        })();
    </script>

    {{-- Dialog konfirmasi global: pengganti confirm() bawaan browser (bisa diblokir/tidak muncul). --}}
    {{-- Pakai atribut data-confirm="pesan" pada tombol atau form; JS dapat memanggil await window.adminConfirm('pesan'). --}}
    <div x-data="{ open: false, message: '', resolve: null,
            init() { window.adminConfirm = (message) => new Promise((resolve) => { this.message = message; this.resolve = resolve; this.open = true; }); },
            answer(value) { this.open = false; if (this.resolve) { this.resolve(value); this.resolve = null; } } }"
         x-show="open" x-cloak style="display:none;"
         x-on:keydown.escape.window="if (open) answer(false)"
         class="fixed inset-0 z-[100] flex items-center justify-center px-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-neutral-900/50" x-on:click="answer(false)"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-neutral-900">Konfirmasi</h3>
            <p class="mt-2 text-sm text-neutral-600 whitespace-pre-line" x-text="message"></p>
            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" color="neutral" variant="ghost" x-on:click="answer(false)">Batal</x-button>
                <x-button type="button" x-on:click="answer(true)">Ya, Lanjutkan</x-button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            // Klik pada elemen ber-data-confirm: tahan, minta konfirmasi, lalu ulangi klik yang sama.
            document.addEventListener('click', async function (e) {
                var el = e.target.closest('[data-confirm]');
                if (!el || el.tagName === 'FORM' || el.hasAttribute('disabled')) return;
                if (el.dataset.confirmed) { delete el.dataset.confirmed; return; }
                e.preventDefault();
                e.stopImmediatePropagation();
                if (await window.adminConfirm(el.dataset.confirm)) {
                    el.dataset.confirmed = '1';
                    el.click();
                }
            }, true);

            // Submit form di dalam elemen ber-data-confirm (form itu sendiri atau pembungkusnya).
            document.addEventListener('submit', async function (e) {
                var form = e.target;
                if (form.dataset.confirmed) { delete form.dataset.confirmed; return; }
                var host = form.closest('[data-confirm]');
                if (!host) return;
                // Tombol submit ber-data-confirm sudah ditangani di handler klik.
                if (e.submitter && e.submitter.hasAttribute('data-confirm')) return;
                e.preventDefault();
                if (await window.adminConfirm(host.dataset.confirm)) {
                    form.dataset.confirmed = '1';
                    form.requestSubmit(e.submitter || undefined);
                }
            }, true);
        })();
    </script>
</body>
</html>
