<?php

namespace App\Exports;

use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrationExport implements FromCollection, WithHeadings, WithMapping
{
    protected $prodiId;
    protected $status;

    public function __construct($prodiId = null, $status = null)
    {
        $this->prodiId = $prodiId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Registration::with(['user', 'referrer.user', 'firstChoiceProgram', 'secondChoiceProgram']);
        
        if ($this->prodiId) {
            $query->where('first_choice_program_id', $this->prodiId);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get();
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
