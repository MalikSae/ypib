@extends('layouts.admin')
@section('title', 'Arsip Pendaftar Terhapus — Admin PMB YPIB')
@section('page-title', 'Arsip Pendaftar Terhapus')

@section('content')

{{-- ============================================================ --}}
{{-- PAGE HEADER --}}
{{-- ============================================================ --}}
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Arsip Pendaftar Terhapus</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar pendaftar yang telah dihapus (soft delete). Komisi afiliasi untuk mereka sudah disesuaikan otomatis.</p>
    </div>
    <a href="{{ route('admin.registrations.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-neutral-600 bg-white border border-neutral-300 rounded-xl shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 whitespace-nowrap">
        &larr; Kembali ke Data Utama
    </a>
</div>

{{-- ============================================================ --}}
{{-- TABLE WRAPPER --}}
{{-- ============================================================ --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-0">

    {{-- Search Bar Row --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100 flex flex-col md:flex-row justify-between gap-4">
        <form method="GET" action="{{ route('admin.registrations.trash') }}" class="flex gap-3 flex-1">
            <div class="flex-1 relative max-w-md">
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
                <a href="{{ route('admin.registrations.trash') }}" class="decoration-none">
                    <x-button type="button" variant="outline" color="neutral" size="sm">Reset</x-button>
                </a>
            @endif
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLE --}}
    {{-- ============================================================ --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">No. Registrasi / Nama</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tanggal Dihapus</th>
                    <th class="px-5 py-3 w-10 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($registrations as $reg)
                    <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                        {{-- Pendaftar (Nama + No. Daftar) --}}
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900 transition-colors">{{ $reg->full_name }}</div>
                            <div class="text-xs text-neutral-500 font-mono mt-0.5">{{ $reg->registration_number ?? 'Belum ada nomor' }}</div>
                        </td>

                        {{-- Pilihan Prodi --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-600 max-w-[160px]">
                                {{ $reg->firstChoiceProgram?->name ?? '—' }}
                            </div>
                        </td>

                        {{-- Tanggal Dihapus --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-900">{{ $reg->deleted_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-neutral-400">{{ $reg->deleted_at->format('H:i') }} WIB</div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-right">
                            <form method="POST" action="{{ route('admin.registrations.restore', $reg->id) }}" onsubmit="return confirm('Yakin ingin memulihkan pendaftar ini? Reward komisi tidak akan dipulihkan secara otomatis.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 whitespace-nowrap">
                                    Pulihkan
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-neutral-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-neutral-900 mb-1">Tidak Ada Arsip</h3>
                            <p class="text-sm text-neutral-500 max-w-sm mx-auto">Semua data pendaftar aktif atau tidak ada pencarian yang cocok.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($registrations->hasPages())
        <div class="px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links() }}
        </div>
    @endif
</div>
@endsection
