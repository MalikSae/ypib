@extends('layouts.admin')
@section('title', 'Rekap Pendaftaran — Admin PMB YPIB')
@section('page-title', 'Rekap Pendaftaran')

@section('content')

{{-- ============================================================ --}}
{{-- PAGE HEADER --}}
{{-- ============================================================ --}}
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Rekap Pendaftaran per Program Studi</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Ringkasan jumlah pendaftar, kelulusan, dan konfirmasi daftar ulang per prodi.</p>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <a href="{{ route('admin.registrations.recap.export') }}"
           id="btn-export-rekap"
           class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- SUMMARY CARDS --}}
{{-- ============================================================ --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-neutral-900 leading-none">{{ $totals['pendaftar'] }}</div>
            <div class="text-xs font-medium text-neutral-400 mt-1 uppercase tracking-wide">Total Pendaftar</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-success-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-success-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-neutral-900 leading-none">{{ $totals['lulus'] }}</div>
            <div class="text-xs font-medium text-neutral-400 mt-1 uppercase tracking-wide">Total Lulus</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-info-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-info-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-neutral-900 leading-none">{{ $totals['daftar_ulang'] }}</div>
            <div class="text-xs font-medium text-neutral-400 mt-1 uppercase tracking-wide">Daftar Ulang Terkonfirmasi</div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TABLE --}}
{{-- ============================================================ --}}
<x-card class="overflow-hidden p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-100">
            <thead class="bg-neutral-50">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-neutral-400 w-10">No.</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-neutral-400">Nama Program Studi</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-neutral-400 w-32">Pendaftar</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-neutral-400 w-32">Lulus</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-neutral-400 w-44">Daftar Ulang</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-100">
                @forelse ($rows as $i => $row)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-5 py-4 text-sm text-neutral-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-neutral-900">{{ $row['nama_prodi'] }}</td>
                        <td class="px-5 py-4 text-sm text-center font-semibold text-neutral-800">{{ $row['pendaftar'] }}</td>
                        <td class="px-5 py-4 text-sm text-center font-semibold text-neutral-800">{{ $row['lulus'] }}</td>
                        <td class="px-5 py-4 text-sm text-center font-semibold text-neutral-800">{{ $row['daftar_ulang'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-neutral-400">
                            Belum ada data pendaftaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            {{-- TOTAL ROW --}}
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-neutral-50 border-t-2 border-neutral-200">
                        <td class="px-5 py-4 text-sm font-bold text-neutral-900" colspan="2">Total</td>
                        <td class="px-5 py-4 text-sm text-center font-bold text-neutral-900">{{ $totals['pendaftar'] }}</td>
                        <td class="px-5 py-4 text-sm text-center font-bold text-success-700">{{ $totals['lulus'] }}</td>
                        <td class="px-5 py-4 text-sm text-center font-bold text-primary-700">{{ $totals['daftar_ulang'] }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-card>

@endsection
