@extends('layouts.landing')
@section('title', 'Instruksi Interview — PMB YPIB Majalengka')
@section('content')

<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
<div class="pub-container">
<div class="max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('registration.status') }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Status Pendaftaran
        </a>
    </div>

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-neutral-900 mb-1">Instruksi Interview</h1>
        <p class="text-sm text-neutral-500">Lihat jadwal dan tunjukkan QR code Anda ke panitia</p>
    </div>

    <div class="pub-card text-center p-8">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 text-primary-600 mb-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-neutral-900 mb-2">Jadwal Interview & Verifikasi</h3>
            <p class="text-neutral-600 max-w-lg mx-auto">
                Silakan datang ke kampus Universitas YPIB Majalengka. 
                Tunjukkan QR code di bawah ini kepada panitia PMB saat tiba.
            </p>
        </div>

        <div class="inline-block p-4 bg-white border-2 border-neutral-100 rounded-xl shadow-sm mb-4">
            {!! QrCode::size(220)->generate($registration->registration_number) !!}
        </div>

        <div class="mt-2 text-neutral-500">
            <p class="text-sm">Nomor Pendaftaran:</p>
            <p class="text-xl font-bold text-neutral-900 tracking-wider">{{ $registration->registration_number }}</p>
        </div>

        @php
            $waNumber = $registration->period->admin_whatsapp ?? null;
            $waNumberFormatted = $waNumber ? '62' . ltrim($waNumber, '0') : null;
        @endphp

        @if($waNumberFormatted)
        <div class="mt-6 pt-6 border-t border-neutral-200 text-center">
            <p class="text-xs text-neutral-500 mb-3">Ada pertanyaan seputar jadwal interview?</p>
            <a href="https://wa.me/{{ $waNumberFormatted }}?text={{ urlencode('Halo, saya ingin bertanya tentang jadwal interview PMB. Nomor pendaftaran saya: ' . $registration->registration_number) }}"
               target="_blank"
               class="btn-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.489 1.2.532 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.663.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824z"/>
                    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.821.487 3.53 1.338 5L2 22l5.216-1.317C8.641 21.502 10.276 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.5c-1.606 0-3.09-.474-4.339-1.286l-.311-.196-3.229.815.848-3.148-.203-.323C3.474 15.121 3 13.606 3 12c0-4.963 4.037-9 9-9s9 4.037 9 9-4.037 9-9 9z"/>
                </svg>
                Hubungi Panitia
            </a>
        </div>
        @endif
    </div>

</div>
</div>
</section>

@endsection
