@extends('layouts.admin')
@section('title', 'Kelola Prospek — Admin PMB YPIB')
@section('page-title', 'Kelola Prospek')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-neutral-900">Kelola Prospek</h1>
        <p class="text-sm text-neutral-400">Calon mahasiswa dari form minat.</p>
    </div>
</div>

@if(session('prospect_link'))
    @php $pl = session('prospect_link'); @endphp
    <x-card class="p-5 mb-6 border-primary-600">
        <div class="text-sm font-semibold text-neutral-900 mb-1">Link daftar untuk {{ $pl['name'] }}</div>
        <div class="rounded-lg bg-neutral-100 border border-neutral-200 px-3.5 py-2.5 font-mono text-xs break-all text-neutral-900 select-all">{{ $pl['url'] }}</div>
        @if($pl['wa'])
            <x-button :href="$pl['wa']" target="_blank" rel="noopener" color="primary" class="mt-3 h-11 md:h-9">Kirim lewat WhatsApp</x-button>
        @endif
    </x-card>
@endif

<div class="md:hidden bg-white rounded-lg border border-neutral-200 divide-y divide-neutral-100 mb-4">
    @foreach($statuses as $key => $meta)
        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
            <span class="text-neutral-500">{{ $meta['label'] }}</span>
            <span class="font-bold text-neutral-900">{{ $counts[$key] ?? 0 }}</span>
        </div>
    @endforeach
</div>
<div class="hidden md:grid md:grid-cols-5 gap-3 mb-4">
    @foreach($statuses as $key => $meta)
        <x-card class="p-3 md:p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ $meta['label'] }}</div>
            <div class="text-xl font-bold text-neutral-900">{{ $counts[$key] ?? 0 }}</div>
        </x-card>
    @endforeach
</div>

<div class="mb-6" x-data="{ open: window.matchMedia('(min-width: 768px)').matches || {{ request()->hasAny(['q', 'event', 'referrer', 'status']) ? 'true' : 'false' }} }"
     x-on:resize.window="if (window.innerWidth >= 768) open = true">
<button type="button" x-on:click="open = !open" :aria-expanded="open.toString()" aria-controls="filter-form"
        class="md:hidden w-full h-11 flex items-center justify-between rounded-md border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-700 mb-3">
    <span>Filter</span>
    <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-400"></span>
</button>
<form method="GET" action="{{ route('admin.prospects.index') }}" id="filter-form" x-show="open" x-cloak
      class="bg-white rounded-lg border border-neutral-200 p-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
    <div class="md:col-span-4">
        <x-input-label for="q" value="Cari" />
        <x-text-input type="text" id="q" name="q" :value="request('q')" placeholder="Nama, sekolah, atau WA" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="event" value="Event" />
        <x-select id="event" name="event">
            <option value="">Semua event</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}" @selected(request('event') == $event->id)>{{ $event->name }}</option>
            @endforeach
        </x-select>
    </div>
    <div class="md:col-span-2 relative" x-data="affiliatorPicker({{ Js::from($selectedAffiliator ? ['id' => $selectedAffiliator->id, 'label' => ($selectedAffiliator->user?->name ?? 'Tanpa nama') . ' (' . $selectedAffiliator->code . ')'] : null) }})">
        <x-input-label for="affiliator_q" value="Affiliator" />
        <x-text-input type="text" id="affiliator_q" autocomplete="off" role="combobox" aria-autocomplete="list"
                      aria-controls="affiliator-listbox" aria-haspopup="listbox"
                      x-bind:aria-expanded="open.toString()" x-bind:aria-activedescendant="activeIndex >= 0 ? 'affiliator-option-' + activeIndex : null"
                      x-model="query" x-on:input="onType()" x-on:blur="close()" x-on:keydown="onKey($event)"
                      placeholder="Ketik nama atau kode" />
        <input type="hidden" name="referrer" :value="selectedId ?? ''">

        {{-- Melayang (absolute) agar tidak menggeser tata letak filter --}}
        <ul id="affiliator-listbox" role="listbox" x-show="open" x-cloak
            class="absolute z-20 mt-1 min-w-full w-max max-w-xs bg-white border border-neutral-200 rounded-lg shadow-md max-h-60 overflow-y-auto">
            <template x-for="(item, index) in results" :key="item.id">
                <li role="option" :id="'affiliator-option-' + index" :aria-selected="(index === activeIndex).toString()"
                    x-on:mousedown.prevent="pick(item)" x-on:mouseenter="activeIndex = index"
                    :class="index === activeIndex ? 'bg-neutral-50' : ''"
                    class="px-3.5 py-2 text-sm text-neutral-800 cursor-pointer" x-text="item.label"></li>
            </template>
            <li x-show="open && !loading && results.length === 0" role="presentation" class="px-3.5 py-2 text-sm text-neutral-400">Affiliator tidak ditemukan</li>
        </ul>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="status" value="Status" />
        <x-select id="status" name="status">
            <option value="">Semua status</option>
            @foreach($statuses as $key => $meta)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $meta['label'] }}</option>
            @endforeach
        </x-select>
    </div>
    <div class="md:col-span-2 flex gap-2">
        <x-button type="submit" color="primary" class="flex-1 justify-center">Terapkan</x-button>
        @if(request()->hasAny(['q', 'event', 'referrer', 'status']))
            <a href="{{ route('admin.prospects.index') }}"
               class="inline-flex items-center justify-center rounded-md px-3 h-11 md:h-9 text-sm font-semibold text-neutral-500 hover:bg-neutral-100 focus-visible:ring-2 focus-visible:ring-primary-500">Reset</a>
        @endif
    </div>
</form>
</div>

