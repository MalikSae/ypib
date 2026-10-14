@extends('layouts.admin')
@section('title', 'Kelola Panitia PMB — Admin PMB YPIB')
@section('page-title', 'Kelola Panitia PMB')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Panitia PMB</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola akun panitia yang bertugas melakukan interview pendaftar.</p>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <form method="GET" action="{{ route('admin.panitia.index') }}" class="w-full sm:w-64">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-neutral-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <x-text-input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau email..." class="pl-10" />
            </div>
        </form>
        
        <a href="{{ route('admin.panitia.create') }}" class="decoration-none shrink-0 w-full sm:w-auto">
            <x-button type="button">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Panitia
            </x-button>
        </a>
    </div>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-16">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama Lengkap</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($panitias as $index => $p)
                <tr class="hover:bg-neutral-50 transition-colors duration-100">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                        {{ $panitias->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-neutral-900">{{ $p->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-neutral-500">{{ $p->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.panitia.edit', $p->id)" />
                            
                            <x-table-action-delete :action="route('admin.panitia.destroy', $p->id)" text="Apakah Anda yakin ingin menghapus panitia ini?" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-sm text-neutral-400">
                        <svg class="mx-auto h-12 w-12 text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <p class="text-base font-semibold text-neutral-900 mb-1">Belum ada data</p>
                        <p>Tidak ada data panitia yang ditemukan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($panitias->hasPages())
    <div class="mt-6">
        {{ $panitias->links() }}
    </div>
@endif

@endsection
