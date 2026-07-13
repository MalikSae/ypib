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

    <div class="header">
        <h1>Universitas YPIB Majalengka</h1>
        <h2>FORMULIR PENDAFTARAN MAHASISWA BARU</h2>
    </div>

    @if($registration->registration_number)
    <div style="text-align: right; margin-bottom: 10px;">
        <strong>No. Registrasi: {{ $registration->registration_number }}</strong>
    </div>
    @endif

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
