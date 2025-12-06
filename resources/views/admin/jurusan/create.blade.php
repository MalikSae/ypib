@extends('layouts.admin')

@section('title', 'Tambah Jurusan')
@section('subtitle', 'Tambah program studi baru')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.jurusan.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Jurusan</label>
                <input type="text" name="kode_jurusan" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required placeholder="Ex: TI-01">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jurusan</label>
                <input type="text" name="nama_jurusan" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required placeholder="Ex: Teknik Informatika">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kuota Mahasiswa</label>
            <input type="number" name="kuota" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required min="1" value="100">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.jurusan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Simpan Jurusan</button>
        </div>
    </form>
</div>
@endsection