<div class="md:hidden space-y-3">
    @forelse($prospects as $p)
        <div class="bg-white rounded-lg border border-neutral-200 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <a href="{{ route('admin.prospects.show', $p) }}" class="rounded-sm font-medium text-neutral-900 hover:text-primary-600 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">{{ $p->full_name }}</a>
                    <div class="text-xs text-neutral-400">{{ $p->school_name ?: '—' }}</div>
                </div>
                <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
            </div>
            @if($p->fraud_flag)
                <p class="mt-2 text-xs font-semibold text-error-600">Dicurigai: WA sama dengan akun affiliator</p>
            @endif
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <div><dt class="text-xs text-neutral-400">WhatsApp</dt><dd class="font-mono text-xs text-neutral-700">{{ $p->phone_normalized }}</dd></div>
                <div><dt class="text-xs text-neutral-400">Prodi</dt><dd class="text-neutral-600">{{ $p->program?->name ?? 'Belum menentukan' }}</dd></div>
                <div><dt class="text-xs text-neutral-400">Event</dt><dd class="text-neutral-600">{{ $p->sources->first()?->event?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-neutral-400">Affiliator</dt><dd class="text-neutral-600">{{ $p->referrer?->user?->name ?? '—' }}</dd></div>
            </dl>
            <div class="mt-4 grid grid-cols-3 gap-2">
                <x-button :href="\App\Support\ProspectService::prospectChatUrl($p, $p->campus)" target="_blank" rel="noopener"
                          color="neutral" variant="outline" class="h-11">Chat</x-button>
                @if(! $p->isRegistered())
                    <form method="POST" action="{{ route('admin.prospects.resend-link', $p) }}" class="contents">
                        @csrf
                        <x-button type="submit" color="primary" class="h-11 w-full">Link</x-button>
                    </form>
                @else
                    <span></span>
                @endif
                <x-button :href="route('admin.prospects.show', $p)" color="neutral" variant="outline" class="h-11">Detail</x-button>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-neutral-200 px-4 py-10 text-center text-sm text-neutral-400">Belum ada prospek.</div>
    @endforelse
</div>

<div class="hidden md:block bg-white rounded-lg border border-neutral-200 overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral-100">
        <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
            <tr>
                <th class="px-4 py-3 text-left whitespace-nowrap">Tgl</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Nama / Sekolah</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">WhatsApp</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Prodi</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Event</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Affiliator</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 text-sm">
            @forelse($prospects as $p)
                <tr class="align-top">
                    <td class="px-4 py-3 text-neutral-500 whitespace-nowrap">{{ $p->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.prospects.show', $p) }}" class="rounded-sm font-medium text-neutral-900 hover:text-primary-600 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none">{{ $p->full_name }}</a>
                        <div class="text-xs text-neutral-400">{{ $p->school_name ?: '—' }}</div>
                        @if($p->fraud_flag)
                            <span class="inline-block mt-1 text-xs font-semibold text-error-600">Dicurigai: WA sama dengan akun affiliator</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-neutral-700 whitespace-nowrap">{{ $p->phone_normalized }}</td>
                    <td class="px-4 py-3 text-neutral-600 whitespace-nowrap">{{ $p->program?->name ?? 'Belum menentukan' }}</td>
                    <td class="px-4 py-3 text-neutral-600 whitespace-nowrap">{{ $p->sources->first()?->event?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-neutral-600">{{ $p->referrer?->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <x-button :href="\App\Support\ProspectService::prospectChatUrl($p, $p->campus)" target="_blank" rel="noopener"
                                      color="neutral" variant="outline" size="sm">Chat</x-button>
                            @unless($p->isRegistered())
                                <form method="POST" action="{{ route('admin.prospects.resend-link', $p) }}">
                                    @csrf
                                    <x-button type="submit" color="primary" size="sm">Link</x-button>
                                </form>
                            @endunless
                            <x-button :href="route('admin.prospects.show', $p)" color="neutral" variant="outline" size="sm">Detail</x-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-neutral-400">Belum ada prospek.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $prospects->links() }}</div>
<script>
    function affiliatorPicker(initial) {
        return {
            query: initial ? initial.label : '',
            selectedId: initial ? initial.id : null,
            results: [],
            open: false,
            loading: false,
            timer: null,
            activeIndex: -1,
            // Keyboard: panah naik/turun pilih saran, Enter pilih, Escape tutup.
            onKey(event) {
                if (!this.open || this.results.length === 0) {
                    return;
                }
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.activeIndex = (this.activeIndex + 1) % this.results.length;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.activeIndex = this.activeIndex <= 0 ? this.results.length - 1 : this.activeIndex - 1;
                } else if (event.key === 'Enter' && this.activeIndex >= 0) {
                    event.preventDefault();
                    this.pick(this.results[this.activeIndex]);
                } else if (event.key === 'Escape') {
                    this.open = false;
                }
            },
            // Teks yang tidak dipilih dari daftar dibuang saat kolom ditinggal.
            close() {
                this.open = false;
                if (this.selectedId === null) {
                    this.query = '';
                }
            },
            onType() {
                this.selectedId = null;
                clearTimeout(this.timer);
                if (this.query.trim().length < 2) {
                    this.results = [];
                    this.open = false;
                    return;
                }
                this.timer = setTimeout(() => this.fetchResults(), 250);
            },
            async fetchResults() {
                this.loading = true;
                const url = @js(route('admin.prospects.affiliator-search')) + '?q=' + encodeURIComponent(this.query.trim());
                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    this.results = res.ok ? await res.json() : [];
                } catch (e) {
                    this.results = [];
                }
                this.loading = false;
                this.activeIndex = -1;
                this.open = true;
            },
            pick(item) {
                this.selectedId = item.id;
                this.query = item.label;
                this.open = false;
                this.activeIndex = -1;
            },
        };
    }
</script>
@endsection
