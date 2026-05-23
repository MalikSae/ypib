@extends('layouts.admin')
@section('title', 'Kelola Komisi — Admin PMB YPIB')
@section('page-title', 'Kelola Komisi')

@section('content')

{{-- ============================================================ --}}
{{-- PAGE HEADER --}}
{{-- ============================================================ --}}
<div class="mb-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-neutral-900 tracking-tight">Kelola Komisi Afiliasi</h1>
        <p class="mt-0.5 text-sm text-neutral-400">Pantau dan cairkan komisi untuk afiliator PMB.</p>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TABS NAVIGATION --}}
{{-- ============================================================ --}}
<div class="flex flex-wrap items-center gap-2 mb-6 border-b border-neutral-200 pb-2">
    <button onclick="switchTab('recap')" id="btn-tab-recap" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 bg-neutral-900 text-white shadow-sm">
        Rekap Siap Cair
        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all bg-neutral-700 text-white">{{ count($recapByReferrer) }}</span>
    </button>
    <button onclick="switchTab('history')" id="btn-tab-history" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 text-neutral-600 bg-white hover:bg-neutral-100 border border-neutral-200">
        Riwayat Pencairan
        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all bg-neutral-100 text-neutral-500">{{ count($historyByReferrer) }}</span>
    </button>
    <button onclick="switchTab('detail')" id="btn-tab-detail" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 text-neutral-600 bg-white hover:bg-neutral-100 border border-neutral-200">
        Detail Transaksi
        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all bg-neutral-100 text-neutral-500">{{ collect($rewards->items() ?? [])->count() }}</span>
    </button>
</div>

