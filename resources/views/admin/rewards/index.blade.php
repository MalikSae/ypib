@extends('layouts.admin')
@section('title', 'Kelola Komisi — Admin PMB YPIB')
@section('page-title', 'Kelola Komisi Afiliasi')

@section('content')
<style>
    @media (max-width: 767px) {
        .filter-row    { flex-direction: column; align-items: stretch !important; }
        .filter-row select { width: 100% !important; }
        .filter-row a, .filter-row button { width: 100% !important; text-align: center; justify-content: center; }
    }
</style>

{{-- ── FILTER ── --}}
<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:20px;margin-bottom:24px;">
    <form method="GET" action="{{ route('admin.rewards.index') }}"
          class="filter-row"
          style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">

        <select name="status"
                style="height:44px;border-radius:8px;border:1px solid #CED0D4;padding:0 12px;font-size:14px;color:#1C1E21;background:#fff;outline:none;font-family:inherit;">
            <option value="">Semua Status</option>
            @foreach(['pending'=>'Pending','approved'=>'Disetujui','disbursed'=>'Dicairkan'] as $val => $lbl)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>

        <button type="submit"
                style="height:44px;border-radius:9999px;padding:0 24px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
            Filter
        </button>

        <a href="{{ route('admin.rewards.index') }}"
           style="height:44px;border-radius:9999px;padding:0 24px;background:transparent;color:#444950;font-size:14px;font-weight:700;border:1px solid #CED0D4;text-decoration:none;display:inline-flex;align-items:center;transition:background 0.15s;"
           onmouseover="this.style.background='#F1F4F7'" onmouseout="this.style.background='transparent'">
            Reset
        </a>
    </form>
</div>

{{-- ── TABS NAVIGATION ── --}}
<div style="display:flex; gap:16px; margin-bottom:16px; border-bottom:1px solid #DEE3E9; overflow-x:auto;">
    <button onclick="switchTab('recap')" id="btn-tab-recap" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid #082e8f; color:#082e8f; font-weight:700; cursor:pointer; font-size:15px; font-family:inherit; transition:all 0.2s; white-space:nowrap;">Rekap per Afiliasi (Siap Cair)</button>
    <button onclick="switchTab('history')" id="btn-tab-history" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid transparent; color:#5D6C7B; font-weight:500; cursor:pointer; font-size:15px; font-family:inherit; transition:all 0.2s; white-space:nowrap;">Riwayat Pencairan</button>
    <button onclick="switchTab('detail')" id="btn-tab-detail" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid transparent; color:#5D6C7B; font-weight:500; cursor:pointer; font-size:15px; font-family:inherit; transition:all 0.2s; white-space:nowrap;">Detail Transaksi (Semua Status)</button>
</div>

<script>
    function switchTab(tabId) {
        document.getElementById('tab-recap').style.display = tabId === 'recap' ? 'block' : 'none';
        document.getElementById('tab-history').style.display = tabId === 'history' ? 'block' : 'none';
        document.getElementById('tab-detail').style.display = tabId === 'detail' ? 'block' : 'none';
        
        const tabs = ['recap', 'history', 'detail'];
        tabs.forEach(id => {
            const btn = document.getElementById('btn-tab-' + id);
            if (id === tabId) {
                btn.style.borderBottomColor = '#082e8f';
                btn.style.color = '#082e8f';
                btn.style.fontWeight = '700';
            } else {
                btn.style.borderBottomColor = 'transparent';
                btn.style.color = '#5D6C7B';
                btn.style.fontWeight = '500';
            }
        });
    }
    
    // Switch to detail tab if there are specific query params
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status') || urlParams.has('page')) {
            switchTab('detail');
        }
    });
</script>

