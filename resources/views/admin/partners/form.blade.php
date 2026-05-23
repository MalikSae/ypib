@extends('layouts.admin')
@section('title', (isset($partner) ? 'Edit Partner' : 'Tambah Partner') . ' — Admin PMB YPIB')
@section('page-title', isset($partner) ? 'Edit Partner' : 'Tambah Partner')

@section('content')

<div class="max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.partners.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">{{ isset($partner) ? 'Edit Partner' : 'Tambah Partner' }}</h1>
            <p class="text-sm text-neutral-500">Lengkapi form di bawah ini.</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ isset($partner) ? route('admin.partners.update', $partner->id) : route('admin.partners.store') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if(isset($partner)) @method('PUT') @endif

            <div class="mb-6">
                <x-input-label for="name" value="Nama Partner" required="true" />
                <x-text-input type="text" id="name" name="name" :value="old('name', $partner->name ?? '')" required
                              placeholder="Contoh: Universitas YPIB Majalengka" :error="$errors->has('name')" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="mb-6">
                <x-input-label for="logo" value="Logo Partner (Opsional)" />
                @if(isset($partner) && $partner->logo_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($partner->logo_path) }}" alt="Logo Saat Ini" class="max-h-20 max-w-[200px] object-contain border border-neutral-300 rounded-lg p-1 bg-white">
                        <div class="text-xs text-neutral-500 mt-1.5">Logo saat ini</div>
                    </div>
                @endif
                <div class="p-4 rounded-lg bg-neutral-50 border border-neutral-200">
                    <input type="file" id="logo" name="logo" accept="image/*" class="text-sm text-neutral-600 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                    <div class="text-xs text-neutral-500 mt-2">Format yang didukung: JPG, PNG, WEBP, SVG. Maks 2MB.</div>
                </div>
                <x-input-error :messages="$errors->get('logo')" />
            </div>

            <div class="mb-6">
                <x-input-label for="is_active" value="Status Aktif" />
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $partner->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600">
                    <span class="text-sm text-neutral-600">Tampilkan di publik</span>
                </label>
                <x-input-error :messages="$errors->get('is_active')" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.partners.index') }}" class="decoration-none">
                    <x-button type="button" color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">Simpan</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
