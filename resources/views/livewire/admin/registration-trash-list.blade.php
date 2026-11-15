<div class="bg-white rounded-lg border border-neutral-200 overflow-hidden">
    {{-- Cari langsung menyaring. --}}
    <div class="px-4 md:px-5 pt-4 md:pt-5 pb-4 border-b border-neutral-100 flex flex-col md:flex-row md:items-center gap-3">
        <div class="relative w-full md:max-w-md">
            <x-input-label for="trash_search" value="Cari arsip pendaftar" class="sr-only" />
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg class="w-4 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </div>
            <x-text-input type="search" :active="trim($search) !== ''" id="trash_search" wire:model.live.debounce.300ms="search" autocomplete="off"
                          placeholder="Cari nama atau nomor pendaftaran" class="pl-10" />
        </div>
        @if(trim($search) !== '')
            <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('search', '')" wire:loading.attr="disabled" wire:target="search">Hapus filter</x-button>
        @endif
    </div>

    <div class="overflow-x-auto transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        <table class="min-w-full">
            <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                <tr>
                    <th class="px-4 md:px-5 py-3 text-left">Pendaftar</th>
                    <th class="px-4 md:px-5 py-3 text-left whitespace-nowrap">Program studi</th>
                    <th class="px-4 md:px-5 py-3 text-left whitespace-nowrap">Tanggal dihapus</th>
                    <th class="px-4 md:px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 text-sm">
                @forelse($registrations as $reg)
                    <tr class="align-middle hover:bg-neutral-50" wire:key="trash-{{ $reg->id }}">
                        <td class="px-4 md:px-5 py-3">
                            <x-truncate :value="$reg->full_name" class="font-medium text-neutral-900" max="max-w-[12rem] md:max-w-xs" fallback="" />
                            <div class="font-mono text-xs text-neutral-500">{{ $reg->registration_number ?? 'Belum ada nomor' }}</div>
                        </td>
                        <td class="px-4 md:px-5 py-3">
                            <x-truncate :value="$reg->firstChoiceProgram?->name" class="text-neutral-700" max="max-w-[10rem] md:max-w-[14rem]" />
                        </td>
                        <td class="px-4 md:px-5 py-3 whitespace-nowrap">
                            <div class="text-neutral-900">{{ $reg->deleted_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-neutral-500">{{ $reg->deleted_at->format('H:i') }} WIB</div>
                        </td>
                        <td class="px-4 md:px-5 py-3">
                            <form method="POST" action="{{ route('admin.registrations.restore', $reg->id) }}" data-confirm="Yakin ingin memulihkan pendaftar ini? Reward komisi tidak akan dipulihkan secara otomatis.">
                                @csrf
                                <x-button type="submit" size="sm">Pulihkan</x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4">
                            <x-empty-state :title="trim($search) !== '' ? 'Tidak ada arsip yang cocok.' : 'Tidak ada arsip.'"
                                           :description="trim($search) !== '' ? 'Ubah kata kunci atau hapus filter.' : 'Pendaftar yang dihapus akan muncul di sini.'" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($registrations->hasPages())
        <div class="px-4 md:px-5 py-4 border-t border-neutral-100">
            {{ $registrations->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
