@extends('layouts.admin')
@section('title', 'Data Pendaftar — Admin PMB YPIB')
@section('page-title', 'Data Pendaftar')

@section('content')
<style>
    @media (max-width: 767px) {
        .filter-search { width: 100% !important; }
        .filter-row    { flex-direction: column; align-items: stretch !important; }
        .filter-row select, .filter-row input { width: 100% !important; }
        .filter-row a, .filter-row button { width: 100% !important; text-align: center; justify-content: center; }
    }
</style>

{{-- ── FILTER SECTION ── --}}
<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:20px;margin-bottom:24px;">
    <form method="GET" action="{{ route('admin.registrations.index') }}"
          class="filter-row"
          style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">

        {{-- Search --}}
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama / nomor pendaftaran..."
               style="height:44px;border-radius:8px;border:1px solid #CED0D4;padding:0 16px;font-size:14px;color:#1C1E21;width:320px;outline:none;font-family:inherit;transition:border 0.15s;"
               onfocus="this.style.border='2px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'">

        {{-- Status filter --}}
        <select name="status"
                style="height:44px;border-radius:8px;border:1px solid #CED0D4;padding:0 12px;font-size:14px;color:#1C1E21;background:#fff;outline:none;font-family:inherit;">
            <option value="">Semua Status</option>
            @foreach([
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                'terdaftar'           => 'Terdaftar',
                'diterima'            => 'Diterima',
                'ditolak'             => 'Ditolak',
                'perlu_revisi'        => 'Perlu Revisi',
            ] as $val => $lbl)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>

        {{-- Tombol Filter --}}
        <button type="submit"
                style="height:44px;border-radius:9999px;padding:0 24px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
            Filter
        </button>

        {{-- Tombol Reset --}}
        <a href="{{ route('admin.registrations.index') }}"
           style="height:44px;border-radius:9999px;padding:0 24px;background:transparent;color:#444950;font-size:14px;font-weight:700;border:1px solid #CED0D4;text-decoration:none;display:inline-flex;align-items:center;transition:background 0.15s;"
           onmouseover="this.style.background='#F1F4F7'" onmouseout="this.style.background='transparent'">
            Reset
        </a>
    </form>
</div>

{{-- ── TABEL ── --}}
<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden;">

    {{-- Info count --}}
    <div style="padding:14px 24px;border-bottom:1px solid #DEE3E9;">
        <span style="font-size:14px;color:#5D6C7B;">{{ $registrations->total() }} pendaftar ditemukan</span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;">No. Daftar</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Nama</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;">Prodi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Referral</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Tanggal</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;"
                    onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;font-size:14px;color:#444950;">
                        <span style="font-family:monospace;font-weight:600;font-size:13px;color:#5D6C7B;">
                            {{ $reg->registration_number ?? '—' }}
                        </span>
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $reg->full_name }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">{{ $reg->user?->email }}</div>
                    </td>
                    <td style="padding:16px 24px;font-size:13px;color:#444950;max-width:160px;">{{ $reg->firstChoiceProgram?->name ?? '—' }}</td>
                    <td style="padding:16px 24px;">
                        @if($reg->referrer)
                            <span style="background:#e6edfc;color:#082e8f;font-size:12px;font-weight:600;padding:3px 10px;border-radius:9999px;">{{ $reg->referrer->code }}</span>
                        @else
                            <span style="color:#CED0D4;font-size:14px;">—</span>
                        @endif
                    </td>
                    <td style="padding:16px 24px;">
                        @include('admin.registrations._status_badge', ['status' => $reg->status])
                    </td>
                    <td style="padding:16px 24px;font-size:14px;color:#8595A4;white-space:nowrap;">{{ $reg->created_at->format('d/m/Y') }}</td>
                    <td style="padding:16px 24px;">
                        <a href="{{ route('admin.registrations.show', $reg->id) }}"
                           style="background:#F1F4F7;color:#082e8f;font-size:13px;font-weight:700;border-radius:9999px;padding:6px 16px;text-decoration:none;display:inline-block;transition:background 0.12s;"
                           onmouseover="this.style.background='#e6edfc'" onmouseout="this.style.background='#F1F4F7'">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                        Tidak ada pendaftar ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($registrations->hasPages())
        <div style="padding:16px 24px;border-top:1px solid #DEE3E9;">
            <style>
                /* Minimal pagination styling — rounded-full buttons */
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
                    transition: background 0.12s !important;
                }
                nav[aria-label] span[aria-current] {
                    background: #082e8f !important;
                    color: #FFFFFF !important;
                    border-color: #082e8f !important;
                    font-weight: 700 !important;
                }
                nav[aria-label] a:hover {
                    background: #F1F4F7 !important;
                }
                nav[aria-label] span.cursor-default {
                    color: #CED0D4 !important;
                    border-color: #DEE3E9 !important;
                }
            </style>
            {{ $registrations->links() }}
        </div>
    @endif
</div>

@endsection
