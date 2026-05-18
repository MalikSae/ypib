@php
$map = [
    'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'bg' => '#FFF3E0', 'color' => '#E65100'],
    'menunggu_konfirmasi' => ['label' => 'Menunggu Konfirmasi',  'bg' => '#E3F2FD', 'color' => '#1565C0'],
    'terdaftar'           => ['label' => 'Terdaftar',            'bg' => '#E8F5E9', 'color' => '#2E7D32'],
    'diterima'            => ['label' => 'Diterima',             'bg' => '#E8F5E9', 'color' => '#1B5E20'],
    'ditolak'             => ['label' => 'Ditolak',              'bg' => '#FFEBEE', 'color' => '#C62828'],
    'perlu_revisi'        => ['label' => 'Perlu Revisi',         'bg' => '#FFF8E1', 'color' => '#F57F17'],
];
$s = $map[$status] ?? ['label' => $status, 'bg' => '#F1F4F7', 'color' => '#5D6C7B'];
@endphp
<span style="background-color:{{ $s['bg'] }};color:{{ $s['color'] }};font-size:12px;font-weight:700;border-radius:9999px;padding:4px 12px;display:inline-block;white-space:nowrap;">
    {{ $s['label'] }}
</span>
