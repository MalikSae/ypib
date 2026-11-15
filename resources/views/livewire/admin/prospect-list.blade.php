<div>
    {{-- Tab status: sekaligus ringkasan angka dan filter. Membungkus ke baris baru di layar sempit. --}}
    <div class="flex flex-wrap gap-2 mb-4" role="group" aria-label="Saring menurut status">
        @php
            $tabs = ['' => ['label' => 'Semua', 'count' => $total]];
            foreach ($statuses as $key => $meta) {
                $tabs[$key] = ['label' => $meta['label'], 'count' => $counts[$key] ?? 0];
            }
        @endphp
        @foreach($tabs as $key => $tab)
            <x-filter-tab :active="$status === $key" :count="$tab['count']" wire:key="tab-{{ $key ?: 'semua' }}"
                          wire:click="setStatus('{{ $key }}')" wire:loading.attr="disabled" wire:target="setStatus">{{ $tab['label'] }}</x-filter-tab>
        @endforeach
    </div>

    <div class="flex flex-col md:flex-row gap-3 mb-3">
        <div class="relative flex-1">
            <label for="q" class="sr-only">Cari prospek</label>
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <x-text-input type="search" :active="trim($q) !== ''" id="q" wire:model.live.debounce.300ms="q" class="pl-10" autocomplete="off"
                          placeholder="Cari nama, sekolah, atau nomor WA" />
        </div>
        <div class="md:w-1/3">
            <label for="event" class="sr-only">Event</label>
            <x-select-menu filter id="event" wire:model.live="event" :value="$event"
                           :options="['' => 'Semua event'] + $events->pluck('name', 'id')->all()" />

        </div>
    </div>

    @if($selectedAffiliator || $hasFilters)
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 mb-3 text-sm">
            @if($selectedAffiliator)
                <span class="inline-flex items-center gap-1 rounded-md border border-neutral-200 bg-white pl-3 text-neutral-700">
                    <span>Affiliator: <span class="font-semibold text-neutral-900">{{ $selectedAffiliator->user?->name ?? 'Tanpa nama' }} ({{ $selectedAffiliator->code }})</span></span>
                    <x-button color="neutral" variant="ghost" size="sm" class="!px-2 min-w-11 md:min-w-9" aria-label="Hapus filter affiliator"
                              wire:click="clearReferrer" wire:loading.attr="disabled" wire:target="clearReferrer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </x-button>
                </span>
            @endif
            @if($hasFilters)
                <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
            @endif
        </div>
    @endif

    <div wire:loading.class="opacity-60" class="transition-opacity motion-reduce:transition-none">
        <div class="md:hidden space-y-3">
            @forelse($prospects as $p)
                <div class="bg-white rounded-lg border border-neutral-200 p-4" wire:key="card-{{ $p->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('admin.prospects.show', $p) }}" class="min-w-0 flex-1 block rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <span class="block text-sm font-medium text-neutral-900 underline underline-offset-4">{{ $p->full_name }}</span>
                            <span class="block text-xs text-neutral-500 mt-1">{{ $p->school_name ?: '—' }}</span>
                        </a>
                        <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
                    </div>
                    @if($p->fraud_flag)
                        <p class="mt-2 text-xs font-semibold text-error-600">Dicurigai: WA sama dengan akun affiliator</p>
                    @endif
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 text-sm">
                        <div>
                            <dt class="text-xs text-neutral-500">Prodi</dt>
                            <dd class="text-neutral-700">{{ $p->program?->name ?? 'Belum menentukan' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Kontak terakhir</dt>
                            <dd>@include('livewire.admin.partials.prospect-contact', ['p' => $p])</dd>
                        </div>
                    </dl>
                    <div class="mt-4 grid {{ $p->isRegistered() ? 'grid-cols-1' : 'grid-cols-2' }} gap-2">
                        <x-button :href="\App\Support\ProspectService::prospectChatUrl($p, $p->campus)" target="_blank" rel="noopener"
                                  color="neutral" variant="outline">Chat WhatsApp</x-button>
                        @unless($p->isRegistered())
                            <form method="POST" action="{{ route('admin.prospects.resend-link', $p) }}" class="contents">
                                @csrf
                                <x-button type="submit" class="w-full">Kirim link daftar</x-button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-neutral-200 px-4"><x-empty-state :title="$hasFilters || $status !== '' ? 'Tidak ada prospek yang cocok.' : 'Belum ada prospek.'" :description="$hasFilters || $status !== '' ? 'Ubah kata kunci, filter, atau tab status.' : null" /></div>
            @endforelse
        </div>

        <div class="hidden md:block bg-white rounded-lg border border-neutral-200">
            <table class="min-w-full divide-y divide-neutral-100">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama / Sekolah</th>
                        <th class="px-4 py-3 text-left">Prodi</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Kontak terakhir</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($prospects as $p)
                        <tr class="align-middle hover:bg-neutral-50" wire:key="row-{{ $p->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.prospects.show', $p) }}" title="{{ $p->full_name }}" class="block truncate max-w-xs rounded-sm font-medium text-neutral-900 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">{{ $p->full_name }}</a>
                                <x-truncate :value="$p->school_name" class="text-xs text-neutral-500" />
                            </td>
                            <td class="px-4 py-3 text-neutral-700">{{ $p->program?->name ?? 'Belum menentukan' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">@include('livewire.admin.partials.prospect-contact', ['p' => $p])</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
                                @if($p->fraud_flag)
                                    {{-- Peringatan tampil sebagai badge kedua, bukan baris tambahan di sel nama. --}}
                                    <span title="Nomor WA sama dengan akun affiliator" class="mt-1 flex w-max items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-error-50 text-error-700 border-error-200">Dicurigai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-button :href="\App\Support\ProspectService::prospectChatUrl($p, $p->campus)" target="_blank" rel="noopener"
                                              color="neutral" variant="outline" size="sm">Chat</x-button>
                                    @unless($p->isRegistered())
                                        <form method="POST" action="{{ route('admin.prospects.resend-link', $p) }}">
                                            @csrf
                                            <x-button type="submit" size="sm">Kirim link</x-button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4"><x-empty-state :title="$hasFilters || $status !== '' ? 'Tidak ada prospek yang cocok.' : 'Belum ada prospek.'" :description="$hasFilters || $status !== '' ? 'Ubah kata kunci, filter, atau tab status.' : null" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $prospects->links('livewire.admin.pagination') }}</div>
</div>
