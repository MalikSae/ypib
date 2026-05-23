@extends('layouts.admin')
@section('title', 'Kelola Program Studi — Admin PMB YPIB')
@section('page-title', 'Kelola Program Studi')

@section('content')

<div style="margin-bottom: 24px; display: flex; justify-content: flex-end;">
    <a href="{{ route('admin.programs.create') }}" style="background: #082e8f; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah Program Studi
    </a>
</div>

<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#F1F4F7;">
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Program Studi</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Fakultas</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Kuota</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Biaya Pendaftaran</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Status</th>
                    <th style="padding:12px 24px;text-align:left;font-size:12px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.06em;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr style="border-bottom:1px solid #DEE3E9;transition:background 0.12s;"
                    onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background=''">
                    <td style="padding:16px 24px;">
                        <div style="font-size:14px;font-weight:600;color:#1C1E21;">{{ $program->name }}</div>
                        <div style="font-size:12px;color:#8595A4;margin-top:2px;">Akreditasi: {{ $program->accreditation ?? '-' }}</div>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;color:#444950;">{{ $program->faculty->name }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;font-weight:600;color:#0A1317;">{{ $program->quota }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        <span style="font-size:14px;font-weight:600;color:#0A1317;">Rp {{ number_format($program->registration_fee, 0, ',', '.') }}</span>
                    </td>
                    <td style="padding:16px 24px;">
                        @if($program->is_active)
                            <span style="background:#E8F5E9;color:#2E7D32;padding:4px 12px;border-radius:9999px;font-size:12px;font-weight:700;">Aktif</span>
                        @else
                            <span style="background:#FFEBEE;color:#C62828;padding:4px 12px;border-radius:9999px;font-size:12px;font-weight:700;">Nonaktif</span>
                        @endif
                    </td>
                    <td style="padding:16px 24px;">
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.programs.edit', $program->id) }}" style="background:#E8F5E9;color:#2E7D32;padding:6px 10px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;" title="Edit">
                                <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </a>
                            
                            <form action="{{ route('admin.programs.destroy', $program->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus prodi ini?');" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:#FFEBEE;color:#C62828;padding:6px 10px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;" title="Hapus">
                                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:48px 24px;text-align:center;font-size:14px;color:#8595A4;">
                        Belum ada program studi terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($programs->hasPages())
        <div style="padding:16px 24px;border-top:1px solid #DEE3E9;">
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection
