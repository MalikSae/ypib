@extends('layouts.admin')
@section('title', 'Akun Staf — Admin PMB YPIB')
@section('page-title', 'Akun Staf')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Akun Staf</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola admin, operator, dan panitia. Akun tidak dihapus, hanya dinonaktifkan.</p>
    </div>
    <x-button :href="route('admin.staff.create')" class="shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah akun
    </x-button>
</div>

<livewire:admin.staff-list />

@endsection
