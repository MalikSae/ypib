@extends('layouts.admin')
@section('title', (isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas') . ' — Admin PMB YPIB')
@section('page-title', isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas')

@section('content')

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div class="max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.faculties.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">{{ isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas' }}</h1>
            <p class="text-sm text-neutral-500">Lengkapi form di bawah ini.</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ isset($faculty) ? route('admin.faculties.update', $faculty->id) : route('admin.faculties.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if(isset($faculty)) @method('PUT') @endif

            <div class="mb-6">
                <x-input-label for="name" value="Nama Fakultas" required="true" />
                <x-text-input type="text" id="name" name="name" :value="old('name', $faculty->name ?? '')" required
                              placeholder="Contoh: Fakultas Ilmu Kesehatan" :error="$errors->has('name')" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="mb-6">
                <x-input-label for="description" value="Deskripsi" />
                <x-textarea name="description" id="description" :error="$errors->has('description')">{{ old('description', $faculty->description ?? '') }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.faculties.index') }}" class="decoration-none">
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

<script>
    $(document).ready(function() {
        $('#description').summernote({
            placeholder: 'Tulis deskripsi fakultas di sini...',
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    });
</script>
@endsection
