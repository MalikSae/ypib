<div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
    @php
        $statusOptions = ['' => 'Semua status (' . $totalRegistrations . ')'];
        foreach (\App\Models\Registration::STATUS_LABELS as $statusKey => $cfg) {
            $statusOptions[$statusKey] = $cfg['label'] . ' (' . ($statusCounts[$statusKey] ?? 0) . ')';
        }
    @endphp

    {{-- Cari selalu tampil. Status dan prodi: di mobile di balik tombol Filter. Semua langsung menyaring. --}}
    <div class="px-4 md:px-5 pt-4 md:pt-5 pb-4 border-b border-neutral-100"
         x-data="{ open: window.matchMedia('(min-width: 768px)').matches || {{ $status !== '' || $prodi !== '' ? 'true' : 'false' }} }"
         x-on:resize.window="if (window.innerWidth >= 768) open = true">
        <div class="flex flex-col md:flex-row md:flex-wrap md:items-center gap-3">
            <div class="relative flex-1 md:min-w-[16rem]">
                <x-input-label for="registration_search" value="Cari pendaftar" class="sr-only" />
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
                <x-text-input type="search" :active="trim($search) !== ''" id="registration_search" wire:model.live.debounce.300ms="search" autocomplete="off"
                              placeholder="Cari nama atau nomor pendaftaran" class="pl-10" />
            </div>

            <x-button type="button" color="neutral" variant="outline" class="md:hidden justify-between"
                      x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-controls="registration-filters">
                <span>Filter</span>
                <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-500"></span>
            </x-button>

            <div id="registration-filters" x-show="open" x-cloak class="flex flex-col md:flex-row gap-3">
                <x-select-menu filter id="filter_status" class="w-full md:w-64" aria-label="Filter status"
                               wire:model.live="status" :value="$status" :options="$statusOptions" />
                <x-select-menu filter id="filter_prodi" class="w-full md:w-56" aria-label="Filter program studi"
                               wire:model.live="prodi" :value="$prodi"
                               :options="['' => 'Semua program studi'] + $programs" />
            </div>

            <div class="flex gap-3">
                {{-- Ekspor tetap form biasa (hasilnya file) dan memakai filter yang sedang aktif. --}}
                <form method="GET" action="{{ route('admin.registrations.export') }}" class="flex-1 md:flex-none">
                    @foreach(['prodi' => $prodi, 'status' => $status, 'search' => trim($search)] as $filter => $filterValue)
                        @if($filterValue !== '')
                            <input type="hidden" name="{{ $filter }}" value="{{ $filterValue }}">
                        @endif
                    @endforeach
                    <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                        <x-button type="button" color="neutral" variant="outline" class="w-full" x-on:click="open = !open" aria-haspopup="true">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Ekspor
                        </x-button>
                        <div x-show="open" x-cloak class="absolute left-0 md:left-auto md:right-0 mt-2 w-60 bg-white border border-neutral-200 rounded-lg shadow-lg z-20 py-1">
                            <div class="px-4 pt-2 pb-1 text-xs text-neutral-500">Sesuai filter yang sedang aktif</div>
                            <button type="submit" class="w-full text-left px-4 min-h-11 text-sm text-neutral-700 hover:bg-neutral-50 focus:outline-none focus-visible:bg-neutral-50">Excel (.xlsx)</button>
                            <button type="submit" name="format" value="csv" class="w-full text-left px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-50 focus:outline-none focus-visible:bg-neutral-50">
                                CSV <span class="block text-xs text-neutral-500">Untuk data besar (ribuan baris)</span>
                            </button>
                        </div>
                    </div>
                </form>

                <x-button :href="route('admin.registrations.trash')" color="neutral" variant="outline" class="flex-1 md:flex-none" title="Lihat pendaftar yang diarsipkan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    Arsip
                </x-button>
            </div>

            @if($hasFilters)
                <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
            @endif
        </div>
    </div>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden divide-y divide-neutral-100">
            @forelse($registrations as $reg)
                <div class="p-4" wire:key="registration-card-{{ $reg->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('admin.registrations.show', $reg->id) }}" class="min-w-0 flex-1 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                            <x-truncate :value="$reg->full_name" class="text-sm font-medium text-neutral-900 underline underline-offset-4" max="max-w-full" />
                            <x-truncate :value="$reg->user?->email" class="text-xs text-neutral-500 mt-1" max="max-w-full" fallback="" />
                        </a>
                        <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold whitespace-nowrap {{ $reg->statusBadgeClass() }}">{{ $reg->getStatusLabel() }}</span>
                    </div>
                    @if(isset($duplicateNiks[$reg->nik]))
                        <span title="NIK ini dipakai lebih dari satu pendaftaran" class="mt-2 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-warning-50 text-warning-700 border-warning-200">NIK ganda</span>
                    @endif
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div class="col-span-2">
                            <dt class="text-xs text-neutral-500">Program studi</dt>
                            <dd><x-truncate :value="$reg->firstChoiceProgram?->name" class="text-neutral-700" max="max-w-full" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Jalur</dt>
                            <dd class="text-neutral-700">{{ $reg->admissionPathLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-neutral-500">Tanggal daftar</dt>
                            <dd class="text-neutral-700">{{ $reg->created_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                    <x-button :href="route('admin.registrations.show', $reg->id)" color="neutral" variant="outline" class="mt-4 w-full">Lihat detail</x-button>
                </div>
            @empty
                <x-empty-state :title="$hasFilters ? 'Tidak ada pendaftar yang cocok.' : 'Belum ada data pendaftar.'" :description="$hasFilters ? 'Ubah kata kunci atau hapus filter.' : null" />
            @endforelse
        </div>

        {{-- Desktop: tabel (delapan kolom; bergeser dengan scrollbar terlihat bila layar sempit) --}}
        <div class="hidden md:block relative overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Pendaftar</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">No. daftar</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Program studi</th>
                        <th class="px-5 py-3 text-left">Jalur</th>
                        <th class="px-5 py-3 text-left">Referral</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($registrations as $reg)
                        <tr class="align-middle hover:bg-neutral-50" wire:key="registration-{{ $reg->id }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 max-w-xs">
                                    <x-truncate :value="$reg->full_name" class="font-medium text-neutral-900" max="max-w-full" />
                                    @if(isset($duplicateNiks[$reg->nik]))
                                        <span title="NIK ini dipakai lebih dari satu pendaftaran" class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-warning-50 text-warning-700 border-warning-200 whitespace-nowrap">NIK ganda</span>
                                    @endif
                                </div>
                                <x-truncate :value="$reg->user?->email" class="text-xs text-neutral-500" fallback="" />
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                @if($reg->registration_number)
                                    <span class="font-mono text-xs text-neutral-700">{{ $reg->registration_number }}</span>
                                @else
                                    <span class="text-neutral-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <x-truncate :value="$reg->firstChoiceProgram?->name" class="text-neutral-700" max="max-w-[14rem]" />
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-neutral-700">{{ $reg->admissionPathLabel() }}</td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                @if($reg->referrer)
                                    <span class="font-mono text-xs text-neutral-700">{{ $reg->referrer->code }}</span>
                                @else
                                    <span class="text-neutral-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $reg->statusBadgeClass() }}">{{ $reg->getStatusLabel() }}</span>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <div class="text-neutral-700">{{ $reg->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-neutral-500">{{ $reg->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-3">
                                <x-table-action-detail :href="route('admin.registrations.show', $reg->id)" title="Lihat detail pendaftar" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4"><x-empty-state :title="$hasFilters ? 'Tidak ada pendaftar yang cocok.' : 'Belum ada data pendaftar.'" :description="$hasFilters ? 'Ubah kata kunci atau hapus filter.' : null" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($registrations->hasPages())
        <div class="px-4 md:px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
