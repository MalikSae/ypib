@extends('layouts.landing')
@section('title', 'Form Pendaftaran — PMB YPIB Majalengka')

@section('content')
<section style="padding:64px 0;background:#F1F4F7;min-height:calc(100vh - 64px);">
<div class="pub-container">
<div style="max-width:800px;margin:0 auto;">

    <div style="text-align:center;margin-bottom:36px;">
        <h1 style="font-size:26px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Form Pendaftaran Mahasiswa Baru</h1>
        @if(isset($period))
            <p style="font-size:14px;color:#5D6C7B;margin:0;">{{ $period->name }} &mdash; Penutupan: {{ \Carbon\Carbon::parse($period->close_date)->isoFormat('D MMMM Y') }}</p>
        @endif
    </div>

    @if(!auth()->check())
        <div style="background:#FFFFFF;border:1px solid #DEE3E9;border-radius:16px;padding:40px 24px;text-align:center;max-width:480px;margin:0 auto;">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg style="width:28px;height:28px;color:#082e8f;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <h2 style="font-size:18px;font-weight:700;color:#0A1317;margin:0 0 8px 0;">Login Diperlukan</h2>
            <p style="font-size:14px;color:#5D6C7B;margin:0 0 24px 0;">Silakan login atau buat akun terlebih dahulu sebelum mengisi formulir pendaftaran.</p>
            <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                <a href="{{ route('login') }}" class="btn-primary" style="font-size:14px;padding:10px 24px;">Masuk</a>
                <a href="{{ route('register') }}" class="btn-secondary" style="font-size:14px;padding:10px 24px;border-color:#DEE3E9;color:#444950;">Buat Akun</a>
            </div>
        </div>
    @else
        @livewire('registration-form')
    @endif

</div>
</div>
</section>
@endsection
