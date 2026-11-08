@extends('layouts.landing')
@section('title', 'Terima Kasih — PMB YPIB Majalengka')

@section('content')
<section style="padding:48px 0;min-height:calc(100vh - 64px);" class="bg-neutral-100">
<div class="pub-container">
<div style="max-width:560px;margin:0 auto;">

    @if(session('error'))
        <div class="pub-flash-error" style="margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    <div class="pub-card" style="padding:24px;">
        <h1 style="font-size:20px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Terima kasih, {{ $prospect->full_name }}</h1>
        <p style="font-size:14px;margin:0 0 20px 0;" class="text-neutral-500">Minatmu sudah tercatat. Chat admin untuk info jadwal dan biaya pendaftaran.</p>

        <div style="font-size:13px;font-weight:700;margin-bottom:4px;" class="text-neutral-900">Link daftar</div>
        <p style="font-size:13px;margin:0 0 8px 0;" class="text-neutral-500">Simpan. Pakai link ini saat kamu siap mendaftar.</p>
        <div style="border-radius:10px;padding:12px 14px;word-break:break-all;font-family:monospace;font-size:13px;" class="bg-neutral-100 border border-neutral-200 text-neutral-900">
            {{ $linkUrl }}
        </div>

        @if($hasWa)
            <form method="POST" action="{{ route('prospect.whatsapp') }}" style="margin-top:20px;">
                @csrf
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">
                    Chat Admin di WhatsApp
                </button>
            </form>
        @else
            <p style="font-size:13px;margin:20px 0 0 0;" class="text-neutral-500">
                Nomor WhatsApp admin belum tersedia. Simpan link di atas dan hubungi panitia kegiatan.
            </p>
        @endif
    </div>

    <p style="font-size:13px;margin:16px 0 0 0;text-align:center;">
        <a href="{{ route('landing') }}" class="text-primary-600" style="font-weight:600;text-decoration:none;">Kembali ke beranda</a>
    </p>
</div>
</div>
</section>
@endsection
