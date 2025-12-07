@extends('layouts.admin')

@section('title', 'Edit Pengumuman')
@section('subtitle', 'Update pengumuman yang ada')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 max-w-3xl mx-auto">
    <form action="{{ route('admin.pengumuman.update', $pengumuman->id) }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Pengumuman</label>
            <input type="text" 
                   name="judul" 
                   value="{{ old('judul', $pengumuman->judul) }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('judul') border-red-500 @enderror" 
                   required 
                   placeholder="Contoh: Jadwal Ujian Masuk">
            @error('judul')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select name="kategori" 
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('kategori') border-red-500 @enderror">
                    <option value="umum" {{ old('kategori', $pengumuman->kategori) == 'umum' ? 'selected' : '' }}>
                        Umum
                    </option>
                    <option value="penerimaan" {{ old('kategori', $pengumuman->kategori) == 'penerimaan' ? 'selected' : '' }}>
                        Penerimaan
                    </option>
                    <option value="pembayaran" {{ old('kategori', $pengumuman->kategori) == 'pembayaran' ? 'selected' : '' }}>
                        Pembayaran
                    </option>
                </select>
                @error('kategori')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publish</label>
                <input type="date" 
                       name="tanggal_publish" 
                       value="{{ old('tanggal_publish', $pengumuman->tanggal_publish->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('tanggal_publish') border-red-500 @enderror" 
                       required>
                @error('tanggal_publish')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman</label>
            <textarea name="isi" 
                      rows="6" 
                      class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('isi') border-red-500 @enderror" 
                      required 
                      placeholder="Tulis isi pengumuman di sini...">{{ old('isi', $pengumuman->isi) }}</textarea>
            @error('isi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" 
                       name="is_active" 
                       value="1" 
                       {{ old('is_active', $pengumuman->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600">Aktifkan Pengumuman (Tampilkan ke mahasiswa)</span>
            </label>
        </div>

        <!-- Info Box -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-lg mr-3 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Informasi:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Pengumuman yang diaktifkan akan langsung terlihat oleh mahasiswa</li>
                        <li>Pastikan informasi yang disampaikan sudah benar sebelum menyimpan</li>
                        <li>Gunakan kategori yang sesuai untuk memudahkan mahasiswa mencari informasi</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.pengumuman.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Update Pengumuman
            </button>
        </div>
    </form>
</div>
@endsection