@extends('layouts.panitia')

@section('content')
<div class="mb-4">
    <a href="{{ route('panitia.dashboard') }}" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Scanner
    </a>
</div>

<div class="bg-red-50 rounded-2xl shadow-sm border border-red-200 overflow-hidden p-6 text-center">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 text-red-600 mb-4">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>
    
    <h2 class="text-xl font-bold text-red-900 mb-2">Status Tidak Valid</h2>
    <p class="text-red-700 mb-4">Pendaftar <strong class="font-semibold">{{ $registration->full_name }}</strong> ({{ $registration->registration_number }}) bukan di tahap interview saat ini.</p>
    
    <div class="inline-block px-4 py-2 bg-white rounded-lg border border-red-200 shadow-sm">
        <span class="text-sm text-gray-500 block mb-1">Status Saat Ini:</span>
        <span class="font-bold text-red-600">{{ $statusLabel }}</span>
    </div>
</div>
@endsection
