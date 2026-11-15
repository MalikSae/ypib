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
    <a href="{{ route('admin.bank-soal.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Soal
        </x-button>
    </a>
</div>

{{-- ALERT SUCCESS --}}
@if(session('success'))
    <div class="bg-primary-50 border border-primary-100 text-primary-700 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

<livewire:admin.exam-question-list />

@endsection
