@php
    $kopHeaderPath = public_path('images/kop_header.png');
    $kopFooterPath = public_path('images/kop_footer.png');
    $stempelPath = public_path('images/stempel_ketua_pmb.png');
    $tahunAjaran = ($registration->period->year ?? date('Y')) . '/' . ((int)($registration->period->year ?? date('Y')) + 1);
@endphp

<!DOCTYPE html>
<html>
<head>
<style>
    html, body { margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
    .content { padding: 20px 70px; }
    table.info td { padding: 3px 0; vertical-align: top; }
    .label-col { width: 140px; }
    .status-box { font-weight: bold; font-size: 14px; }
    ol { padding-left: 18px; }
    ol li { margin-bottom: 6px; }
    .signature { margin-top: 30px; }
    .signature-img { position: relative; }
</style>
</head>
<body>

@if(file_exists($kopHeaderPath))
    <img src="{{ $kopHeaderPath }}" style="width:100%; display:block;">
@endif

<div class="content">
    <table style="width:100%; margin-bottom:15px;">
        <tr><td style="width:100px;">Nomor</td><td style="width:10px;">:</td><td>{{ $registration->letter_number ?? '-' }}</td></tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
        <tr><td>Perihal</td><td>:</td><td><strong>Pemberitahuan Kelulusan PMB TA {{ $tahunAjaran }}</strong></td></tr>
    </table>

    <p>Kepada Yth.<br>Calon Mahasiswa Baru<br>di Tempat</p>

    <p>Dengan hormat,</p>

    <p>Berdasarkan hasil seleksi <strong>Penerimaan Mahasiswa Baru (PMB) Universitas YPIB Majalengka Tahun Akademik {{ $tahunAjaran }}</strong>, yang meliputi seleksi administrasi, tes tulis, dan wawancara, dengan ini disampaikan bahwa:</p>

    <table class="info" style="margin: 10px 0;">
        <tr><td class="label-col"><strong>Nama</strong></td><td>: {{ $registration->full_name }}</td></tr>
        <tr><td><strong>Nomor Pendaftaran</strong></td><td>: {{ $registration->registration_number }}</td></tr>
        @if($registration->nim)
        <tr><td><strong>NIM</strong></td><td>: {{ $registration->nim }}</td></tr>
        @endif
        <tr><td><strong>Program Studi</strong></td><td>: {{ $registration->firstChoiceProgram->name ?? '-' }} {{ $registration->firstChoiceProgram->registration_track === 'non_reguler' ? 'Non-Reguler' : 'Reguler' }}</td></tr>
    </table>

    <div style="text-align:center; margin: 16px 0;">
        <div class="status-box" style="border: 2px solid #1a1a1a; padding: 8px; display: inline-block;">DINYATAKAN LULUS / DITERIMA</div>
    </div>

    <p>sebagai <strong>Calon Mahasiswa Baru Universitas YPIB Majalengka Tahun Akademik {{ $tahunAjaran }}</strong>.</p>

    <p>Sehubungan dengan hal tersebut, Saudara/i diwajibkan melakukan <strong>daftar ulang</strong> dengan ketentuan sebagai berikut:</p>

    <ol>
        <li>Pembayaran paket kuliah dilaksanakan sesuai jadwal yang diinformasikan panitia
            @if(($registration->firstChoiceProgram->re_registration_minimum_payment ?? 0) > 0)
                , dengan pembayaran tahap awal minimal <strong>Rp {{ number_format($registration->firstChoiceProgram->re_registration_minimum_payment, 0, ',', '.') }}</strong>
            @endif
            .</li>
        <li>Pembayaran dilakukan melalui:<br>
            <strong>{{ $registration->period->university_bank_name ?? '-' }}</strong><br>
            No. Rekening: <strong>{{ $registration->period->university_bank_account ?? '-' }}</strong><br>
            Atas Nama: <strong>{{ $registration->period->university_bank_account_name ?? '-' }}</strong></li>
        <li>Total biaya Paket Kuliah sebesar <strong>Rp {{ number_format($registration->firstChoiceProgram->re_registration_fee ?? 0, 0, ',', '.') }}</strong>
            @if(!empty($registration->firstChoiceProgram->re_registration_fee_details))
                , dengan rincian:
                <ul>
                @foreach($registration->firstChoiceProgram->re_registration_fee_details as $detail)
                    <li>{{ $detail['name'] }}: Rp {{ number_format($detail['amount'], 0, ',', '.') }}</li>
                @endforeach
                </ul>
            @endif
        </li>
        <li>Bukti pembayaran dikirim ke WhatsApp Admin: <strong>{{ $registration->period->admin_whatsapp ?? '-' }}</strong>.</li>
        <li>Melengkapi seluruh berkas administrasi yang dipersyaratkan oleh panitia PMB.</li>
    </ol>

    <p>Demikian pemberitahuan ini disampaikan. Atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>

    <div class="signature">
        <p>Majalengka, {{ now()->locale('id')->translatedFormat('d F Y') }}<br>Ketua Panitia PMB</p>
        @if(file_exists($stempelPath))
            <img src="{{ $stempelPath }}" style="width:160px; margin: 10px 0 0 -10px;">
        @endif
    </div>
</div>

@if(file_exists($kopFooterPath))
    <img src="{{ $kopFooterPath }}" style="width:100%; display:block; position: fixed; bottom: 0; left: 0;">
@endif

</body>
</html>
