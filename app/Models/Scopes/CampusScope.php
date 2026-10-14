<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Admin, operator, dan panitia yang dibatasi ke satu kampus (users.campus_id) hanya melihat
 * pendaftaran kampusnya. Pengguna lain (pendaftar, referrer, admin tanpa kampus) dan proses
 * konsol tidak terpengaruh. Pendaftaran lama tanpa kampus dianggap milik kampus bawaan.
 *
 * Pemeriksaan yang butuh seluruh data (NIK ganda, urutan NIM/nomor daftar, hitung kursi)
 * memakai withoutGlobalScope(CampusScope::class).
 */
class CampusScope implements Scope
{
    public const STAFF_ROLES = ['admin', 'operator', 'panitia'];

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user || !$user->campus_id || !in_array($user->role, self::STAFF_ROLES, true)) {
            return;
        }

        $column = $model->getTable() . '.campus_id';
        $campusId = (int) $user->campus_id;

        $builder->where(function (Builder $query) use ($column, $campusId) {
            $query->where($column, $campusId);

            if ($campusId === \App\Models\Campus::defaultId()) {
                $query->orWhereNull($column);
            }
        });
    }
}