{{-- ── TAB: REKAP PER AFILIASI ── --}}
<div id="tab-recap" style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden; display:block;">
    <form id="mass_action_referrers_form" method="POST" action="">
        @csrf
        <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <span style="font-size:14px;color:#5D6C7B;">{{ count($recapByReferrer) }} afiliasi dengan komisi siap cair</span>
            
            <div style="display:flex;gap:12px;">
                <button type="submit" id="btn-mass-disburse-referrers" disabled formaction="{{ route('admin.rewards.referrers.mass-disburse') }}" onclick="return confirm('Cairkan semua reward untuk afiliasi yang dipilih? Pastikan transfer manual sudah dilakukan.')" style="height:36px;border-radius:8px;padding:0 16px;background:#F1F4F7;color:#A0AAB2;font-size:13px;font-weight:700;border:1px solid #CED0D4;cursor:not-allowed;transition:all 0.15s;">
                    Cairkan Terpilih
                </button>
                <button type="submit" formaction="{{ route('admin.rewards.referrers.export') }}" style="height:36px;border-radius:8px;padding:0 16px;background:#F1F4F7;color:#0A1317;font-size:13px;font-weight:700;border:1px solid #CED0D4;cursor:pointer;transition:background 0.15s;">
                    Export CSV
                </button>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#F1F4F7;">
                        <th style="padding:12px 24px;width:40px;"><input type="checkbox" id="check-all-referrers" onchange="document.querySelectorAll('.referrer-checkbox').forEach(cb => { cb.checked = this.checked; cb.dispatchEvent(new Event('change')); })" style="width:16px;height:16px;cursor:pointer;"></th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Afiliasi</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Info Bank</th>
                        <th style="padding:12px 24px;text-align:center;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Jml Pendaftar</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Total Siap Cair</th>
                        <th style="padding:12px 24px;text-align:right;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Aksi</th>
                    </tr>
            </thead>
            <tbody>
                @forelse($recapByReferrer as $referrerId => $recap)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <input type="checkbox" name="referrer_ids[]" value="{{ $referrerId }}" class="referrer-checkbox" style="width:16px;height:16px;cursor:pointer;">
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1E21;">{{ $recap['referrer']?->user?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#082e8f;font-family:monospace;margin-top:2px;">{{ $recap['referrer']?->code }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1E21;">{{ $recap['referrer']?->bank_name ?? '—' }}</div>
                        <div style="font-size:13px;color:#0A1317;margin-top:2px;">{{ $recap['referrer']?->bank_account_number ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">a.n {{ $recap['referrer']?->bank_account_name ?? '—' }}</div>
                    </td>
                    <td style="padding:16px 24px; text-align:center;">
                        <span style="font-size:14px;font-weight:600;color:#1C1E21;">{{ $recap['total_registrations'] }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:16px;font-weight:700;color:#2E7D32;">Rp {{ number_format($recap['total_amount'], 0, ',', '.') }}</span>
                    </td>
                    <td style="padding:16px 24px; text-align:right;">
                        <button type="submit"
                                formaction="{{ route('admin.rewards.disburse.referrer', $referrerId) }}"
                                onclick="return confirm('Apakah Anda yakin sudah mentransfer Rp {{ number_format($recap['total_amount'], 0, ',', '.') }} ke afiliasi ini dan ingin mengubah statusnya menjadi Cair?')"
                                style="border-radius:9999px;padding:8px 20px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;background:#082e8f;color:#FFFFFF;transition:opacity 0.12s;"
                                onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                            Cairkan Semua
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                        Tidak ada afiliasi yang memiliki komisi dengan status Siap Cair.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>
</div>

{{-- ── TAB: RIWAYAT PENCAIRAN ── --}}
<div id="tab-history" style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden; display:none;">
    <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:14px;color:#5D6C7B;">{{ count($historyByReferrer) }} afiliasi yang telah menerima pencairan komisi</span>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Afiliasi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Info Bank</th>
                    <th style="padding:12px 24px;text-align:center;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Jml Pendaftar</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Total Dicairkan</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Terakhir Dicairkan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyByReferrer as $referrerId => $history)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1E21;">{{ $history['referrer']?->user?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#082e8f;font-family:monospace;margin-top:2px;">{{ $history['referrer']?->code }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1E21;">{{ $history['referrer']?->bank_name ?? '—' }}</div>
                        <div style="font-size:13px;color:#0A1317;margin-top:2px;">{{ $history['referrer']?->bank_account_number ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">a.n {{ $history['referrer']?->bank_account_name ?? '—' }}</div>
                    </td>
                    <td style="padding:16px 24px; text-align:center;">
                        <span style="font-size:14px;font-weight:600;color:#1C1E21;">{{ $history['total_registrations'] }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:16px;font-weight:700;color:#1565C0;">Rp {{ number_format($history['total_amount'], 0, ',', '.') }}</span>
                    </td>
                    <td style="padding:16px 24px;font-size:14px;color:#5D6C7B;">
                        {{ $history['last_disbursed_at'] ? $history['last_disbursed_at']->format('d/m/Y H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
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
        
        function updateMassDisburseBtn(checkboxClass, btnElement) {
            const hasChecked = document.querySelectorAll(`.${checkboxClass}:checked`).length > 0;
            if (hasChecked) {
                btnElement.disabled = false;
                btnElement.style.background = '#E8F5E9';
                btnElement.style.color = '#2E7D32';
                btnElement.style.borderColor = '#A5D6A7';
                btnElement.style.cursor = 'pointer';
            } else {
                btnElement.disabled = true;
                btnElement.style.background = '#F1F4F7';
                btnElement.style.color = '#A0AAB2';
                btnElement.style.borderColor = '#CED0D4';
                btnElement.style.cursor = 'not-allowed';
            }
        }

        // Logic for Recap Tab
        referrerCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => updateMassDisburseBtn('referrer-checkbox', btnMassDisburse));
        });
    });
</script>

{{-- ── TAB: DETAIL TRANSAKSI ── --}}
<div id="tab-detail" style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden; display:none;">

<form id="mass_action_form" method="POST" action="">
    @csrf
    <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <span style="font-size:14px;color:#5D6C7B;">{{ $rewards->total() }} komisi ditemukan</span>
        
        <div style="display:flex;gap:12px;">
            <button type="submit" formaction="{{ route('admin.rewards.export') }}" style="height:36px;border-radius:8px;padding:0 16px;background:#F1F4F7;color:#0A1317;font-size:13px;font-weight:700;border:1px solid #CED0D4;cursor:pointer;transition:background 0.15s;">
                Export CSV
            </button>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Afiliasi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Info Bank</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Pendaftar</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Nominal</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rewards as $reward)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;"
                    onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $reward->referrer?->user?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#082e8f;font-family:monospace;margin-top:2px;">{{ $reward->referrer?->code }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1E21;">{{ $reward->referrer?->bank_name ?? '—' }}</div>
                        <div style="font-size:13px;color:#0A1317;margin-top:2px;">{{ $reward->referrer?->bank_account_number ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">a.n {{ $reward->referrer?->bank_account_name ?? '—' }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $reward->registration?->full_name ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;font-family:monospace;margin-top:2px;">{{ $reward->registration?->registration_number }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:15px;font-weight:700;color:#0A1317;display:block;">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                        <span style="font-size:10px;color:#8595A4;font-weight:600;text-transform:uppercase;">{{ $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar Ulang' }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        @php
                        $rw = [
                            'pending'   => ['Pending',   '#FFF3E0', '#E65100'],
                            'approved'  => ['Siap Cair', '#E8F5E9', '#2E7D32'],
                            'disbursed' => ['Dicairkan', '#E3F2FD', '#1565C0'],
                        ];
                        $rs = $rw[$reward->status] ?? ['—', '#F1F4F7', '#5D6C7B'];
                        @endphp
                        <span style="background:{{ $rs[1] }};color:{{ $rs[2] }};font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;display:inline-block;">{{ $rs[0] }}</span>
                    </td>
                    <td style="padding:16px 24px;font-size:14px;color:#8595A4;white-space:nowrap;">{{ $reward->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                        Belum ada data reward.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>

    @if($rewards->hasPages())
        <div style="padding:16px 24px;border-top:1px solid #DEE3E9;">
            <style>
                nav[aria-label] span, nav[aria-label] a {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    min-width: 36px !important;
                    height: 36px !important;
                    border-radius: 9999px !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    padding: 0 8px !important;
                    margin: 0 2px !important;
                    text-decoration: none !important;
                    color: #444950 !important;
                    border: 1px solid #DEE3E9 !important;
                    background: #FFFFFF !important;
                }
                nav[aria-label] span[aria-current] {
                    background: #082e8f !important;
                    color: #FFFFFF !important;
                    border-color: #082e8f !important;
                    font-weight: 700 !important;
                }
                nav[aria-label] a:hover { background: #F1F4F7 !important; }
                nav[aria-label] span.cursor-default { color: #CED0D4 !important; }
            </style>
            {{ $rewards->links() }}
        </div>
    @endif
</div>
</div>

@endsection
