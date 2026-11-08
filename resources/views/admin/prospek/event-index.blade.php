@extends('layouts.admin')
@section('title', 'Event dan Kampanye — Admin PMB YPIB')
@section('page-title', 'Event dan Kampanye')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-neutral-900">Event dan Kampanye</h1>
        <p class="text-sm text-neutral-400">Form minat per event atau kampanye.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2">
        <x-button :href="route('admin.prospects.index')" color="neutral" variant="outline" class="whitespace-nowrap">Daftar Prospek</x-button>
        <x-button :href="route('admin.prospects.events.create')" color="primary" class="whitespace-nowrap">Tambah Event / Kampanye</x-button>
    </div>
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
                <div><dt class="text-xs text-neutral-400">Prospek</dt><dd class="text-neutral-600">{{ $event->sources_count }}</dd></div>
            </dl>
            <div class="mt-4 grid grid-cols-3 gap-2" x-data="{ copied: false }">
                <x-button type="button" color="neutral" variant="outline" class="h-11"
                          x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(route('prospect.show', $event->slug)) }}); copied = true; setTimeout(() => copied = false, 2000)">
                    <span x-show="!copied">Salin</span>
                    <span x-show="copied" x-cloak>Tersalin</span>
                </x-button>
                <x-button :href="route('admin.prospects.events.qr', $event)" color="neutral" variant="outline" class="h-11">QR</x-button>
                <x-button :href="route('admin.prospects.events.edit', $event)" color="neutral" variant="outline" class="h-11">Edit</x-button>
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
                <th class="px-4 py-3 text-left whitespace-nowrap">Event</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Kampus / Periode</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Potongan Daftar Ulang</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Prospek</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-sm">
            @forelse($events as $event)
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium text-neutral-900">{{ $event->name }}</div>
                        <div class="text-[11px] font-semibold {{ $event->isOnline() ? 'text-primary-600' : 'text-neutral-500' }}">{{ \App\Models\ProspectEvent::KIND_LABELS[$event->kind] ?? $event->kind }}</div>
                        <div class="text-xs text-neutral-400">{{ $event->school_name ?: '—' }}{{ $event->event_date ? ' · ' . $event->event_date->format('d/m/Y') : '' }}</div>
                        <div class="text-xs text-neutral-400 font-mono truncate max-w-[16rem] mt-1" title="{{ route('prospect.show', $event->slug) }}">{{ route('prospect.show', $event->slug) }}</div>
                    </td>
                    <td class="px-4 py-3 text-neutral-600 whitespace-nowrap">{{ $event->campus?->name }}<div class="text-xs text-neutral-400">{{ $event->period?->name ?? 'Periode berjalan' }}</div></td>
                    <td class="px-4 py-3 font-semibold text-neutral-900 whitespace-nowrap">Rp {{ number_format($event->discount_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-neutral-600 whitespace-nowrap">{{ $event->sources_count }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <x-badge-status :active="$event->is_active" />
                    </td>
                    <td class="px-4 py-3" x-data="{ copied: false }">
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
                <tr><td colspan="6" class="px-4 py-10 text-center text-neutral-400">Belum ada event.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
