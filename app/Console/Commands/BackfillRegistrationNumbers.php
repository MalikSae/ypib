<?php

namespace App\Console\Commands;

use App\Models\PaymentLog;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi NIM dan nomor SKL untuk pendaftar lama yang diproses sebelum fitur
 * itu ada (atau sebelum Kode Prodi diisi).
 *
 * Default: SIMULASI (dry-run). Seluruh proses dijalankan di dalam transaksi
 * lalu di-rollback, sehingga nomor yang ditampilkan persis sama dengan yang
 * akan ditulis oleh --apply (kecuali ada pendaftar baru di antaranya).
 */
class BackfillRegistrationNumbers extends Command
{
    protected $signature = 'registrations:backfill-numbers
        {--apply : Tulis perubahan ke database (tanpa opsi ini hanya simulasi)}
        {--acted-by= : ID user admin/operator sebagai pencatat audit (wajib bersama --apply)}
        {--letter-date= : Tanggal acuan bulan/tahun nomor SKL, format YYYY-MM-DD (default: hari ini)}';

    protected $description = 'Backfill NIM (daftar_ulang_selesai) dan nomor SKL (diterima dst.) yang masih kosong';

    private const LETTER_STATUSES = ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $actor = null;
        if ($apply) {
            $actor = User::whereIn('role', ['admin', 'operator'])->find((int) $this->option('acted-by'));
            if (!$actor) {
                $this->error('--apply memerlukan --acted-by=<ID user admin/operator yang valid> untuk catatan audit.');

                return self::FAILURE;
            }
        }

        $letterDate = null;
        if ($this->option('letter-date')) {
            try {
                $letterDate = Carbon::createFromFormat('Y-m-d', (string) $this->option('letter-date'))->startOfDay();
            } catch (\Throwable $e) {
                $this->error('--letter-date harus berformat YYYY-MM-DD.');

                return self::FAILURE;
            }
        }

        // Urut id agar nomor terbit berurutan sesuai kronologi pendaftaran.
        $candidates = Registration::with(['firstChoiceProgram', 'period'])
            ->where(function ($q) {
                $q->where(fn ($w) => $w->where('status', 'daftar_ulang_selesai')->whereNull('nim'))
                    ->orWhere(fn ($w) => $w->whereIn('status', self::LETTER_STATUSES)->whereNull('letter_number'));
            })
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Tidak ada pendaftar yang perlu di-backfill.');

            return self::SUCCESS;
        }

        $rows = [];
        $nimCount = $letterCount = $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($candidates as $r) {
                $changes = [];
                $skip = [];

                if ($r->status === 'daftar_ulang_selesai' && empty($r->nim)) {
                    if (empty($r->firstChoiceProgram?->kode_prodi)) {
                        $skip[] = 'NIM dilewati: prodi "' . ($r->firstChoiceProgram?->name ?? '?') . '" belum punya Kode Prodi';
                    } else {
                        try {
                            $nim = $r->generateNim();
                        } catch (\RuntimeException $e) {
                            $nim = null;
                            $skip[] = 'NIM dilewati: ' . $e->getMessage();
                        }

                        if ($nim === null) {
                            $skip[] = 'NIM dilewati: tidak dapat dibentuk';
                        } else {
                            $r->forceFill(['nim' => $nim]);
                            $changes[] = "NIM {$nim}";
                            $nimCount++;
                        }
                    }
                }

                if (in_array($r->status, self::LETTER_STATUSES, true) && empty($r->letter_number)) {
                    $letter = $r->generateLetterNumber($letterDate);
                    $r->forceFill(['letter_number' => $letter]);
                    $changes[] = "SKL {$letter}";
                    $letterCount++;
                }

                if ($changes !== []) {
                    $r->timestamps = false; // jangan mengubah updated_at pendaftar
                    $r->save();

                    PaymentLog::create([
                        'registration_id' => $r->id,
                        'acted_by'        => $actor?->id ?? User::whereIn('role', ['admin', 'operator'])->value('id') ?? $r->user_id,
                        'action'          => 'numbers_backfilled',
                        'note'            => 'Backfill otomatis: ' . implode(', ', $changes),
                    ]);
                }

                if ($skip !== []) {
                    $skipped++;
                }

                $rows[] = [
                    $r->id,
                    $r->registration_number ?? '-',
                    $r->status,
                    $changes ? implode("\n", $changes) : '-',
                    $skip ? implode("\n", $skip) : '',
                ];
            }

            $this->table(['ID', 'No. Daftar', 'Status', 'Akan/telah diisi', 'Dilewati'], $rows);

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
        $this->line("NIM: {$nimCount} | SKL: {$letterCount} | pendaftar dengan item dilewati: {$skipped}");

        if ($apply) {
            $this->info('Selesai: perubahan DITULIS ke database (dicatat di payment_logs: numbers_backfilled).');
        } else {
            $this->warn('DRY-RUN: tidak ada data yang berubah. Jalankan ulang dengan --apply --acted-by=<ID> untuk menulis.');
        }

        return self::SUCCESS;
    }
}
