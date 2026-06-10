@extends('layouts.landing')
@section('title', 'Program Afiliasi — PMB YPIB Majalengka')

@section('content')
@php
if (!function_exists('formatJuta')) {
    function formatJuta($amount) {
        if ($amount >= 1000000) {
            $juta = $amount / 1000000;
            if ($juta == (int)$juta) {
                return 'Rp ' . number_format($juta, 0, ',', '.') . ' Juta';
            }
            return 'Rp ' . str_replace('.', ',', (string)round($juta, 1)) . ' Juta';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
@endphp
<style>
    /* Global Variables based on new UI */
    :root {
        --ypib-dark: #06193E;
        --ypib-yellow: #FDB813;
        --ypib-yellow-hover: #e5a30e;
        --ypib-gray: #5D6C7B;
        --ypib-bg-light: #F8FAFC;
    }

    /* Typography Utilities */
    .text-yellow { color: var(--ypib-yellow); }
    .bg-yellow { background-color: var(--ypib-yellow); }
    .text-dark { color: var(--ypib-dark); }
    .bg-dark { background-color: var(--ypib-dark); }

    /* Buttons */
    .btn-yellow {
        background: var(--ypib-yellow);
        color: var(--ypib-dark);
        padding: 14px 28px;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .btn-yellow:hover {
        background: var(--ypib-yellow-hover);
        transform: translateY(-2px);
    }
    .btn-outline-light {
        border: 1px solid rgba(255,255,255,0.3);
        color: #FFFFFF;
        padding: 14px 28px;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
        background: transparent;
    }
    .btn-outline-light:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.5);
    }

    /* Section Badges */
    .section-badge-light {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e6edfc;
        color: var(--ypib-dark);
        font-size: 12px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .section-badge-dark {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(253, 184, 19, 0.1);
        border: 1px solid rgba(253, 184, 19, 0.3);
        color: var(--ypib-yellow);
        font-size: 12px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
    }

    /* Hero */
    .hero-section {
        background: linear-gradient(90deg, var(--ypib-dark) 0%, rgba(6, 25, 62, 0.95) 45%, rgba(6, 25, 62, 0) 75%), url('{{ asset('images/hero-affiliator.png') }}');
        background-size: cover;
        background-position: center;
        min-height: 88vh;
        display: flex;
        align-items: center;
        position: relative;
    }
    .hero-grid { display:grid;grid-template-columns:1.1fr 0.9fr;gap:64px;align-items:center; }

    /* Persona Cards (Program Studi Style) */
    .persona-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        border-radius: 16px;
        padding: 32px 24px;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .persona-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 12px 32px rgba(0,0,0,0.06);
        transform: translateY(-4px);
    }
    .persona-badge {
        position: absolute;
        top: 24px;
        right: 24px;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 9999px;
        border: 1px solid #E2E8F0;
        color: #64748B;
        background: #F8FAFC;
    }
    .persona-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }

    /* Commission Cards (Replaces Tables) */
    .komisi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 48px;
    }
    .komisi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 32px 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .komisi-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 12px 32px rgba(0,0,0,0.06);
        transform: translateY(-4px);
    }
    .tier-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    .tier-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    .tier-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 12px 32px rgba(0,0,0,0.06);
        transform: translateY(-4px);
    }
    .tier-header {
        padding: 24px;
        text-align: center;
        border-bottom: 1px solid #F1F5F9;
        background: #F8FAFC;
    }
    .tier-body {
        padding: 24px;
        flex: 1;
    }
    .tier-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        color: var(--ypib-gray);
    }
    .tier-item:last-child { margin-bottom: 0; }
    .tier-footer {
        background: var(--ypib-dark);
        color: #FFFFFF;
        padding: 20px 24px;
        text-align: center;
    }
    .tier-footer-highlight {
        background: var(--ypib-yellow);
        color: var(--ypib-dark);
    }
    @media (max-width: 1023px) {
        .tier-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
        .tier-card[style*="transform"] { transform: none !important; z-index: 1 !important; }
    }

    /* Steps (Minimalist Circles) */
    .steps-container {
        display: flex;
        justify-content: space-between;
        position: relative;
        max-width: 1000px;
        margin: 0 auto;
    }
    .steps-line {
        position: absolute;
        top: 32px;
        left: 50px;
        right: 50px;
        height: 1px;
        background: #E2E8F0;
        z-index: 1;
    }
    .step-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
        padding: 0 10px;
    }
    .step-circle {
        width: 64px;
        height: 64px;
        border-radius: 9999px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
        color: var(--ypib-dark);
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }
    .step-title { font-size: 15px; font-weight: 700; color: var(--ypib-dark); margin-bottom: 8px; }
    .step-desc { font-size: 13px; color: var(--ypib-gray); line-height: 1.5; }

    /* Animations */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    @keyframes pulse-glow {
        0% { box-shadow: 0 0 0 0 rgba(253, 184, 19, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(253, 184, 19, 0); }
        100% { box-shadow: 0 0 0 0 rgba(253, 184, 19, 0); }
    }
    .btn-pulse {
        animation: pulse-glow 2s infinite;
    }

    /* FAQ */
    .faq-item {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .faq-item:hover { border-color: #CBD5E1; }
    .faq-button {
        width: 100%;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: transparent;
        border: none;
        font-size: 16px;
        font-weight: 700;
        color: var(--ypib-dark);
        cursor: pointer;
        text-align: left;
    }
    .faq-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.4s ease;
        padding: 0 24px;
        color: var(--ypib-gray);
        line-height: 1.6;
        font-size: 15px;
    }
    .faq-content.open {
        max-height: 500px;
        padding-bottom: 20px;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--ypib-dark) 0%, #030d22 100%);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }
    .cta-glow {
        position: absolute;
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(30, 64, 175, 0.4) 0%, transparent 60%);
        top: 50%;
        right: -100px;
        transform: translateY(-50%);
        z-index: 0;
        pointer-events: none;
    }
    .cta-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 48px;
        position: relative;
        z-index: 1;
    }
    @media (min-width: 1024px) {
        .cta-grid {
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 64px;
        }
    }
    .form-card {
        background: #FFFFFF;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 24px 50px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
    }
    @media (max-width: 767px) {
        .form-card { padding: 32px 24px; }
    }

    /* Responsive */
    @media (max-width:1023px) {
        .hero-grid { grid-template-columns:1fr !important; gap:40px; }
        .hero-section { 
            background: linear-gradient(180deg, var(--ypib-dark) 0%, rgba(6, 25, 62, 0.9) 45%, rgba(6, 25, 62, 0.2) 100%), url('{{ asset('images/hero-affiliator.png') }}'); 
            background-size: cover; 
            background-position: 85% center; 
        }
    }
    @media (max-width:767px) {
        .hero-section { 
            min-height:auto; 
            padding: 80px 0 0 0; 
            background: radial-gradient(circle at top left, #1E40AF 0%, var(--ypib-dark) 80%);
            flex-direction: column;
            justify-content: space-between;
        }
        .steps-container { flex-direction: column; gap: 32px; }
        .steps-line { display: none; }
        .step-item { width: 100%; max-width: 300px; margin: 0 auto; }
        .hero-buttons { flex-direction: column; width: 100%; }
        .hero-buttons a { width: 100%; }
        .mobile-img-container { display: block; margin-top: 60px; width: 100%; align-self: flex-start; }
    }
    @media (min-width: 768px) {
        .mobile-img-container { display: none; }
    }
    @keyframes pulse-yellow { 0%,100%{opacity:1} 50%{opacity:0.5} }
</style>

{{-- ═══ HERO ═══ --}}
<section class="hero-section">
    <div class="pub-container" style="position:relative;z-index:1;width:100%;">
        <div class="hero-grid">
            <div>
                <div class="section-badge-dark">
                    <span style="width:6px;height:6px;border-radius:9999px;display:inline-block;background:var(--ypib-yellow);animation:pulse-yellow 2s infinite;"></span>
                    1.000+ Affiliator Terdaftar
                </div>

                <h1 style="font-size:clamp(2.5rem,5vw,3.75rem);font-weight:800;line-height:1.15;margin:0 0 24px 0;" class="text-white">
                    Sebar link,<br><span class="text-yellow">komisi masuk rekening.</span>
                </h1>
                
                <p style="font-size:18px;color:rgba(255,255,255,0.8);line-height:1.6;margin:0 0 40px 0;max-width:520px;">
                    Program Affiliator YPIB terbuka untuk siapa saja — mahasiswa, alumni, guru, maupun konten kreator. Cukup daftar, dapat link unik, dan mulai sebar. Komisi cair otomatis setiap bulan.
                </p>
                
                <div class="hero-buttons" style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
                    <a href="#form-daftar" class="btn-yellow btn-pulse">
                        Daftar Sekarang
                        <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="#cara-kerja" class="btn-outline-light">
                        Pelajari Cara Kerjanya
                    </a>
                </div>
            </div>
            <div></div>
        </div>
    </div>

    <!-- Mobile Only Image -->
    <div class="mobile-img-container">
        <img src="{{ asset('images/affiliator-ypib.png') }}" alt="Affiliator YPIB" style="width: 100%; max-width: 450px; height: auto; display: block; margin: 0;" class="animate-float">
    </div>
</section>

{{-- ═══ COCOK UNTUK SIAPA? (Persona Cards) ═══ --}}
<section style="padding:100px 0;background:var(--ypib-bg-light);">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:64px;">
            <div class="section-badge-light">Persona</div>
            <h2 style="font-size:clamp(2rem,4vw,2.5rem);font-weight:800;margin:0;color:var(--ypib-dark);">Program ini untuk kamu.</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:24px;margin-bottom:32px;">
            <div class="persona-card">
                <div class="persona-badge">Mahasiswa</div>
                <div class="persona-icon" style="background:#E8F5E9;color:#1B5E20;">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                </div>
                <h3 style="font-size:18px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Mahasiswa Aktif YPIB</h3>
                <p style="font-weight:600;font-size:14px;margin:0 0 12px 0;color:#1D4ED8;">Kamu paling tahu YPIB — manfaatkan itu.</p>
                <p style="font-size:14px;color:var(--ypib-gray);margin:0;line-height:1.6;">Rekomendasikan YPIB ke adik kelas atau teman. Setiap yang daftar lewat linkmu, komisi langsung masuk rekeningmu — tanpa ganggu waktu kuliah.</p>
            </div>
            
            <div class="persona-card">
                <div class="persona-badge">Alumni</div>
                <div class="persona-icon" style="background:#E3F2FD;color:#0D47A1;">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                </div>
                <h3 style="font-size:18px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Alumni YPIB</h3>
                <p style="font-weight:600;font-size:14px;margin:0 0 12px 0;color:#1D4ED8;">Kontribusi berlanjut, komisi nyata.</p>
                <p style="font-size:14px;color:var(--ypib-gray);margin:0;line-height:1.6;">Jaringanmu luas. Rekomendasikan YPIB ke orang-orang di sekitarmu dan bantu mereka menemukan kampus yang tepat — sambil dapat komisi.</p>
            </div>

            <div class="persona-card">
                <div class="persona-badge">Pendidik</div>
                <div class="persona-icon" style="background:#FFF3E0;color:#E65100;">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
                </div>
                <h3 style="font-size:18px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Guru / BK SMA-SMK</h3>
                <p style="font-weight:600;font-size:14px;margin:0 0 12px 0;color:#1D4ED8;">Bantu siswa, dapat apresiasi.</p>
                <p style="font-size:14px;color:var(--ypib-gray);margin:0;line-height:1.6;">Bantu siswa kamu menemukan pilihan kuliah di YPIB. Setiap siswa yang mendaftar dan diterima, kamu dapat komisi hingga Rp{{ number_format($maxReferralReward + $maxReRegistrationReward, 0, ',', '.') }} per orang.</p>
            </div>

            <div class="persona-card">
                <div class="persona-badge">Kreator</div>
                <div class="persona-icon" style="background:#F3E5F5;color:#4A148C;">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                </div>
                <h3 style="font-size:18px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Konten Kreator</h3>
                <p style="font-weight:600;font-size:14px;margin:0 0 12px 0;color:#1D4ED8;">Audiens adalah aset berharga.</p>
                <p style="font-size:14px;color:var(--ypib-gray);margin:0;line-height:1.6;">Buat konten tentang YPIB, sebar link affiliatemu, dan dapat komisi dari setiap pendaftar. Plus ada reward khusus untuk kreator terbaik.</p>
            </div>
        </div>

        <div style="background:#FFFFFF;border-radius:24px;padding:48px;text-align:center;border:1px solid #E2E8F0;box-shadow:0 4px 12px rgba(0,0,0,0.02);">
            <h3 style="font-size:24px;font-weight:800;margin:0 0 16px 0;color:var(--ypib-dark);">Untuk kamu yang...</h3>
            <p style="font-size:16px;color:var(--ypib-gray);margin:0 0 32px 0;max-width:700px;margin-left:auto;margin-right:auto;line-height:1.6;">
                Punya kenalan yang lagi cari kampus, punya platform untuk berbagi, atau sekadar ingin dapat penghasilan tambahan dari hal yang bermanfaat — program ini terbuka untuk siapa saja, tanpa syarat.
            </p>
            <a href="#form-daftar" class="btn-yellow btn-pulse">Daftar sekarang, gratis.</a>
        </div>
    </div>
</section>

{{-- ═══ KOMISI ═══ --}}
<section style="padding:100px 0;background:#FFFFFF;">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:48px;">
            <div class="section-badge-light" style="display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                </svg>
                Reward
            </div>
            <h2 style="font-size:clamp(2rem,4vw,2.5rem);font-weight:800;margin:0 0 16px 0;color:var(--ypib-dark);">Komisi nyata <br class="block md:hidden"> untuk Kamu.</h2>
            <h3 style="font-size:22px;font-weight:700;color:var(--ypib-dark);margin:0;">Simulasi Potensi Pendapatan</h3>
        </div>

        <div style="max-width:1000px;margin:0 auto;">
            <div class="tier-grid">
                <!-- Tier 1 -->
                <div class="tier-card">
                    <div class="tier-header">
                        <div style="font-size:13px;font-weight:700;color:var(--ypib-gray);text-transform:uppercase;letter-spacing:0.05em;">Skenario Awal</div>
                        <div style="font-size:28px;font-weight:800;color:var(--ypib-dark);margin-top:8px;">20 Mahasiswa</div>
                    </div>
                    <div class="tier-body">
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Pendaftaran</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReferralReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReferralReward * 20) }}</span>
                        </div>
                        <div style="border-bottom:1px dashed #E2E8F0;margin:12px 0;"></div>
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Daftar Ulang</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReRegistrationReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReRegistrationReward * 20) }}</span>
                        </div>
                    </div>
                    <div class="tier-footer">
                        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.7);margin-bottom:4px;">Total Pendapatan</div>
                        <div style="font-size:24px;font-weight:800;">Rp {{ number_format(($maxReferralReward + $maxReRegistrationReward) * 20, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Tier 2 -->
                <div class="tier-card" style="transform:scale(1.05);z-index:2;border-color:var(--ypib-yellow);box-shadow:0 24px 48px rgba(0,0,0,0.08);">
                    <div class="tier-header" style="background:#FFF9E6;">
                        <div style="font-size:13px;font-weight:700;color:var(--ypib-yellow-hover);text-transform:uppercase;letter-spacing:0.05em;">Skenario Menengah</div>
                        <div style="font-size:28px;font-weight:800;color:var(--ypib-dark);margin-top:8px;">50 Mahasiswa</div>
                    </div>
                    <div class="tier-body">
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Pendaftaran</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReferralReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReferralReward * 50) }}</span>
                        </div>
                        <div style="border-bottom:1px dashed #E2E8F0;margin:12px 0;"></div>
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Daftar Ulang</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReRegistrationReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReRegistrationReward * 50) }}</span>
                        </div>
                    </div>
                    <div class="tier-footer tier-footer-highlight">
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:rgba(0,0,0,0.6);margin-bottom:4px;">Total Pendapatan</div>
                        <div style="font-size:28px;font-weight:800;">Rp {{ number_format(($maxReferralReward + $maxReRegistrationReward) * 50, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Tier 3 -->
                <div class="tier-card">
                    <div class="tier-header">
                        <div style="font-size:13px;font-weight:700;color:var(--ypib-gray);text-transform:uppercase;letter-spacing:0.05em;">Skenario Pro</div>
                        <div style="font-size:28px;font-weight:800;color:var(--ypib-dark);margin-top:8px;">100 Mahasiswa</div>
                    </div>
                    <div class="tier-body">
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Pendaftaran</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReferralReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReferralReward * 100) }}</span>
                        </div>
                        <div style="border-bottom:1px dashed #E2E8F0;margin:12px 0;"></div>
                        <div class="tier-item" style="align-items:center;">
                            <div>
                                <div style="color:var(--ypib-dark);font-weight:600;">Komisi Daftar Ulang</div>
                                <div style="font-size:12px;color:var(--ypib-gray);margin-top:2px;">Rp{{ number_format($maxReRegistrationReward, 0, ',', '.') }}/orang</div>
                            </div>
                            <span style="font-weight:700;color:var(--ypib-dark);">{{ formatJuta($maxReRegistrationReward * 100) }}</span>
                        </div>
                    </div>
                    <div class="tier-footer">
                        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.7);margin-bottom:4px;">Total Pendapatan</div>
                        <div style="font-size:24px;font-weight:800;">Rp {{ number_format(($maxReferralReward + $maxReRegistrationReward) * 100, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <p style="font-size:15px;color:var(--ypib-gray);text-align:center;margin:40px 0 0 0;font-style:italic;">*Note: Asumsi jika seluruh pendaftar berhasil melakukan daftar ulang.</p>
        </div>
    </div>
</section>

{{-- ═══ CARA KERJANYA (Minimalist Steps) ═══ --}}
<section id="cara-kerja" style="padding:100px 0;background:var(--ypib-bg-light);">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:80px;">
            <div class="section-badge-light">Panduan</div>
            <h2 style="font-size:clamp(2rem,4vw,2.5rem);font-weight:800;margin:0;color:var(--ypib-dark);">Cara Mendaftar & Bekerja</h2>
            <p style="font-size:16px;color:var(--ypib-gray);margin:16px 0 0 0;">Proses yang simpel, transparan, dan sangat menguntungkan.</p>
        </div>

        <div class="steps-container">
            <div class="steps-line"></div>
            
            <div class="step-item">
                <div class="step-circle">01</div>
                <h3 class="step-title">Daftar Akun</h3>
                <p class="step-desc">Isi form pendaftaran di bawah. Gratis dan tanpa syarat.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">02</div>
                <h3 class="step-title">Dapat Link Unik</h3>
                <p class="step-desc">Akses dashboard untuk menyalin link affiliasi personalmu.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">03</div>
                <h3 class="step-title">Sebar Link</h3>
                <p class="step-desc">Bagikan link ke calon mahasiswa lewat medsos atau chat.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">04</div>
                <h3 class="step-title">Pantau Proses</h3>
                <p class="step-desc">Cek progress pendaftaran mereka melalui dashboard real-time.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">05</div>
                <h3 class="step-title">Komisi Cair</h3>
                <p class="step-desc">Uang otomatis ditransfer ke rekeningmu setiap bulan.</p>
            </div>
        </div>
    </div>
</section>


{{-- ═══ CTA & FORM PENDAFTARAN ═══ --}}
<section id="form-daftar" class="cta-section">
    <div class="cta-glow"></div>
    <div class="pub-container">
        
        <div class="cta-grid">
            {{-- Bagian Teks (Kiri) --}}
            <div style="text-align:left;">
                <div class="section-badge-dark" style="margin:0 0 24px 0; display:inline-flex;">Siap Memulai?</div>
                <h2 style="font-size:clamp(2.5rem,5vw,3.5rem);font-weight:800;margin:0 0 16px 0;color:#FFFFFF;line-height:1.1;">Langkah Kecil, <br><span class="text-yellow">Dampak Besar.</span></h2>
                <p style="font-size:18px;color:rgba(255,255,255,0.7);line-height:1.6;margin:0 0 32px 0;max-width:600px;">
                    Bergabunglah bersama 1.000+ affiliator yang sudah aktif merekomendasikan YPIB — dan mulai dapatkan komisi sekarang juga.
                </p>
                
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div style="display:flex;align-items:center;gap:12px;font-size:16px;font-weight:600;color:rgba(255,255,255,0.9);">
                        <svg style="width:24px;height:24px;color:var(--ypib-yellow);" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Gratis Daftar & Tanpa Syarat Ribet
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;font-size:16px;font-weight:600;color:rgba(255,255,255,0.9);">
                        <svg style="width:24px;height:24px;color:var(--ypib-yellow);" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Langsung Aktif & Bisa Jualan
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;font-size:16px;font-weight:600;color:rgba(255,255,255,0.9);">
                        <svg style="width:24px;height:24px;color:var(--ypib-yellow);" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Komisi Cair Otomatis Tiap Bulan
                    </div>
                </div>
            </div>

            {{-- Bagian Form (Kanan) --}}
            <div>
                <div class="form-card">
                
                @auth
                    {{-- State: Sudah Login tapi belum aktif afiliasi --}}
                    <div style="text-align:center;margin-bottom:32px;">
                        <div style="width:72px;height:72px;border-radius:9999px;background:#e6edfc;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                            <svg style="width:36px;height:36px;color:#1D4ED8;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </div>
                        <h2 style="font-size:24px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Halo, {{ auth()->user()->name }}!</h2>
                        <p style="font-size:15px;margin:0;line-height:1.6;color:var(--ypib-gray);">Anda selangkah lagi untuk menjadi mitra afiliasi kami. Klik tombol di bawah untuk mengaktifkan kode referral Anda.</p>
                    </div>

                    <form method="POST" action="{{ route('referrer.store') }}">
                        @csrf
                        <button type="submit" class="btn-yellow" style="width:100%;height:56px;font-size:16px;">
                            Aktifkan Akun Affiliator
                        </button>
                    </form>
                @else
                    {{-- State: Belum Login (Form Registrasi Publik) --}}
                    <div style="text-align:center;margin-bottom:32px;">
                        <h2 style="font-size:28px;font-weight:800;margin:0 0 8px 0;color:var(--ypib-dark);">Mulai Sekarang</h2>
                        <p style="font-size:15px;margin:0;color:var(--ypib-gray);">Buat akun afiliasi Anda dan dapatkan link unik.</p>
                    </div>

                    <form id="referrer_form" method="POST" action="{{ route('referrer.register') }}">
                        @csrf
                        
                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:var(--ypib-dark);">Nama Lengkap</label>
                            <input type="text" name="name" class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] text-[15px] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                            @error('name')<div style="font-size:13px;margin-top:6px;color:#EF4444;">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:var(--ypib-dark);">Email Aktif</label>
                            <input type="email" id="reg_email" name="email" class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] text-[15px] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors" value="{{ old('email') }}" placeholder="Contoh: budi@gmail.com" onblur="validateLiveEmail(this)" oninput="clearLiveError(this, 'email_live_error')" required>
                            <div id="email_live_error" style="display:none;font-size:13px;margin-top:6px;color:#EF4444;">Format email tidak valid. Gunakan domain lengkap seperti .com, .id</div>
                            @error('email')<div style="font-size:13px;margin-top:6px;color:#EF4444;">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:var(--ypib-dark);">Nomor HP / WhatsApp</label>
                            <input type="tel" id="reg_phone" name="phone" class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] text-[15px] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" onblur="validateLivePhone(this)" oninput="this.value = this.value.replace(/[^0-9]/g, ''); clearLiveError(this, 'phone_live_error');" required>
                            <div id="phone_live_error" style="display:none;font-size:13px;margin-top:6px;color:#EF4444;">Nomor HP harus berupa angka minimal 10 digit.</div>
                            @error('phone')<div style="font-size:13px;margin-top:6px;color:#EF4444;">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:var(--ypib-dark);">Password</label>
                            <div style="position:relative;">
                                <input type="password" id="reg_password" name="password" class="w-full h-12 px-4 pr-12 rounded-xl border border-[#E2E8F0] text-[15px] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors" placeholder="Minimal 8 karakter" required>
                                <button type="button" onclick="togglePassword('reg_password', 'icon_reg_password')" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#94A3B8;">
                                    <svg id="icon_reg_password" style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')<div style="font-size:13px;margin-top:6px;color:#EF4444;">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:32px;">
                            <label style="display:block;font-size:14px;font-weight:700;margin-bottom:8px;color:var(--ypib-dark);">Konfirmasi Password</label>
                            <div style="position:relative;">
                                <input type="password" id="reg_password_confirmation" name="password_confirmation" class="w-full h-12 px-4 pr-12 rounded-xl border border-[#E2E8F0] text-[15px] focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8] transition-colors" placeholder="Ulangi password" required>
                                <button type="button" onclick="togglePassword('reg_password_confirmation', 'icon_reg_password_confirmation')" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#94A3B8;">
                                    <svg id="icon_reg_password_confirmation" style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-yellow" style="width:100%;height:56px;font-size:16px;">
                            Daftar Jadi Affiliator — Gratis
                        </button>

                        <div style="margin-top:32px;padding-top:24px;border-top:1px solid #E2E8F0;text-align:center;font-size:15px;color:var(--ypib-gray);">
                            Sudah punya akun? <a href="{{ route('referrer.dashboard') }}" style="font-weight:700;text-decoration:none;color:#1D4ED8;">Login di Sini</a>
                        </div>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</section>

