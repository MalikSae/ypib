@extends('layouts.landing')
@section('title', 'Status Pendaftaran — PMB YPIB Majalengka')

@section('content')
<style>
    .status-page { padding:64px 0;background:#F1F4F7;min-height:calc(100vh - 64px); }
    .timeline-line { position:absolute;left:19px;top:0;bottom:0;width:2px;background:#DEE3E9; }
    .timeline-dot-done  { background:#0064E0;color:#FFFFFF; }
    .timeline-dot-active{ background:#E65100;color:#FFFFFF; }
    .timeline-dot-wait  { background:#DEE3E9;color:#8595A4; }
    .rekening-box { background:#FFF8E1;border:1px solid #FFD54F;border-radius:12px;padding:20px;margin-top:12px; }
    .reg-summary-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px 24px; }
    @media(max-width:480px){ .reg-summary-grid{ grid-template-columns:1fr; } }
</style>

<section class="status-page">
<div class="pub-container">
<div style="max-width:640px;margin:0 auto;">

    {{-- Page header --}}
    <div style="text-align:center;margin-bottom:32px;">
        <h1 style="font-size:26px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Status Pendaftaran</h1>
        <p style="font-size:14px;color:#5D6C7B;margin:0;">PMB YPIB Majalengka 2025/2026</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="pub-flash-success">
            <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="pub-flash-error">
            <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="pub-flash-error" style="flex-direction:column;align-items:flex-start;">
            @foreach($errors->all() as $e)
                <div style="display:flex;gap:8px;align-items:center;">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $e }}
                </div>
            @endforeach
        </div>
    @endif

    @if(!$registration)
    {{-- Belum daftar --}}
    <div class="pub-card" style="text-align:center;padding:48px 24px;">
        <div style="width:56px;height:56px;border-radius:16px;background:#EFF4FF;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:28px;height:28px;color:#0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
            </svg>
        </div>
        <h2 style="font-size:18px;font-weight:700;color:#0A1317;margin:0 0 8px 0;">Belum Ada Pendaftaran</h2>
        <p style="font-size:14px;color:#5D6C7B;margin:0 0 24px 0;">Anda belum mengisi formulir pendaftaran PMB YPIB.</p>
        <a href="{{ route('registration.create') }}" class="btn-primary" style="display:inline-flex;">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z"/></svg>
            Isi Formulir Sekarang
        </a>
    </div>

    @else
    @php
        $status     = $registration->status;
        $step2Active= in_array($status, ['menunggu_pembayaran','menunggu_konfirmasi']);
        $step2Done  = !in_array($status, ['menunggu_pembayaran','menunggu_konfirmasi']);
        $step3Done  = in_array($status, ['terdaftar','diterima','ditolak','perlu_revisi']);
        $step4Done  = in_array($status, ['diterima','ditolak','perlu_revisi']);
    @endphp

    {{-- Timeline Card --}}
    <div class="pub-card" style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px;">Tahapan Pendaftaran</div>

        <div style="position:relative;padding-left:40px;">
            <div class="timeline-line"></div>

            {{-- Step 1 --}}
            <div style="position:relative;margin-bottom:24px;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;" class="timeline-dot-done">
                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </div>
                <div style="font-size:14px;font-weight:600;color:#0A1317;">Formulir Dikirim</div>
                <div style="font-size:12px;color:#8595A4;margin-top:2px;">{{ $registration->created_at->isoFormat('D MMMM Y, HH:mm') }}</div>
            </div>

            {{-- Step 2 --}}
            <div style="position:relative;margin-bottom:24px;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;"
                     class="{{ $step2Done ? 'timeline-dot-done' : ($step2Active ? 'timeline-dot-active' : 'timeline-dot-wait') }}">
                    @if($step2Done)
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else
                        2
                    @endif
                </div>
                <div style="font-size:14px;font-weight:600;color:{{ $step2Active ? '#E65100' : ($step2Done ? '#0A1317' : '#8595A4') }};">
                    Pembayaran
                    @if($status === 'menunggu_konfirmasi')
                        <span style="background:#EFF4FF;color:#0064E0;font-size:11px;font-weight:700;padding:3px 10px;border-radius:9999px;margin-left:8px;">Menunggu Konfirmasi</span>
                    @elseif($status === 'menunggu_pembayaran')
                        <span style="background:#FFF3E0;color:#E65100;font-size:11px;font-weight:700;padding:3px 10px;border-radius:9999px;margin-left:8px;">Belum Dibayar</span>
                    @endif
                </div>

                @if($step2Active)
                {{-- Info rekening --}}
                <div class="rekening-box">
                    <div style="font-size:13px;font-weight:700;color:#795548;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        Informasi Pembayaran
                    </div>
                    <div style="display:grid;grid-template-columns:100px 1fr;gap:8px;font-size:13px;color:#5D4037;">
                        <span>Bank</span><strong>BRI</strong>
                        <span>No. Rekening</span><strong style="font-family:monospace;">1234567890</strong>
                        <span>Atas Nama</span><strong>YPIB Majalengka</strong>
                        <span>Nominal</span><strong style="color:#BF360C;">Rp 250.000</strong>
                    </div>
                </div>

                {{-- Upload bukti --}}
                <div style="margin-top:12px;">
                    @if($registration->payment_proof)
                        <div style="display:flex;align-items:center;gap:10px;background:#E8F5E9;border:1px solid #A5D6A7;border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:10px;">
                            <svg style="width:16px;height:16px;color:#2E7D32;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                            <div>
                                <div style="font-weight:600;color:#2E7D32;">Bukti sudah diunggah</div>
                                <div style="font-size:11px;color:#388E3C;">{{ basename($registration->payment_proof) }}</div>
                            </div>
                            <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank"
                               style="margin-left:auto;font-size:12px;color:#2E7D32;text-decoration:underline;">Lihat</a>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('registration.upload-proof') }}" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                            <div style="flex:1;min-width:200px;">
                                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf"
                                       style="width:100%;font-size:13px;color:#5D6C7B;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;">
                                <span style="font-size:11px;color:#8595A4;display:block;margin-top:4px;">JPG, PNG, PDF. Maks 2MB</span>
                            </div>
                            <button type="submit" class="btn-primary" style="height:40px;padding:0 20px;font-size:14px;flex-shrink:0;">Upload</button>
                        </div>
                    </form>
                    <p style="font-size:12px;color:#8595A4;margin-top:8px;">Atau konfirmasi ke admin: <strong>(0233) 123456</strong></p>
                </div>

                @elseif($step2Done && $registration->payment_proof)
                    <div style="margin-top:4px;">
                        <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank"
                           style="font-size:12px;color:#0064E0;text-decoration:none;">Lihat bukti bayar</a>
                    </div>
                @endif
            </div>

            {{-- Step 3 --}}
            <div style="position:relative;margin-bottom:24px;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;"
                     class="{{ $step3Done ? 'timeline-dot-done' : 'timeline-dot-wait' }}">
                    @if($step3Done)
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else 3 @endif
                </div>
                <div style="font-size:14px;font-weight:600;color:{{ $step3Done ? '#0A1317' : '#8595A4' }};">Pembayaran Dikonfirmasi</div>
                @if($step3Done && $registration->registration_number)
                    <div style="margin-top:10px;background:#EFF4FF;border:1px solid #DEE3E9;border-radius:12px;padding:14px 18px;display:inline-block;">
                        <div style="font-size:11px;color:#0064E0;font-weight:700;margin-bottom:4px;">NOMOR PENDAFTARAN RESMI</div>
                        <div style="font-size:22px;font-weight:700;color:#0A1317;font-family:monospace;letter-spacing:0.08em;">{{ $registration->registration_number }}</div>
                    </div>
                    <div style="font-size:12px;color:#8595A4;margin-top:8px;">Simpan nomor ini untuk keperluan administrasi.</div>
                @elseif(!$step3Done)
                    <div style="font-size:12px;color:#CED0D4;margin-top:2px;">Aktif setelah pembayaran dikonfirmasi admin.</div>
                @endif
            </div>

            {{-- Step 4 --}}
            <div style="position:relative;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;"
                     style="background:{{ $status === 'diterima' ? '#2E7D32' : ($status === 'ditolak' ? '#C62828' : ($status === 'perlu_revisi' ? '#E65100' : '#DEE3E9')) }};color:{{ $step4Done ? '#FFFFFF' : '#8595A4' }};">
                    @if($step4Done)
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else 4 @endif
                </div>
                <div style="font-size:14px;font-weight:600;color:{{ $step4Done ? '#0A1317' : '#8595A4' }};">Hasil Seleksi</div>
                @if($status === 'diterima')
                    <div style="background:#E8F5E9;border:1px solid #A5D6A7;border-radius:12px;padding:14px;margin-top:10px;">
                        <div style="font-weight:700;color:#2E7D32;font-size:15px;display:flex;align-items:center;gap:6px;">
                            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Selamat! Anda DITERIMA
                        </div>
                        <div style="font-size:13px;color:#388E3C;margin-top:4px;">Pantau informasi registrasi ulang dari PMB YPIB.</div>
                    </div>
                @elseif($status === 'ditolak')
                    <div style="background:#FFEBEE;border:1px solid #EF9A9A;border-radius:12px;padding:14px;margin-top:10px;">
                        <div style="font-weight:700;color:#C62828;font-size:15px;display:flex;align-items:center;gap:6px;">
                            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            Pendaftaran Ditolak
                        </div>
                        <div style="font-size:13px;color:#E53935;margin-top:4px;">Hubungi admin PMB untuk informasi lebih lanjut.</div>
                    </div>
                @elseif($status === 'perlu_revisi')
                    <div style="background:#FFF3E0;border:1px solid #FFCC02;border-radius:12px;padding:14px;margin-top:10px;">
                        <div style="font-weight:700;color:#E65100;font-size:15px;display:flex;align-items:center;gap:6px;">
                            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                            Perlu Revisi Data
                        </div>
                        <div style="font-size:13px;color:#BF360C;margin-top:4px;">Hubungi admin PMB untuk instruksi revisi.</div>
                    </div>
                @else
                    <div style="font-size:12px;color:#CED0D4;margin-top:2px;">Akan diumumkan setelah verifikasi selesai.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ringkasan Pendaftaran --}}
    <div class="pub-card">
        <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Ringkasan Pendaftaran</div>
        <div class="reg-summary-grid">
            <div><div style="font-size:12px;color:#8595A4;margin-bottom:3px;">Nama</div><div style="font-size:14px;font-weight:500;color:#0A1317;">{{ $registration->full_name }}</div></div>
            <div><div style="font-size:12px;color:#8595A4;margin-bottom:3px;">Tanggal Daftar</div><div style="font-size:14px;font-weight:500;color:#0A1317;">{{ $registration->created_at->isoFormat('D MMM Y') }}</div></div>
            <div><div style="font-size:12px;color:#8595A4;margin-bottom:3px;">Program Studi</div><div style="font-size:14px;font-weight:500;color:#0A1317;">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div></div>
        </div>
    </div>
    @endif

</div>
</div>
</section>
@endsection
