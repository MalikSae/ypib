@extends('layouts.admin')
@section('title', 'Data Afiliasi — Admin PMB YPIB')
@section('page-title', 'Data Afiliasi')

@section('content')

{{-- ── TABEL REFERRER ── --}}
<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden;">

    <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;">
        <span style="font-size:14px;color:#5D6C7B;">{{ $referrers->total() }} afiliasi terdaftar</span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Nama</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;">Kode Referral</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;">Total Klik</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Konversi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;">Total Reward</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrers as $referrer)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;"
                    onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $referrer->user?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">{{ $referrer->user?->email }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="background:#EFF4FF;color:#0064E0;font-size:13px;font-weight:700;padding:4px 12px;border-radius:9999px;font-family:monospace;">
                            {{ $referrer->code }}
                        </span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;font-weight:700;color:#1565C0;">{{ $referrer->total_clicks }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;font-weight:700;color:#2E7D32;">{{ $referrer->total_conversions }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;font-weight:700;color:#0A1317;">
                            Rp {{ number_format($referrer->rewards->whereIn('status',['approved','disbursed'])->sum('amount'), 0, ',', '.') }}
                        </span>
                    </td>
                    <td style="padding:16px 24px;">
                        @if($referrer->status === 'active')
                            <span style="background:#E8F5E9;color:#2E7D32;font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;">Aktif</span>
                        @else
                            <span style="background:#FFEBEE;color:#C62828;font-size:12px;font-weight:700;padding:4px 12px;border-radius:9999px;">Nonaktif</span>
                        @endif
                    </td>
                    <td style="padding:16px 24px;">
                        <form method="POST" action="{{ route('admin.referrers.toggle', $referrer->id) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Toggle status afiliasi ini?')"
                                    style="border-radius:9999px;padding:6px 16px;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:opacity 0.12s;
                                    background:{{ $referrer->status === 'active' ? '#FFEBEE' : '#E8F5E9' }};
                                    color:{{ $referrer->status === 'active' ? '#C62828' : '#2E7D32' }};"
                                    onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                {{ $referrer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                        Belum ada afiliasi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($referrers->hasPages())
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
            {{ $referrers->links() }}
        </div>
    @endif
</div>

@endsection
