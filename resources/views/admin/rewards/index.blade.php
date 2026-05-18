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
                style="height:44px;border-radius:9999px;padding:0 24px;background:#0064E0;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                onmouseover="this.style.background='#0457CB'" onmouseout="this.style.background='#0064E0'">
            Filter
        </button>

        <a href="{{ route('admin.rewards.index') }}"
           style="height:44px;border-radius:9999px;padding:0 24px;background:transparent;color:#444950;font-size:14px;font-weight:700;border:1px solid #CED0D4;text-decoration:none;display:inline-flex;align-items:center;transition:background 0.15s;"
           onmouseover="this.style.background='#F1F4F7'" onmouseout="this.style.background='transparent'">
            Reset
        </a>
    </form>
</div>

{{-- ── TABEL REWARD ── --}}
<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden;">

    <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;">
        <span style="font-size:14px;color:#5D6C7B;">{{ $rewards->total() }} komisi ditemukan</span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Afiliasi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Pendaftar</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Nominal</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Tanggal</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rewards as $reward)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;"
                    onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $reward->referrer?->user?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#0064E0;font-family:monospace;margin-top:2px;">{{ $reward->referrer?->code }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $reward->registration?->full_name ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;font-family:monospace;margin-top:2px;">{{ $reward->registration?->registration_number }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:15px;font-weight:700;color:#0A1317;">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
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
                    <td style="padding:16px 24px;">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            @if($reward->status === 'approved')
                                <form method="POST" action="{{ route('admin.rewards.disburse', $reward->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Cairkan reward ini? Pastikan sudah ditransfer ke afiliasi.')"
                                            style="border-radius:9999px;padding:6px 16px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;background:#EFF4FF;color:#0064E0;transition:opacity 0.12s;"
                                            onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                        Cairkan
                                    </button>
                                </form>
                            @elseif($reward->status === 'disbursed')
                                <span style="color:#2E7D32;font-size:13px;font-weight:600;">Sudah Dicairkan</span>
                            @else
                                <span style="color:#CED0D4;font-size:14px;">—</span>
                            @endif
                        </div>
                    </td>
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
                    background: #0064E0 !important;
                    color: #FFFFFF !important;
                    border-color: #0064E0 !important;
                    font-weight: 700 !important;
                }
                nav[aria-label] a:hover { background: #F1F4F7 !important; }
                nav[aria-label] span.cursor-default { color: #CED0D4 !important; }
            </style>
            {{ $rewards->links() }}
        </div>
    @endif
</div>

@endsection
