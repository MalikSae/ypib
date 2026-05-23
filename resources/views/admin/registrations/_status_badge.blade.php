@php
$map = [
    'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'bg' => 'bg-warning-50', 'text' => 'text-warning-700', 'border' => 'border-warning-200'],
    'menunggu_konfirmasi' => ['label' => 'Menunggu Konfirmasi',  'bg' => 'bg-info-50', 'text' => 'text-info-700', 'border' => 'border-info-200'],
    'terdaftar'           => ['label' => 'Terdaftar',            'bg' => 'bg-success-50', 'text' => 'text-success-700', 'border' => 'border-success-200'],
    'diterima'            => ['label' => 'Diterima',             'bg' => 'bg-success-100', 'text' => 'text-success-800', 'border' => 'border-success-300'],
    'ditolak'             => ['label' => 'Ditolak',              'bg' => 'bg-error-50', 'text' => 'text-error-700', 'border' => 'border-error-200'],
    'perlu_revisi'        => ['label' => 'Perlu Revisi',         'bg' => 'bg-warning-100', 'text' => 'text-warning-800', 'border' => 'border-warning-300'],
    'menunggu_konfirmasi_daftar_ulang' => ['label' => 'Menunggu Konfirmasi Daftar Ulang', 'bg' => 'bg-info-50', 'text' => 'text-info-700', 'border' => 'border-info-200'],
    'daftar_ulang_selesai' => ['label' => 'Selesai Daftar Ulang', 'bg' => 'bg-primary-50', 'text' => 'text-primary-700', 'border' => 'border-primary-200'],
];
$s = $map[$status] ?? ['label' => str_replace('_', ' ', Str::title($status)), 'bg' => 'bg-neutral-100', 'text' => 'text-neutral-700', 'border' => 'border-neutral-200'];
@endphp
<span class="inline-flex items-center px-2.5 py-1 {{ $s['bg'] }} {{ $s['text'] }} border {{ $s['border'] }} rounded-full text-xs font-semibold whitespace-nowrap">
    {{ $s['label'] }}
</span>
