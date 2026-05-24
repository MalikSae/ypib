@extends('layouts.admin')
@section('title', 'Kelola Program Studi — Admin PMB YPIB')
@section('page-title', 'Kelola Program Studi')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Program Studi</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar program studi yang ditawarkan di kampus.</p>
    </div>
    <a href="{{ route('admin.programs.create') }}" class="decoration-none shrink-0">
        <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all duration-200 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Program Studi
        </button>
    </a>
</div>

{{-- FILTER SECTION --}}
<div class="mb-6 bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
    <form action="{{ route('admin.programs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="per_page" class="text-sm font-semibold text-neutral-600 whitespace-nowrap">Tampilkan:</label>
            <select name="per_page" id="per_page" onchange="this.form.submit()" class="text-sm border border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 block py-2 pl-3 pr-8 bg-white text-neutral-700 cursor-pointer shadow-sm">
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
        
        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2 pl-10 text-sm font-medium border border-neutral-300 rounded-lg bg-white focus:ring-primary-500 focus:border-primary-500 placeholder-neutral-400 text-neutral-900 shadow-sm transition-shadow" placeholder="Cari program studi...">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Fakultas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kuota</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Biaya</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($programs as $program)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $program->name }}</div>
                        <div class="text-xs font-mono mt-0.5 text-neutral-400">Akreditasi: {{ $program->accreditation ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-neutral-600">{{ $program->faculty->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-200">
                            {{ $program->quota }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-neutral-900">Rp {{ number_format($program->registration_fee, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        {{-- Toggle Status --}}
                        <form action="{{ route('admin.programs.destroy', $program->id) }}" method="POST" class="m-0 inline-block">
                            {{-- We don't have a toggle endpoint yet for programs, but if there's one, it goes here. For now, just display status --}}
                            @if($program->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 bg-neutral-900 text-white rounded-full text-xs font-semibold">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-neutral-100 text-neutral-500 border border-neutral-200 rounded-full text-xs font-semibold">Nonaktif</span>
                            @endif
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.programs.edit', $program->id) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-primary-50 hover:text-primary-700 transition-colors duration-150" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('admin.programs.destroy', $program->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus prodi ini?')">
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
                    <td colspan="6" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada program studi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($programs->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $programs->links() }}
        </div>
    @endif
</div>

@endsection
