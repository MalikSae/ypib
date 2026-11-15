<div>
    @php
        // Tombol urut kolom: ikon Heroicons chevron sesuai arah urutan aktif.
        $sortHeader = function (string $column, string $label, string $align = 'left') use ($sort, $dir) {
            $active = $sort === $column;
            $icon = ! $active
                ? 'M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9'
                : ($dir === 'asc' ? 'm4.5 15.75 7.5-7.5 7.5 7.5' : 'm19.5 8.25-7.5 7.5-7.5-7.5');
            return [$active, $icon, $label, $align];
        };
        $sortColumns = [
            'prospek' => 'Prospek', 'klik' => 'Klik', 'konversi' => 'Konversi', 'rate' => '% Konversi', 'reward' => 'Total komisi',
        ];
    @endphp

    {{-- Cari selalu tampil. Jumlah per halaman dan ekspor: di mobile di balik tombol Filter. --}}
    <div class="mb-4 bg-white p-4 rounded-lg border border-neutral-200"
         x-data="{ open: window.matchMedia('(min-width: 768px)').matches }"
         x-on:resize.window="if (window.innerWidth >= 768) open = true">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="relative flex-1">
                <x-input-label for="referrer_search" value="Cari affiliator" class="sr-only" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
                <x-text-input type="search" :active="trim($search) !== ''" id="referrer_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama, email, atau kode" class="pl-10" />
            </div>

            <x-button type="button" color="neutral" variant="outline" class="md:hidden justify-between"
                      x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-controls="referrer-filters">
                <span>Filter dan ekspor</span>
                <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-500"></span>
            </x-button>

            <div id="referrer-filters" x-show="open" x-cloak class="flex flex-col md:flex-row md:items-end gap-3">
                <div>
                    <x-input-label for="per_page" value="Per halaman" />
                    <x-select-menu id="per_page" class="w-full md:w-24" wire:model.live="perPage" :value="$perPage" :options="$perPageOptions" />
                </div>

                {{-- Ekspor tetap form biasa (hasilnya file), ikut kata kunci cari yang sedang aktif. --}}
                <form method="GET" action="{{ route('admin.referrers.export') }}" class="flex flex-col md:flex-row md:items-end gap-3">
                    @if(trim($search) !== '')
                        <input type="hidden" name="search" value="{{ trim($search) }}">
                    @endif
                    <div>
                        <x-input-label for="export_period_id" value="Periode ekspor" />
                        <x-select-menu id="export_period_id" name="period_id" class="w-full md:w-56"
                                       :value="\App\Support\AdminPeriod::id()"
                                       :options="['' => 'Semua periode'] + \App\Support\AdminPeriod::options()->mapWithKeys(fn ($p) => [$p->id => $p->name . ' (' . $p->phaseLabel() . ')'])->all()" />
                    </div>
                    <div>
                        <x-input-label for="export_status" value="Status ekspor" />
                        <x-select-menu id="export_status" name="status" class="w-full md:w-40"
                                       :value="request('status')" :options="['' => 'Semua status', 'active' => 'Aktif', 'inactive' => 'Nonaktif']" />
                    </div>
                    <x-button type="submit" color="neutral" variant="outline">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Ekspor Excel
                    </x-button>
                </form>
            </div>
        </div>
        @if(trim($search) !== '')
            <div class="mt-3">
                <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
            </div>
        @endif
    </div>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        <p class="mb-3 text-xs text-neutral-500">{{ $referrers->total() }} afiliasi terdaftar</p>

        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden space-y-3">
            @forelse($referrers as $referrer)
                <div class="bg-white rounded-lg border border-neutral-200 p-4" wire:key="referrer-card-{{ $referrer->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('admin.referrers.show', $referrer->id) }}" class="min-w-0 flex-1 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <x-truncate :value="$referrer->user?->name" class="text-sm font-medium text-neutral-900 underline underline-offset-4" max="max-w-full" />
                            <x-truncate :value="$referrer->user?->email" class="text-xs text-neutral-500 mt-1" max="max-w-full" fallback="" />
                        </a>
                        <x-badge-status :active="$referrer->status === 'active'" />
                    </div>
                    <dl class="mt-3 grid grid-cols-3 gap-x-4 gap-y-2 text-sm">
                        <div><dt class="text-xs text-neutral-500">Kode</dt><dd class="font-mono text-xs text-neutral-700">{{ $referrer->code }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Prospek</dt><dd class="text-neutral-700">{{ $referrer->prospects_count }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Konversi</dt><dd class="text-neutral-700">{{ $referrer->total_conversions }} / {{ $referrer->total_clicks }}</dd></div>
                        <div class="col-span-3"><dt class="text-xs text-neutral-500">Total komisi</dt><dd class="font-medium text-neutral-900">Rp {{ number_format($referrer->total_reward ?? 0, 0, ',', '.') }}</dd></div>
                    </dl>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <x-button :href="route('admin.referrers.show', $referrer->id)" color="neutral" variant="outline">Lihat detail</x-button>
                        <x-button :href="route('admin.prospects.index', ['referrer' => $referrer->id])" color="neutral" variant="outline">Lihat prospek</x-button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-neutral-200 px-4"><x-empty-state :title="trim($search) !== '' ? 'Tidak ada afiliasi yang cocok.' : 'Belum ada afiliasi terdaftar.'" :description="trim($search) !== '' ? 'Ubah kata kunci atau hapus filter.' : null" /></div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden md:block relative bg-white rounded-lg border border-neutral-200 overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama dan email</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Kode referral</th>
                        @foreach($sortColumns as $column => $label)
                            @php [$active, $icon] = $sortHeader($column, $label); @endphp
                            <th class="px-4 py-3 {{ $column === 'reward' ? 'text-right' : 'text-left' }}" aria-sort="{{ $active ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <button type="button" wire:click="sortBy('{{ $column }}')" wire:loading.attr="disabled" wire:target="sortBy"
                                        class="inline-flex items-center gap-1 uppercase tracking-wide rounded-sm whitespace-nowrap hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:cursor-wait {{ $active ? 'text-primary-600' : '' }}">
                                    {{ $label }}
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                                </button>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($referrers as $referrer)
                        <tr class="align-middle hover:bg-neutral-50" wire:key="referrer-{{ $referrer->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.referrers.show', $referrer->id) }}" class="block rounded-sm hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                    <x-truncate :value="$referrer->user?->name" class="font-medium text-neutral-900" />
                                </a>
                                <x-truncate :value="$referrer->user?->email" class="text-xs text-neutral-500" fallback="" />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap"><span class="font-mono text-xs text-neutral-700">{{ $referrer->code }}</span></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($referrer->prospects_count > 0)
                                    <a href="{{ route('admin.prospects.index', ['referrer' => $referrer->id]) }}" title="Lihat prospek affiliator ini"
                                       class="rounded-sm font-medium text-neutral-900 underline underline-offset-4 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">{{ $referrer->prospects_count }}</a>
                                @else
                                    <span class="text-neutral-500">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">{{ $referrer->total_clicks }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">{{ $referrer->total_conversions }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">{{ $referrer->total_clicks > 0 ? number_format(($referrer->total_conversions / $referrer->total_clicks) * 100, 0, ',', '.') : 0 }}%</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-neutral-900">Rp {{ number_format($referrer->total_reward ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap"><x-badge-status :active="$referrer->status === 'active'" /></td>
                            <td class="px-4 py-3"><x-table-action-detail :href="route('admin.referrers.show', $referrer->id)" title="Detail afiliasi" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4"><x-empty-state :title="trim($search) !== '' ? 'Tidak ada afiliasi yang cocok.' : 'Belum ada afiliasi terdaftar.'" :description="trim($search) !== '' ? 'Ubah kata kunci atau hapus filter.' : null" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($referrers->hasPages())
        <div class="mt-4">{{ $referrers->links('livewire.admin.pagination') }}</div>
    @endif
</div>
