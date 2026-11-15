<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-0">

    {{-- Cari, status, prodi, ekspor, arsip. Semua langsung menyaring. --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px]">
            <label for="registration_search" class="sr-only">Cari pendaftar</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </div>
            <x-text-input type="search" id="registration_search" wire:model.live.debounce.300ms="search" autocomplete="off"
                          placeholder="Cari nama atau nomor pendaftaran" class="pl-10" />
        </div>

        @php
            $statusOptions = ['' => 'Semua status (' . $totalRegistrations . ')'];
            foreach (\App\Models\Registration::STATUS_LABELS as $statusKey => $cfg) {
                $statusOptions[$statusKey] = $cfg['label'] . ' (' . ($statusCounts[$statusKey] ?? 0) . ')';
            }
        @endphp
        <x-select-menu id="filter_status" class="w-full md:w-64" aria-label="Filter status"
                       wire:model.live="status" :value="$status" :options="$statusOptions" />

        <x-select-menu id="filter_prodi" class="w-full md:w-56" aria-label="Filter program studi"
                       wire:model.live="prodi" :value="$prodi"
                       :options="['' => 'Semua program studi'] + $programs" />

        {{-- Ekspor tetap form biasa (hasilnya file) dan memakai filter yang sedang aktif. --}}
        <form method="GET" action="{{ route('admin.registrations.export') }}">
            @foreach(['prodi' => $prodi, 'status' => $status, 'search' => trim($search)] as $filter => $filterValue)
                @if($filterValue !== '')
                    <input type="hidden" name="{{ $filter }}" value="{{ $filterValue }}">
                @endif
            @endforeach
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                <x-button type="button" color="neutral" variant="outline" x-on:click="open = !open" aria-haspopup="true">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Export
                </x-button>
                <div x-show="open" x-cloak class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-60 bg-white border border-neutral-200 rounded-xl shadow-lg z-20 py-1">
                    <div class="px-4 pt-2 pb-1 text-xs text-neutral-500">Sesuai filter yang sedang aktif</div>
                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50">Excel (.xlsx)</button>
                    <button type="submit" name="format" value="csv" class="w-full text-left px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50">
                        CSV <span class="block text-xs text-neutral-500">Untuk data besar (ribuan baris)</span>
                    </button>
                </div>
            </div>
        </form>

        <x-button :href="route('admin.registrations.trash')" color="neutral" variant="outline" title="Lihat pendaftar yang diarsipkan">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
            Arsip
        </x-button>

        @if($hasFilters)
            <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- TABLE --}}
    {{-- ============================================================ --}}
    <div class="overflow-x-auto transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        <table class="min-w-full whitespace-nowrap whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pendaftar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">No. Daftar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Jalur</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Referral</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tanggal</th>
                    <th class="px-5 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($registrations as $reg)
                    @php
                        $color = $reg->getStatusColor();
                        $bgClass = match($color) {
                            'warning' => 'bg-warning-50 text-warning-700',
                            'info' => 'bg-info-50 text-info-700',
                            'success' => 'bg-success-50 text-success-700',
                            'error' => 'bg-error-50 text-error-700',
                            'primary' => 'bg-primary-50 text-primary-700',
                            default => 'bg-neutral-100 text-neutral-600',
                        };
                    @endphp
                    <tr class="hover:bg-neutral-50 transition-colors duration-100 group" wire:key="registration-{{ $reg->id }}">
                        {{-- Pendaftar (Nama + Email) --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2 max-w-xs">
                                <span class="text-sm font-medium text-neutral-900 group-hover:text-primary-700 transition-colors truncate" title="{{ $reg->full_name }}">{{ $reg->full_name }}</span>
                                @if(isset($duplicateNiks[$reg->nik]))
                                    <span title="NIK ini dipakai lebih dari satu pendaftaran" class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-warning-50 text-warning-700 border border-warning-200 whitespace-nowrap">NIK ganda</span>
                                @endif
                            </div>
                            <x-truncate :value="$reg->user?->email" class="text-xs text-neutral-500" fallback="" />
                        </td>

                        {{-- No. Daftar --}}
                        <td class="px-5 py-4">
                            @if($reg->registration_number)
                                <span class="font-mono text-xs font-medium text-neutral-500">{{ $reg->registration_number }}</span>
                            @else
                                <span class="text-xs text-neutral-300 italic">—</span>
                            @endif
                        </td>

                        {{-- Pilihan Prodi --}}
                        <td class="px-5 py-4">
                            <x-truncate :value="$reg->firstChoiceProgram?->name" class="text-sm text-neutral-700" max="max-w-[14rem]" />
                        </td>

                        {{-- Jalur Pendaftaran --}}
                        <td class="px-5 py-4">
                            @php
                                $pathLabels = ['umum' => 'Reguler', 'prestasi' => 'Prestasi', 'tahfidz' => 'Tahfidz'];
                                $pathColors = [
                                    'umum'     => 'bg-neutral-100 text-neutral-600',
                                    'prestasi' => 'bg-primary-50 text-primary-700',
                                    'tahfidz'  => 'bg-primary-100 text-primary-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $pathColors[$reg->admission_path] ?? 'bg-neutral-100 text-neutral-600' }}">
                                {{ $pathLabels[$reg->admission_path] ?? $reg->admission_path ?? '—' }}
                            </span>
                        </td>

                        {{-- Referral --}}
                        <td class="px-5 py-4">
                            @if($reg->referrer)
                                <span class="inline-flex items-center px-2 py-0.5 bg-neutral-100 text-neutral-700 rounded-md text-xs font-mono font-medium border border-neutral-200">
                                    {{ $reg->referrer->code }}
                                </span>
                            @else
                                <span class="text-neutral-300">—</span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $bgClass }}">
                                {{ $reg->getStatusLabel() }}
                            </span>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-600">{{ $reg->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-neutral-400 mt-0.5">{{ $reg->created_at->format('H:i') }} WIB</div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('admin.registrations.show', $reg->id) }}"
                               class="decoration-none inline-flex items-center justify-center w-8 h-8 rounded-lg text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 transition-colors duration-150"
                               title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-sm text-neutral-500">{{ $hasFilters || $status !== '' ? 'Tidak ada pendaftar yang cocok.' : 'Belum ada data pendaftar.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($registrations->hasPages())
        <div class="px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
