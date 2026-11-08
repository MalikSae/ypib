@extends('layouts.landing')
@section('title', 'Dashboard Afiliasi — PMB YPIB Majalengka')

@section('content')
<style>
    .ref-stat-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px; }
    @media(max-width:767px){ 
        .ref-stat-grid{ grid-template-columns:1fr 1fr; } 
        .onboarding-gate-header { flex-direction:column; align-items:flex-start !important; gap:12px !important; }
    }
    @media(max-width:400px){ .ref-stat-grid{ grid-template-columns:1fr; } }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; }
    .ref-action-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px; }
    .ref-action-btn {
        height:44px;border-radius:10px;font-size:14px;font-weight:600;
        display:flex;align-items:center;justify-content:center;gap:8px;
        cursor:pointer;transition:background 0.15s,border-color 0.15s;
        text-decoration:none;
    }
    .ref-btn-copy {
        border:1.5px solid #CED0D4;background:#FFFFFF;color:#0A1317;
    }
    .ref-btn-copy:hover { background:#F1F4F7; }
    .ref-btn-wa { background:#25D366;color:#FFFFFF;border:none; }
    .ref-btn-wa:hover { background:#1ebe5a; }
    .ref-btn-copied { background:#E8F5E9 !important;border-color:#A5D6A7 !important;color:#2E7D32 !important; }
</style>

<section style="padding:48px 0;min-height:calc(100vh - 64px);" class="bg-neutral-100">
<div class="pub-container">
<div style="max-width:960px;margin:0 auto;">

    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <h1 style="font-size:24px;font-weight:700;margin:0 0 4px 0;" class="text-neutral-900">Dashboard Afiliasi</h1>
        <p style="font-size:14px;margin:0;" class="text-neutral-500">Halo, {{ auth()->user()->name }}! Pantau performa referralmu di sini.</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="pub-flash-success">
            <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(empty($referrer->bank_name) || empty($referrer->bank_account_number) || empty($referrer->bank_account_name))
        <div class="pub-card border-primary-600" style="margin-bottom:24px;background:#f8faff;">
            <div class="onboarding-gate-header" style="display:flex;align-items:flex-start;gap:16px;">
                <div style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;" class="bg-primary-600">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
                </div>
                <div style="flex:1;">
                    <h2 style="font-size:18px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Lengkapi Data Rekening Bank</h2>
                    <p style="font-size:14px;margin:0 0 16px 0;" class="text-neutral-500">Untuk mulai membagikan link referral dan mendapatkan komisi, silakan isi data rekening pencairan Anda di bawah ini.</p>
                    
                    <form method="POST" action="{{ route('referrer.bank.update') }}">
                        @csrf
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:16px;">
                            <div>
                                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nama Bank</label>
                                <select name="bank_name" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="BCA">BCA</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="BSI">BSI</option>
                                    <option value="CIMB Niaga">CIMB Niaga</option>
                                    <option value="DANA">DANA (E-Wallet)</option>
                                    <option value="OVO">OVO (E-Wallet)</option>
                                    <option value="GoPay">GoPay (E-Wallet)</option>
                                    <option value="ShopeePay">ShopeePay (E-Wallet)</option>
                                    <option value="Lainnya">Lainnya...</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nomor Rekening</label>
                                <input type="text" name="bank_account_number" placeholder="Contoh: 1234567890" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                            </div>
                            <div>
                                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Atas Nama</label>
                                <input type="text" name="bank_account_name" placeholder="Sesuai buku tabungan" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary" style="height:40px;font-size:14px;border-radius:8px;padding:0 20px;">
                            Simpan Rekening
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div style="background:#E8F5E9;border:1px solid #A5D6A7;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="text-success-700"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
                <div style="font-size:14px;" class="text-neutral-900">
                    Rekening Pencairan: <strong class="text-success-700">{{ $referrer->bank_name }} - {{ $referrer->bank_account_number }} (a.n {{ $referrer->bank_account_name }})</strong>
                </div>
            </div>
            <button onclick="document.getElementById('edit_bank_modal').style.display='block'" style="background:none;border:none;font-size:13px;font-weight:600;cursor:pointer;padding:0;text-decoration:underline;" class="text-primary-600">
                Edit Rekening
            </button>
        </div>

        {{-- Edit Bank Modal --}}
        <div id="edit_bank_modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(10,19,23,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
            <div class="pub-card" style="max-width:500px;width:100%;margin:50px auto;position:relative;">
                <button onclick="document.getElementById('edit_bank_modal').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;cursor:pointer;" class="text-neutral-400">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
                <h3 style="font-size:18px;font-weight:700;margin:0 0 16px 0;" class="text-neutral-900">Ubah Data Rekening</h3>
                <form method="POST" action="{{ route('referrer.bank.update') }}">
                    @csrf
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nama Bank</label>
                        <select name="bank_name" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                            @foreach(['BCA','Mandiri','BNI','BRI','BSI','CIMB Niaga','DANA','OVO','GoPay','ShopeePay','Lainnya'] as $bank)
                                <option value="{{ $bank }}" {{ $referrer->bank_name === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" value="{{ $referrer->bank_account_number }}" placeholder="Contoh: 1234567890" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                    </div>
                    <div style="margin-bottom:24px;">
                        <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Atas Nama</label>
                        <input type="text" name="bank_account_name" value="{{ $referrer->bank_account_name }}" placeholder="Sesuai buku tabungan" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;height:44px;">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

    {{-- STAT CARDS --}}
    @php
    $statCards = [
        ['label'=>'Total Klik', 'value'=>$stats['total_clicks'], 'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672Zm-7.518-.267A8.25 8.25 0 1 1 20.25 10.5M8.288 14.212A5.25 5.25 0 1 1 17.25 10.5"/>', 'color'=>'#082e8f','bg'=>'#e6edfc'],
        ['label'=>'Konversi',   'value'=>$stats['total_conversions'], 'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>', 'color'=>'#2E7D32','bg'=>'#E8F5E9'],
        ['label'=>'Total Reward', 'value'=>'Rp '.number_format($stats['total_rewards'],0,',','.'), 'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>', 'color'=>'#082e8f','bg'=>'#e6edfc'],
        ['label'=>'Reward Dicairkan', 'value'=>'Rp '.number_format($stats['disbursed_rewards'],0,',','.'), 'svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>', 'color'=>'#082e8f','bg'=>'#e6edfc'],
    ];
    @endphp
    <div class="ref-stat-grid">
        @foreach($statCards as $card)
        <div style="border-radius:14px;padding:20px;" class="bg-white border-neutral-200">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $card['bg'] }};display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:18px;height:18px;color:{{ $card['color'] }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $card['svg'] !!}</svg>
            </div>
            <div style="font-size:20px;font-weight:700;" class="text-neutral-900">{{ $card['value'] }}</div>
            <div style="font-size:12px;margin-top:2px;" class="text-neutral-400">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- LINK REFERRAL: semua link (pendaftaran per kampus, link umum, form minat) dalam accordion --}}
    <div class="pub-card" style="margin-bottom:16px;padding:0;overflow:hidden;" x-data="{ open: 0 }">
        <div style="padding:16px 20px 12px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;" class="text-neutral-400">Link Referral</div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ $referrer->isActive() ? '#2E7D32' : '#C62828' }};display:inline-block;"></span>
                <span style="font-size:12px;" class="text-neutral-400">{{ $referrer->isActive() ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
        </div>

        @foreach($shareLinks as $i => $link)
        <div style="border-top:1px solid #EEF1F5;" x-data="{ copied: false }">
            <button type="button" x-on:click="open = (open === {{ $i }} ? null : {{ $i }})"
                    :aria-expanded="(open === {{ $i }}).toString()" aria-controls="ref-link-{{ $i }}"
                    style="width:100%;display:flex;align-items:center;gap:12px;padding:14px 20px;background:none;border:none;cursor:pointer;text-align:left;">
                {{-- Badge selalu di baris sendiri di atas judul, supaya posisinya sama walau judul panjang. --}}
                <span style="flex:1;min-width:0;">
                    <span style="display:inline-block;font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px;white-space:nowrap;margin-bottom:4px;"
                          class="{{ $link['tag'] === 'Form minat' ? 'bg-primary-50 text-primary-700' : 'bg-neutral-100 text-neutral-600' }}">{{ $link['tag'] }}</span>
                    <span style="display:block;font-size:14px;font-weight:700;" class="text-neutral-900">{{ $link['label'] }}</span>
                    <span style="display:block;font-size:12px;margin-top:2px;" class="text-neutral-400">{{ $link['hint'] }}</span>
                </span>
                {{-- Ukuran lewat class, rotasi lewat :class. :style akan menimpa style bawaan (termasuk ukuran). --}}
                <svg class="w-5 h-5 shrink-0 text-neutral-400 transition-transform duration-150 motion-reduce:transition-none"
                     :class="open === {{ $i }} ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <div id="ref-link-{{ $i }}" x-show="open === {{ $i }}" x-cloak style="padding:0 20px 16px 20px;">
                <div style="border-radius:10px;padding:10px 12px;font-size:13px;font-family:monospace;word-break:break-all;line-height:1.6;user-select:all;"
                     class="bg-neutral-100 text-neutral-900">{{ $link['url'] }}</div>
                <div class="ref-action-grid">
                    <button type="button"
                        x-on:click="refCopy({{ \Illuminate\Support\Js::from($link['url']) }}).then(() => { copied = true; setTimeout(() => copied = false, 2500); })"
                        class="ref-action-btn ref-btn-copy" :class="copied ? 'ref-btn-copied' : ''">
                        <span x-show="!copied">Salin Link</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                    </button>
                    <a href="https://wa.me/?text={{ urlencode($link['share'] . $link['url']) }}" target="_blank" rel="noopener" class="ref-action-btn ref-btn-wa">Bagikan WA</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        // Salin ke clipboard. Clipboard API hanya tersedia di HTTPS; di HTTP pakai cara lama.
        window.refCopy = window.refCopy || function (text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            const area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            document.body.removeChild(area);
            return Promise.resolve();
        };
    </script>
    @endif

    {{-- FORM MINAT (prospek) --}}
    @include('referrer._prospects')

    {{-- RIWAYAT PENDAFTAR --}}
    <div style="border-radius:16px;overflow:hidden;" class="bg-white border-neutral-200">
        <div style="padding:16px 24px;display:flex;align-items:center;gap:8px;" class="border-b-neutral-200">
            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-neutral-500"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            <span style="font-size:15px;font-weight:700;" class="text-neutral-900">Riwayat Pendaftar via Linkmu</span>
        </div>

        @if($registrations->isEmpty())
            <div style="padding:48px 24px;text-align:center;">
                <div class="w-12 h-12 rounded-[14px] bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-primary-600"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614"/></svg>
                </div>
                <p style="font-size:14px;margin:0 0 4px 0;" class="text-neutral-400">Belum ada pendaftar via link referralmu.</p>
                <p style="font-size:13px;margin:0;" class="text-neutral-300">Bagikan linkmu sekarang!</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="table-nowrap" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr class="bg-neutral-100">
                            <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;" class="text-neutral-400">Tgl</th>
                            <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;" class="text-neutral-400">Nama</th>
                            <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;" class="text-neutral-400">Prodi</th>
                            <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;" class="text-neutral-400">Reward</th>
                            <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;" class="text-neutral-400">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $reg)
                        @php
                        $rw = [
                            'pending'   => ['Pending',   '#FFF3E0', '#E65100'],
                            'approved'  => ['Siap Cair', '#E8F5E9', '#2E7D32'],
                            'disbursed' => ['Dicairkan', '#e6edfc', '#082e8f'],
                        ];
                        @endphp
                        <tr style="transition:background 0.12s;"
                            onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''" class="border-b-neutral-200">
                            <td style="padding:14px 20px;font-size:13px;white-space:nowrap;" class="text-neutral-400">
                                {{ $reg->created_at->format('d/m/Y') }}
                            </td>
                            <td style="padding:14px 20px;">
                                <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $reg->full_name }}</div>
                                @if($reg->registration_number)
                                    <div style="font-size:11px;font-family:monospace;margin-top:2px;" class="text-neutral-400">{{ $reg->registration_number }}</div>
                                @endif
                            </td>
                            <td style="padding:14px 20px;font-size:13px;" class="text-neutral-500">{{ $reg->firstChoiceProgram?->name ?? '—' }}</td>
                            <td style="padding:14px 20px;font-size:14px;font-weight:700;" class="text-neutral-900">
                                @if($reg->rewards && $reg->rewards->isNotEmpty())
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($reg->rewards as $reward)
                                        <div>
                                            <span style="font-size:10px;display:block;font-weight:600;text-transform:uppercase;" class="text-neutral-400">
                                                {{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar Ulang' }}
                                            </span>
                                            Rp {{ number_format($reward->amount, 0, ',', '.') }}
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-neutral-300">—</span>
                                @endif
                            </td>
                            <td style="padding:14px 20px;">
                                @if($reg->rewards && $reg->rewards->isNotEmpty())
                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($reg->rewards as $reward)
                                        @php $rs = $rw[$reward->status] ?? ['—', '#F1F4F7', '#5D6C7B']; @endphp
                                        <div>
                                            <span style="background:{{ $rs[1] }};color:{{ $rs[2] }};font-size:12px;font-weight:700;padding:4px 10px;border-radius:9999px;display:inline-block;">
                                                {{ $rs[0] }}
                                            </span>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span style="font-size:13px;" class="text-neutral-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
</div>
</section>
@endsection
