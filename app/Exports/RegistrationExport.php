<?php

namespace App\Exports;

use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrationExport extends SafeValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder
{
    /**
     * XLSX menyimpan seluruh sel di memori (PhpSpreadsheet): ±25 MB per 1.000 baris.
     * Di atas batas ini ekspor harus memakai CSV streaming.
     */
    public const XLSX_MAX_ROWS = 5000;

    protected $prodiId;
    protected $status;

    public function __construct($prodiId = null, $status = null)
    {
        $this->prodiId = $prodiId;
        $this->status = $status;
    }

    /** Query terfilter tanpa urutan; dipakai collection() dan ekspor CSV streaming. */
    public function query()
    {
        $query = Registration::with(['user', 'referrer.user', 'firstChoiceProgram', 'secondChoiceProgram']);

        if ($this->prodiId) {
            $query->where('first_choice_program_id', $this->prodiId);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function collection()
    {
        return $this->query()->latest()->get();
    }

    /**
     * Baris CSV: nilai teks dinetralkan dari injeksi formula, dan deretan digit yang
     * akan rusak di Excel (angka nol di depan hilang, >= 15 digit dibulatkan) dibungkus
     * ="..." agar tampil sebagai teks. Digit murni, jadi aman dari injeksi.
     */
    public function csvRow($registration): array
    {
        return array_map(function ($value) {
            $value = \App\Support\CsvSanitizer::cell($value);

            if (is_string($value) && $value !== '' && ctype_digit($value)
                && (($value[0] === '0' && strlen($value) > 1) || strlen($value) >= 15)) {
                return '="' . $value . '"';
            }

            return $value;
        }, $this->map($registration));
    }

    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Nama Lengkap',
            'Email',
            'NIK',
            'No. HP',
            'Asal Sekolah',
            'Tahun Lulus',
            'Jalur Masuk',
            'Pilihan 1',
            'Pilihan 2',
            'Kode Referral',
            'Nama Referrer',
            'Status',
            'Tanggal Daftar',
        ];
    }

    public function map($registration): array
    {
        return [
            $registration->registration_number,
            $registration->full_name,
            $registration->user?->email,
            $registration->nik,
            $registration->phone,
            $registration->school_name,
            $registration->graduation_year,
            $registration->admission_path,
            $registration->firstChoiceProgram?->name,
            $registration->secondChoiceProgram?->name,
            $registration->referrer?->code,
            $registration->referrer?->user?->name,
            $registration->status,
            $registration->created_at->format('d/m/Y H:i'),
        ];
    }
}
