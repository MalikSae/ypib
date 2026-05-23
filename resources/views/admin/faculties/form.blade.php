@extends('layouts.admin')
@section('title', (isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas') . ' — Admin PMB YPIB')
@section('page-title', isset($faculty) ? 'Edit Fakultas' : 'Tambah Fakultas')

@section('content')

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:32px;max-width:800px;">
    
    <form action="{{ isset($faculty) ? route('admin.faculties.update', $faculty->id) : route('admin.faculties.store') }}" method="POST">
        @csrf
        @if(isset($faculty)) @method('PUT') @endif

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Nama Fakultas</label>
            <input type="text" name="name" value="{{ old('name', $faculty->name ?? '') }}" required
                   style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;"
                   placeholder="Contoh: Fakultas Ilmu Kesehatan">
            @error('name') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Deskripsi</label>
            <textarea name="description" id="description" style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;min-height:120px;">{{ old('description', $faculty->description ?? '') }}</textarea>
            @error('description') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;gap:12px;margin-top:32px;">
            <button type="submit" style="background:#082e8f;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('admin.faculties.index') }}" style="background:#F1F4F7;color:#444950;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid #DEE3E9;">
                Batal
            </a>
        </div>
    </form>
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
