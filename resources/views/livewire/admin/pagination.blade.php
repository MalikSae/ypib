{{-- Paginasi untuk daftar Livewire di admin: pindah halaman tanpa muat ulang. --}}
<div>
    @if($paginator->hasPages())
        <div class="flex items-center justify-between gap-3">
            <x-button type="button" color="neutral" variant="outline" size="sm"
                      wire:click="previousPage('{{ $paginator->getPageName() }}')" :disabled="$paginator->onFirstPage()">Sebelumnya</x-button>
            <span class="text-sm text-neutral-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }} · {{ $paginator->total() }} data</span>
            <x-button type="button" color="neutral" variant="outline" size="sm"
                      wire:click="nextPage('{{ $paginator->getPageName() }}')" :disabled="! $paginator->hasMorePages()">Berikutnya</x-button>
        </div>
    @endif
</div>
