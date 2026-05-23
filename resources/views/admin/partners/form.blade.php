@extends('layouts.admin')
@section('title', (isset($partner) ? 'Edit Partner' : 'Tambah Partner') . ' — Admin PMB YPIB')
@section('page-title', isset($partner) ? 'Edit Partner' : 'Tambah Partner')

@section('content')

<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:32px;max-width:800px;">
    
    <form action="{{ isset($partner) ? route('admin.partners.update', $partner->id) : route('admin.partners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($partner)) @method('PUT') @endif

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Nama Partner</label>
            <input type="text" name="name" value="{{ old('name', $partner->name ?? '') }}" required
                   style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;"
                   placeholder="Contoh: Universitas YPIB Majalengka">
            @error('name') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Logo Partner (Opsional)</label>
            @if(isset($partner) && $partner->logo_path)
                <div style="margin-bottom: 12px;">
                    <img src="{{ Storage::url($partner->logo_path) }}" alt="Logo Saat Ini" style="max-height: 80px; max-width: 200px; object-fit: contain; border: 1px solid #DEE3E9; border-radius: 8px; padding: 4px;">
                    <div style="font-size: 12px; color: #8595A4; margin-top: 4px;">Logo saat ini</div>
                </div>
            @endif
            <input type="file" name="logo" accept="image/*"
                   style="width:100%;padding:10px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;background:#F9FAFB;">
            <div style="font-size:12px;color:#8595A4;margin-top:6px;">Format yang didukung: JPG, PNG, WEBP, SVG. Maks 2MB.</div>
            @error('logo') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:flex;align-items:center;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $partner->is_active ?? true) ? 'checked' : '' }} style="width:18px;height:18px;margin-right:10px;cursor:pointer;">
                <span style="font-size:14px;font-weight:600;color:#444950;">Aktif Tampil</span>
            </label>
        </div>

        <div style="display:flex;gap:12px;margin-top:32px;">
            <button type="submit" style="background:#082e8f;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('admin.partners.index') }}" style="background:#F1F4F7;color:#444950;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid #DEE3E9;">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
