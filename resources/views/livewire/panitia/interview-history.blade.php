<div>
<x-card class="mt-6 p-0 overflow-hidden">
    <div class="p-5 border-b border-neutral-100">
        <h3 class="text-sm font-semibold text-neutral-900">Riwayat interview saya</h3>
    </div>

    <div class="p-5 bg-neutral-50 border-b border-neutral-100 flex flex-col gap-3">
        <x-input-label for="history_search" value="Cari riwayat interview" class="sr-only" />
        <x-text-input type="search" :active="trim($search) !== ''" id="history_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama atau nomor pendaftaran" />
        @if(trim($search) !== '')
            <div>
                <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('search', '')" wire:loading.attr="disabled" wire:target="search">Hapus filter</x-button>
            </div>
        @endif
    </div>

    <div class="p-5 bg-white transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        @if($riwayatInterview->isEmpty())
            <x-empty-state :title="trim($search) !== '' ? 'Tidak ada riwayat yang cocok.' : 'Belum ada interview yang Anda proses.'"
                           :description="trim($search) !== '' ? 'Ubah kata kunci atau hapus filter.' : 'Hasil interview yang Anda simpan akan muncul di sini.'" />
        @else
            <div class="space-y-3">
                @foreach($riwayatInterview as $log)
                    @php $reg = $log->registration; @endphp
                    <div class="p-4 bg-white border border-neutral-200 rounded-lg flex flex-col md:flex-row md:items-center justify-between gap-3" wire:key="log-{{ $log->id }}">
                        <div class="min-w-0">
                            <div class="font-mono text-xs text-neutral-500">{{ $reg?->registration_number ?? 'Data tidak tersedia' }}</div>
                            <x-truncate :value="$reg?->full_name ?? 'Data tidak tersedia'" class="text-sm font-medium text-neutral-900 mt-1" max="max-w-full" />
                            <div class="text-xs text-neutral-500 mt-1">{{ $reg?->firstChoiceProgram?->name ?? 'Data tidak tersedia' }} · {{ $log->created_at->translatedFormat('d M Y, H:i') }}</div>
                        </div>
                        @if($reg)
                            {{-- Label dan warna dari Registration::STATUS_LABELS, sama dengan halaman admin. --}}
                            <span class="shrink-0 self-start md:self-auto inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold whitespace-nowrap {{ $reg->statusBadgeClass() }}">{{ $reg->getStatusLabel() }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $riwayatInterview->links('livewire.admin.pagination') }}
            </div>
        @endif
    </div>
</x-card>
</div>