<script>
    function switchTab(tabId) {
        document.getElementById('tab-recap').style.display = tabId === 'recap' ? 'block' : 'none';
        document.getElementById('tab-history').style.display = tabId === 'history' ? 'block' : 'none';
        document.getElementById('tab-detail').style.display = tabId === 'detail' ? 'block' : 'none';
        
        const tabs = ['recap', 'history', 'detail'];
        tabs.forEach(id => {
            const btn = document.getElementById('btn-tab-' + id);
            const badge = btn.querySelector('span');
            
            if (id === tabId) {
                btn.className = "inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 bg-neutral-900 text-white shadow-sm";
                badge.className = "ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all bg-neutral-700 text-white";
            } else {
                btn.className = "inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 text-neutral-600 bg-white hover:bg-neutral-100 border border-neutral-200";
                badge.className = "ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold transition-all bg-neutral-100 text-neutral-500";
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status') || urlParams.has('page') || urlParams.has('search')) {
            switchTab('detail');
        }
    });
</script>

{{-- ============================================================ --}}
{{-- TAB 1: REKAP PER AFILIASI --}}
{{-- ============================================================ --}}
<div id="tab-recap" class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8" style="display:block;">
    <form id="mass_action_referrers_form" method="POST" action="">
        @csrf
        <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between gap-4 flex-wrap">
            <span class="text-sm text-neutral-500">{{ count($recapByReferrer) }} afiliasi dengan komisi siap cair</span>
            
            <div class="flex items-center gap-3">
                <button type="submit" id="btn-mass-disburse-referrers" disabled 
                        formaction="{{ route('admin.rewards.referrers.mass-disburse') }}" 
                        onclick="return confirm('Cairkan semua reward untuk afiliasi yang dipilih? Pastikan transfer manual sudah dilakukan.')" 
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-neutral-100 text-neutral-400">
                    Cairkan Terpilih
                </button>
                <button type="submit" formaction="{{ route('admin.rewards.referrers.export') }}" 
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 transition-colors duration-200">
                    Export CSV
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-neutral-50 border-b border-neutral-100">
                    <tr>
                        <th class="px-5 py-3 w-10">
                            <input type="checkbox" id="check-all-referrers" onchange="document.querySelectorAll('.referrer-checkbox').forEach(cb => { cb.checked = this.checked; cb.dispatchEvent(new Event('change')); })" class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Afiliasi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Info Bank</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">Jml Pendaftar</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Total Siap Cair</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($recapByReferrer as $referrerId => $recap)
                    <tr class="hover:bg-neutral-50 transition-colors duration-100">
                        <td class="px-5 py-4">
                            <input type="checkbox" name="referrer_ids[]" value="{{ $referrerId }}" class="referrer-checkbox rounded border-neutral-300 text-primary-600 focus:ring-primary-500 w-4 h-4 cursor-pointer">
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900">{{ $recap['referrer']?->user?->name ?? '—' }}</div>
                            <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $recap['referrer']?->code }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900">{{ $recap['referrer']?->bank_name ?? '—' }}</div>
                            <div class="text-xs text-neutral-600 mt-0.5">{{ $recap['referrer']?->bank_account_number ?? '—' }}</div>
                            <div class="text-xs text-neutral-400">a.n {{ $recap['referrer']?->bank_account_name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-neutral-100 text-neutral-700 text-sm font-bold">{{ $recap['total_registrations'] }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-base font-bold text-neutral-900">Rp {{ number_format($recap['total_amount'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button type="submit"
                                    formaction="{{ route('admin.rewards.disburse.referrer', $referrerId) }}"
                                    onclick="return confirm('Apakah Anda yakin sudah mentransfer Rp {{ number_format($recap['total_amount'], 0, ',', '.') }} ke afiliasi ini dan ingin mengubah statusnya menjadi Cair?')"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl bg-neutral-900 text-white hover:bg-neutral-800 transition-colors duration-200">
                                Cairkan Semua
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-sm text-neutral-400">
                            Tidak ada afiliasi yang memiliki komisi dengan status Siap Cair.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

{{-- ============================================================ --}}
{{-- TAB 2: RIWAYAT PENCAIRAN --}}
{{-- ============================================================ --}}
<div id="tab-history" class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8" style="display:none;">
    <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
        <span class="text-sm text-neutral-500">{{ count($historyByReferrer) }} afiliasi yang telah menerima pencairan komisi</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Afiliasi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Info Bank</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">Jml Pendaftar</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Total Dicairkan</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Terakhir Dicairkan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse($historyByReferrer as $referrerId => $history)
                <tr class="hover:bg-neutral-50 transition-colors duration-100">
                    <td class="px-5 py-4">
                        <div class="text-sm font-semibold text-neutral-900">{{ $history['referrer']?->user?->name ?? '—' }}</div>
                        <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $history['referrer']?->code }}</div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-semibold text-neutral-900">{{ $history['referrer']?->bank_name ?? '—' }}</div>
                        <div class="text-xs text-neutral-600 mt-0.5">{{ $history['referrer']?->bank_account_number ?? '—' }}</div>
                        <div class="text-xs text-neutral-400">a.n {{ $history['referrer']?->bank_account_name ?? '—' }}</div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-neutral-100 text-neutral-700 text-sm font-bold">{{ $history['total_registrations'] }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-base font-bold text-primary-700">Rp {{ number_format($history['total_amount'], 0, ',', '.') }}</span>
                    </td>
                    <td class="px-5 py-4 text-sm text-neutral-500">
                        {{ $history['last_disbursed_at'] ? $history['last_disbursed_at']->format('d M Y, H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center text-sm text-neutral-400">
                        Belum ada riwayat pencairan komisi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const referrerCheckboxes = document.querySelectorAll('.referrer-checkbox');
        const btnMassDisburse = document.getElementById('btn-mass-disburse-referrers');
        
        function updateMassDisburseBtn() {
            const hasChecked = document.querySelectorAll('.referrer-checkbox:checked').length > 0;
            if (hasChecked) {
                btnMassDisburse.disabled = false;
                btnMassDisburse.className = "inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl transition-all duration-200 bg-neutral-900 text-white hover:bg-neutral-800 shadow-sm";
            } else {
                btnMassDisburse.disabled = true;
                btnMassDisburse.className = "inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl transition-all duration-200 bg-neutral-100 text-neutral-400 cursor-not-allowed";
            }
        }

        referrerCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateMassDisburseBtn);
        });
    });
</script>

{{-- ============================================================ --}}
{{-- TAB 3: DETAIL TRANSAKSI --}}
{{-- ============================================================ --}}
<div id="tab-detail" class="bg-white rounded-2xl border border-neutral-200 overflow-hidden mb-8" style="display:none;">

    {{-- Filter Bar --}}
    <div class="px-5 pt-5 pb-4 border-b border-neutral-100">
        <form method="GET" action="{{ route('admin.rewards.index') }}" class="flex flex-wrap gap-3 items-center">
            <input type="hidden" name="tab" value="detail"> {{-- Helps keep the detail tab active on reload if backend supports it --}}
            
            <div class="w-full md:w-auto flex-1 md:flex-none">
                <select name="status" class="w-full md:w-48 px-3 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="">Semua Status</option>
                    @foreach(['pending'=>'Pending','approved'=>'Disetujui / Siap Cair','disbursed'=>'Dicairkan'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <x-button type="submit" color="primary" size="sm">Filter</x-button>
            @if(request('status'))
                <a href="{{ route('admin.rewards.index', ['tab' => 'detail']) }}" class="decoration-none">
                    <x-button type="button" variant="outline" color="neutral" size="sm">Reset</x-button>
                </a>
            @endif

            <div class="flex-1"></div>
            
            <button form="mass_action_form" type="submit" formaction="{{ route('admin.rewards.export') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 transition-colors duration-200">
                Export CSV
            </button>
        </form>
    </div>

    <form id="mass_action_form" method="POST" action="">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-full">
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
                            <div class="text-sm font-semibold text-neutral-900">{{ $reward->referrer?->user?->name ?? '—' }}</div>
                            <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $reward->referrer?->code }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900">{{ $reward->referrer?->bank_name ?? '—' }}</div>
                            <div class="text-xs text-neutral-600 mt-0.5">{{ $reward->referrer?->bank_account_number ?? '—' }}</div>
                            <div class="text-xs text-neutral-400">a.n {{ $reward->referrer?->bank_account_name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-neutral-900">{{ $reward->registration?->full_name ?? '—' }}</div>
                            <div class="text-xs font-mono mt-0.5 text-neutral-500">{{ $reward->registration?->registration_number }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-sm font-bold text-neutral-900 block">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                            <span class="text-[10px] font-bold text-neutral-400 uppercase">{{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar Ulang' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @php
                            $badgeStyles = [
                                'pending'   => 'bg-neutral-100 text-neutral-600',
                                'approved'  => 'bg-neutral-100 text-neutral-900 border border-neutral-200',
                                'disbursed' => 'bg-primary-50 text-primary-700',
                            ];
                            $labels = [
                                'pending'   => 'Pending',
                                'approved'  => 'Siap Cair',
                                'disbursed' => 'Dicairkan',
                            ];
                            $style = $badgeStyles[$reward->status] ?? 'bg-neutral-100 text-neutral-600';
                            $label = $labels[$reward->status] ?? '—';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $style }}">
                                {{ $label }}
                            </span>
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
            {{ $rewards->links() }}
        </div>
    @endif
</div>

@endsection
