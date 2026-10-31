@extends('layouts.admin')
@section('title', 'Gallery Image — Admin PMB YPIB')
@section('page-title', 'Gallery Image')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Gallery Image</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola gambar gallery untuk ditampilkan di halaman depan.</p>
    </div>
    <a href="{{ route('admin.facilities.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Gambar
        </x-button>
    </a>
</div>

{{-- ALERT SUCCESS --}}
@if(session('success'))
    <div class="bg-primary-50 border border-primary-100 text-primary-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Gambar & Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Urutan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($facilities as $facility)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            @if($facility->image_path)
                                <img src="{{ Storage::url($facility->image_path) }}" alt="{{ $facility->name }}" class="w-16 h-12 rounded-lg object-cover border border-neutral-200 shadow-sm">
                            @else
                                <div class="w-16 h-12 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400">
                                    <i class="ti ti-photo text-xl"></i>
                                </div>
                            @endif
                            <div class="text-sm font-semibold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $facility->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-neutral-500 max-w-xs truncate" title="{{ $facility->description }}">
                            {{ $facility->description ?: '—' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-neutral-600 font-medium">
                        {{ $facility->order }}
                    </td>
                    <td class="px-6 py-4">
                        <x-badge-status :active="$facility->is_active" :toggle-action="route('admin.facilities.toggle', $facility)" active-text="Tampil" inactive-text="Sembunyi" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.facilities.edit', $facility)" />
                            
                            <x-table-action-delete :action="route('admin.facilities.destroy', $facility)" text="Apakah Anda yakin ingin menghapus gambar ini?" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada gambar gallery terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
