@extends('layouts.admin')
@section('title', 'Data Pendaftar — Admin PMB YPIB')
@section('page-title', 'Data Pendaftar')

@section('content')

{{-- ============================================================ --}}
{{-- PAGE HEADER --}}
{{-- ============================================================ --}}
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Data Pendaftar</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola dan pantau seluruh proses penerimaan mahasiswa baru.</p>
    </div>
</div>

{{-- ============================================================ --}}
{{-- METRIC SUMMARY CARDS --}}
{{-- ============================================================ --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $totalRegistrations }}</div>
            <div class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Pendaftar</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            @php
                $pendaftarLunas = $totalRegistrations - ($statusCounts['menunggu_pembayaran'] ?? 0) - ($statusCounts['menunggu_konfirmasi'] ?? 0);
            @endphp
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $pendaftarLunas }}</div>
            <div class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Pendaftar Lunas</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            @php
                $uploadBerkas = ($statusCounts['diterima'] ?? 0) + ($statusCounts['menunggu_konfirmasi_daftar_ulang'] ?? 0) + ($statusCounts['daftar_ulang_selesai'] ?? 0) + ($statusCounts['ditolak'] ?? 0);
            @endphp
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $uploadBerkas }}</div>
            <div class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Upload Berkas</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-primary-600 leading-none mb-2">{{ $statusCounts['daftar_ulang_selesai'] ?? 0 }}</div>
            <div class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Daftar Ulang</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
        </div>
    </div>
</div>

<livewire:admin.registration-list />

@endsection
