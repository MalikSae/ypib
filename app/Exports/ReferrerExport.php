<?php

namespace App\Exports;

use App\Models\Referrer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReferrerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $periodId;
    protected $status;

    public function __construct($periodId = null, $status = null)
    {
        $this->periodId = $periodId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Referrer::with(['user'])
            ->withSum(['rewards' => function($q) {
                $q->whereIn('status', ['approved', 'disbursed']);
            }], 'amount');
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        if ($this->periodId) {
            $query->whereHas('registrations', function($q) {
                $q->where('period_id', $this->periodId);
            });
        }
        
        $query->withCount(['registrations as total_pendaftar_periode' => function($q) {
            if ($this->periodId) {
                $q->where('period_id', $this->periodId);
            }
        }]);

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Kode Referral',
            'Nama',
            'Email',
            'Status',
            'Total Klik',
            'Total Konversi',
            'Jumlah Pendaftar (Periode Terpilih)',
            'Total Komisi',
            'Bank',
            'No. Rekening',
            'Atas Nama',
        ];
    }

    public function map($referrer): array
    {
        return [
            $referrer->code,
            $referrer->user?->name,
            $referrer->user?->email,
            $referrer->status,
            $referrer->total_clicks,
            $referrer->total_conversions,
            $referrer->total_pendaftar_periode,
            $referrer->total_reward ?? $referrer->rewards_sum_amount ?? 0,
            $referrer->bank_name,
            $referrer->bank_account_number,
            $referrer->bank_account_name,
        ];
    }
}
