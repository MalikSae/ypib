@extends('layouts.admin')

@section('title', 'Edit Jurusan')
@section('subtitle', 'Update data program studi')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.jurusan.update', $jurusan->id) }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Jurusan</label>
                <input type="text" 
                       name="kode_jurusan" 
                       value="{{ old('kode_jurusan', $jurusan->kode_jurusan) }}"
                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('kode_jurusan') border-red-500 @enderror" 
                       required 
                       placeholder="Ex: TI-01">
                @error('kode_jurusan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jurusan</label>
                <input type="text" 
                       name="nama_jurusan" 
                       value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}"
                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('nama_jurusan') border-red-500 @enderror" 
                       required 
                       placeholder="Ex: Teknik Informatika">
                @error('nama_jurusan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kuota Mahasiswa</label>
            <input type="number" 
                   name="kuota" 
                   value="{{ old('kuota', $jurusan->kuota) }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('kuota') border-red-500 @enderror" 
                   required 
                   min="{{ $jurusan->mahasiswas_count ?? 1 }}" 
                   value="100">
            @error('kuota')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Saat ini: <span class="font-semibold">{{ $jurusan->mahasiswas_count ?? 0 }}</span> mahasiswa terdaftar. 
                Kuota minimal harus {{ $jurusan->mahasiswas_count ?? 1 }}.
            </p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" 
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                <option value="active" {{ old('status', $jurusan->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ old('status', $jurusan->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" 
                      rows="3" 
                      class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('deskripsi') border-red-500 @enderror">{{ old('deskripsi', $jurusan->deskripsi) }}</textarea>
            @error('deskripsi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Info Box -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-lg mr-3 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Informasi Penting:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Kode jurusan harus unik dan tidak boleh sama dengan jurusan lain</li>
                        <li>Kuota akan berkurang otomatis saat mahasiswa mendaftar</li>
                        <li>Kuota tidak boleh lebih kecil dari jumlah mahasiswa yang sudah terdaftar</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.jurusan.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Update Jurusan
            </button>
        </div>
    </form>
</div>
@endsection