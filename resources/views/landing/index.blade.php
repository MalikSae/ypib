@extends('layouts.landing')
@section('title', 'PMB YPIB Majalengka 2025/2026 — Daftar Sekarang')
@section('meta-description', 'Penerimaan Mahasiswa Baru YPIB Majalengka 2025/2026. Pilihan 5 program studi. Daftarkan dirimu sekarang!')

@section('content')
<style>
    /* Hero */
    .hero-section {
        background: linear-gradient(135deg, rgba(8, 46, 143, 0.85) 0%, rgba(5, 32, 102, 0.85) 60%, rgba(10, 19, 23, 0.9) 100%), url('{{ asset('images/hero.png') }}');
        background-size: cover;
        background-position: center;
        min-height: 88vh;
        display: flex;
        align-items: center;
        overflow: hidden;
        position: relative;
    }
    .hero-circle-1 { position:absolute;top:-120px;right:-120px;width:400px;height:400px;border-radius:9999px;background:rgba(255,255,255,0.05); }
    .hero-circle-2 { position:absolute;bottom:-80px;left:-80px;width:280px;height:280px;border-radius:9999px;background:rgba(255,255,255,0.04); }
    .hero-grid { display:grid;grid-template-columns:1.2fr 0.8fr;gap:64px;align-items:center; }
    .hero-stat-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .hero-stat-card {
        background:rgba(255,255,255,0.1);
        backdrop-filter:blur(8px);
        border:1px solid rgba(255,255,255,0.15);
        border-radius:16px;
        padding:20px;
        text-align:center;
    }
    /* Tabs */
    .prog-tabs { display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-bottom:40px; }
    .prog-tab-btn {
        background: transparent; border: 1px solid #DEE3E9; color: #5D6C7B;
        font-size: 14px; font-weight: 600; padding: 10px 20px; border-radius: 9999px;
        cursor: pointer; transition: all 0.2s ease;
    }
    .prog-tab-btn.active {
        background: #082e8f; border-color: #082e8f; color: #FFFFFF;
    }
    .prog-tab-btn:hover:not(.active) {
        background: #F8FAFC; color: #0A1317; border-color: #0A1317;
    }
    /* Programs */
    .prog-grid { display:flex;flex-wrap:wrap;justify-content:center;gap:24px; }
    .prog-card {
        width:calc(33.333% - 16px);
        background:#FFFFFF;
        border:1px solid rgba(255,255,255,0.8);
        box-shadow:0 4px 24px rgba(0,0,0,0.04);
        border-radius:20px;
        padding:32px 24px;
        display:flex;
        flex-direction:column;
        transition:all 0.3s ease;
    }
    .prog-card:hover { box-shadow:0 12px 32px rgba(0,0,0,0.08);transform:translateY(-4px); }
    /* Steps */
    .steps-grid { display:flex;flex-wrap:wrap;justify-content:center;gap:24px;position:relative; }
    .step-card {
        width:calc(33.333% - 16px);
        background:#FFFFFF;
        border:1px solid rgba(255,255,255,0.8);
        box-shadow:0 4px 24px rgba(0,0,0,0.04);
        border-radius:20px;
        padding:32px 20px;
        text-align:center;
        position:relative;
        transition:all 0.3s ease;
    }
    .step-card:hover { transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,0.08); }
    .step-arrow {
        display:none;
    }
    .step-num {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:40px;height:40px;
        border-radius:9999px;
        background:#e6edfc;
        color:#082e8f;
        font-size:14px;
        font-weight:700;
        margin:0 auto 16px;
    }
    /* CTA section */
    .cta-card {
        background:linear-gradient(135deg,#082e8f,#052066);
        border-radius:32px;
        padding:64px 48px;
        text-align:center;
        position:relative;
        overflow:hidden;
    }

    /* Responsive */
    @media (max-width:1023px) {
        .hero-grid { grid-template-columns:1fr !important; gap:40px; }
        .prog-card, .step-card { width:calc(50% - 12px); }
    }
    @media (max-width:767px) {
        .hero-section { min-height:auto; padding:0; }
        .hero-stat-grid { grid-template-columns:repeat(2,1fr); gap:12px; }
        .prog-card, .step-card { width:100%; }
        .cta-card { padding:40px 24px; border-radius:20px; }
        .hero-img-container { height: 100% !important; }
        .hero-image { max-height: 380px !important; right: -20px !important; bottom: 0 !important; }
        .hero-content-container { padding-top: 48px !important; padding-bottom: 350px !important; }
        .hero-buttons { flex-direction: column; width: 100%; }
        .hero-buttons a { width: 100%; justify-content: center; }
    }
</style>

{{-- ═══ HERO ═══ --}}
<section class="hero-section">
    <div class="hero-circle-1"></div>
    <div class="hero-circle-2"></div>
    
    {{-- Image Container for Bottom Anchoring --}}
    <div class="pub-container hero-img-container" style="position:absolute; bottom:0; left:0; right:0; height:100%; pointer-events:none;">
        <div style="position:relative; width:100%; height:100%;">
            <img src="{{ asset('images/mahasiswa.png') }}" class="hero-image" alt="Mahasiswa YPIB" style="position:absolute; bottom:0; right:0; max-height: 90%; width: auto; object-fit: contain; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15)); pointer-events:auto;">
        </div>
    </div>

    <div class="pub-container hero-content-container" style="position:relative;z-index:1;width:100%;padding-top:40px;padding-bottom:40px;">
        <div class="hero-grid">
            {{-- Kiri --}}
            <div>
                @if($period)
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);font-size:12px;font-weight:600;padding:6px 14px;border-radius:9999px;margin-bottom:24px;" class="text-white">
                    <span style="width:7px;height:7px;border-radius:9999px;display:inline-block;animation:pulse 2s infinite;" class="bg-success"></span>
                    Pendaftaran dibuka hingga {{ \Carbon\Carbon::parse($period->close_date)->isoFormat('D MMMM Y') }}
                </div>
                @endif

                <h1 style="font-size:clamp(2rem,5vw,3.25rem);font-weight:700;line-height:1.18;margin:0 0 20px 0;" class="text-white">
                    Kuliah di<br><span style="color:#FBBF24;">YPIB Majalengka</span><br>Mulai di Sini
                </h1>
                <p style="font-size:18px;color:rgba(255,255,255,0.75);line-height:1.6;margin:0 0 32px 0;max-width:480px;">
                    Bergabung bersama ribuan mahasiswa berprestasi. 9 program studi unggulan dari 3 Fakultas siap mengantarmu menuju karir impian.
                </p>
                <div class="hero-buttons" style="display:flex;flex-wrap:wrap;gap:12px;">
                    <a href="#prodi" class="btn-white">
                        <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                        </svg>
                        Pilih Program Studi
                    </a>
                    <a href="#prodi" class="btn-white-outline">
                        Program Studi
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </a>
                </div>
            </div>
            
            {{-- Kolom Kanan Kosong (diisi oleh absolute image di atas) --}}
            <div></div>
        </div>
    </div>
