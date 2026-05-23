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
