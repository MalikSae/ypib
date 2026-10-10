<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penyimpanan berkas sensitif pendaftar (bukti bayar, kartu alumni, dokumen).
 *
 * Berkas baru disimpan di disk `local` (storage/app/private) sehingga tidak bisa
 * diakses lewat URL publik; penayangan hanya lewat FileController (wajib login
 * dan terotorisasi). Berkas lama di disk `public` tetap terbaca (fallback)
 * sampai dipindahkan dengan `php artisan files:privatize`.
 */
class PrivateFile
{
    public const DISK = 'local';
    public const LEGACY_DISK = 'public';

    public static function store(UploadedFile $file, string $directory): string
    {
        // store() memakai nama acak dan ekstensi dari isi berkas, bukan dari klien.
        return $file->store($directory, self::DISK);
    }

    /** Disk tempat berkas berada, atau null bila tidak ada. */
    public static function diskFor(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        return self::diskFor($path) !== null;
    }

    public static function absolutePath(?string $path): ?string
    {
        $disk = self::diskFor($path);

        return $disk ? Storage::disk($disk)->path($path) : null;
    }

    public static function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    public static function response(string $path): ?StreamedResponse
    {
        $disk = self::diskFor($path);
        if (!$disk) {
            return null;
        }

        return Storage::disk($disk)->response($path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, max-age=0, must-revalidate',
        ]);
    }
}
