<?php

namespace App\Livewire\Concerns;

/**
 * Request pembaruan Livewire tidak melewati middleware rute, jadi tiap komponen daftar memanggil
 * salah satu pemeriksaan ini dari boot(). Setiap method setara dengan middleware rutenya.
 */
trait AuthorizesAccess
{
    /** role:admin,operator (staf kampus tunggal boleh, data dibatasi CampusScope). */
    protected function authorizeStaff(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'operator'], true), 403);
    }

    /** role:admin,operator + global.admin: hanya staf tanpa batasan kampus. */
    protected function authorizeGlobalStaff(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['admin', 'operator'], true) && ! $user->campus_id, 403);
    }

    /** super.admin: hanya admin tanpa batasan kampus. */
    protected function authorizeSuperAdmin(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin' && ! $user->campus_id, 403);
    }

    /** role:panitia. */
    protected function authorizePanitia(): void
    {
        abort_unless(auth()->user()?->role === 'panitia', 403);
    }

    /** Pilihan "jumlah per halaman" untuk x-select-menu: [5 => '5', 10 => '10', ...]. */
    protected function perPageOptions(array $sizes): array
    {
        return array_combine($sizes, array_map('strval', $sizes));
    }
}
