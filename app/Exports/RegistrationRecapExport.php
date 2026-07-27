<?php

namespace App\Exports;

use App\Models\Program;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RegistrationRecapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama Program Studi',
            'Pendaftar',
            'Lulus (Diterima)',
            'Daftar Ulang (Terkonfirmasi)',
        ];
    }

    public function map($row): array
    {
        static $no = 0;

        // Baris total tidak punya index numerik — tandai dengan string 'Total'
        if ($row['is_total'] ?? false) {
            return [
                '',
                'TOTAL',
                $row['pendaftar'],
                $row['lulus'],
                $row['daftar_ulang'],
            ];
        }

        $no++;
        return [
            $no,
            $row['nama_prodi'],
            $row['pendaftar'],
            $row['lulus'],
            $row['daftar_ulang'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->rows->count() + 2; // +1 heading, +1 total

        return [
            // Header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0B41CB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Total row (last data row)
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEF2FF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 45,
            'C' => 14,
            'D' => 18,
            'E' => 28,
        ];
    }
}
