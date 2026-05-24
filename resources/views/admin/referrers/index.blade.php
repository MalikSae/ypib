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
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Nama & Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Kode Referral</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">Klik</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">Konversi</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Total Reward</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-24">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($referrers as $referrer)
                <tr class="hover:bg-neutral-50 transition-colors duration-100 group">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-neutral-900 group-hover:text-primary-700 transition-colors">{{ $referrer->user?->name ?? '—' }}</div>
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
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-neutral-900">
                            Rp {{ number_format($referrer->rewards->whereIn('status',['approved','disbursed'])->sum('amount'), 0, ',', '.') }}
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
                        <form method="POST" action="{{ route('admin.referrers.toggle', $referrer->id) }}" class="m-0 inline-block">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Toggle status afiliasi ini?')"
                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-bold transition-colors duration-150 border {{ $referrer->status === 'active' ? 'border-neutral-200 bg-white text-error-600 hover:bg-error-50 hover:border-error-200' : 'border-neutral-200 bg-white text-success-600 hover:bg-success-50 hover:border-success-200' }}">
                                {{ $referrer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
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
