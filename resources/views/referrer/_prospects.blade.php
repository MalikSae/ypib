{{-- FORM MINAT: link form minat dan daftar prospek dari link affiliator ini --}}
<div class="pub-card" style="margin-bottom:16px;">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;" class="text-neutral-400">Form Minat Calon Mahasiswa</div>

    @forelse($interestLinks as $link)
        <div x-data="{ copied: false }" style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #EEF1F5;">
            <div style="font-size:14px;font-weight:700;" class="text-neutral-900">{{ $link['label'] }}</div>
            <p style="font-size:12px;margin:2px 0 8px 0;" class="text-neutral-400">{{ $link['campus'] ? $link['campus'] . ' · ' : '' }}Potongan Rp {{ number_format($link['discount'], 0, ',', '.') }}</p>
            <div style="border-radius:10px;padding:10px 12px;font-family:monospace;font-size:12px;word-break:break-all;" class="bg-neutral-100 border-neutral-200 text-neutral-900">{{ $link['url'] }}</div>
            <div class="ref-action-grid">
                <button
                    x-on:click="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($link['url']) }});copied=true;setTimeout(()=>copied=false,2500);"
                    class="ref-action-btn ref-btn-copy"
                    :class="copied ? 'ref-btn-copied' : ''">
                    <span x-show="!copied">Salin Link</span>
                    <span x-show="copied">Tersalin!</span>
                </button>
                <a href="https://wa.me/?text={{ urlencode('Ikuti form minat kuliah di YPIB ' . $link['label'] . ': ' . $link['url']) }}"
                   target="_blank" rel="noopener" class="ref-action-btn ref-btn-wa">Bagikan WA</a>
            </div>
        </div>
    @empty
        <p style="font-size:13px;margin:0;" class="text-neutral-400">Belum ada kegiatan form minat yang aktif.</p>
    @endforelse
</div>

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
