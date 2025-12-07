@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')
@section('subtitle', 'Buat pengumuman baru')

@section('content')
<div class="max-w-3xl mx-auto fade-in">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50">
            <h3 class="font-bold text-slate-800">Form Pengumuman</h3>
        </div>
        
        <form action="{{ route('admin.pengumuman.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Judul Pengumuman</label>
                <input type="text" name="judul" 
                       class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                       required placeholder="Contoh: Jadwal Ujian Masuk">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Kategori</label>
                    <select name="kategori" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="umum">Umum</option>
                        <option value="penerimaan">Penerimaan</option>
                        <option value="pembayaran">Pembayaran</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Publish</label>
                    <input type="date" name="tanggal_publish" 
                           class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                           required value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Gambar Cover (Opsional)</label>
                <div class="flex items-center gap-4">
                    <label class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all group">
                        <i class="fas fa-image group-hover:scale-110 transition-transform"></i>
                        <span>Pilih Gambar</span>
                        <input type="file" name="gambar" accept="image/*" class="hidden" onchange="document.getElementById('fileName').innerText = this.files[0].name">
                    </label>
                    <span id="fileName" class="text-xs text-slate-400">Belum ada file dipilih</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG (Max. 2MB)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Isi Pengumuman</label>
                <textarea name="isi" rows="6" 
                          class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                          required placeholder="Tulis isi pengumuman di sini..."></textarea>
            </div>

            <div>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked 
                           class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-slate-600 font-medium">Aktifkan Pengumuman</span>
                </label>
            </div>

            <div class="flex justify-end space-x-3 border-t border-slate-50 pt-6">
                <a href="{{ route('admin.pengumuman.index') }}" 
                   class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50 transition-colors text-sm">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors text-sm shadow-sm shadow-indigo-200">
                    Simpan Pengumuman
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
