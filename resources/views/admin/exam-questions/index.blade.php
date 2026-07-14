@extends('layouts.admin')
@section('title', 'Bank Soal — Admin PMB YPIB')
@section('page-title', 'Bank Soal')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Bank Soal</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola daftar soal untuk Tes Tulis Online peserta.</p>
    </div>
    <a href="{{ route('admin.bank-soal.create') }}" class="decoration-none shrink-0">
        <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all duration-200 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Soal
        </button>
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

{{-- FILTER & SEARCH --}}
<div class="mb-6 bg-white p-4 rounded-2xl border border-neutral-200 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="w-full sm:w-1/2 md:w-1/3">
        <form method="GET" action="{{ route('admin.bank-soal.index') }}" class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ $search ?? '' }}" class="block w-full pl-10 pr-3 py-2 border border-neutral-300 rounded-xl leading-5 bg-white placeholder-neutral-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Cari pertanyaan...">
        </form>
    </div>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-12">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pertanyaan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kunci Jawaban</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($questions as $index => $q)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4 text-sm text-neutral-500">{{ $questions->firstItem() + $index }}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors line-clamp-2" title="{{ $q->question_text }}">{{ Str::limit($q->question_text, 100) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-neutral-600 font-semibold uppercase">Opsi {{ $q->correct_option }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($q->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 bg-success-50 text-success-700 border border-success-200 rounded-full text-xs font-semibold">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-neutral-100 text-neutral-500 border border-neutral-200 rounded-full text-xs font-semibold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.bank-soal.edit', $q) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-primary-50 hover:text-primary-700 transition-colors duration-150" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('admin.bank-soal.destroy', $q) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?')">
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
                    <td colspan="5" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada soal terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-8">
    {{ $questions->links() }}
</div>

@endsection
