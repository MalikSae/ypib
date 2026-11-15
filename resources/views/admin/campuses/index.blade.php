@extends('layouts.admin')
@section('title', 'Kelola Kampus — Admin PMB YPIB')
@section('page-title', 'Kelola Kampus')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Kampus</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kampus penyelenggara. Prodi yang dibuka di tiap kampus diatur di form <strong>Program Studi</strong> (kartu "Dibuka di Kampus Mana?"); biaya daftar ulang dan komisi di <strong>Periode &rarr; Biaya &amp; Kuota</strong>.</p>
    </div>
    <a href="{{ route('admin.campuses.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Kampus
        </x-button>
    </a>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-lg border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kampus</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Alamat</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pendaftar</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($campuses as $campus)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 max-w-xs">
                            <span class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors truncate" title="{{ $campus->name }}">{{ $campus->name }}</span>
                            @if($campus->is_default)
                                <span class="shrink-0 px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 border border-primary-200 text-xs font-semibold whitespace-nowrap">Bawaan</span>
                            @endif
                        </div>
                        <div class="text-xs text-neutral-500">{{ $campus->code }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-neutral-700"><x-truncate :value="$campus->address" /></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-200 whitespace-nowrap">
                            {{ $campus->registrations_count }} pendaftar
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $campus->is_active ? 'bg-success-50 text-success-700' : 'bg-neutral-100 text-neutral-500' }}">
                            {{ $campus->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.campuses.edit', $campus->id)" />
                            @unless($campus->is_default)
                                <x-table-action-delete :action="route('admin.campuses.destroy', $campus->id)" />
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4"><x-empty-state title="Belum ada kampus terdaftar." /></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($campuses->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $campuses->links() }}
        </div>
    @endif
</div>

@endsection
