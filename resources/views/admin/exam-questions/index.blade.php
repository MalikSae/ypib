@extends('layouts.admin')
@section('title', 'Bank Soal — Admin PMB YPIB')
@section('page-title', 'Bank Soal')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Bank Soal</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola daftar soal untuk Tes Tulis Online peserta.</p>
    </div>
    <x-button :href="route('admin.bank-soal.create')" class="shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah soal
    </x-button>
</div>

<livewire:admin.exam-question-list />

@endsection
