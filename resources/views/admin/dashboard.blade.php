@extends('layouts.admin')
@section('title', 'Dashboard Admin PMB YPIB')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stat-grid">

    {{-- Card 1: Total Pendaftar --}}
    <div style="background:#FFFFFF;border:1px solid #DEE3E9;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#e6edfc;display:flex;align-items:center;justify-content:center;">
                <svg style="width:24px;height:24px;color:#082e8f;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:#0A1317;line-height:1;">{{ $stats['total'] }}</div>
        <div style="font-size:14px;color:#5D6C7B;margin-top:6px;">Total Pendaftar</div>
    </div>

    {{-- Card 2: Menunggu Konfirmasi --}}
    <div style="background:#FFFFFF;border:1px solid #DEE3E9;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#FFF8E1;display:flex;align-items:center;justify-content:center;">
                <svg style="width:24px;height:24px;color:#F2A918;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:#0A1317;line-height:1;">{{ $stats['pending_payment'] + ($stats['pending_verify'] ?? 0) }}</div>
        <div style="font-size:14px;color:#5D6C7B;margin-top:6px;">Menunggu Konfirmasi</div>
    </div>

    {{-- Card 3: Terdaftar --}}
    <div style="background:#FFFFFF;border:1px solid #DEE3E9;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#E8F5E9;display:flex;align-items:center;justify-content:center;">
                <svg style="width:24px;height:24px;color:#31A24C;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:#0A1317;line-height:1;">{{ $stats['accepted'] ?? 0 }}</div>
        <div style="font-size:14px;color:#5D6C7B;margin-top:6px;">Terdaftar</div>
    </div>

    {{-- Card 4: Total Referrer --}}
    <div style="background:#FFFFFF;border:1px solid #DEE3E9;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#e6edfc;display:flex;align-items:center;justify-content:center;">
                <svg style="width:24px;height:24px;color:#082e8f;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:#0A1317;line-height:1;">{{ $stats['referrers'] ?? 0 }}</div>
        <div style="font-size:14px;color:#5D6C7B;margin-top:6px;">Total Referrer</div>
    </div>

</div>

{{-- ── PENDAFTAR TERBARU ── --}}
<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:700;color:#0A1317;margin:0;">Pendaftar Terbaru</h2>
        <a href="{{ route('admin.registrations.index') }}"
           style="font-size:14px;font-weight:500;color:#082e8f;text-decoration:none;display:flex;align-items:center;gap:4px;">
            Lihat Semua
            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#F1F4F7;">
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">No. Daftar</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Nama</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Prodi</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Referral</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                        <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $reg)
                    <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                        <td style="padding:16px 24px;font-size:14px;color:#444950;">
                            @if($reg->registration_number)
                                <a href="{{ route('admin.registrations.show', $reg->id) }}"
                                   style="font-family:monospace;font-weight:600;color:#082e8f;text-decoration:none;">{{ $reg->registration_number }}</a>
                            @else
                                <span style="color:#8595A4;font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px;font-size:14px;font-weight:500;color:#1C1E21;">{{ $reg->full_name }}</td>
                        <td style="padding:16px 24px;font-size:14px;color:#444950;">{{ $reg->firstChoiceProgram?->name ?? '—' }}</td>
                        <td style="padding:16px 24px;font-size:14px;color:#444950;">
                            @if($reg->referrer)
                                <span style="background:#e6edfc;color:#082e8f;font-size:12px;font-weight:600;padding:3px 10px;border-radius:9999px;">{{ $reg->referrer->code }}</span>
                            @else
                                <span style="color:#CED0D4;">—</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px;">
                            @include('admin.registrations._status_badge', ['status' => $reg->status])
                        </td>
                        <td style="padding:16px 24px;font-size:14px;color:#8595A4;">{{ $reg->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                            Belum ada data pendaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
