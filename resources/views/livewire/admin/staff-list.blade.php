<div>
{{-- FILTER --}}
<div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
    <div class="sm:col-span-2">
        <label for="staff_search" class="sr-only">Cari akun</label>
        <x-text-input type="search" id="staff_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama atau email" />
    </div>
    <x-select-menu id="filter_role" wire:model.live="role" aria-label="Filter peran" :value="$role" :options="['' => 'Semua peran'] + $roles" />
    <x-select-menu id="filter_campus" wire:model.live="campus" aria-label="Filter kampus" :value="$campus" :options="['' => 'Semua kampus', 'none' => 'Akses semua kampus'] + $campuses" />
</div>
@if($hasFilters)
    <div class="mb-3">
        <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
    </div>
@endif

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8 transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
    <div class="overflow-x-auto">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Peran</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kampus</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-40">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($staff as $account)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 {{ $account->is_active ? '' : 'opacity-70' }}" wire:key="staff-{{ $account->id }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 max-w-xs">
                            <span class="text-sm font-medium text-neutral-900 truncate" title="{{ $account->name }}">{{ $account->name }}</span>
                            @if($account->id === auth()->id())
                                <span class="shrink-0 px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 border border-primary-200 text-xs font-semibold whitespace-nowrap">Anda</span>
                            @endif
                        </div>
                        <x-truncate :value="$account->email" class="text-xs text-neutral-500" fallback="" />
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-3 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-200 whitespace-nowrap">
                            {{ $roles[$account->role] ?? $account->role }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-neutral-600 whitespace-nowrap">
                        {{ $account->campus?->name ?? 'Semua kampus' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($account->id === auth()->id())
                            <x-badge-status :active="$account->is_active" />
                        @elseif($account->is_active)
                            {{-- Menonaktifkan memutus sesi akun seketika: minta konfirmasi (event submit naik ke pembungkus) --}}
                            <div data-confirm="{{ 'Nonaktifkan akun ' . $account->name . '? Sesi login akun ini akan diakhiri.' }}">
                                <x-badge-status :active="true" :toggle-action="route('admin.staff.toggle', $account->id)" />
                            </div>
                        @else
                            <x-badge-status :active="false" :toggle-action="route('admin.staff.toggle', $account->id)" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <x-table-action-edit :href="route('admin.staff.edit', $account->id)" />
                            <a href="{{ route('admin.staff.edit', $account->id) }}#reset-password" class="decoration-none">
                                <x-button type="button" color="neutral" variant="outline" size="sm" title="Reset password" aria-label="Reset password" class="!px-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                    </svg>
                                </x-button>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Tidak ada akun yang cocok.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $staff->links('livewire.admin.pagination') }}
        </div>
    @endif
</div>
</div>
