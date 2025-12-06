@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')
@section('subtitle', 'Buat pengumuman baru')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 max-w-3xl mx-auto">
    <form action="{{ route('admin.pengumuman.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Pengumuman</label>
            <input type="text" name="judul" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required placeholder="Contoh: Jadwal Ujian Masuk">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="kategori" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="umum">Umum</option>
                    <option value="penerimaan">Penerimaan</option>
                    <option value="pembayaran">Pembayaran</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publish</label>
                <input type="date" name="tanggal_publish" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required value="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman</label>
            <textarea name="isi" rows="6" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required placeholder="Tulis isi pengumuman di sini..."></textarea>
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600">Aktifkan Pengumuman (Tampilkan ke mahasiswa)</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.pengumuman.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Simpan Pengumuman</button>
        </div>
    </form>
</div>
@endsection
