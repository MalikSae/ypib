@extends('layouts.admin')
@section('title', 'Kelola Fakultas — Admin PMB YPIB')
@section('page-title', 'Kelola Fakultas')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Fakultas</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar fakultas yang menaungi berbagai program studi.</p>
    </div>
    <a href="{{ route('admin.faculties.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Fakultas
        </x-button>
    </a>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama Fakultas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Total Prodi</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($faculties as $faculty)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $faculty->name }}</div>
                        <div class="text-xs font-mono mt-0.5 text-neutral-400">{{ $faculty->slug }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-200">
                            {{ $faculty->programs()->count() }} Prodi
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.faculties.edit', $faculty->id)" />
                            
                            <x-table-action-delete :action="route('admin.faculties.destroy', $faculty->id)" text="Apakah Anda yakin ingin menghapus fakultas ini? Semua prodi di dalamnya mungkin akan kehilangan referensi ke fakultas ini." />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada fakultas terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($faculties->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $faculties->links() }}
        </div>
    @endif
</div>

@endsection
