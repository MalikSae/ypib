<?php

namespace App\Console\Commands;

use App\Models\PaymentLog;
use App\Models\PmbPeriod;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Memindahkan pendaftaran ke periode lain (PLAN_MULTI_PERIODE.md T8, T-6).
 *
 * Dipakai bila data lama yang seluruhnya berada di satu periode ternyata perlu dipecah per
 * tahun ajaran. Default: SIMULASI (transaksi di-rollback). Tulis dengan --apply --acted-by=<ID>.
 *
 * Tidak mengubah nomor pendaftaran, NIM, atau nomor SKL yang sudah terbit.
 */
class AssignRegistrationsToPeriod extends Command
{
    protected $signature = 'periods:assign
        {--to= : ID periode tujuan (wajib)}
        {--from= : Hanya pendaftaran yang saat ini berada di periode ini}
        {--ids= : Daftar ID pendaftaran dipisah koma}
        {--created-from= : Dibuat mulai tanggal (YYYY-MM-DD, inklusif)}
        {--created-until= : Dibuat sampai tanggal (YYYY-MM-DD, inklusif)}
        {--apply : Tulis perubahan ke database (tanpa opsi ini hanya simulasi)}
        {--acted-by= : ID user admin/operator sebagai pencatat audit (wajib bersama --apply)}';

    protected $description = 'Pindahkan pendaftaran ke periode lain (dry-run bawaan); NIM dan nomor yang sudah terbit tidak diubah';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $target = PmbPeriod::find((int) $this->option('to'));
        if (!$target) {
            $this->error('--to=<ID periode tujuan> wajib dan harus valid.');

            return self::FAILURE;
        }

        if ($target->isArchived()) {
            $this->error('Periode tujuan sudah diarsipkan. Buka kembali periode itu terlebih dahulu.');

            return self::FAILURE;
        }

        $from = $this->option('from') ? (int) $this->option('from') : null;
        $ids = $this->option('ids')
            ? array_values(array_filter(array_map('intval', explode(',', (string) $this->option('ids')))))
            : [];

        $createdFrom = $createdUntil = null;
        try {
            if ($this->option('created-from')) {
                $createdFrom = Carbon::createFromFormat('Y-m-d', (string) $this->option('created-from'))->startOfDay();
            }
            if ($this->option('created-until')) {
                $createdUntil = Carbon::createFromFormat('Y-m-d', (string) $this->option('created-until'))->endOfDay();
            }
        } catch (\Throwable $e) {
            $this->error('--created-from / --created-until harus berformat YYYY-MM-DD.');

            return self::FAILURE;
        }

        if (!$from && !$ids && !$createdFrom && !$createdUntil) {
            $this->error('Tentukan minimal satu pemilih: --from, --ids, --created-from, atau --created-until.');

            return self::FAILURE;
        }

        $actor = null;
        if ($apply) {
            $actor = User::whereIn('role', ['admin', 'operator'])->find((int) $this->option('acted-by'));
            if (!$actor) {
                $this->error('--apply memerlukan --acted-by=<ID user admin/operator yang valid> untuk catatan audit.');

                return self::FAILURE;
            }
        }

        $candidates = Registration::with('period')
            ->where('period_id', '!=', $target->id)
            ->when($from, fn ($q) => $q->where('period_id', $from))
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($createdFrom, fn ($q) => $q->where('created_at', '>=', $createdFrom))
            ->when($createdUntil, fn ($q) => $q->where('created_at', '<=', $createdUntil))
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Tidak ada pendaftaran yang cocok dengan pemilih.');

            return self::SUCCESS;
        }

        $rows = [];
        $moved = $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($candidates as $registration) {
                // Satu pendaftaran aktif per akun per periode: lewati bila akun sudah punya di tujuan.
                $conflict = Registration::where('user_id', $registration->user_id)
                    ->where('period_id', $target->id)
                    ->exists();

                if ($conflict) {
                    $skipped++;
                    $rows[] = [$registration->id, $registration->full_name, $registration->period?->name ?? '-', 'DILEWATI: akun sudah punya pendaftaran di periode tujuan'];

                    continue;
                }

                $oldName = $registration->period?->name ?? ('#' . $registration->period_id);
                $registration->timestamps = false; // jangan mengubah updated_at pendaftar
                $registration->period_id = $target->id;
                $registration->save();

                PaymentLog::create([
                    'registration_id' => $registration->id,
                    'acted_by'        => $actor?->id ?? User::whereIn('role', ['admin', 'operator'])->value('id') ?? $registration->user_id,
                    'action'          => 'period_reassigned',
                    'note'            => "Periode dipindah dari \"{$oldName}\" ke \"{$target->name}\" (command periods:assign).",
                ]);

                $moved++;
                $rows[] = [$registration->id, $registration->full_name, $oldName, 'dipindah ke ' . $target->name];
            }

            $this->table(['ID', 'Nama', 'Periode lama', 'Hasil'], $rows);

            if ($apply) {
                DB::commit();
                $this->info("Selesai: {$moved} dipindah, {$skipped} dilewati. Dicatat di payment_logs (period_reassigned).");
            } else {
                DB::rollBack();
                $this->warn("DRY-RUN: {$moved} akan dipindah, {$skipped} dilewati. Tidak ada yang ditulis. Ulangi dengan --apply --acted-by=<ID>.");
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Dibatalkan, tidak ada perubahan: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->line('Catatan: nomor pendaftaran, NIM, dan nomor SKL yang sudah terbit tidak diubah.');

        return self::SUCCESS;
    }
}
