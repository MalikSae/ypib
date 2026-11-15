{{-- Kolom "Kontak terakhir": waktu relatif sebagai baris utama (menilai urgensi), tanggal sebagai keterangan. --}}
@if($p->last_contacted_at)
    <div class="text-neutral-900">{{ $p->last_contacted_at->diffForHumans() }}</div>
    <div class="text-xs text-neutral-500">{{ $p->last_contacted_at->format('d/m/Y') }}</div>
@elseif($p->isRegistered())
    <div class="text-neutral-500">—</div>
@else
    <div class="text-neutral-900">Belum dihubungi</div>
    <div class="text-xs text-neutral-500">masuk {{ $p->created_at->diffForHumans() }}</div>
@endif