{{-- ═══ FAQ ═══ --}}
<section style="padding:100px 0;background:#FFFFFF;">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:64px;">
            <div class="section-badge-light">FAQ</div>
            <h2 style="font-size:clamp(2rem,4vw,2.5rem);font-weight:800;margin:0;color:var(--ypib-dark);">Pertanyaan Umum</h2>
        </div>

        <div style="max-width:800px;margin:0 auto;">
            @php
            $faqs = [
                "Apakah ada biaya untuk bergabung?" => "Tidak ada. Daftar sebagai Affiliator YPIB sepenuhnya gratis dan tidak ada biaya apapun yang dipungut.",
                "Siapa saja yang bisa jadi affiliator?" => "Siapa pun bisa — mahasiswa aktif, alumni, guru, konten kreator, maupun masyarakat umum. Tidak ada syarat khusus untuk bergabung.",
                "Kapan komisi saya cair?" => "Komisi dicairkan otomatis setiap bulan langsung ke rekening yang kamu daftarkan. Tidak perlu request manual.",
                "Bagaimana sistem tahu pendaftar berasal dari saya?" => "Setiap affiliator mendapat link unik personal. Siapa pun yang mendaftar lewat link tersebut otomatis tercatat atas namamu — meskipun mereka mendaftar beberapa hari setelah klik link pertama kali.",
                "Apa bedanya komisi pendaftaran dan daftar ulang?" => "Komisi pendaftaran (Rp{{ number_format($maxReferralReward, 0, ',', '.') }}) didapat saat calon mahasiswa mengisi formulir lewat linkmu. Komisi daftar ulang (Rp{{ number_format($maxReRegistrationReward, 0, ',', '.') }}) didapat saat mereka resmi diterima dan melakukan daftar ulang."
            ];
            @endphp

            @foreach($faqs as $q => $a)
            <div class="faq-item">
                <button class="faq-button" onclick="toggleFaq(this)">
                    <span>{{ $q }}</span>
                    <svg style="width:20px;height:20px;transition:transform 0.3s;color:var(--ypib-yellow-hover);" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div class="faq-content">
                    {{ $a }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<script>
function toggleFaq(button) {
    const content = button.nextElementSibling;
    const svg = button.querySelector('svg');
    if (content.classList.contains('open')) {
        content.classList.remove('open');
        svg.style.transform = "rotate(0deg)";
    } else {
        content.classList.add('open');
        svg.style.transform = "rotate(180deg)";
    }
}

// Scroll Reveal & Stagger Animation
document.addEventListener('DOMContentLoaded', function() {
    // Add reveal class to cards so they are hidden initially
    document.querySelectorAll('.persona-card, .komisi-card, .step-item, .faq-item, .form-card, .section-badge-light, .section-badge-dark').forEach(el => {
        el.classList.add('reveal');
    });

    const observer = new IntersectionObserver((entries) => {
        // Group intersecting entries
        let delay = 0;
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('active');
                }, delay);
                delay += 100; // Stagger effect 100ms
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
});

function togglePassword(inputId, iconSvgId) {
    const input = document.getElementById(inputId);
    const svg = document.getElementById(iconSvgId);
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
    } else {
        input.type = 'password';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
    }
}

