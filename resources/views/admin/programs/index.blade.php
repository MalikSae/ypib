@extends('layouts.admin')
@section('title', 'Kelola Program Studi — Admin PMB YPIB')
@section('page-title', 'Kelola Program Studi')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Program Studi</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar program studi yang ditawarkan di kampus.</p>
    </div>
    <x-button :href="route('admin.programs.create')" class="shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah program studi
    </x-button>
</div>

<livewire:admin.program-list />

@endsection