</section>

{{-- ═══ TENTANG YPIB ═══ --}}
<section style="padding:80px 0;" class="bg-white">
    <div class="pub-container">
        <div style="max-width:800px;margin:0 auto;text-align:center;">
            <div style="display:inline-flex;align-items:center;gap:6px;background:#e6edfc;font-size:12px;font-weight:700;padding:5px 14px;border-radius:9999px;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;" class="text-primary-600">
                Profil Universitas
            </div>
            <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;margin:0 0 24px 0;" class="text-neutral-900">Tentang YPIB Majalengka</h2>
            <p style="font-size:16px;line-height:1.8;margin:0;" class="text-neutral-500">
                Universitas Yayasan Pendidikan Imam Bonjol (YPIB) Majalengka adalah perguruan tinggi swasta di Majalengka, Jawa Barat, yang berdiri pada 8 Juni 2022 dari penggabungan STIKes YPIB Majalengka dan STF YPIB Cirebon. Universitas ini memiliki tiga fakultas utama: Ilmu Kesehatan, Farmasi, serta Psikologi, Bisnis, dan Teknologi, dengan program studi di bidang keperawatan, kebidanan, farmasi, psikologi, perdagangan internasional, dan ilmu komputer. Berkomitmen mencetak lulusan profesional, islami, dan mandiri, Universitas YPIB terus berkembang sebagai institusi pendidikan unggulan di wilayahnya.
            </p>
        </div>
    </div>
</section>

