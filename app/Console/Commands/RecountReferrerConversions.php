<?php

namespace App\Console\Commands;

use App\Models\Referrer;
use App\Models\ReferrerLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Menghitung ulang counter afiliator dari data sumbernya:
 *  - total_conversions = pendaftar (tidak dihapus) yang punya reward 'registration'
 *    berstatus selain cancelled, per afiliator.
 *  - total_clicks (opsional, --clicks) = jumlah baris referral_clicks.
 *
 * Default: SIMULASI (dry-run). Tulis dengan --apply --acted-by=<ID>.
 */
class RecountReferrerConversions extends Command
{
    protected $signature = 'referrers:recount
        {--apply : Tulis perubahan ke database (tanpa opsi ini hanya simulasi)}
        {--acted-by= : ID user admin/operator sebagai pencatat audit (wajib bersama --apply)}
        {--clicks : Hitung ulang juga total_clicks dari tabel referral_clicks}';

    protected $description = 'Hitung ulang total_conversions (dan opsional total_clicks) afiliator dari data reward/klik';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $withClicks = (bool) $this->option('clicks');

        $actor = null;
        if ($apply) {
            $actor = User::whereIn('role', ['admin', 'operator'])->find((int) $this->option('acted-by'));
            if (!$actor) {
                $this->error('--apply memerlukan --acted-by=<ID user admin/operator yang valid> untuk catatan audit.');

                return self::FAILURE;
            }
        }

        $conversions = DB::table('rewards')
            ->join('registrations', 'registrations.id', '=', 'rewards.registration_id')
            ->whereNull('registrations.deleted_at')
            ->where('rewards.reward_type', 'registration')
            ->where('rewards.status', '!=', 'cancelled')
            ->selectRaw('rewards.referrer_id, COUNT(DISTINCT rewards.registration_id) as n')
            ->groupBy('rewards.referrer_id')
            ->pluck('n', 'referrer_id');

        $clicks = $withClicks
            ? DB::table('referral_clicks')->selectRaw('referrer_id, COUNT(*) as n')->groupBy('referrer_id')->pluck('n', 'referrer_id')
            : collect();

        $rows = [];
        $changed = 0;

        DB::beginTransaction();

        try {
            foreach (Referrer::orderBy('id')->get() as $ref) {
                $newConv = (int) ($conversions[$ref->id] ?? 0);
                $diffs = [];

                if ((int) $ref->total_conversions !== $newConv) {
                    $diffs['total_conversions'] = [(int) $ref->total_conversions, $newConv];
                }

                if ($withClicks) {
                    $newClicks = (int) ($clicks[$ref->id] ?? 0);
                    if ((int) $ref->total_clicks !== $newClicks) {
                        $diffs['total_clicks'] = [(int) $ref->total_clicks, $newClicks];
                    }
                }

                if ($diffs !== []) {
                    $changed++;
                    $note = [];
                    foreach ($diffs as $field => [$old, $new]) {
                        $ref->{$field} = $new;
                        $note[] = "{$field}: {$old} -> {$new}";
                    }
                    $ref->timestamps = false;
                    $ref->save();

                    ReferrerLog::create([
                        'referrer_id' => $ref->id,
                        'acted_by'    => $actor?->id ?? User::whereIn('role', ['admin', 'operator'])->value('id') ?? $ref->user_id,
                        'action'      => 'counter_recounted',
                        'note'        => 'Hitung ulang counter: ' . implode(', ', $note),
                    ]);
                }

                $rows[] = [
                    $ref->id,
                    $ref->code,
                    $diffs['total_conversions'][0] ?? (int) $ref->total_conversions,
                    $diffs['total_conversions'][1] ?? (int) $ref->total_conversions,
                    $withClicks ? ($diffs['total_clicks'][0] ?? (int) $ref->total_clicks) . ' -> ' . ($diffs['total_clicks'][1] ?? (int) $ref->total_clicks) : '-',
                    $diffs ? 'BERUBAH' : 'sama',
                ];
            }

            $this->table(['ID', 'Kode', 'Konversi (lama)', 'Konversi (baru)', 'Klik (lama -> baru)', 'Hasil'], $rows);

            if ($apply) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Dibatalkan, tidak ada data yang berubah: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->line("Afiliator berubah: {$changed} dari " . count($rows));

        if ($apply) {
            $this->info('Selesai: counter DITULIS ke database (dicatat di referrer_logs: counter_recounted).');
        } else {
            $this->warn('DRY-RUN: tidak ada data yang berubah. Jalankan ulang dengan --apply --acted-by=<ID> untuk menulis.');
        }

        return self::SUCCESS;
    }
}
