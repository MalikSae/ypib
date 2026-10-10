<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Support\PrivateFile;
use Illuminate\Support\Facades\Auth;

/**
 * Penayangan berkas pendaftar yang terotorisasi. Path berkas selalu berasal
 * dari database, bukan dari input pengguna.
 */
class FileController extends Controller
{
    public const REGISTRATION_FIELDS = [
        'payment_proof',
        'alumni_card_proof',
        're_registration_payment_proof',
    ];

    /** Bukti bayar / kartu alumni / bukti daftar ulang: pemilik, admin, operator. */
    public function registration(int $id, string $field)
    {
        abort_unless(in_array($field, self::REGISTRATION_FIELDS, true), 404);

        $registration = Registration::withTrashed()->findOrFail($id);
        $this->authorizeAccess($registration, allowPanitia: false);

        return $this->serve($registration->{$field});
    }

    /** Dokumen pendaftaran: pemilik, admin, operator, panitia. */
    public function document(int $id)
    {
        $document = RegistrationDocument::findOrFail($id);
        $registration = Registration::withTrashed()->findOrFail($document->registration_id);
        $this->authorizeAccess($registration, allowPanitia: true);

        return $this->serve($document->file_path);
    }

    private function authorizeAccess(Registration $registration, bool $allowPanitia): void
    {
        $user = Auth::user();
        $roles = ['admin', 'operator'];
        if ($allowPanitia) {
            $roles[] = 'panitia';
        }

        abort_unless(
            $user && ($registration->user_id === $user->id || in_array($user->role, $roles, true)),
            403,
            'Akses ditolak.'
        );
    }

    private function serve(?string $path)
    {
        $response = $path ? PrivateFile::response($path) : null;
        abort_if($response === null, 404, 'Berkas tidak ditemukan.');

        return $response;
    }
}
