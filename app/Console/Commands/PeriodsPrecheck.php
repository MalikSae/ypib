<?php

namespace App\Console\Commands;

use App\Support\PeriodPrecheck;
use Illuminate\Console\Command;

/**
 * Laporan pra-pemeriksaan migrasi multi-periode. Hanya membaca, tidak mengubah data.
 */
class PeriodsPrecheck extends Command
{
    protected $signature = 'periods:precheck';

    protected $description = 'Periksa data yang akan melanggar unique multi-periode (pendaftaran ganda per periode, NIM ganda) dan periode non-aktif yang masih berjalan';

    public function handle(): int
    {
        $problems = 0;

        $duplicateActive = PeriodPrecheck::duplicateActiveRegistrations();
        $this->line('Pendaftaran aktif ganda per akun+periode: ' . count($duplicateActive));
        foreach ($duplicateActive as $row) {
            $this->warn("  user_id={$row->user_id} period_id={$row->period_id} jumlah={$row->total}");
        }
        $problems += count($duplicateActive);

        $duplicateNims = PeriodPrecheck::duplicateNims();
        $this->line('NIM ganda: ' . count($duplicateNims));
        foreach ($duplicateNims as $row) {
            $this->warn("  nim={$row->nim} jumlah={$row->total}");
        }
        $problems += count($duplicateNims);

        $inactive = PeriodPrecheck::inactivePeriodsWithOpenRegistrations();
        $this->line('Periode non-aktif dengan pendaftaran berjalan (akan diarsipkan, tinjau): ' . count($inactive));
        foreach ($inactive as $row) {
            $this->warn("  period_id={$row->id} \"{$row->name}\" pendaftaran berjalan={$row->total}");
        }

        if ($problems > 0) {
            $this->error('Selesaikan duplikat di atas sebelum menjalankan migrasi multi-periode.');

            return self::FAILURE;
        }

        $this->info('Tidak ada pelanggar unique. Aman untuk migrasi.');

        return self::SUCCESS;
    }
}
