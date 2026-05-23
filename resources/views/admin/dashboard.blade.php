@extends('layouts.admin')
@section('title', 'Dashboard — Admin PMB YPIB')
@section('page-title', 'Dashboard')

@php
$badgeMap = [
    'menunggu_pembayaran'              => ['label' => 'Belum Bayar',         'class' => 'bg-neutral-100 text-neutral-600'],
    'menunggu_konfirmasi'              => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-neutral-100 text-neutral-600'],
    'terdaftar'                        => ['label' => 'Terdaftar',           'class' => 'bg-primary-50 text-primary-700'],
    'diterima'                         => ['label' => 'Diterima',            'class' => 'bg-primary-100 text-primary-800'],
    'ditolak'                          => ['label' => 'Ditolak',             'class' => 'bg-neutral-200 text-neutral-600'],
    'perlu_revisi'                     => ['label' => 'Perlu Revisi',        'class' => 'bg-neutral-100 text-neutral-600'],
    'menunggu_konfirmasi_daftar_ulang' => ['label' => 'Konfirmasi Daftar Ulang', 'class' => 'bg-neutral-100 text-neutral-600'],
    'daftar_ulang_selesai'             => ['label' => 'Selesai Daftar Ulang',    'class' => 'bg-primary-50 text-primary-700'],
];
@endphp

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-xl font-bold text-neutral-900">Dashboard</h1>
        <p class="text-sm text-neutral-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <a href="{{ route('admin.registrations.index') }}" class="decoration-none">
        <x-button color="primary" size="sm">Lihat Semua Pendaftar</x-button>
    </a>
</div>

{{-- 4 KPI Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $stats['total'] }}</div>
            <div class="text-xs text-neutral-400 font-medium uppercase tracking-wide">Total Pendaftar</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $stats['pending'] }}</div>
            <div class="text-xs text-neutral-400 font-medium uppercase tracking-wide">Proses Pembayaran</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-neutral-900 leading-none mb-2">{{ $stats['terdaftar'] }}</div>
            <div class="text-xs text-neutral-400 font-medium uppercase tracking-wide">Terdaftar</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            <div class="text-3xl font-extrabold text-primary-600 leading-none mb-2">{{ $stats['diterima'] }}</div>
            <div class="text-xs text-neutral-400 font-medium uppercase tracking-wide">Diterima / Selesai</div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
        </div>
    </div>

</div>

{{-- Pendaftar Terbaru --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-neutral-900">Pendaftar Terbaru</h2>
        <a href="{{ route('admin.registrations.index') }}"
           class="text-xs font-medium text-primary-600 hover:text-primary-700 decoration-none transition-colors">
            Lihat semua →
        </a>
    </div>
    <table class="min-w-full">
        <thead class="bg-neutral-50 border-b border-neutral-100">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pendaftar</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">No. Daftar</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Waktu</th>
                <th class="px-5 py-3 w-10"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100">
            @forelse($recent as $reg)
                @php
                    $badge = $badgeMap[$reg->status] ?? ['label' => $reg->status, 'class' => 'bg-neutral-100 text-neutral-600'];
                @endphp
                <tr class="hover:bg-neutral-50 transition-colors group">
                    <td class="px-5 py-3.5">
                        <span class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $reg->full_name }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs text-neutral-500">{{ $reg->registration_number ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-neutral-600">{{ Str::limit($reg->firstChoiceProgram?->name ?? '—', 28) }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-neutral-400">{{ $reg->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <a href="{{ route('admin.registrations.show', $reg->id) }}"
                           class="decoration-none inline-flex items-center justify-center w-7 h-7 rounded-lg text-neutral-400 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                           title="Detail">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-sm text-neutral-400">Belum ada data pendaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
