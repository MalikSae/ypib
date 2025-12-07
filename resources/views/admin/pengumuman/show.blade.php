@extends('layouts.admin')

@section('title', 'Detail Pengumuman')
@section('subtitle', 'Informasi lengkap pengumuman')

@section('content')
<div class="max-w-4xl mx-auto fade-in">
    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-8 border-b border-slate-50 flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        {{ $pengumuman->kategori ?? 'Informasi' }}
                    </span>
                    <span class="text-slate-400 text-sm flex items-center gap-1">
                        <i class="far fa-calendar"></i>
                        {{ $pengumuman->tanggal_publish->format('d F Y') }}
                    </span>
                    @if($pengumuman->is_active)
                        <span class="text-emerald-600 text-xs font-medium flex items-center gap-1">
                            <i class="fas fa-check-circle"></i> Aktif
                        </span>
                    @else
                        <span class="text-slate-400 text-xs font-medium flex items-center gap-1">
                            <i class="fas fa-eye-slash"></i> Non-Aktif
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-slate-800 leading-tight">
                    {{ $pengumuman->judul }}
                </h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.pengumuman.edit', $pengumuman->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        </div>

        @if($pengumuman->gambar)
        <div class="w-full h-64 md:h-96 bg-slate-100 border-b border-slate-50">
            <img src="{{ Storage::url($pengumuman->gambar) }}" 
                 alt="{{ $pengumuman->judul }}" 
                 class="w-full h-full object-contain">
        </div>
        @endif

        <!-- Content -->
        <div class="p-8">
            <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                {!! nl2br(e($pengumuman->isi)) !!}
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 p-6 border-t border-slate-100 flex justify-between items-center">
            <a href="{{ route('admin.pengumuman.index') }}" 
               class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <form action="{{ route('admin.pengumuman.destroy', $pengumuman->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800 flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Hapus Pengumuman
                </button>
            </form>
        </div>
    </div>

</div>
@endsection