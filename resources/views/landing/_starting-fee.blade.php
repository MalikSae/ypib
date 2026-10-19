{{-- Biaya pendaftaran terendah di hero. $position: "beside" (di samping tombol) atau "below" (di bawah tombol). --}}
<div data-fee-position="{{ $position }}"
     class="flex items-center gap-3 px-1 sm:px-2 {{ $position === 'below' ? 'mb-4 lg:mb-16' : '' }} relative z-20">
    <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0">
        <i class="ti ti-wallet text-base sm:text-lg"></i>
    </div>
    <div class="text-left">
        <div class="text-[10px] sm:text-[11px] font-bold text-white/40 uppercase tracking-wider mb-0.5">Biaya Pendaftaran</div>
        <div class="text-sm sm:text-base font-bold text-white whitespace-nowrap">Mulai Rp {{ number_format($startingFee, 0, ',', '.') }}</div>
    </div>
</div>
