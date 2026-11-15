@extends('layouts.admin')
@section('title', 'Kelola Prospek — Admin PMB YPIB')
@section('page-title', 'Kelola Prospek')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-neutral-900">Kelola Prospek</h1>
        <p class="text-sm text-neutral-400">Calon mahasiswa dari form minat. Yang paling lama belum dihubungi tampil di atas.</p>
    </div>
</div>

@if(session('prospect_link'))
    @php $pl = session('prospect_link'); @endphp
    <x-card class="p-5 mb-6 border-primary-600">
        <div class="text-sm font-semibold text-neutral-900 mb-1">Link daftar untuk {{ $pl['name'] }}</div>
        <div class="rounded-lg bg-neutral-100 border border-neutral-200 px-3.5 py-2.5 font-mono text-xs break-all text-neutral-900 select-all">{{ $pl['url'] }}</div>
        @if($pl['wa'])
            <x-button :href="$pl['wa']" target="_blank" rel="noopener" color="primary" size="sm" class="mt-3">Kirim lewat WhatsApp</x-button>
        @endif
    </x-card>
@endif

<livewire:admin.prospect-list />
@endsection
