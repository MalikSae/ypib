<div>
{{-- FILTER SECTION --}}
<div class="mb-6 bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
        <div class="flex flex-col sm:flex-row gap-4 items-center w-full lg:w-auto">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label for="per_page" class="text-sm font-semibold text-neutral-600 whitespace-nowrap">Tampilkan:</label>
                <x-select-menu id="per_page" class="w-24" wire:model.live="perPage" :value="$perPage" :options="$perPageOptions" />
            </div>
            
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <label for="referrer_search" class="sr-only">Cari affiliator</label>
                <x-text-input type="search" id="referrer_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama, email, atau kode" class="pl-10" />
            </div>
        </div>

        {{-- Ekspor tetap form biasa: hasilnya file unduhan. --}}
        <form method="GET" action="{{ route('admin.referrers.export') }}" class="flex gap-3 items-center w-full lg:w-auto overflow-x-auto">
            <x-select-menu id="export_period_id" name="period_id" class="w-56" aria-label="Periode ekspor"
                           :value="\App\Support\AdminPeriod::id()"
                           :options="['' => 'Semua periode'] + \App\Support\AdminPeriod::options()->mapWithKeys(fn ($p) => [$p->id => $p->name . ' (' . $p->phaseLabel() . ')'])->all()" />
            <x-select-menu id="export_status" name="status" class="w-40" aria-label="Status ekspor"
                           :value="request('status')" :options="['' => 'Semua status', 'active' => 'Aktif', 'inactive' => 'Nonaktif']" />
            <x-button type="submit" color="neutral" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Excel
            </x-button>
        </form>
    </div>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8 transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
    <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
        <span class="text-sm font-semibold text-neutral-500">{{ $referrers->total() }} afiliasi terdaftar</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50/50">
                @php
                    $sortIcon = function($column) use ($sort, $dir) {
                        $isAsc = $sort === $column && $dir === 'asc';
                        $isDesc = $sort === $column && $dir === 'desc';
                        $colorTop = $isAsc ? 'text-primary-600' : 'text-neutral-300';
                        $colorBottom = $isDesc ? 'text-primary-600' : 'text-neutral-300';
                        
                        return '<div class="flex flex-col ml-1">
                            <svg class="w-2.5 h-2.5 ' . $colorTop . ' -mb-[1px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                            <svg class="w-2.5 h-2.5 ' . $colorBottom . ' -mt-[1px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </div>';
                    };
                @endphp
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama & Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kode Referral</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <button type="button" wire:click="sortBy('prospek')" aria-label="Urutkan menurut Prospek" class="uppercase tracking-wider rounded-sm hover:text-primary-600 transition-colors inline-flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 {{ $sort === 'prospek' ? 'text-primary-600' : '' }}">
                            Prospek {!! $sortIcon('prospek') !!}
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <button type="button" wire:click="sortBy('klik')" aria-label="Urutkan menurut Klik" class="uppercase tracking-wider rounded-sm hover:text-primary-600 transition-colors inline-flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 {{ $sort === 'klik' ? 'text-primary-600' : '' }}">
                            Klik {!! $sortIcon('klik') !!}
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <button type="button" wire:click="sortBy('konversi')" aria-label="Urutkan menurut Konversi" class="uppercase tracking-wider rounded-sm hover:text-primary-600 transition-colors inline-flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 {{ $sort === 'konversi' ? 'text-primary-600' : '' }}">
                            Konversi {!! $sortIcon('konversi') !!}
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <button type="button" wire:click="sortBy('rate')" aria-label="Urutkan menurut % Konversi" class="uppercase tracking-wider rounded-sm hover:text-primary-600 transition-colors inline-flex items-center gap-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 {{ $sort === 'rate' ? 'text-primary-600' : '' }}">
                            % Konversi {!! $sortIcon('rate') !!}
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <button type="button" wire:click="sortBy('reward')" aria-label="Urutkan menurut Total Reward" class="uppercase tracking-wider rounded-sm hover:text-primary-600 transition-colors inline-flex items-center gap-1 justify-end w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 {{ $sort === 'reward' ? 'text-primary-600' : '' }}">
                            Total Reward {!! $sortIcon('reward') !!}
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-24">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($referrers as $referrer)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group" wire:key="referrer-{{ $referrer->id }}">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.referrers.show', $referrer->id) }}" title="{{ $referrer->user?->name }}" class="block text-sm font-medium text-neutral-900 hover:text-primary-600 transition-colors truncate max-w-xs">{{ $referrer->user?->name ?? '—' }}</a>
                        <x-truncate :value="$referrer->user?->email" class="text-xs text-neutral-500" fallback="" />
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-mono font-bold border border-primary-100">
                            {{ $referrer->code }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($referrer->prospects_count > 0)
                            <a href="{{ route('admin.prospects.index', ['referrer' => $referrer->id]) }}" title="Lihat prospek affiliator ini"
                               class="rounded-sm text-sm font-bold text-neutral-900 underline underline-offset-4 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">{{ $referrer->prospects_count }}</a>
                        @else
                            <span class="text-sm font-bold text-neutral-400">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-600">{{ $referrer->total_clicks }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-900">{{ $referrer->total_conversions }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-600">
                            {{ $referrer->total_clicks > 0 ? number_format(($referrer->total_conversions / $referrer->total_clicks) * 100, 0, ',', '.') : 0 }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-neutral-900">
                            Rp {{ number_format($referrer->total_reward ?? $referrer->rewards->whereIn('status',['approved','disbursed'])->sum('amount'), 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <x-badge-status :active="$referrer->status === 'active'" />
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-detail :href="route('admin.referrers.show', $referrer->id)" title="Detail afiliasi" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada afiliasi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($referrers->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $referrers->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
</div>
