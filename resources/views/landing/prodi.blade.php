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
    .rich-text-content h1 { font-size: 2em; font-weight: bold; margin-top: 0.67em; margin-bottom: 0.67em; }
    .rich-text-content h2 { font-size: 1.5em; font-weight: bold; margin-top: 0.83em; margin-bottom: 0.83em; }
    .rich-text-content h3 { font-size: 1.17em; font-weight: bold; margin-top: 1em; margin-bottom: 1em; }
    .rich-text-content h4 { font-size: 1em; font-weight: bold; margin-top: 1.33em; margin-bottom: 1.33em; }
    .rich-text-content h5 { font-size: 0.83em; font-weight: bold; margin-top: 1.67em; margin-bottom: 1.67em; }
    .rich-text-content h6 { font-size: 0.67em; font-weight: bold; margin-top: 2.33em; margin-bottom: 2.33em; }
    .rich-text-content ul { list-style-type: disc; padding-left: 20px; margin-top: 1em; margin-bottom: 1em; }
    .rich-text-content ol { list-style-type: decimal; padding-left: 20px; margin-top: 1em; margin-bottom: 1em; }
    .rich-text-content li { display: list-item; margin-bottom: 0.5em; }
    .rich-text-content p { margin-top: 1em; margin-bottom: 1em; }
    .rich-text-content strong, .rich-text-content b { font-weight: bolder; }
    .rich-text-content em, .rich-text-content i { font-style: italic; }
    .rich-text-content a { color: #082e8f; text-decoration: underline; }
</style>
<section style="min-height:calc(100vh - 64px);" class="bg-white">
    {{-- Hero Section --}}
    <div style="background:linear-gradient(135deg, #0056b3 0%, #002d62 100%);  padding: 64px 0; position:relative; overflow:hidden;" class="text-white">
        <div style="position:absolute; top:-50%; right:-10%; width:600px; height:600px; background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); border-radius:50%;"></div>
        
        <div class="pub-container" style="position:relative; z-index:10;">
            {{-- Breadcrumb --}}
            <nav style="display:flex;align-items:center;gap:8px;font-size:14px;color:rgba(255,255,255,0.7);margin-bottom:32px;flex-wrap:wrap;">
                <a href="{{ route('landing') }}" style="color:rgba(255,255,255,0.7);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Beranda</a>
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <a href="{{ route('landing') }}#prodi" style="color:rgba(255,255,255,0.7);text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Program Studi</a>
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                <span style="font-weight:600;" class="text-white">{{ $program->name }}</span>
            </nav>

            <div style="max-width: 700px;">
                <div style="font-size:14px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:#93c5fd; margin-bottom:12px;">
                    Fakultas {{ $program->faculty->name }}
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
                <h2 style="font-size:28px; font-weight:700;  margin:0 0 24px 0; letter-spacing:-0.5px;" class="text-neutral-900">
                    Mengapa Memilih {{ $program->name }}?
                </h2>
                
                @if($program->description)
                    <div class="rich-text-content text-neutral-600" style="font-size:16px;  line-height:1.8; margin-bottom:48px;">
                        {!! $program->description !!}
                    </div>
                @else
                    <p style="font-size:16px;  font-style:italic; margin-bottom:48px;" class="text-neutral-400">
                        Deskripsi program studi akan segera tersedia.
                    </p>
                @endif

                @if(!empty($program->re_registration_fee_details) && count($program->re_registration_fee_details) > 0)
                <div style="margin-bottom:48px;">
                    <h3 style="font-size:22px; font-weight:700;  margin:0 0 24px 0; letter-spacing:-0.5px;" class="text-neutral-900">
                        Rincian Biaya Daftar Ulang
                    </h3>
                    <div style="border-radius:12px; overflow:hidden;  box-shadow: 0 4px 12px rgba(0,0,0,0.02);" class="border-neutral-200">
                        <table style="width:100%; border-collapse:collapse; text-align:left;">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th style="padding:16px 24px; font-weight:600; font-size:14px; width:60px; text-align:center;">NO</th>
                                    <th style="padding:16px 24px; font-weight:600; font-size:14px; text-transform:uppercase;">Jenis Biaya</th>
                                    <th style="padding:16px 24px; font-weight:600; font-size:14px; text-align:right;">NOMINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($program->re_registration_fee_details as $index => $detail)
                                <tr style="border-bottom:1px solid #DEE3E9; background:{{ $loop->even ? '#F9FAFB' : '#FFFFFF' }};">
                                    <td style="padding:16px 24px; font-size:14px;  text-align:center;" class="text-neutral-500">{{ $index + 1 }}</td>
                                    <td style="padding:16px 24px; font-size:14px; font-weight:500;  text-transform:uppercase;" class="text-neutral-900">{{ $detail['name'] }}</td>
                                    <td style="padding:16px 24px; font-size:15px; font-weight:600;  text-align:right;" class="text-neutral-600">
                                        {{ number_format($detail['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-neutral-100">
                                <tr>
                                    <td colspan="2" style="padding:16px 24px; text-align:right; font-weight:800; font-size:15px;" class="text-neutral-900">JUMLAH</td>
                                    <td style="padding:16px 24px; text-align:right; font-weight:800; font-size:18px;" class="text-primary-600">
                                        {{ number_format($program->re_registration_fee, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

                @if($program->galleries->count() > 0)
                <div style="margin-bottom:48px;">
                    <h3 style="font-size:22px; font-weight:700;  margin:0 0 24px 0; letter-spacing:-0.5px;" class="text-neutral-900">
                        Galeri Kegiatan
                    </h3>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
                        @foreach($program->galleries as $gallery)
                            <div style="border-radius:12px; overflow:hidden; aspect-ratio:4/3; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="Galeri {{ $program->name }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="height:1px;  margin-bottom:40px;" class="bg-neutral-100"></div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:32px;">
                    <div style="display:flex; gap:16px;">
                        <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff;  display:flex; align-items:center; justify-content:center; flex-shrink:0;" class="text-primary-600">
                            <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:700;  margin:0 0 6px 0;" class="text-neutral-900">Kurikulum Relevan</h3>
                            <p style="font-size:14px;  margin:0; line-height:1.5;" class="text-neutral-500">Kurikulum dirancang khusus untuk memenuhi standar industri dan kebutuhan profesional masa kini.</p>
                        </div>
                    </div>
                    <div style="display:flex; gap:16px;">
                        <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff;  display:flex; align-items:center; justify-content:center; flex-shrink:0;" class="text-primary-600">
                            <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:700;  margin:0 0 6px 0;" class="text-neutral-900">Pengajar Ahli</h3>
                            <p style="font-size:14px;  margin:0; line-height:1.5;" class="text-neutral-500">Dibimbing langsung oleh praktisi terkemuka dan dosen yang kompeten di bidangnya.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (Sticky Card) --}}
            <div class="prodi-sidebar">
                <div style="border-radius:24px; box-shadow: 0 12px 32px rgba(0,0,0,0.06); border: 1px solid #E8ECEF; padding:32px; display:flex; flex-direction:column; gap:24px;" class="bg-white">
                    
                    <div>
                        <div style="font-size:14px;  margin-bottom:4px; font-weight:500;" class="text-neutral-400">Biaya Pendaftaran</div>
                        <div style="font-size:32px; font-weight:800;  letter-spacing:-1px;" class="text-neutral-900">
                            Rp {{ number_format($program->registration_fee,0,',','.') }}
                        </div>


                    <div style="height:1px;" class="bg-neutral-100"></div>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:13px;  margin-bottom:4px;" class="text-neutral-400">Akreditasi</div>
                            <div style="font-size:18px; font-weight:700;  display:flex; align-items:center; gap:6px;" class="text-primary-600">
                                {{ $program->accreditation }}
                            </div>
                        </div>
                        <div style="width:1px; height:40px;" class="bg-neutral-100"></div>
                        <div>
                            <div style="font-size:13px;  margin-bottom:4px;" class="text-neutral-400">Kuota Mahasiswa</div>
                            <div style="font-size:18px; font-weight:700;  display:flex; align-items:center; gap:6px;" class="text-neutral-900">
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
