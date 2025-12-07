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
                <input type="text" name="kode_jurusan" value="{{ old('kode_jurusan') }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('kode_jurusan') border-red-500 ring-red-500 @enderror" required placeholder="Ex: TI-01">
                @error('kode_jurusan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jurusan</label>
                <input type="text" name="nama_jurusan" value="{{ old('nama_jurusan') }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('nama_jurusan') border-red-500 ring-red-500 @enderror" required placeholder="Ex: Teknik Informatika">
                @error('nama_jurusan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kuota Mahasiswa</label>
            <input type="number" name="kuota" value="{{ old('kuota', 100) }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('kuota') border-red-500 ring-red-500 @enderror" required min="1">
            @error('kuota')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.jurusan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Simpan Jurusan</button>
        </div>
    </form>
</div>
@endsection