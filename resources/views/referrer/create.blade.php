@extends('layouts.landing')
@section('title', 'Daftar Jadi Afiliasi — PMB YPIB Majalengka')

@section('content')
<section style="padding:64px 0;background:#F1F4F7;min-height:calc(100vh - 64px);">
<div class="pub-container">
<div style="max-width:560px;margin:0 auto;">

    <div style="text-align:center;margin-bottom:32px;">
        <div style="width:56px;height:56px;border-radius:16px;background:#EFF4FF;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:28px;height:28px;color:#0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
            </svg>
        </div>
        <h1 style="font-size:26px;font-weight:700;color:#0A1317;margin:0 0 8px 0;">Daftar Jadi Afiliasi PMB YPIB</h1>
        <p style="font-size:15px;color:#5D6C7B;margin:0;">Bagikan link unikmu, dapatkan reward untuk setiap pendaftar yang berhasil</p>
    </div>

    {{-- Info akun --}}
    <div class="pub-card" style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Informasi Akunmu</div>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
            @php
            $accountItems = [
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>', 'label'=>'Nama', 'value'=>auth()->user()->name],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>', 'label'=>'Email', 'value'=>auth()->user()->email],
                ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>', 'label'=>'Nomor HP', 'value'=>auth()->user()->phone ?? '—'],
            ];
            @endphp
            @foreach($accountItems as $item)
            <div style="display:flex;align-items:center;gap:12px;background:#F1F4F7;border-radius:10px;padding:12px 14px;">
                <svg style="width:18px;height:18px;color:#5D6C7B;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['svg'] !!}</svg>
                <div>
                    <div style="font-size:11px;color:#8595A4;">{{ $item['label'] }}</div>
                    <div style="font-size:14px;font-weight:500;color:#0A1317;">{{ $item['value'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Reward info --}}
        <div style="background:linear-gradient(135deg,#0064E0,#0457CB);border-radius:14px;padding:20px;margin-bottom:20px;">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px;">Reward Per Konversi</div>
            <div style="font-size:32px;font-weight:700;color:#FFFFFF;">Rp 50.000</div>
            <p style="font-size:13px;color:rgba(255,255,255,0.75);margin:8px 0 0 0;line-height:1.5;">
                Kamu mendapat <strong style="color:#FFFFFF;">link unik</strong> untuk dibagikan. Setiap pendaftar yang bayar via linkmu = reward <strong style="color:#FFFFFF;">Rp 50.000</strong>.
            </p>
        </div>

        <form method="POST" action="{{ route('referrer.store') }}">
            @csrf
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;height:48px;font-size:15px;">
                <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7Z"/></svg>
                Aktifkan Akun Afiliasi Saya
            </button>
        </form>
    </div>

    {{-- Cara kerja --}}
    <div class="pub-card">
        <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Cara Kerja Sistem Referral</div>
        @php
        $steps = [
            ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>', 'title'=>'Dapatkan Link Unik','desc'=>'Setelah mendaftar, kamu mendapat link unik: ypib.test/ref/REF-XXXXXX'],
            ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/>', 'title'=>'Bagikan Link','desc'=>'Share ke teman, keluarga, atau media sosial'],
            ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>', 'title'=>'Pendaftar Bayar','desc'=>'Orang yang klik linkmu berhasil membayar biaya pendaftaran'],
            ['svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>', 'title'=>'Reward Cair','desc'=>'Kamu mendapat reward Rp 50.000 per konversi berhasil'],
        ];
        @endphp
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($steps as $i => $step)
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#EFF4FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:18px;height:18px;color:#0064E0;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $step['svg'] !!}</svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:600;color:#0A1317;">{{ $step['title'] }}</div>
                    <div style="font-size:13px;color:#5D6C7B;margin-top:2px;">{{ $step['desc'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
</div>
</section>
@endsection
