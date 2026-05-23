@extends('layouts.admin')

@section('content')
<div class="max-w-4xl">

    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.facilities.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">Edit Fasilitas</h1>
            <p class="text-sm text-neutral-500">Ubah data fasilitas yang sudah ada</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2 lg:col-span-1">
                    <x-input-label for="name" value="Nama Fasilitas" required="true" />
                    <x-text-input type="text" id="name" name="name" :value="old('name', $facility->name)" required :error="$errors->has('name')" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="md:col-span-2 lg:col-span-1">
                    <x-input-label for="icon" value="Nama Icon (Tabler Icons)" />
                    <x-text-input type="text" id="icon" name="icon" :value="old('icon', $facility->icon)" placeholder="Contoh: ti-flask" :error="$errors->has('icon')" />
                    <p class="text-xs text-neutral-500 mt-1.5 block">Cari referensi icon di <a href="https://tabler.io/icons" target="_blank" class="text-primary-600 hover:text-primary-700 hover:underline">tabler.io/icons</a></p>
                    <x-input-error :messages="$errors->get('icon')" />
                </div>
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
                    <x-input-label for="is_active" value="Status" />
                    <div class="flex items-center h-12">
                        <label class="flex items-center cursor-pointer gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $facility->is_active) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600 cursor-pointer">
                            <span class="text-sm font-medium text-neutral-600">Aktif</span>
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
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>

</div>
@endsection
