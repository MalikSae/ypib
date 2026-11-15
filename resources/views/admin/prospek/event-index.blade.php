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
                <th class="px-3 py-3 text-left whitespace-nowrap">Potongan</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Prospek</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Daftar</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Daftar ulang</th>
                <th class="px-3 py-3 text-left whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-sm">
            @forelse($events as $event)
                <tr>
                    <td class="px-3 py-3">
                        {{-- Kampus dan periode masuk baris kedua (kolom sendiri membuat tabel melebar di layar laptop). --}}
                        @php $eventMeta = collect([\App\Models\ProspectEvent::KIND_LABELS[$event->kind] ?? $event->kind, $event->campus?->name, $event->period?->name ?? 'Periode berjalan', $event->school_name, $event->event_date?->format('d/m/Y')])->filter()->implode(' · '); @endphp
                        {{-- Badge status di samping nama (kolom Status sendiri membuat tabel melebar). --}}
                        <div class="flex items-center gap-2">
                            <x-truncate :value="$event->name" class="font-medium text-neutral-900" max="max-w-[11rem]" fallback="" />
                            <span class="shrink-0"><x-badge-status :active="$event->is_active" /></span>
                        </div>
                        <x-truncate :value="$eventMeta" class="text-xs text-neutral-500" max="max-w-[15rem]" fallback="" />
                    </td>
                                        <td class="px-3 py-3 font-semibold text-neutral-900 whitespace-nowrap">Rp {{ number_format($event->discount_amount, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.prospects.index', ['event' => $event->id]) }}" class="rounded-sm font-semibold text-neutral-900 underline underline-offset-4 hover:text-primary-600 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">{{ $event->prospects_count }}</a>
                    </td>
                    @foreach([$event->registered_count, $event->reregistered_count] as $count)
                        <td class="px-3 py-3 whitespace-nowrap font-semibold text-neutral-900">{{ $count }}</td>
                    @endforeach
                    <td class="px-3 py-3" x-data="{ copied: false }">
                        {{-- Tombol ikon (bukan teks) agar tabel muat di layar laptop. Kartu mobile tetap memakai teks. --}}
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <x-button type="button" color="neutral" variant="outline" size="sm" class="!px-2 min-w-11 md:min-w-9"
                                      x-bind:aria-label="copied ? 'Link form minat tersalin' : 'Salin link form minat'" x-bind:title="copied ? 'Tersalin' : 'Salin link form minat'"
                                      x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(route('prospect.show', $event->slug)) }}); copied = true; setTimeout(() => copied = false, 2000)">
                                <svg x-show="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                <svg x-show="copied" x-cloak class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </x-button>
                            <x-button :href="route('admin.prospects.events.qr', $event)" color="neutral" variant="outline" size="sm" class="!px-2 min-w-11 md:min-w-9" aria-label="Unduh QR form minat" title="Unduh QR form minat">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" /></svg>
                            </x-button>
                            <x-table-action-edit :href="route('admin.prospects.events.edit', $event)" title="Edit event" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-neutral-400">Belum ada event.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
