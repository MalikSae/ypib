<div>
    {{-- Cari selalu tampil. Kampus dan jumlah per halaman: di mobile di balik tombol Filter. Semua langsung menyaring. --}}
    <div class="mb-4 bg-white p-4 rounded-lg border border-neutral-200"
         x-data="{ open: window.matchMedia('(min-width: 768px)').matches || {{ $campus !== '' ? 'true' : 'false' }} }"
         x-on:resize.window="if (window.innerWidth >= 768) open = true">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="relative flex-1">
                <x-input-label for="program_search" value="Cari program studi" class="sr-only" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
                <x-text-input type="search" :active="trim($search) !== ''" id="program_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari program studi atau fakultas" class="pl-10" />
            </div>
            <x-button type="button" color="neutral" variant="outline" class="md:hidden justify-between"
                      x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-controls="program-filters">
                <span>Filter</span>
                <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-500"></span>
            </x-button>
            <div id="program-filters" x-show="open" x-cloak class="flex flex-col md:flex-row md:items-end gap-3">
                <div>
                    <x-input-label for="campus" value="Kampus" />
                    <x-select-menu filter id="campus" class="w-full md:w-56" wire:model.live="campus" :value="$campus" :options="['' => 'Semua kampus'] + $campusOptions" />
                </div>
                <div>
                    <x-input-label for="per_page" value="Per halaman" />
                    <x-select-menu id="per_page" class="w-full md:w-24" wire:model.live="perPage" :value="$perPage" :options="$perPageOptions" />
                </div>
            </div>
        </div>
        @if($hasFilters)
            <div class="mt-3">
                <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
            </div>
        @endif
    </div>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        <p class="mb-3 text-xs text-neutral-500">Kuota dan biaya: periode berjalan, {{ $termsCampus->name }}.</p>

        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden space-y-3">
            @forelse($programs as $program)
                @php $terms = $program->termsFor($displayPeriod, $termsCampus); @endphp
                <div class="bg-white rounded-lg border border-neutral-200 p-4" wire:key="program-card-{{ $program->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <x-truncate :value="$program->name" class="text-sm font-medium text-neutral-900" max="max-w-full" fallback="" />
                            <x-truncate :value="$program->faculty->name" class="text-xs text-neutral-500 mt-1" max="max-w-full" fallback="" />
                        </div>
                        <x-badge-status :active="$program->is_active" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div><dt class="text-xs text-neutral-500">Kode prodi</dt><dd class="text-neutral-700">{{ $program->kode_prodi ?: 'Belum diisi' }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Tipe</dt><dd class="text-neutral-700">{{ $program->registration_track === 'non_reguler' ? 'Non-reguler' : 'Reguler' }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Kuota</dt><dd class="text-neutral-700">{{ $terms->quota }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Biaya</dt><dd class="font-medium text-neutral-900">Rp {{ number_format($terms->registration_fee, 0, ',', '.') }}</dd></div>
                    </dl>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <x-button :href="route('admin.programs.edit', $program->id)" color="neutral" variant="outline">Edit prodi</x-button>
                        <div class="flex justify-end items-center"><x-table-action-delete :action="route('admin.programs.destroy', $program->id)" text="Apakah Anda yakin ingin menghapus prodi ini?" /></div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-neutral-200 px-4"><x-empty-state :title="$hasFilters ? 'Tidak ada program studi yang cocok.' : 'Belum ada program studi terdaftar.'" :description="$hasFilters ? 'Ubah kata kunci atau hapus filter.' : null" /></div>
            @endforelse
        </div>

        {{-- Desktop: tabel (sembilan kolom; bergeser dengan scrollbar terlihat bila layar sempit) --}}
        <div class="hidden md:block relative bg-white rounded-lg border border-neutral-200 overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Program studi</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Kode prodi</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Fakultas</th>
                        <th class="px-4 py-3 text-left">Kampus</th>
                        <th class="px-4 py-3 text-left">Kuota</th>
                        <th class="px-4 py-3 text-left">Biaya</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($programs as $program)
                        @php $terms = $program->termsFor($displayPeriod, $termsCampus); @endphp
                        <tr class="align-middle hover:bg-neutral-50" wire:key="program-{{ $program->id }}">
                            <td class="px-4 py-3">
                                <x-truncate :value="$program->name" class="font-medium text-neutral-900" max="max-w-[16rem]" fallback="" />
                                <div class="text-xs text-neutral-500 whitespace-nowrap">Akreditasi: {{ $program->accreditation ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($program->kode_prodi)
                                    <span class="font-mono text-xs text-neutral-700">{{ $program->kode_prodi }}</span>
                                @else
                                    <span class="text-xs text-neutral-500">Belum diisi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">{{ $program->registration_track === 'non_reguler' ? 'Non-reguler' : 'Reguler' }}</td>
                            <td class="px-4 py-3"><x-truncate :value="$program->faculty->name" class="text-neutral-700" max="max-w-[14rem]" fallback="" /></td>
                            <td class="px-4 py-3">
                                @php $offering = collect($program->offeringCampuses ?? []); @endphp
                                <div class="flex items-center gap-1.5 whitespace-nowrap" title="{{ $offering->map(fn ($c) => $c->city ?: $c->name)->implode(', ') }}">
                                    @forelse($offering->take(2) as $offeringCampus)
                                        <span class="text-neutral-700">{{ $offeringCampus->city ?: $offeringCampus->name }}{{ ! $loop->last ? ',' : '' }}</span>
                                    @empty
                                        <span class="text-xs text-neutral-500">Belum ditawarkan</span>
                                    @endforelse
                                    @if($offering->count() > 2)
                                        <span class="text-xs text-neutral-500">+{{ $offering->count() - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-neutral-700">{{ $terms->quota }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-neutral-900">Rp {{ number_format($terms->registration_fee, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap"><x-badge-status :active="$program->is_active" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-table-action-edit :href="route('admin.programs.edit', $program->id)" title="Edit prodi" />
                                    <x-table-action-delete :action="route('admin.programs.destroy', $program->id)" text="Apakah Anda yakin ingin menghapus prodi ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4"><x-empty-state :title="$hasFilters ? 'Tidak ada program studi yang cocok.' : 'Belum ada program studi terdaftar.'" :description="$hasFilters ? 'Ubah kata kunci atau hapus filter.' : null" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($programs->hasPages())
        <div class="mt-4">{{ $programs->links('livewire.admin.pagination') }}</div>
    @endif
</div>
