@extends('layouts.admin')
@section('title', (isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas') . ' — Admin PMB YPIB')
@section('page-title', isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas')

@section('content')

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.faculties.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-neutral-900 tracking-tight">{{ isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas' }}</h1>
            <p class="text-sm text-neutral-500">Lengkapi form di bawah ini.</p>
        </div>
    </div>

    <x-card class="p-4 md:p-8">
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
                        <svg class="w-4 h-4 animate-spin motion-reduce:animate-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Menyimpan...
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
