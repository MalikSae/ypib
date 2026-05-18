@extends('layouts.landing')
@section('title', $program->name . ' — PMB YPIB Majalengka')

@section('content')
<style>
    .prodi-layout { display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start; }
    .prodi-main { flex: 1 1 0%; min-width: 320px; }
    .prodi-sidebar { width: 380px; position: sticky; top: 100px; }
    @media (max-width: 991px) {
        .prodi-layout { gap: 32px; }
        .prodi-main { flex: 1 1 100%; min-width: 100%; }
        .prodi-sidebar { width: 100%; position: relative; top: 0; }
    }
</style>
<section style="background:#FFFFFF; min-height:calc(100vh - 64px);">
    {{-- Hero Section --}}
    <div style="background:linear-gradient(135deg, #0056b3 0%, #002d62 100%); color:#ffffff; padding: 64px 0; position:relative; overflow:hidden;">
        <div style="position:absolute; top:-50%; right:-10%; width:600px; height:600px; background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); border-radius:50%;"></div>
        
        <div class="pub-container" style="position:relative; z-index:10;">
            {{-- Breadcrumb --}}
            <nav style="display:flex;align-items:center;gap:8px;font-size:14px;color:rgba(255,255,255,0.7);margin-bottom:32px;flex-wrap:wrap;">
                <a href="{{ route('landing') }}" style="color:rgba(255,255,255,0.7);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Beranda</a>
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <a href="{{ route('landing') }}#prodi" style="color:rgba(255,255,255,0.7);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Program Studi</a>
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span style="color:#ffffff;font-weight:600;">{{ $program->name }}</span>
            </nav>

            <div style="max-width: 700px;">
                <div style="font-size:14px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:#93c5fd; margin-bottom:12px;">
                    Fakultas {{ $program->faculty }}
                </div>
                <h1 style="font-size:clamp(2.5rem, 5vw, 3.5rem); font-weight:800; line-height:1.15; margin:0 0 24px 0; letter-spacing:-0.5px;">
                    {{ $program->name }}
                </h1>
                <p style="font-size:18px; color:rgba(255,255,255,0.85); line-height:1.6; margin:0; max-width:600px;">
                    Mulai perjalanan karir profesional Anda. Dapatkan pendidikan terbaik dengan fasilitas modern dan pengajar berpengalaman.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="pub-container" style="padding-top: 56px; padding-bottom: 80px;">
        <div class="prodi-layout">
            
            {{-- Left Column --}}
            <div class="prodi-main">
                <h2 style="font-size:28px; font-weight:700; color:#0A1317; margin:0 0 24px 0; letter-spacing:-0.5px;">
                    Mengapa Memilih {{ $program->name }}?
                </h2>
                
                @if($program->description)
                    <div style="font-size:16px; color:#444950; line-height:1.8; margin-bottom:48px;">
                        {!! $program->description !!}
                    </div>
                @else
                    <p style="font-size:16px; color:#8595A4; font-style:italic; margin-bottom:48px;">
                        Deskripsi program studi akan segera tersedia.
                    </p>
                @endif

                <div style="height:1px; background:#F1F4F7; margin-bottom:40px;"></div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:32px;">
                    <div style="display:flex; gap:16px;">
                        <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff; color:#0064E0; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:700; color:#0A1317; margin:0 0 6px 0;">Kurikulum Relevan</h3>
                            <p style="font-size:14px; color:#5D6C7B; margin:0; line-height:1.5;">Kurikulum dirancang khusus untuk memenuhi standar industri dan kebutuhan profesional masa kini.</p>
                        </div>
                    </div>
                    <div style="display:flex; gap:16px;">
                        <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff; color:#0064E0; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:700; color:#0A1317; margin:0 0 6px 0;">Pengajar Ahli</h3>
                            <p style="font-size:14px; color:#5D6C7B; margin:0; line-height:1.5;">Dibimbing langsung oleh praktisi terkemuka dan dosen yang kompeten di bidangnya.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Sticky Card) --}}
            <div class="prodi-sidebar">
                <div style="background:#ffffff; border-radius:24px; box-shadow: 0 12px 32px rgba(0,0,0,0.06); border: 1px solid #E8ECEF; padding:32px; display:flex; flex-direction:column; gap:24px;">
                    
                    <div>
                        <div style="font-size:14px; color:#8595A4; margin-bottom:4px; font-weight:500;">Biaya Pendaftaran</div>
                        @if($period)
                            <div style="font-size:32px; font-weight:800; color:#0A1317; letter-spacing:-1px;">
                                Rp {{ number_format($period->registration_fee,0,',','.') }}
                            </div>
                        @else
                            <div style="font-size:20px; font-weight:600; color:#E41E3F;">
                                Belum ada periode aktif
                            </div>
                        @endif
                    </div>

                    <div style="height:1px; background:#F1F4F7;"></div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:13px; color:#8595A4; margin-bottom:4px;">Akreditasi</div>
                            <div style="font-size:18px; font-weight:700; color:#0064E0; display:flex; align-items:center; gap:6px;">
                                {{ $program->accreditation }}
                            </div>
                        </div>
                        <div style="width:1px; height:40px; background:#F1F4F7;"></div>
                        <div>
                            <div style="font-size:13px; color:#8595A4; margin-bottom:4px;">Kuota Mahasiswa</div>
                            <div style="font-size:18px; font-weight:700; color:#0A1317; display:flex; align-items:center; gap:6px;">
                                {{ $program->quota }}
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('registration.create', ['prodi' => $program->id]) }}" class="btn-primary" style="justify-content:center; padding:16px 24px; font-size:16px; margin-top:8px;">
                        Daftar ke Prodi Ini
                        <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"/></svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
