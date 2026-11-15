<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-0">

    {{-- Search Bar Row --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100 flex flex-col md:flex-row justify-between gap-4">
        <div class="flex gap-3 flex-1 items-center">
            <div class="flex-1 relative max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <label for="trash_search" class="sr-only">Cari arsip pendaftar</label>
                <x-text-input type="search" id="trash_search" wire:model.live.debounce.300ms="search" autocomplete="off"
                       placeholder="Cari nama atau nomor pendaftaran" class="pl-10" />
            </div>
            @if(trim($search) !== '')
                <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('search', '')">Hapus filter</x-button>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLE --}}
    {{-- ============================================================ --}}
    <div class="overflow-x-auto transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">No. Registrasi / Nama</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Program Studi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tanggal Dihapus</th>
                    <th class="px-5 py-3 w-10 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($registrations as $reg)
                    <tr class="hover:bg-neutral-50 transition-colors duration-100 group" wire:key="trash-{{ $reg->id }}">
                        {{-- Pendaftar (Nama + No. Daftar) --}}
                        <td class="px-5 py-4">
                            <x-truncate :value="$reg->full_name" class="text-sm font-medium text-neutral-900" fallback="" />
                            <div class="text-xs text-neutral-500 font-mono mt-0.5">{{ $reg->registration_number ?? 'Belum ada nomor' }}</div>
                        </td>

                        {{-- Pilihan Prodi --}}
                        <td class="px-5 py-4">
                            <x-truncate :value="$reg->firstChoiceProgram?->name" class="text-sm text-neutral-700" max="max-w-[14rem]" />
                        </td>

                        {{-- Tanggal Dihapus --}}
                        <td class="px-5 py-4">
                            <div class="text-sm text-neutral-900">{{ $reg->deleted_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-neutral-400">{{ $reg->deleted_at->format('H:i') }} WIB</div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-right">
                            <form method="POST" action="{{ route('admin.registrations.restore', $reg->id) }}" data-confirm="Yakin ingin memulihkan pendaftar ini? Reward komisi tidak akan dipulihkan secara otomatis.">
                                @csrf
                                <x-button type="submit" size="sm">
                                    Pulihkan
                                </x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-neutral-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-neutral-900 mb-1">Tidak Ada Arsip</h3>
                            <p class="text-sm text-neutral-500 max-w-sm mx-auto">Semua data pendaftar aktif atau tidak ada pencarian yang cocok.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($registrations->hasPages())
        <div class="px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
