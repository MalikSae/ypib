@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.facilities.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Edit Gambar Gallery</h1>
            <p class="text-sm text-neutral-500">Ubah data gambar gallery yang sudah ada</p>
        </div>
    </div>

    <x-card class="p-4 md:p-8">
        <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false, previewUrl: '{{ $facility->image_path ? Storage::url($facility->image_path) : '' }}' }" @submit="submitting = true">
            @csrf @method('PUT')
            
            <div class="mb-6">
                <x-input-label for="image" value="Gambar Gallery" />
                <div class="mt-2 flex items-center gap-6">
                    <div class="shrink-0">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" class="h-32 w-48 object-cover rounded-xl border border-neutral-200 shadow-sm" alt="Preview" />
                        </template>
                        <template x-if="!previewUrl">
                            <div class="h-32 w-48 rounded-xl border border-dashed border-neutral-300 flex items-center justify-center bg-neutral-50 text-neutral-400">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                            </div>
                        </template>
                    </div>
                    <div class="flex-grow">
                        <label class="block">
                            <span class="sr-only">Pilih gambar baru</span>
                            <input type="file" name="image" id="image" accept="image/*" class="block w-full text-sm text-neutral-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                hover:file:bg-primary-100 cursor-pointer transition-colors"
                                @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                            />
                        </label>
                        <p class="text-xs text-neutral-500 mt-2">Biarkan kosong jika tidak ingin mengubah gambar. Format: JPG, PNG, WEBP. Maks 5MB.</p>
                        <x-input-error :messages="$errors->get('image')" />
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <x-input-label for="name" value="Judul Gambar" required="true" />
                <x-text-input type="text" id="name" name="name" :value="old('name', $facility->name)" required :error="$errors->has('name')" placeholder="Misal: Kegiatan Mahasiswa" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="mb-6">
                <x-input-label for="description" value="Deskripsi (Opsional)" />
                <x-textarea id="description" name="description" rows="3" :error="$errors->has('description')">{{ old('description', $facility->description) }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <x-input-label for="order" value="Urutan Tampil" />
                    <x-text-input type="number" id="order" name="order" :value="old('order', $facility->order)" :error="$errors->has('order')" />
                    <x-input-error :messages="$errors->get('order')" />
                </div>
                <div>
                    <x-input-label for="is_active" value="Status Tampil" />
                    <div class="flex items-center h-12">
                        <label class="flex items-center cursor-pointer gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $facility->is_active) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600 cursor-pointer">
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
                    <span x-show="!submitting">Simpan Perubahan</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin motion-reduce:animate-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