function validateLiveEmail(input) {
    const errorDiv = document.getElementById('email_live_error');
    const regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    if (input.value.length > 0 && !regex.test(input.value)) {
        errorDiv.style.display = 'block';
        input.classList.add('border-[#EF4444]', 'focus:border-[#EF4444]', 'focus:ring-[#EF4444]');
        input.classList.remove('border-[#E2E8F0]', 'focus:border-[#1D4ED8]', 'focus:ring-[#1D4ED8]');
        input.setCustomValidity('Format email tidak valid.');
    } else {
        clearLiveError(input, 'email_live_error');
    }
}

function validateLivePhone(input) {
    const errorDiv = document.getElementById('phone_live_error');
    if (input.value.length > 0 && input.value.length < 10) {
        errorDiv.style.display = 'block';
        input.classList.add('border-[#EF4444]', 'focus:border-[#EF4444]', 'focus:ring-[#EF4444]');
        input.classList.remove('border-[#E2E8F0]', 'focus:border-[#1D4ED8]', 'focus:ring-[#1D4ED8]');
        input.setCustomValidity('Nomor HP minimal 10 digit.');
    } else {
        clearLiveError(input, 'phone_live_error');
    }
}

function clearLiveError(input, errorDivId) {
    const errorDiv = document.getElementById(errorDivId);
    if (errorDiv) errorDiv.style.display = 'none';
    input.classList.remove('border-[#EF4444]', 'focus:border-[#EF4444]', 'focus:ring-[#EF4444]');
    input.classList.add('border-[#E2E8F0]', 'focus:border-[#1D4ED8]', 'focus:ring-[#1D4ED8]');
    input.setCustomValidity('');
}

const form = document.getElementById('referrer_form');
if (form) {
    form.addEventListener('submit', function(e) {
        const emailInput = document.getElementById('reg_email');
        const phoneInput = document.getElementById('reg_phone');
        
        if (emailInput) validateLiveEmail(emailInput);
        if (phoneInput) validateLivePhone(phoneInput);
    });
}
</script>
@endsection
