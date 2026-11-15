<div>
{{-- FILTER SECTION --}}
<div class="mb-6 bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="per_page" class="text-sm font-semibold text-neutral-600 whitespace-nowrap">Tampilkan:</label>
            <x-select-menu id="per_page" class="w-24" wire:model.live="perPage" :value="$perPage" :options="$perPageOptions" />
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="campus" class="text-sm font-semibold text-neutral-600 whitespace-nowrap">Kampus:</label>
            <x-select-menu id="campus" class="w-full sm:w-56" wire:model.live="campus" :value="$campus" :options="['' => 'Semua kampus'] + $campusOptions" />
        </div>
        
        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <label for="program_search" class="sr-only">Cari program studi</label>
            <x-text-input type="search" id="program_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari program studi atau fakultas" class="pl-10" />
        </div>
    </div>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8 transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kode Prodi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Fakultas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kampus</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kuota <span class="normal-case font-normal">(periode berjalan, {{ $termsCampus->name }})</span></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Biaya</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($programs as $program)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group" wire:key="program-{{ $program->id }}">
                    <td class="px-6 py-4">
                        <x-truncate :value="$program->name" class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors" fallback="" />
                        <div class="text-xs text-neutral-500">Akreditasi: {{ $program->accreditation ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($program->kode_prodi)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-mono font-bold border border-neutral-200">{{ $program->kode_prodi }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-400 text-xs font-medium border border-neutral-200 border-dashed">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($program->registration_track === 'non_reguler')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold border border-purple-200">Non-Reguler</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold border border-primary-200">Reguler</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <x-truncate :value="$program->faculty->name" class="text-sm text-neutral-700" max="max-w-[14rem]" fallback="" />
                    </td>
                    <td class="px-6 py-4">
                        @php $offering = collect($program->offeringCampuses ?? []); @endphp
                        <div class="flex items-center gap-1.5 whitespace-nowrap" title="{{ $offering->map(fn ($c) => $c->city ?: $c->name)->implode(', ') }}">
                            @forelse($offering->take(2) as $offeringCampus)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold border border-neutral-200">{{ $offeringCampus->city ?: $offeringCampus->name }}</span>
                            @empty
                                <span class="text-xs text-neutral-500">Belum ditawarkan</span>
                            @endforelse
                            @if($offering->count() > 2)
                                <span class="text-xs text-neutral-500">+{{ $offering->count() - 2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-200">
                            {{ $program->termsFor($displayPeriod, $termsCampus)->quota }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-neutral-900">Rp {{ number_format($program->termsFor($displayPeriod, $termsCampus)->registration_fee, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        {{-- Toggle Status --}}
                        <form action="{{ route('admin.programs.destroy', $program->id) }}" method="POST" class="m-0 inline-block">
                            {{-- We don't have a toggle endpoint yet for programs, but if there's one, it goes here. For now, just display status --}}
                            @if($program->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 bg-neutral-900 text-white rounded-full text-xs font-semibold">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-neutral-100 text-neutral-500 border border-neutral-200 rounded-full text-xs font-semibold">Nonaktif</span>
                            @endif
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.programs.edit', $program->id)" />
                            
                            <x-table-action-delete :action="route('admin.programs.destroy', $program->id)" text="Apakah Anda yakin ingin menghapus prodi ini?" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada program studi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($programs->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $programs->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
</div>
