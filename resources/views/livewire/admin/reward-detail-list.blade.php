<div>

    {{-- Filter Bar --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100">
        <div class="flex flex-wrap gap-3 items-center">
            <div class="w-full md:w-auto flex-1 md:flex-none">
                <x-select-menu id="reward_status" class="md:w-56" aria-label="Status komisi"
                               wire:model.live="status" :value="$status"
                               :options="['' => 'Semua status', 'pending' => 'Pending', 'approved' => 'Disetujui / siap cair', 'disbursed' => 'Dicairkan']" />
            </div>

            @if($status !== '')
                <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('status', '')">Hapus filter</x-button>
            @endif

            <div class="flex-1"></div>
            
            <x-button form="mass_action_form" type="submit" formaction="{{ route('admin.rewards.export') }}" 
                    color="neutral" variant="outline">
                Export CSV
            </x-button>
        </div>
    </div>

    <form id="mass_action_form" method="POST" action="">
        @csrf
        <div class="overflow-x-auto transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
            <table class="min-w-full whitespace-nowrap">
                <thead class="bg-neutral-50 border-b border-neutral-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Afiliasi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Info Bank</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Pendaftar</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nominal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($rewards as $reward)
                    <tr class="hover:bg-neutral-50 transition-colors duration-100">
                        <td class="px-5 py-4">
                            <x-truncate :value="$reward->referrer?->user?->name" class="text-sm font-medium text-neutral-900" />
                            <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $reward->referrer?->code }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-neutral-900 whitespace-nowrap">{{ $reward->referrer?->bank_name ?? '—' }} · {{ $reward->referrer?->bank_account_number ?? '—' }}</div>
                            <div class="text-xs text-neutral-500 truncate max-w-xs" title="a.n {{ $reward->referrer?->bank_account_name ?? '—' }}">a.n {{ $reward->referrer?->bank_account_name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <x-truncate :value="$reward->registration?->full_name" class="text-sm font-medium text-neutral-900" />
                            <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $reward->registration?->registration_number }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm font-bold text-neutral-900 block">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                            <span class="text-xs text-neutral-500">{{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar Ulang' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <x-reward-status-badge :status="$reward->status" />
                        </td>
                        <td class="px-5 py-4 text-sm text-neutral-500 whitespace-nowrap">
                            {{ $reward->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-sm text-neutral-400">
                            Belum ada data reward.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @if($rewards->hasPages())
        <div class="px-5 py-4 border-t border-neutral-100">
            {{ $rewards->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
