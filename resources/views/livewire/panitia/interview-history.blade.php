<div>
<x-card class="mt-6 p-0 overflow-hidden">
    <div class="p-5 border-b border-neutral-100 flex items-center justify-between">
        <h3 class="font-bold text-neutral-800">Riwayat Interview Saya</h3>
    </div>
    
    <div class="p-5 bg-neutral-50 border-b border-neutral-100">
        <label for="history_search" class="sr-only">Cari riwayat interview</label>
        <x-text-input type="search" id="history_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama atau nomor pendaftaran" />
    </div>

    <div class="p-5 bg-white transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        @if($riwayatInterview->isEmpty())
            <div class="text-center py-6 text-neutral-500 text-sm">
                @if(trim($search) !== '')
                    Tidak ditemukan riwayat yang cocok dengan pencarian Anda.
                @else
                    Anda belum memproses interview apapun.
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach($riwayatInterview as $log)
                    <div class="p-4 bg-white border border-neutral-200 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3" wire:key="log-{{ $log->id }}">
                        <div>
                            <div class="text-xs font-mono font-bold text-neutral-500 mb-1">{{ $log->registration?->registration_number ?? 'Data tidak tersedia' }}</div>
                            <div class="font-bold text-neutral-900">{{ $log->registration?->full_name ?? 'Data tidak tersedia' }}</div>
                            <div class="text-xs font-medium text-primary-600 mt-1">{{ $log->registration?->firstChoiceProgram?->name ?? 'Data tidak tersedia' }}</div>
                            <div class="text-[10px] text-neutral-400 mt-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                        <div>
                            @if($log->registration?->status === 'diterima' || $log->registration?->status === 'menunggu_konfirmasi_daftar_ulang' || $log->registration?->status === 'daftar_ulang_selesai')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-success-50 text-success-700 border border-success-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Lulus
                                </span>
                            @elseif($log->registration?->status === 'ditolak')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-error-50 text-error-700 border border-error-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Tidak Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">
                                    {{ ucfirst(str_replace('_', ' ', $log->registration?->status ?? 'Data tidak tersedia')) }}
                                </span>
                            @endif
                        </div>
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
