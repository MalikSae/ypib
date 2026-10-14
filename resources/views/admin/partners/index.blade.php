@extends('layouts.admin')
@section('title', 'Kelola Partner — Admin PMB YPIB')
@section('page-title', 'Kelola Partner')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Partner</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar logo institusi atau perusahaan rekanan kampus.</p>
    </div>
    <a href="{{ route('admin.partners.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Partner
        </x-button>
    </a>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-24">Logo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama Partner</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($partners as $partner)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        @if($partner->logo_path)
                            <div class="w-16 h-12 rounded-lg bg-white border border-neutral-200 flex items-center justify-center overflow-hidden p-2">
                                <img src="{{ Storage::url($partner->logo_path) }}" alt="{{ $partner->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="w-16 h-12 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $partner->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <x-badge-status :active="$partner->is_active" :toggle-action="route('admin.partners.toggle', $partner->id)" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.partners.edit', $partner->id)" />
                            
                            <x-table-action-delete :action="route('admin.partners.destroy', $partner->id)" text="Apakah Anda yakin ingin menghapus partner ini?" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada partner terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($partners->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $partners->links() }}
        </div>
    @endif
</div>

@endsection
