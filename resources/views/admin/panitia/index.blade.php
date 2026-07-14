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
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau email..." 
                    class="w-full pl-10 pr-4 py-2 bg-white border border-neutral-200 rounded-xl text-sm focus:outline-none focus:border-primary-600 focus:ring-2 focus:ring-primary-200 transition-colors placeholder-neutral-400 text-neutral-900">
            </div>
        </form>
        
        <a href="{{ route('admin.panitia.create') }}" class="decoration-none shrink-0 w-full sm:w-auto">
            <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-bold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Panitia
            </button>
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
                            <a href="{{ route('admin.panitia.edit', $p->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-primary-50 hover:text-primary-700 transition-colors duration-150" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('admin.panitia.destroy', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus panitia ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-error-50 hover:text-error-600 transition-colors duration-150" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
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
