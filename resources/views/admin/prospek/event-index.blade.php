@extends('layouts.admin')
@section('title', 'Event dan Kampanye — Admin PMB YPIB')
@section('page-title', 'Event dan Kampanye')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-neutral-900">Event dan Kampanye</h1>
        <p class="text-sm text-neutral-400">Form minat per event atau kampanye, beserta jumlah prospek, daftar, dan daftar ulang.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2">
        <x-button :href="route('admin.prospects.index')" color="neutral" variant="outline" class="whitespace-nowrap">Daftar Prospek</x-button>
        <x-button :href="route('admin.prospects.events.create')" color="primary" class="whitespace-nowrap">Tambah Event / Kampanye</x-button>
    </div>
</div>

<livewire:admin.event-list />
@endsection
