<div>
    {{-- Filter status langsung menyaring. --}}
    <div class="px-4 md:px-5 pt-4 md:pt-5 pb-4 border-b border-neutral-100">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <x-select-menu id="reward_status" class="w-full md:w-56" aria-label="Status komisi"
                           wire:model.live="status" :value="$status"
                           :options="['' => 'Semua status', 'pending' => 'Pending', 'approved' => 'Disetujui / siap cair', 'disbursed' => 'Dicairkan']" />

            @if($status !== '')
                <x-button color="neutral" variant="ghost" size="sm" wire:click="$set('status', '')" wire:loading.attr="disabled" wire:target="status">Hapus filter</x-button>
            @endif

            <div class="hidden md:block flex-1"></div>

            {{-- Berkas pencairan: selalu berisi komisi berstatus siap cair, tidak mengikuti filter di atas. --}}
            <x-button form="mass_action_form" type="submit" formaction="{{ route('admin.rewards.export') }}"
                      color="neutral" variant="outline" title="Berisi semua komisi berstatus siap cair">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Ekspor siap cair (CSV)
            </x-button>
        </div>
    </div>

    <form id="mass_action_form" method="POST" action="">
        @csrf
    </form>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden divide-y divide-neutral-100">
            @forelse($rewards as $reward)
                <div class="p-4" wire:key="reward-card-{{ $reward->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <x-truncate :value="$reward->referrer?->user?->name" class="text-sm font-medium text-neutral-900" max="max-w-full" />
                            <div class="font-mono text-xs text-neutral-500 mt-1">{{ $reward->referrer?->code }}</div>
                        </div>
                        <x-reward-status-badge :status="$reward->status" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div><dt class="text-xs text-neutral-500">Nominal</dt><dd class="font-medium text-neutral-900">Rp {{ number_format($reward->amount, 0, ',', '.') }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Jenis</dt><dd class="text-neutral-700">{{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar ulang' }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs text-neutral-500">Pendaftar</dt><dd><x-truncate :value="$reward->registration?->full_name" class="text-neutral-700" max="max-w-full" /></dd></div>
                        <div class="col-span-2"><dt class="text-xs text-neutral-500">Rekening</dt><dd class="text-neutral-700">{{ $reward->referrer?->bank_name ?? '—' }} · {{ $reward->referrer?->bank_account_number ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Tanggal</dt><dd class="text-neutral-700">{{ $reward->created_at->format('d/m/Y') }}</dd></div>
                    </dl>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-neutral-500">{{ $status !== '' ? 'Tidak ada komisi dengan status ini.' : 'Belum ada data komisi.' }}</div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden md:block">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Afiliasi</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Info bank</th>
                        <th class="px-5 py-3 text-left">Pendaftar</th>
                        <th class="px-5 py-3 text-left">Nominal</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($rewards as $reward)
                        <tr class="align-middle hover:bg-neutral-50" wire:key="reward-{{ $reward->id }}">
                            <td class="px-5 py-3">
                                <x-truncate :value="$reward->referrer?->user?->name" class="font-medium text-neutral-900" />
                                <div class="font-mono text-xs text-neutral-500">{{ $reward->referrer?->code }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="text-neutral-900 whitespace-nowrap">{{ $reward->referrer?->bank_name ?? '—' }} · {{ $reward->referrer?->bank_account_number ?? '—' }}</div>
                                <x-truncate :value="$reward->referrer?->bank_account_name ? 'a.n ' . $reward->referrer->bank_account_name : null" class="text-xs text-neutral-500" />
                            </td>
                            <td class="px-5 py-3">
                                <x-truncate :value="$reward->registration?->full_name" class="font-medium text-neutral-900" />
                                <div class="font-mono text-xs text-neutral-500">{{ $reward->registration?->registration_number }}</div>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-neutral-900">Rp {{ number_format($reward->amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-neutral-500">{{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar ulang' }}</div>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap"><x-reward-status-badge :status="$reward->status" /></td>
                            <td class="px-5 py-3 whitespace-nowrap text-neutral-700">{{ $reward->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-sm text-neutral-500">{{ $status !== '' ? 'Tidak ada komisi dengan status ini.' : 'Belum ada data komisi.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($rewards->hasPages())
        <div class="px-4 md:px-5 py-4 border-t border-neutral-100">
            {{ $rewards->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
