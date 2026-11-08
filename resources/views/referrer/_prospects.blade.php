{{-- Prospek form minat dari link affiliator ini (hanya lihat). Link form minat ada di accordion Link Referral. --}}
<div style="border-radius:16px;overflow:hidden;margin-bottom:24px;" class="bg-white border-neutral-200">
    <div style="padding:16px 24px;" class="border-b-neutral-200">
        <span style="font-size:15px;font-weight:700;" class="text-neutral-900">Prospek dari Form Minat</span>
        <span style="font-size:12px;margin-left:6px;" class="text-neutral-400">({{ $prospects->count() }})</span>
    </div>

    @if($prospects->isEmpty())
        <div style="padding:40px 24px;text-align:center;">
            <p style="font-size:14px;margin:0;" class="text-neutral-400">Belum ada calon yang mengisi form minat lewat linkmu.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="table-nowrap" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr class="bg-neutral-100">
                        <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;" class="text-neutral-400">Tgl</th>
                        <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;" class="text-neutral-400">Nama / Sekolah</th>
                        <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;" class="text-neutral-400">WhatsApp</th>
                        <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;" class="text-neutral-400">Prodi</th>
                        <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:700;text-transform:uppercase;" class="text-neutral-400">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prospects as $p)
                    <tr class="border-b-neutral-200" style="vertical-align:top;">
                        <td style="padding:14px 20px;font-size:13px;white-space:nowrap;" class="text-neutral-400">{{ $p->created_at->format('d/m/Y') }}</td>
                        <td style="padding:14px 20px;">
                            <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $p->full_name }}</div>
                            <div style="font-size:12px;" class="text-neutral-400">{{ $p->school_name ?: '—' }}</div>
                        </td>
                        <td style="padding:14px 20px;font-size:13px;font-family:monospace;" class="text-neutral-600">
                            {{ $p->isRegistered() ? $p->phone_normalized : $p->maskedPhone() }}
                        </td>
                        <td style="padding:14px 20px;font-size:13px;" class="text-neutral-500">{{ $p->program?->name ?? 'Belum menentukan' }}</td>
                        {{-- Affiliator hanya melihat. Status dan catatan diurus admin. --}}
                        <td style="padding:14px 20px;font-size:13px;white-space:nowrap;">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
