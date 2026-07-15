<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran - {{ $registration->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14px;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            width: 30%;
            font-weight: bold;
        }
        .colon {
            width: 2%;
        }
        .value {
            width: 68%;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    @php
        $photoDoc = $registration->documents->firstWhere('document_type', 'foto');
        $photoPath = $photoDoc ? \Illuminate\Support\Facades\Storage::disk('public')->path($photoDoc->file_path) : null;
        $hasPhoto = $photoPath && file_exists($photoPath);
        $logoPath = public_path('images/favicon.png');
    @endphp

    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:95px; vertical-align:middle;">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="width:80px; height:80px; object-fit:contain;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div style="font-size:16px; font-weight:bold; text-transform:uppercase; margin:0;">Universitas YPIB Majalengka</div>
                <div style="font-size:12px; margin:4px 0 0;">Formulir Pendaftaran Mahasiswa Baru</div>
            </td>
            <td style="width:95px;"></td>
        </tr>
    </table>
    <div style="border-bottom:2px solid #333; margin:10px 0 15px;"></div>

    <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <tr>
            <td style="vertical-align:top;">
                @if($registration->registration_number)
                    <strong>No. Registrasi: {{ $registration->registration_number }}</strong>
                @endif
                @if(isset($qrCodeBase64))
                    <div style="margin-top:8px;">
                        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" style="width:100px; height:100px;">
                    </div>
                @endif
            </td>
            <td style="width:120px; vertical-align:top; text-align:right;">
                @if($hasPhoto)
                    <img src="{{ $photoPath }}" style="width:112px; height:150px; object-fit:cover; border:1px solid #333;">
                @else
                    <div style="width:112px; height:150px; border:1px dashed #999; display:inline-block; text-align:center; font-size:11px; color:#999; line-height:150px;">
                        Pas Foto
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">A. PROGRAM STUDI & JALUR</div>
    <table>
        <tr>
            <td class="label">Program Studi</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->firstChoiceProgram ? $registration->firstChoiceProgram->name : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jalur Pendaftaran</td>
            <td class="colon">:</td>
            <td class="value">{{ str_replace('_', ' ', Str::title($registration->admission_path)) }}</td>
        </tr>
    </table>

    <div class="section-title">B. DATA DIRI</div>
    <table>
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->full_name }}</td>
        </tr>
        <tr>
            <td class="label">NISN</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->nisn }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->nik }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->birth_place }}, {{ $registration->birth_date ? $registration->birth_date->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td class="label">Agama</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->religion }}</td>
        </tr>
        <tr>
            <td class="label">No. HP (WhatsApp)</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->phone }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->user ? $registration->user->email : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Lengkap</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->address }}</td>
        </tr>
    </table>

    <div class="section-title">C. DATA SEKOLAH ASAL</div>
    <table>
        <tr>
            <td class="label">Nama Sekolah</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->school_name }}</td>
        </tr>
        <tr>
            <td class="label">Jurusan</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->school_major }}</td>
        </tr>
        <tr>
            <td class="label">Tahun Lulus</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->graduation_year }}</td>
        </tr>
        <tr>
            <td class="label">No. Ijazah / SKL</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->certificate_number }}</td>
        </tr>
        <tr>
            <td class="label">Nilai Rata-rata Rapor</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->school_grade ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">D. DATA ORANG TUA / WALI</div>
    <table>
        <tr>
            <td class="label">Nama Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->father_name }}</td>
        </tr>
        <tr>
            <td class="label">Pekerjaan Ayah</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->father_occupation }}</td>
        </tr>
        <tr>
            <td class="label">Nama Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->mother_name }}</td>
        </tr>
        <tr>
            <td class="label">Pekerjaan Ibu</td>
            <td class="colon">:</td>
            <td class="value">{{ $registration->mother_occupation }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada {{ date('d-m-Y H:i:s') }}<br>
        Dokumen ini digenerate otomatis oleh sistem PMB YPIB Majalengka.
    </div>

</body>
</html>
