<?php

namespace App\Console\Commands;

use App\Support\PrivateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeRegistrationFiles extends Command
{
    protected $signature = 'files:privatize {--dry-run : Tampilkan berkas yang akan dipindah tanpa memindahkan}';

    protected $description = 'Pindahkan berkas sensitif pendaftar dari disk public ke disk private';

    private const DIRECTORIES = [
        'bukti-bayar',
        'bukti-bayar-daftar-ulang',
        'bukti-daftar-ulang',
        'kartu-alumni',
        'dokumen-pendaftaran',
        'dokumen-ijazah',
    ];

    public function handle(): int
    {
        $public = Storage::disk(PrivateFile::LEGACY_DISK);
        $private = Storage::disk(PrivateFile::DISK);
        $dry = (bool) $this->option('dry-run');

        $moved = $skipped = $failed = 0;

        foreach (self::DIRECTORIES as $dir) {
            foreach ($public->allFiles($dir) as $path) {
                if ($dry) {
                    $this->line("[dry-run] {$path}");
                    $moved++;
                    continue;
                }

                if ($private->exists($path)) {
                    // Sudah ada di private: hapus salinan publik saja bila isinya sama ukuran.
                    if ($private->size($path) === $public->size($path)) {
                        $public->delete($path);
                        $skipped++;
                    } else {
                        $this->warn("Konflik (ukuran beda), dilewati: {$path}");
                        $failed++;
                    }
                    continue;
                }

                $stream = $public->readStream($path);
                if ($stream === false || !$private->writeStream($path, $stream)) {
                    $this->error("Gagal menyalin: {$path}");
                    $failed++;
                    continue;
                }
                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($private->size($path) !== $public->size($path)) {
                    $private->delete($path);
                    $this->error("Ukuran tidak cocok setelah salin: {$path}");
                    $failed++;
                    continue;
                }

                $public->delete($path);
                $moved++;
            }
        }

        $this->info(($dry ? 'Akan dipindah' : 'Dipindah') . ": {$moved}, sudah ada: {$skipped}, gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
