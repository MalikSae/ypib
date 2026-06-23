@extends('layouts.admin')
@section('title', 'Data Pendaftar — Admin PMB YPIB')
@section('page-title', 'Data Pendaftar')

@php
$statusConfig = [
    ''                                 => ['label' => 'Semua',            'color' => 'neutral'],
    'menunggu_pembayaran'              => ['label' => 'Belum Bayar',      'color' => 'warning'],
    'menunggu_konfirmasi'              => ['label' => 'Konfirmasi',       'color' => 'info'],
    'terdaftar'                        => ['label' => 'Terdaftar',        'color' => 'success'],
    'menunggu_review_berkas'           => ['label' => 'Review Berkas',    'color' => 'warning'],
    'perlu_revisi_berkas'              => ['label' => 'Revisi Berkas',    'color' => 'orange'],
    'diterima'                         => ['label' => 'Diterima',         'color' => 'green'],
    'ditolak'                          => ['label' => 'Ditolak',          'color' => 'error'],
    'menunggu_konfirmasi_daftar_ulang' => ['label' => 'Konfirmasi DU',   'color' => 'info'],
    'daftar_ulang_selesai'             => ['label' => 'Selesai DU',      'color' => 'primary'],
];

$badgeMap = [
    'menunggu_pembayaran'              => ['label' => 'Belum Bayar',         'class' => 'bg-neutral-100 text-neutral-600'],
    'menunggu_konfirmasi'              => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-neutral-100 text-neutral-600'],
    'terdaftar'                        => ['label' => 'Terdaftar (Belum Upload)', 'class' => 'bg-primary-50 text-primary-700'],
    'menunggu_review_berkas'           => ['label' => 'Menunggu Review Berkas', 'class' => 'bg-orange-50 text-orange-600'],
    'perlu_revisi_berkas'              => ['label' => 'Perlu Revisi Berkas', 'class' => 'bg-orange-50 text-orange-600'],
    'diterima'                         => ['label' => 'Diterima',            'class' => 'bg-primary-100 text-primary-800'],
    'ditolak'                          => ['label' => 'Ditolak',             'class' => 'bg-neutral-200 text-neutral-600'],
    'menunggu_konfirmasi_daftar_ulang' => ['label' => 'Konfirmasi Daftar Ulang', 'class' => 'bg-neutral-100 text-neutral-600'],
    'daftar_ulang_selesai'             => ['label' => 'Selesai Daftar Ulang',    'class' => 'bg-primary-50 text-primary-700'],
];

$activeStatus = request('status', '');
@endphp

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
    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
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

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
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

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
        <div>
            @php
                $uploadBerkas = ($statusCounts['menunggu_review_berkas'] ?? 0) + ($statusCounts['perlu_revisi_berkas'] ?? 0) + ($statusCounts['diterima'] ?? 0) + ($statusCounts['menunggu_konfirmasi_daftar_ulang'] ?? 0) + ($statusCounts['daftar_ulang_selesai'] ?? 0) + ($statusCounts['ditolak'] ?? 0);
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

    <div class="bg-white rounded-2xl border border-neutral-200 p-5 flex items-center justify-between">
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

{{-- ============================================================ --}}
{{-- TABLE WRAPPER --}}
{{-- ============================================================ --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-0">

    {{-- Search Bar Row --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100">
        <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex gap-3">
            @if($activeStatus)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, nomor pendaftaran..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            </div>
            <x-button type="submit" color="primary" size="sm">Cari</x-button>
            @if(request('search'))
                <a href="{{ route('admin.registrations.index', array_filter(['status' => $activeStatus])) }}" class="decoration-none">
                    <x-button type="button" variant="outline" color="neutral" size="sm">Reset</x-button>
                </a>
            @endif
        </form>
    </div>

    {{-- Status Tab Pills --}}
    <div class="flex items-center gap-2 px-4 py-3 border-b border-neutral-100 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        @foreach($statusConfig as $statusKey => $cfg)
            @php
                $count = $statusKey === '' ? $totalRegistrations : ($statusCounts[$statusKey] ?? 0);
                $isActive = $activeStatus === $statusKey;
                $href = route('admin.registrations.index', array_filter([
                    'status' => $statusKey,
                    'search' => request('search'),
                ]));
            @endphp
            <a href="{{ $href }}" class="decoration-none shrink-0">
                <span @class([
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-150',
                    'bg-neutral-900 text-white shadow-sm' => $isActive,
                    'text-neutral-600 bg-white hover:bg-neutral-100 border border-neutral-200' => !$isActive,
                ])>
                    {{ $cfg['label'] }}
                    <span @class([
                        'inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all',
                        'bg-neutral-700 text-white' => $isActive,
                        'bg-neutral-100 text-neutral-500' => !$isActive,
                    ])>{{ $count }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- TABLE --}}
    {{-- ============================================================ --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pendaftar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">No. Daftar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Jalur</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Referral</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tanggal</th>
                    <th class="px-5 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($registrations as $reg)
                    @php
                        $badge = $badgeMap[$reg->status] ?? ['label' => str_replace('_', ' ', $reg->status), 'class' => 'bg-neutral-100 text-neutral-600'];
                    @endphp
                    <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                        {{-- Pendaftar (Nama + Email) --}}
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $reg->full_name }}</div>
                            <div class="text-xs text-neutral-400 mt-0.5">{{ $reg->user?->email }}</div>
                        </td>

                        {{-- No. Daftar --}}
                        <td class="px-5 py-4">
                            @if($reg->registration_number)
                                <span class="font-mono text-xs font-medium text-neutral-500">{{ $reg->registration_number }}</span>
                            @else
                                <span class="text-xs text-neutral-300 italic">—</span>
                            @endif
                        </td>

                        {{-- Pilihan Prodi --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-600 max-w-[160px]">
                                {{ $reg->firstChoiceProgram?->name ?? '—' }}
                            </div>
                        </td>

                        {{-- Jalur Pendaftaran --}}
                        <td class="px-5 py-4">
                            @php
                                $pathLabels = ['umum' => 'Reguler', 'prestasi' => 'Prestasi', 'tahfidz' => 'Tahfidz'];
                                $pathColors = [
                                    'umum'     => 'bg-neutral-100 text-neutral-600',
                                    'prestasi' => 'bg-primary-50 text-primary-700',
                                    'tahfidz'  => 'bg-primary-100 text-primary-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $pathColors[$reg->admission_path] ?? 'bg-neutral-100 text-neutral-600' }}">
                                {{ $pathLabels[$reg->admission_path] ?? $reg->admission_path ?? '—' }}
                            </span>
                        </td>

                        {{-- Referral --}}
                        <td class="px-5 py-4">
                            @if($reg->referrer)
                                <span class="inline-flex items-center px-2 py-0.5 bg-neutral-100 text-neutral-700 rounded-md text-xs font-mono font-medium border border-neutral-200">
                                    {{ $reg->referrer->code }}
                                </span>
                            @else
                                <span class="text-neutral-300">—</span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-600">{{ $reg->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-neutral-400 mt-0.5">{{ $reg->created_at->format('H:i') }} WIB</div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('admin.registrations.show', $reg->id) }}"
                               class="decoration-none inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 transition-colors duration-150"
                               title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-sm text-neutral-400">Belum ada data pendaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($registrations->hasPages())
        <div class="px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links() }}
        </div>
    @endif
</div>

@endsection
