@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">

    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.facilities.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">Tambah Gambar Gallery</h1>
            <p class="text-sm text-neutral-500">Masukkan data gambar baru untuk gallery</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ route('admin.facilities.store') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false, previewUrl: null }" @submit="submitting = true">
            @csrf
            
            <div class="mb-6">
                <x-input-label for="image" value="Gambar Gallery" required="true" />
                <div class="mt-2 flex items-center gap-6">
                    <div class="shrink-0">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" class="h-32 w-48 object-cover rounded-xl border border-neutral-200 shadow-sm" alt="Preview" />
                        </template>
                        <template x-if="!previewUrl">
                            <div class="h-32 w-48 rounded-xl border border-dashed border-neutral-300 flex items-center justify-center bg-neutral-50 text-neutral-400">
                                <i class="ti ti-photo-plus text-3xl"></i>
                            </div>
                        </template>
                    </div>
                    <div class="flex-grow">
                        <label class="block">
                            <span class="sr-only">Pilih gambar</span>
                            <input type="file" name="image" id="image" accept="image/*" class="block w-full text-sm text-neutral-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                hover:file:bg-primary-100 cursor-pointer transition-colors"
                                @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                            />
                        </label>
                        <p class="text-xs text-neutral-500 mt-2">Format: JPG, PNG, WEBP. Maks 2MB.</p>
                        <x-input-error :messages="$errors->get('image')" />
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <x-input-label for="name" value="Judul Gambar" required="true" />
                <x-text-input type="text" id="name" name="name" :value="old('name')" required :error="$errors->has('name')" placeholder="Misal: Kegiatan Mahasiswa" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <!-- Deskripsi -->
            <div class="mb-6">
                <x-input-label for="description" value="Deskripsi (Opsional)" />
                <x-textarea id="description" name="description" rows="3" :error="$errors->has('description')">{{ old('description') }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <x-input-label for="order" value="Urutan Tampil" />
                    <x-text-input type="number" id="order" name="order" :value="old('order', 0)" :error="$errors->has('order')" />
                    <x-input-error :messages="$errors->get('order')" />
                </div>
                <div>
                    <x-input-label for="is_active" value="Status Tampil" />
                    <div class="flex items-center h-12">
                        <label class="flex items-center cursor-pointer gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked class="w-5 h-5 accent-primary-600 cursor-pointer">
                            <span class="text-sm font-medium text-neutral-600">Ditampilkan</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200">
                <a href="{{ route('admin.facilities.index') }}" class="decoration-none">
                    <x-button color="neutral" variant="ghost" type="button">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">Simpan Gambar</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