{{-- ═══ PROGRAM STUDI ═══ --}}
<section id="prodi" style="padding:80px 0;background:#F8FAFC;">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:48px;">
            <div style="display:inline-flex;align-items:center;gap:6px;background:#e6edfc;font-size:12px;font-weight:700;padding:5px 14px;border-radius:9999px;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;" class="text-primary-600">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-1.342m-7.48 0a49.994 49.994 0 0 1 3.74 1.342" /></svg>
                Akademik
            </div>
            <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;margin:0 0 12px 0;" class="text-neutral-900">Program Studi</h2>
            <p style="font-size:16px;max-width:480px;margin:0 auto;" class="text-neutral-500">Pilih program studi yang sesuai dengan minat dan passion-mu</p>
        </div>

        <div x-data="{ activeTab: 'Semua' }">
            <div class="prog-tabs">
                <button @click="activeTab = 'Semua'" :class="{'active': activeTab === 'Semua'}" class="prog-tab-btn">Semua</button>
                <button @click="activeTab = 'Fakultas Ilmu Kesehatan'" :class="{'active': activeTab === 'Fakultas Ilmu Kesehatan'}" class="prog-tab-btn">Ilmu Kesehatan</button>
                <button @click="activeTab = 'Fakultas Farmasi'" :class="{'active': activeTab === 'Fakultas Farmasi'}" class="prog-tab-btn">Farmasi</button>
                <button @click="activeTab = 'Fakultas Psikologi, Bisnis, dan Teknologi'" :class="{'active': activeTab === 'Fakultas Psikologi, Bisnis, dan Teknologi'}" class="prog-tab-btn">Psikologi, Bisnis & Teknologi</button>
            </div>

            <div class="prog-grid">
                @foreach($programs as $program)
                <div x-show="activeTab === 'Semua' || activeTab === '{{ $program->faculty->name }}'" class="prog-card" style="display:none;" x-transition.opacity.duration.300ms>
                    <h3 style="font-size:18px;font-weight:700;margin:0 0 4px 0;" class="text-neutral-900">{{ $program->name }}</h3>
                    <p style="font-size:13px;margin:0 0 16px 0;" class="text-neutral-400">{{ $program->faculty->name }}</p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
                        <span style="background:#E8F5E9;font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;" class="text-success-700">Akreditasi {{ $program->accreditation ?? '-' }}</span>
                        <span style="background:#e6edfc;font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;" class="text-primary-600">Kuota {{ $program->quota }}</span>
                    </div>
                    <div style="margin-top:auto;padding-top:16px;" class="border-t-neutral-100">
                        <a href="{{ route('prodi.show', $program->slug) }}"
                           style="display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:700;color:#082e8f;text-decoration:none;transition:color 0.15s;"
                           onmouseover="this.style.color='#052066';"
                           onmouseout="this.style.color='#082e8f';">
                            Lihat Detail <span style="font-size:16px;">&rarr;</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══ CARA DAFTAR ═══ --}}
<section id="cara-daftar" style="padding:80px 0;" class="bg-neutral-100">
    <div class="pub-container">
        <div style="text-align:center;margin-bottom:48px;">
            <div style="display:inline-flex;align-items:center;gap:6px;background:#e6edfc;font-size:12px;font-weight:700;padding:5px 14px;border-radius:9999px;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;" class="text-primary-600">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                Panduan
            </div>
            <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;margin:0 0 12px 0;" class="text-neutral-900">Cara Mendaftar</h2>
            <p style="font-size:16px;max-width:480px;margin:0 auto;" class="text-neutral-500">Ikuti 4 langkah mudah untuk menyelesaikan pendaftaranmu</p>
        </div>

        <div class="steps-grid">
            @php
            $steps = [
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.974 0-5.699-.85-7.843-2.318m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />','num'=>'01','title'=>'PMB Online','desc'=>'Kunjungi laman PMB Online untuk memulai pendaftaran atau datang ke kampus Universitas YPIB Majalengka'],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />','num'=>'02','title'=>'Biodata Diri','desc'=>'Isi seluruh form biodata diri dan kelengkapan lainnya seperti data orang tua dan wali'],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />','num'=>'03','title'=>'Lengkapi Berkas','desc'=>'Lengkapi berkas pendaftaran sesuai dengan persyaratan, bisa diserahkan langsung ke staf PMB'],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />','num'=>'04','title'=>'Seleksi','desc'=>'Ikuti tes seleksi (bisa dilakukan di hari yang sama dengan sistem One Day Service)'],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />','num'=>'05','title'=>'Pengumuman','desc'=>'Setelah pengumuman dinyatakan LULUS, selanjutnya lakukan proses daftar ulang'],
            ];
            @endphp
            @foreach($steps as $step)
            <div class="step-card">
                <div class="step-num">{{ $step['num'] }}</div>
                <div style="width:44px;height:44px;border-radius:12px;background:#e6edfc;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="text-primary-600">{!! $step['svg'] !!}</svg>
                </div>
                <h3 style="font-size:16px;font-weight:700;margin:0 0 8px 0;" class="text-neutral-900">{{ $step['title'] }}</h3>
                <p style="font-size:13px;margin:0;line-height:1.5;" class="text-neutral-500">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ BIAYA & CTA ═══ --}}
@if($period)
<section id="biaya" style="padding:80px 0;" class="bg-white">
    <div class="pub-container">
        <div class="cta-card">
            <div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:9999px;background:rgba(255,255,255,0.05);"></div>
            <div style="position:relative;z-index:1;">
                <div style="display:inline-block;background:rgba(255,255,255,0.15);font-size:12px;font-weight:700;padding:5px 14px;border-radius:9999px;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.06em;" class="text-white">
                    {{ $period->name }}
                </div>
                <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;margin:0 0 8px 0;" class="text-white">Biaya Pendaftaran</h2>
                <div style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:700;margin:20px 0;" class="text-white">
                    Bervariasi Tiap Program Studi
                </div>
                <p style="font-size:15px;color:rgba(255,255,255,0.7);margin:0 0 32px 0;">
                    Masa pendaftaran: {{ \Carbon\Carbon::parse($period->open_date)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($period->close_date)->isoFormat('D MMM Y') }}<br>
                    Silakan lihat rincian biaya pendaftaran pada masing-masing Program Studi.
                </p>
                <a href="#prodi" class="btn-white">
                    <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Pilih Program Studi
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
</style>
@endsection
