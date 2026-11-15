<div>
    {{-- Cari selalu tampil. Peran dan kampus: di mobile di balik tombol Filter. Semua langsung menyaring. --}}
    <div class="mb-4" x-data="{ open: window.matchMedia('(min-width: 768px)').matches || {{ $role !== '' || $campus !== '' ? 'true' : 'false' }} }"
         x-on:resize.window="if (window.innerWidth >= 768) open = true">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex-1">
                <x-input-label for="staff_search" value="Cari akun" class="sr-only" />
                <x-text-input type="search" :active="trim($search) !== ''" id="staff_search" wire:model.live.debounce.300ms="search" autocomplete="off" placeholder="Cari nama atau email" />
            </div>
            <x-button type="button" color="neutral" variant="outline" class="md:hidden justify-between"
                      x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-controls="staff-filters">
                <span>Filter</span>
                <span x-text="open ? 'Tutup' : 'Buka'" class="text-xs text-neutral-500"></span>
            </x-button>
            <div id="staff-filters" x-show="open" x-cloak class="flex flex-col md:flex-row gap-3">
                <x-select-menu filter id="filter_role" class="w-full md:w-48" wire:model.live="role" aria-label="Filter peran" :value="$role" :options="['' => 'Semua peran'] + $roles" />
                <x-select-menu filter id="filter_campus" class="w-full md:w-56" wire:model.live="campus" aria-label="Filter kampus" :value="$campus" :options="['' => 'Semua kampus', 'none' => 'Akses semua kampus'] + $campuses" />
            </div>
            @if($hasFilters)
                <x-button color="neutral" variant="ghost" size="sm" wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters">Hapus filter</x-button>
            @endif
        </div>
    </div>

    <div class="transition-opacity motion-reduce:transition-none" wire:loading.class="opacity-60">
        {{-- Mobile: daftar kartu --}}
        <div class="md:hidden space-y-3">
            @forelse($staff as $account)
                <div class="bg-white rounded-lg border border-neutral-200 p-4 {{ $account->is_active ? '' : 'opacity-70' }}" wire:key="staff-card-{{ $account->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <x-truncate :value="$account->name" class="text-sm font-medium text-neutral-900" max="max-w-full" />
                                @if($account->id === auth()->id())
                                    <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-primary-50 text-primary-700 border-primary-200">Anda</span>
                                @endif
                            </div>
                            <x-truncate :value="$account->email" class="text-xs text-neutral-500 mt-1" max="max-w-full" fallback="" />
                        </div>
                        @include('livewire.admin.partials.staff-status', ['account' => $account])
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 text-sm">
                        <div><dt class="text-xs text-neutral-500">Peran</dt><dd class="text-neutral-700">{{ $roles[$account->role] ?? $account->role }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Kampus</dt><dd class="text-neutral-700">{{ $account->campus?->name ?? 'Semua kampus' }}</dd></div>
                    </dl>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <x-button :href="route('admin.staff.edit', $account->id)" color="neutral" variant="outline">Edit akun</x-button>
                        <x-button :href="route('admin.staff.edit', $account->id) . '#reset-password'" color="neutral" variant="outline">Reset sandi</x-button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-neutral-200 px-4"><x-empty-state title="Tidak ada akun yang cocok." /></div>
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden md:block bg-white rounded-lg border border-neutral-200 overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-left">Peran</th>
                        <th class="px-6 py-3 text-left">Kampus</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm">
                    @forelse($staff as $account)
                        <tr class="align-middle hover:bg-neutral-50 {{ $account->is_active ? '' : 'opacity-70' }}" wire:key="staff-{{ $account->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2 max-w-xs">
                                    <x-truncate :value="$account->name" class="font-medium text-neutral-900" max="max-w-full" />
                                    @if($account->id === auth()->id())
                                        <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-primary-50 text-primary-700 border-primary-200">Anda</span>
                                    @endif
                                </div>
                                <x-truncate :value="$account->email" class="text-xs text-neutral-500" fallback="" />
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-neutral-700">{{ $roles[$account->role] ?? $account->role }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-neutral-700">{{ $account->campus?->name ?? 'Semua kampus' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">@include('livewire.admin.partials.staff-status', ['account' => $account])</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <x-table-action-edit :href="route('admin.staff.edit', $account->id)" title="Edit akun" />
                                    <x-button :href="route('admin.staff.edit', $account->id) . '#reset-password'" color="neutral" variant="outline" size="sm"
                                              class="!px-2 min-w-11 md:min-w-9" title="Reset sandi" aria-label="Reset sandi">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                        </svg>
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4"><x-empty-state title="Tidak ada akun yang cocok." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($staff->hasPages())
        <div class="mt-4">{{ $staff->links('livewire.admin.pagination') }}</div>
    @endif
</div>
