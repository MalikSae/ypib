@extends('layouts.admin')
@section('title', 'Arsip Pendaftar Terhapus — Admin PMB YPIB')
@section('page-title', 'Arsip Pendaftar Terhapus')

@section('content')

{{-- ============================================================ --}}
{{-- PAGE HEADER --}}
{{-- ============================================================ --}}
<div class="mb-6 flex items-start justify-between">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Arsip Pendaftar Terhapus</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar pendaftar yang telah dihapus (soft delete). Komisi afiliasi untuk mereka sudah disesuaikan otomatis.</p>
    </div>
    <x-button :href="route('admin.registrations.index')" color="neutral" variant="outline">Kembali ke data pendaftar</x-button>
</div>

{{-- ============================================================ --}}
{{-- TABLE WRAPPER --}}
{{-- ============================================================ --}}
<livewire:admin.registration-trash-list />
@endsection
