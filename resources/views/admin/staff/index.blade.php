@extends('layouts.admin')
@section('title', 'Akun Staf — Admin PMB YPIB')
@section('page-title', 'Akun Staf')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Akun Staf</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Kelola admin, operator, dan panitia. Akun tidak dihapus, hanya dinonaktifkan.</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="decoration-none shrink-0">
        <x-button type="button">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Akun
        </x-button>
    </a>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('admin.staff.index') }}" class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <div class="sm:col-span-2">
        <x-text-input type="text" name="search" :value="$search" placeholder="Cari nama atau email..." />
    </div>
    <x-select name="role" onchange="this.form.submit()">
        <option value="">Semua peran</option>
        @foreach($roles as $value => $label)
            <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
        @endforeach
    </x-select>
    <x-select name="campus" onchange="this.form.submit()">
        <option value="">Semua kampus</option>
        <option value="none" @selected($campus === 'none')>Akses semua kampus</option>
        @foreach($campuses as $option)
            <option value="{{ $option->id }}" @selected($campus === (string) $option->id)>{{ $option->name }}</option>
        @endforeach
    </x-select>
</form>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="min-w-full">
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
                <tr class="hover:bg-neutral-50 transition-colors duration-100 {{ $account->is_active ? '' : 'opacity-70' }}">
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-neutral-900">
                            {{ $account->name }}
                            @if($account->id === auth()->id())
                                <span class="ml-1 px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 text-xs font-bold whitespace-nowrap">Anda</span>
                            @endif
                        </div>
                        <div class="text-xs text-neutral-400 mt-0.5">{{ $account->email }}</div>
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
                            <div onsubmit="return confirm({{ \Illuminate\Support\Js::from('Nonaktifkan akun ' . $account->name . '? Sesi login akun ini akan diakhiri.') }})">
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
            {{ $staff->links() }}
        </div>
    @endif
</div>

@endsection
