<?php

namespace App\Console\Commands;

use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Registration;
use App\Support\PeriodPrecheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Kesiapan multi-periode sebelum menyalakan saklar (PLAN_MULTI_PERIODE.md T8).
 * Hanya membaca. Kode keluar 1 bila ada temuan GAGAL.
 */
class PeriodsReadiness extends Command
{
    protected $signature = 'periods:readiness';

    protected $description = 'Periksa kesiapan data multi-periode (tenggat, rekening, matriks biaya, irisan tanggal, duplikat) sebelum PMB_ENFORCE_WINDOW / PMB_ENFORCE_NIK_BLOCK dinyalakan';

    private int $failures = 0;
    private int $warnings = 0;

    public function handle(): int
    {
        if (!Schema::hasTable('period_programs') || !Schema::hasColumn('pmb_periods', 'status')) {
            $this->error('Migrasi multi-periode belum dijalankan (php artisan migrate).');

            return self::FAILURE;
        }

        $published = PmbPeriod::active()->orderBy('open_date')->get();

        $this->section('Periode');
        if ($published->isEmpty()) {
            $this->problem('Tidak ada periode berstatus tayang (published). Pendaftar tidak bisa mendaftar.');
        } else {
            $this->ok($published->count() . ' periode tayang: ' . $published->pluck('name')->implode(', '));
        }

        $current = PmbPeriod::current();
        $current
            ? $this->ok('Periode berjalan: ' . $current->name . ' (sampai ' . $current->close_date->toDateString() . ')')
            : $this->caution('Tidak ada periode yang sedang membuka pendaftaran. Landing menampilkan periode tayang terbaru.');

        foreach ($published as $period) {
            $label = '"' . $period->name . '"';

            if (!$period->re_registration_deadline) {
                $this->problem("{$label}: tenggat daftar ulang belum diisi (wajib untuk periode tayang; K7(b) dan gerbang daftar ulang tidak berlaku tanpanya).");
            } elseif ($period->re_registration_deadline->lt($period->close_date)) {
                $this->problem("{$label}: tenggat daftar ulang lebih awal dari tanggal tutup.");
            }

            foreach ([
                'university_bank_name'         => 'nama bank',
                'university_bank_account'      => 'nomor rekening',
                'university_bank_account_name' => 'atas nama rekening',
                'admin_whatsapp'               => 'WhatsApp admin',
            ] as $column => $name) {
                if (trim((string) $period->{$column}) === '') {
                    $this->problem("{$label}: {$name} kosong (dibaca halaman bayar, SKL, dan daftar ulang).");
                }
            }

            $overlap = PmbPeriod::overlapping($period->open_date, $period->close_date, $period->id)->first();
            if ($overlap) {
                $this->problem("{$label}: jendela pendaftaran beririsan dengan \"{$overlap->name}\".");
            }

            $this->line(sprintf(
                '  - %s: buka %s s.d. %s, masa bayar +%d hari, daftar ulang s.d. %s, urutan NIM mulai %d',
                $label,
                $period->open_date->toDateString(),
                $period->close_date->toDateString(),
                (int) $period->payment_grace_days,
                $period->re_registration_deadline?->toDateString() ?? '(kosong)',
                (int) $period->nim_sequence_start
            ));
        }

        $this->section('Biaya, kuota, dan komisi (period_programs)');
        $activePrograms = Program::where('is_active', true)->get();
        foreach ($published as $period) {
            $rows = PeriodProgram::where('period_id', $period->id)->get()->keyBy('program_id');
            $missing = $activePrograms->filter(fn ($program) => !$rows->has($program->id));

            if ($missing->isNotEmpty()) {
                $this->caution("\"{$period->name}\": {$missing->count()} prodi aktif belum punya baris biaya (memakai nilai cadangan dari master prodi): "
                    . $missing->pluck('name')->take(5)->implode(', ') . ($missing->count() > 5 ? ', ...' : ''));
            }

            $offered = $rows->filter(fn ($row) => $row->is_offered);
            if ($offered->isEmpty() && $activePrograms->isNotEmpty() && $rows->isNotEmpty()) {
                $this->problem("\"{$period->name}\": tidak ada prodi yang ditawarkan.");
            }

            $zeroFee = $offered->filter(fn ($row) => (int) $row->registration_fee === 0)->count();
            if ($zeroFee > 0) {
                $this->caution("\"{$period->name}\": {$zeroFee} prodi ditawarkan dengan biaya pendaftaran Rp 0.");
            }

            if ($missing->isEmpty() && $offered->isNotEmpty()) {
                $this->ok("\"{$period->name}\": {$offered->count()} prodi ditawarkan, matriks lengkap.");
            }
        }

        $noCode = Program::where('is_active', true)->where(fn ($q) => $q->whereNull('kode_prodi')->orWhere('kode_prodi', ''))->get();
        if ($noCode->isNotEmpty()) {
            $this->caution($noCode->count() . ' prodi aktif tanpa kode_prodi (konfirmasi daftar ulang ditolak): ' . $noCode->pluck('name')->take(5)->implode(', '));
        } else {
            $this->ok('Semua prodi aktif punya kode_prodi.');
        }

        $this->section('Data');
        $duplicateActive = PeriodPrecheck::duplicateActiveRegistrations();
        $duplicateNims = PeriodPrecheck::duplicateNims();
        $duplicateActive || $duplicateNims
            ? $this->problem(count($duplicateActive) . ' pasangan akun+periode ganda dan ' . count($duplicateNims) . ' NIM ganda (jalankan periods:precheck).')
            : $this->ok('Tidak ada pendaftaran aktif ganda per akun+periode maupun NIM ganda.');

        $stranded = Registration::whereHas('period', fn ($q) => $q->where('status', '!=', PmbPeriod::STATUS_PUBLISHED))
            ->whereNotIn('status', ['daftar_ulang_selesai', 'ditolak'])
            ->count();
        $stranded > 0
            ? $this->caution("{$stranded} pendaftaran berjalan berada di periode non-tayang (draft/arsip); pendaftarnya tidak bisa mengubah data.")
            : $this->ok('Tidak ada pendaftaran berjalan di periode non-tayang.');

        $duplicateNiks = Registration::select('nik')->groupBy('nik')->havingRaw('COUNT(*) > 1')->pluck('nik')->count();
        $duplicateNiks > 0
            ? $this->caution("{$duplicateNiks} NIK dipakai lebih dari satu pendaftaran aktif (tanda \"NIK ganda\"); tinjau sebelum menyalakan PMB_ENFORCE_NIK_BLOCK.")
            : $this->ok('Tidak ada NIK ganda.');

        $this->section('Saklar');
        $this->line('  PMB_ENFORCE_WINDOW    = ' . (config('pmb.enforce_window') ? 'MENYALA' : 'mati'));
        $this->line('  PMB_ENFORCE_NIK_BLOCK = ' . (config('pmb.enforce_nik_block') ? 'MENYALA' : 'mati'));

        $this->newLine();
        if ($this->failures > 0) {
            $this->error("{$this->failures} temuan GAGAL, {$this->warnings} peringatan. Selesaikan temuan GAGAL sebelum menyalakan saklar.");

            return self::FAILURE;
        }

        $this->info("Tidak ada temuan GAGAL ({$this->warnings} peringatan).");

        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<options=bold>{$title}</>");
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>[OK]</>     {$message}");
    }

    private function caution(string $message): void
    {
        $this->warnings++;
        $this->line("  <fg=yellow>[PERIKSA]</> {$message}");
    }

    private function problem(string $message): void
    {
        $this->failures++;
        $this->line("  <fg=red>[GAGAL]</>  {$message}");
    }
}
