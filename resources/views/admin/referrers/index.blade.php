@extends('layouts.admin')
@section('title', 'Data Afiliasi — Admin PMB YPIB')
@section('page-title', 'Data Afiliasi')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Data Afiliasi</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Daftar afiliator terdaftar beserta performa referral mereka.</p>
    </div>
</div>

{{-- FILTER SECTION --}}
<div class="mb-6 bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
    <form action="{{ route('admin.referrers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="per_page" class="text-sm font-semibold text-neutral-600 whitespace-nowrap">Tampilkan:</label>
            <select name="per_page" id="per_page" onchange="this.form.submit()" class="text-sm border border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 block py-2 pl-3 pr-8 bg-white text-neutral-700 cursor-pointer shadow-sm">
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
        
        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2 pl-10 text-sm font-medium border border-neutral-300 rounded-lg bg-white focus:ring-primary-500 focus:border-primary-500 placeholder-neutral-400 text-neutral-900 shadow-sm transition-shadow" placeholder="Cari nama, email, kode...">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>
</div>

{{-- TABLE WRAPPER --}}
<div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
        <span class="text-sm font-semibold text-neutral-500">{{ $referrers->total() }} afiliasi terdaftar</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50/50">
                @php
                    $sort = request('sort');
                    $dir = request('dir', 'desc');
                    
                    $sortUrl = function($column) use ($sort, $dir) {
                        $newDir = ($sort === $column && $dir === 'desc') ? 'asc' : 'desc';
                        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $newDir]);
                    };
                    
                    $sortIcon = function($column) use ($sort, $dir) {
                        $isAsc = $sort === $column && $dir === 'asc';
                        $isDesc = $sort === $column && $dir === 'desc';
                        $colorTop = $isAsc ? 'text-primary-600' : 'text-neutral-300';
                        $colorBottom = $isDesc ? 'text-primary-600' : 'text-neutral-300';
                        
                        return '<div class="flex flex-col ml-1">
                            <svg class="w-2.5 h-2.5 ' . $colorTop . ' -mb-[1px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                            <svg class="w-2.5 h-2.5 ' . $colorBottom . ' -mt-[1px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </div>';
                    };
                @endphp
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama & Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kode Referral</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <a href="{{ $sortUrl('klik') }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1 {{ $sort === 'klik' ? 'text-primary-600' : '' }}">
                            Klik {!! $sortIcon('klik') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <a href="{{ $sortUrl('konversi') }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1 {{ $sort === 'konversi' ? 'text-primary-600' : '' }}">
                            Konversi {!! $sortIcon('konversi') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <a href="{{ $sortUrl('rate') }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1 {{ $sort === 'rate' ? 'text-primary-600' : '' }}">
                            % Konversi {!! $sortIcon('rate') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                        <a href="{{ $sortUrl('reward') }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1 justify-end w-full {{ $sort === 'reward' ? 'text-primary-600' : '' }}">
                            Total Reward {!! $sortIcon('reward') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-24">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($referrers as $referrer)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.referrers.show', $referrer->id) }}" class="block text-sm font-bold text-primary-600 hover:text-primary-800 transition-colors">
                            {{ $referrer->user?->name ?? '—' }}
                        </a>
                        <div class="text-xs mt-0.5 text-neutral-500">{{ $referrer->user?->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-mono font-bold border border-primary-100">
                            {{ $referrer->code }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-600">{{ $referrer->total_clicks }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-900">{{ $referrer->total_conversions }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-neutral-600">
                            {{ $referrer->total_clicks > 0 ? number_format(($referrer->total_conversions / $referrer->total_clicks) * 100, 0) : 0 }}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-neutral-900">
                            Rp {{ number_format($referrer->total_reward ?? $referrer->rewards->whereIn('status',['approved','disbursed'])->sum('amount'), 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($referrer->status === 'active')
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-neutral-900 text-white rounded-full text-xs font-bold">Aktif</span>
                        @else
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-neutral-100 text-neutral-400 border border-neutral-200 rounded-full text-xs font-bold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.referrers.show', $referrer->id) }}" 
                               class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-bold transition-colors duration-150 border border-neutral-200 bg-white text-primary-600 hover:bg-primary-50 hover:border-primary-200">
                                Detail
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-sm text-neutral-400">
                        Belum ada afiliasi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($referrers->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100">
            {{ $referrers->links() }}
        </div>
    @endif
</div>


@endsection
