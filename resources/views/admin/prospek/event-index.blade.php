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

@php $filtered = request()->hasAny(['jenis', 'periode', 'urut']); @endphp
<div class="mb-6" x-data="{ open: window.matchMedia('(min-width: 768px)').matches || {{ $filtered ? 'true' : 'false' }} }"
     x-on:resize.window="if (window.innerWidth >= 768) open = true">
<button type="button" x-on:click="open = !open" :aria-expanded="open.toString()" aria-controls="filter-form"
        class="md:hidden w-full h-11 flex items-center justify-between rounded-md border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-700 mb-3 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">
    <span>Filter</span>
    <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-400"></span>
</button>
<form method="GET" action="{{ route('admin.prospects.events.index') }}" id="filter-form" x-show="open" x-cloak
      class="bg-white rounded-lg border border-neutral-200 p-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <div class="md:col-span-3">
        <x-input-label for="jenis" value="Jenis" />
        <x-select-menu id="jenis" name="jenis" :options="['' => 'Semua jenis'] + $kinds" :value="request('jenis')" />
    </div>
    <div class="md:col-span-4">
        <x-input-label for="periode" value="Periode" />
        <x-select-menu id="periode" name="periode" :options="['' => 'Semua periode'] + $periods->pluck('name', 'id')->all()" :value="request('periode')" />
    </div>
    <div class="md:col-span-3">
        <x-input-label for="urut" value="Urutkan" />
        <x-select-menu id="urut" name="urut" :options="$sorts" :value="$sort" />
    </div>
    <div class="md:col-span-2 flex gap-2">
        <x-button type="submit" color="primary" class="flex-1 justify-center">Terapkan</x-button>
        @if($filtered)
            <x-button :href="route('admin.prospects.events.index')" color="neutral" variant="ghost">Reset</x-button>
        @endif
    </div>
</form>
</div>

<div class="md:hidden space-y-3">
    @forelse($events as $event)
        <div class="bg-white rounded-lg border border-neutral-200 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-medium text-neutral-900">{{ $event->name }}</div>
                    <div class="text-xs font-semibold {{ $event->isOnline() ? 'text-primary-600' : 'text-neutral-500' }}">{{ \App\Models\ProspectEvent::KIND_LABELS[$event->kind] ?? $event->kind }}</div>
                </div>
                <x-badge-status :active="$event->is_active" />
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <div><dt class="text-xs text-neutral-400">Kampus</dt><dd class="text-neutral-600">{{ $event->campus?->name }}</dd></div>
                <div><dt class="text-xs text-neutral-400">Potongan</dt><dd class="font-semibold text-neutral-900">Rp {{ number_format($event->discount_amount, 0, ',', '.') }}</dd></div>
            </dl>
            <dl class="mt-3 pt-3 border-t border-neutral-100 grid grid-cols-3 gap-x-4 text-sm">
                <div>
                    <dt class="text-xs text-neutral-400">Prospek</dt>
                    <dd><a href="{{ route('admin.prospects.index', ['event' => $event->id]) }}" class="inline-flex items-center h-11 rounded-sm font-semibold text-neutral-900 underline underline-offset-4 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">{{ $event->prospects_count }}</a></dd>
                </div>
                @foreach(['Daftar' => $event->registered_count, 'Daftar ulang' => $event->reregistered_count] as $label => $count)
                    <div>
                        <dt class="text-xs text-neutral-500">{{ $label }}</dt>
                        <dd class="h-11 flex items-center font-semibold text-neutral-900">{{ $count }}</dd>
                    </div>
                @endforeach
            </dl>
            <div class="mt-4 grid grid-cols-3 gap-2" x-data="{ copied: false }">
                <x-button type="button" color="neutral" variant="outline"
                          x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(route('prospect.show', $event->slug)) }}); copied = true; setTimeout(() => copied = false, 2000)">
                    <span x-show="!copied">Salin</span>
                    <span x-show="copied" x-cloak>Tersalin</span>
                </x-button>
                <x-button :href="route('admin.prospects.events.qr', $event)" color="neutral" variant="outline">QR</x-button>
                <x-button :href="route('admin.prospects.events.edit', $event)" color="neutral" variant="outline">Edit</x-button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-neutral-200 px-4 py-10 text-center text-sm text-neutral-400">Belum ada event.</div>
    @endforelse
</div>

<div class="hidden md:block bg-white rounded-lg border border-neutral-200 overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral-100">
        <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
            <tr>
                <th class="px-3 py-3 text-left whitespace-nowrap">Event</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Kampus / Periode</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Potongan</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Prospek</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Daftar</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Daftar ulang</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Status</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-sm">
            @forelse($events as $event)
                <tr>
                    <td class="px-3 py-3">
                        @php $eventMeta = collect([\App\Models\ProspectEvent::KIND_LABELS[$event->kind] ?? $event->kind, $event->school_name, $event->event_date?->format('d/m/Y')])->filter()->implode(' · '); @endphp
                        <div class="font-medium text-neutral-900 truncate max-w-[16rem]" title="{{ $event->name }}">{{ $event->name }}</div>
                        <div class="text-xs text-neutral-500 truncate max-w-[16rem]" title="{{ $eventMeta }}">{{ $eventMeta }}</div>
                    </td>
                    <td class="px-3 py-3 text-neutral-700"><div class="truncate max-w-[10rem]" title="{{ $event->campus?->name }}">{{ $event->campus?->name }}</div><div class="text-xs text-neutral-500 truncate max-w-[10rem]">{{ $event->period?->name ?? 'Periode berjalan' }}</div></td>
                    <td class="px-3 py-3 font-semibold text-neutral-900 whitespace-nowrap">Rp {{ number_format($event->discount_amount, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.prospects.index', ['event' => $event->id]) }}" class="rounded-sm font-semibold text-neutral-900 underline underline-offset-4 hover:text-primary-600 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">{{ $event->prospects_count }}</a>
                    </td>
                    @foreach([$event->registered_count, $event->reregistered_count] as $count)
                        <td class="px-3 py-3 whitespace-nowrap font-semibold text-neutral-900">{{ $count }}</td>
                    @endforeach
                    <td class="px-3 py-3 whitespace-nowrap">
                        <x-badge-status :active="$event->is_active" />
                    </td>
                    <td class="px-3 py-3" x-data="{ copied: false }">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <x-button type="button" color="neutral" variant="outline" size="sm"
                                      x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(route('prospect.show', $event->slug)) }}); copied = true; setTimeout(() => copied = false, 2000)">
                                <span x-show="!copied">Salin</span>
                                <span x-show="copied" x-cloak>Tersalin</span>
                            </x-button>
                            <x-button :href="route('admin.prospects.events.qr', $event)" color="neutral" variant="outline" size="sm">QR</x-button>
                            <x-button :href="route('admin.prospects.events.edit', $event)" color="neutral" variant="outline" size="sm">Edit</x-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-neutral-400">Belum ada event.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
