@extends('layouts.landing')
@section('title', 'Status Pendaftaran — PMB YPIB Majalengka')

@section('content')
<style>
    .status-page { padding:64px 0;background:#F1F4F7;min-height:calc(100vh - 64px); }
    .timeline-line { position:absolute;left:19px;top:0;bottom:0;width:2px;background:#DEE3E9;z-index:0; }
    .timeline-dot-done  { background:#082e8f;color:#FFFFFF;border:3px solid #FFFFFF;z-index:10; }
    .timeline-dot-active{ background:#E65100;color:#FFFFFF;border:3px solid #FFFFFF;z-index:10; }
    .timeline-dot-wait  { background:#DEE3E9;color:#8595A4;border:3px solid #FFFFFF;z-index:10; }
    .rekening-box { background:#FFF8E1;border:1px solid #FFD54F;border-radius:12px;padding:20px;margin-top:12px; }
    .reg-summary-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px 24px; }
    @media(max-width:480px){ .reg-summary-grid{ grid-template-columns:1fr; } }
</style>

<section class="status-page">
<div class="pub-container">
<div style="max-width:640px;margin:0 auto;">

    {{-- Page header --}}
    <div style="text-align:center;margin-bottom:32px;">
        <h1 style="font-size:26px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Status Pendaftaran</h1>
        <p style="font-size:14px;margin:0;" class="text-neutral-500">PMB YPIB Majalengka 2025/2026</p>
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
        <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
            <svg style="width:28px;height:28px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-primary-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
            </svg>
        </div>
        <h2 style="font-size:18px;font-weight:700;margin:0 0 8px 0;" class="text-neutral-900">Belum Ada Pendaftaran</h2>
        <p style="font-size:14px;margin:0 0 24px 0;" class="text-neutral-500">Anda belum mengisi formulir pendaftaran PMB YPIB.</p>
        <a href="{{ route('registration.create') }}" class="btn-primary inline-flex">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z"/></svg>
            Isi Formulir Sekarang
        </a>
    </div>

    @else
    @php
        $status     = $registration->status;
        $step2Active= in_array($status, ['menunggu_pembayaran','menunggu_konfirmasi']);
        $step2Done  = !in_array($status, ['menunggu_pembayaran','menunggu_konfirmasi']);
        $step3Done  = in_array($status, ['terdaftar','diterima','ditolak','perlu_revisi', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']);
        $step4Done  = in_array($status, ['diterima','ditolak','perlu_revisi', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']);
        $step5Active= in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang']);
        $step5Done  = in_array($status, ['daftar_ulang_selesai']);
    @endphp

    {{-- Timeline Card --}}
    <div class="pub-card" style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px;" class="text-neutral-400">Tahapan Pendaftaran</div>

        <div style="position:relative;padding-left:40px;">
            <div class="timeline-line"></div>

            {{-- Step 1 --}}
            <div style="position:relative;margin-bottom:24px;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;" class="timeline-dot-done">
                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </div>
                <div style="font-size:14px;font-weight:600;" class="text-neutral-900">Formulir Dikirim</div>
                <div style="font-size:12px;margin-top:2px;" class="text-neutral-400">{{ $registration->created_at->isoFormat('D MMMM Y, HH:mm') }}</div>
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
                <div style="font-size:14px;font-weight:600;color:{{ $step2Active ? '#E65100' : ($step2Done ? '#0A1317' : '#8595A4') }};display:flex;align-items:center;">
                    Pembayaran
                    @if($status === 'menunggu_konfirmasi')
                        <span style="background:#e6edfc;font-size:11px;font-weight:700;padding:2px 10px;border-radius:9999px;margin-left:8px;line-height:1.2;" class="text-primary-600">Menunggu Konfirmasi</span>
                    @elseif($status === 'menunggu_pembayaran')
                        <span style="background:#FFF3E0;color:#E65100;font-size:11px;font-weight:700;padding:2px 10px;border-radius:9999px;margin-left:8px;line-height:1.2;">Belum Dibayar</span>
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
                        <span>Bank</span><strong>{{ $registration->period->university_bank_name ?? '-' }}</strong>
                        <span>No. Rekening</span>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <strong style="font-family:monospace;">{{ $registration->period->university_bank_account ?? '-' }}</strong>
                            @if(!empty($registration->period->university_bank_account))
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $registration->period->university_bank_account }}').then(() => alert('Nomor rekening berhasil disalin!'))" style="background:none;border:none;padding:2px;cursor:pointer;color:#795548;display:flex;align-items:center;justify-content:center;transition:color 0.15s;" onmouseover="this.style.color='#082e8f'" onmouseout="this.style.color='#795548'" title="Salin nomor rekening">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                        <span>Atas Nama</span><strong>{{ $registration->period->university_bank_account_name ?? '-' }}</strong>
                        <span>Nominal</span><strong style="color:#BF360C;">Rp {{ number_format($registration->period->registration_fee ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Upload bukti --}}
                <div style="margin-top:12px;">
                    @if($registration->payment_proof)
                        <div style="display:flex;align-items:center;gap:10px;background:#E8F5E9;border:1px solid #A5D6A7;border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:10px;">
                            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-success-700"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                            <div>
                                <div style="font-weight:600;" class="text-success-700">Bukti sudah diunggah</div>
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
                                <label style="display:flex;align-items:center;gap:10px;border-radius:8px;padding:8px 12px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#F1F4F7'" onmouseout="this.style.background='#FFFFFF'" class="border-neutral-300 bg-white">
                                    <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-primary-600"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                    <span id="file-name" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="text-neutral-500">Pilih file bukti bayar...</span>
                                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'Pilih file bukti bayar...'; document.getElementById('file-name').style.color = '#0A1317';">
                                </label>
                            </div>
                            <button type="submit" class="btn-primary" style="height:40px;padding:0 20px;font-size:14px;flex-shrink:0;">Upload</button>
                        </div>
                        <div style="font-size:11px;margin-top:8px;line-height:1.5;" class="text-neutral-400">
                            <div>Format file: JPG, PNG, PDF. Maks: 2MB.</div>
                            <div style="margin-top:2px;">Atau konfirmasi ke admin: <strong>{{ $registration->period->admin_whatsapp ?? '(0233) 123456' }}</strong></div>
                        </div>
                    </form>
                </div>

                @elseif($step2Done && $registration->payment_proof)
                    <div style="margin-top:4px;">
                        <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank"
                           style="font-size:12px;color:#082e8f;text-decoration:none;">Lihat bukti bayar</a>
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
                <div style="font-size:14px;font-weight:600;" class="text-neutral-900">Pembayaran Dikonfirmasi</div>
                @if($step3Done && $registration->registration_number)
                    <div class="mt-2.5 bg-primary/10 border border-[#DEE3E9] rounded-xl px-[18px] py-[14px] inline-block">
                        <div style="font-size:11px;font-weight:700;margin-bottom:4px;" class="text-primary-600">NOMOR PENDAFTARAN RESMI</div>
                        <div style="font-size:22px;font-weight:700;font-family:monospace;letter-spacing:0.08em;" class="text-neutral-900">{{ $registration->registration_number }}</div>
                    </div>
                    <div style="font-size:12px;margin-top:8px;" class="text-neutral-400">Simpan nomor ini untuk keperluan administrasi.</div>
                @elseif(!$step3Done)
                    <div style="font-size:12px;margin-top:2px;" class="text-neutral-300">Aktif setelah pembayaran dikonfirmasi admin.</div>
                @endif
            </div>

            {{-- Step 4 --}}
            <div style="position:relative;margin-bottom:24px;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;z-index:10;" class="border-white bg-success-700 text-white">
                    @if($step4Done)
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else 4 @endif
                </div>
                <div style="font-size:14px;font-weight:600;" class="text-neutral-900">Hasil Seleksi</div>
                @if($step4Done && $status !== 'ditolak' && $status !== 'perlu_revisi')
                    <div style="background:#E8F5E9;border:1px solid #A5D6A7;border-radius:12px;padding:14px;margin-top:10px;">
                        <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:6px;" class="text-success-700">
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
                    <div style="font-size:12px;margin-top:2px;" class="text-neutral-300">Akan diumumkan setelah verifikasi selesai.</div>
                @endif
            </div>

            {{-- Step 5 --}}
            <div style="position:relative;">
                <div style="position:absolute;left:-40px;top:0;width:38px;height:38px;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;z-index:10;" class="border-white bg-primary-600 text-white">
                    @if($step5Done)
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @else 5 @endif
                </div>
                <div style="font-size:14px;font-weight:600;color:{{ $step5Active ? '#E65100' : ($step5Done ? '#0A1317' : '#8595A4') }};display:flex;align-items:center;">
                    Daftar Ulang
                    @if($status === 'menunggu_konfirmasi_daftar_ulang' && $registration->re_registration_payment_proof)
                        <span style="background:#e6edfc;font-size:11px;font-weight:700;padding:2px 10px;border-radius:9999px;margin-left:8px;line-height:1.2;" class="text-primary-600">Menunggu Konfirmasi</span>
                    @elseif($status === 'diterima')
                        <span style="background:#FFF3E0;color:#E65100;font-size:11px;font-weight:700;padding:2px 10px;border-radius:9999px;margin-left:8px;line-height:1.2;">Belum Dibayar</span>
                    @endif
                </div>

                @if($step5Active && !$registration->re_registration_payment_proof)
                {{-- Info rekening --}}
                <div class="rekening-box">
                    <div style="font-size:13px;font-weight:700;color:#795548;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        Informasi Pembayaran Daftar Ulang
                    </div>
                    <div style="display:grid;grid-template-columns:100px 1fr;gap:8px;font-size:13px;color:#5D4037;">
                        <span>Bank</span><strong>{{ $registration->period->university_bank_name ?? '-' }}</strong>
                        <span>No. Rekening</span>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <strong style="font-family:monospace;">{{ $registration->period->university_bank_account ?? '-' }}</strong>
                            @if(!empty($registration->period->university_bank_account))
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $registration->period->university_bank_account }}').then(() => alert('Nomor rekening berhasil disalin!'))" style="background:none;border:none;padding:2px;cursor:pointer;color:#795548;display:flex;align-items:center;justify-content:center;transition:color 0.15s;" onmouseover="this.style.color='#082e8f'" onmouseout="this.style.color='#795548'" title="Salin nomor rekening">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                        <span>Atas Nama</span><strong>{{ $registration->period->university_bank_account_name ?? '-' }}</strong>
                        <span>Nominal</span><strong style="color:#BF360C;font-size:16px;">Rp {{ number_format($registration->firstChoiceProgram?->re_registration_fee ?? 25000000, 0, ',', '.') }}</strong>
                    </div>

                    @if($registration->firstChoiceProgram && !empty($registration->firstChoiceProgram->re_registration_fee_details) && count($registration->firstChoiceProgram->re_registration_fee_details) > 0)
                    <div style="margin-top:16px; border-top:1px dashed #FFD54F; padding-top:12px;">
                        <div style="font-size:12px;font-weight:700;color:#795548;margin-bottom:8px;">Rincian Tagihan:</div>
                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                            <tbody>
                                @foreach($registration->firstChoiceProgram->re_registration_fee_details as $detail)
                                <tr>
                                    <td style="padding:4px 0; color:#5D4037;">{{ $detail['name'] }}</td>
                                    <td style="padding:4px 0; text-align:right; font-weight:600; color:#5D4037;">Rp {{ number_format($detail['amount'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- Upload bukti --}}
                <div style="margin-top:12px;">
                    <form method="POST" action="{{ route('registration.upload-re-registration-proof') }}" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                            <div style="flex:1;min-width:200px;">
                                <label style="display:flex;align-items:center;gap:10px;border-radius:8px;padding:8px 12px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#F1F4F7'" onmouseout="this.style.background='#FFFFFF'" class="border-neutral-300 bg-white">
                                    <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-primary-600"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                    <span id="file-name-re" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="text-neutral-500">Pilih file bukti bayar...</span>
                                    <input type="file" name="re_registration_payment_proof" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="document.getElementById('file-name-re').textContent = this.files[0] ? this.files[0].name : 'Pilih file bukti bayar...'; document.getElementById('file-name-re').style.color = '#0A1317';">
                                </label>
                            </div>
                            <button type="submit" class="btn-primary" style="height:40px;padding:0 20px;font-size:14px;flex-shrink:0;">Upload</button>
                        </div>
                        <div style="font-size:11px;margin-top:8px;line-height:1.5;" class="text-neutral-400">
                            <div>Format file: JPG, PNG, PDF. Maks: 2MB.</div>
                        </div>
                    </form>
                </div>

                @elseif(($step5Active || $step5Done) && $registration->re_registration_payment_proof)
                    <div style="display:flex;align-items:center;gap:10px;background:#E8F5E9;border:1px solid #A5D6A7;border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:10px;margin-top:12px;">
                        <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-success-700"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                        <div>
                            <div style="font-weight:600;" class="text-success-700">Bukti Daftar Ulang sudah diunggah</div>
                            <div style="font-size:11px;color:#388E3C;">{{ basename($registration->re_registration_payment_proof) }}</div>
                        </div>
                        <a href="{{ Storage::url($registration->re_registration_payment_proof) }}" target="_blank"
                           style="margin-left:auto;font-size:12px;color:#2E7D32;text-decoration:underline;">Lihat</a>
                    </div>
                @elseif(!$step5Done)
                    <div style="font-size:12px;margin-top:2px;" class="text-neutral-300">Akan aktif setelah Anda diterima.</div>
                @else
                    <div style="font-size:12px;font-weight:600;margin-top:4px;" class="text-success-700">Daftar ulang berhasil dikonfirmasi.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ringkasan Pendaftaran --}}
    <div class="pub-card">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Ringkasan Pendaftaran</div>
        <div class="reg-summary-grid">
            <div><div style="font-size:12px;margin-bottom:3px;" class="text-neutral-400">Nama</div><div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->full_name }}</div></div>
            <div><div style="font-size:12px;margin-bottom:3px;" class="text-neutral-400">Tanggal Daftar</div><div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->created_at->isoFormat('D MMM Y') }}</div></div>
            <div><div style="font-size:12px;margin-bottom:3px;" class="text-neutral-400">Program Studi</div><div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div></div>
        </div>
    </div>
    @endif

</div>
</div>
</section>
@endsection
