<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmbPeriod extends Model
{
    use HasFactory;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    protected $fillable = [
        'name',
        'year',
        'open_date',
        'close_date',
        're_registration_deadline',
        'payment_grace_days',
        'nim_sequence_start',
        'is_active',
        'status',
        'published_at',
        'archived_at',
        'university_bank_name',
        'university_bank_account',
        'university_bank_account_name',
        'admin_whatsapp',
    ];

    protected $casts = [
        'open_date'                => 'date',
        'close_date'               => 'date',
        're_registration_deadline' => 'date',
        'payment_grace_days'       => 'integer',
        'nim_sequence_start'       => 'integer',
        'is_active'                => 'boolean',
        'published_at'             => 'datetime',
        'archived_at'              => 'datetime',
    ];

    /**
     * `status` adalah sumber kebenaran; `is_active` hanya dipertahankan untuk penulis lama
     * sampai T9. Penentunya atribut yang diubah (PLAN_MULTI_PERIODE.md 5.1):
     *  - `status` diubah           -> is_active mengikuti (published = true).
     *  - hanya `is_active` diubah  -> status mengikuti, kecuali periode sudah arsip.
     *  - keduanya diubah           -> `status` menang.
     * `update()` massal lewat query builder tidak memicu hook ini.
     */
    protected static function booted(): void
    {
        static::saving(function (PmbPeriod $period) {
            $statusChanged = $period->isDirty('status');
            $activeChanged = $period->isDirty('is_active');

            if (!$statusChanged && $activeChanged) {
                if ($period->status === self::STATUS_ARCHIVED) {
                    $period->is_active = false;
                } else {
                    $period->status = $period->is_active ? self::STATUS_PUBLISHED : self::STATUS_DRAFT;
                    $statusChanged = true;
                }
            }

            if ($statusChanged) {
                $period->is_active = $period->status === self::STATUS_PUBLISHED;

                if ($period->status === self::STATUS_PUBLISHED && !$period->published_at) {
                    $period->published_at = now();
                }
                if ($period->status === self::STATUS_ARCHIVED && !$period->archived_at) {
                    $period->archived_at = now();
                }
            }
        });
    }

    /** Periode yang ditayangkan (published), terlepas dari tanggal. */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Periode berjalan: ditayangkan dan hari ini berada di jendela open_date..close_date.
     * Bila ada lebih dari satu (tidak seharusnya, ditolak saat tayang), ambil yang paling baru.
     */
    public static function current(): ?self
    {
        $today = now()->toDateString();

        return static::active()
            ->whereDate('open_date', '<=', $today)
            ->whereDate('close_date', '>=', $today)
            ->orderByDesc('open_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Periode yang dipakai untuk tampilan publik dan pembaca lama: periode berjalan;
     * bila tidak ada, periode tayang dengan open_date terbaru (mis. sudah tutup atau belum buka).
     */
    public static function displayPeriod(): ?self
    {
        return static::current()
            ?? static::active()->orderByDesc('open_date')->orderByDesc('id')->first();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Pendaftaran baru dibuka: ditayangkan dan hari ini berada di antara tanggal buka
     * dan tanggal tutup (inklusif sampai akhir hari, WIB).
     */
    public function isOpen(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $today = now()->startOfDay();

        return $today->gte($this->open_date->copy()->startOfDay())
            && $today->lte($this->close_date->copy()->endOfDay());
    }

    /** Masa bayar: sampai akhir hari close_date + payment_grace_days (inklusif). */
    public function isPaymentOpen(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $today = now()->startOfDay();
        $lastDay = $this->close_date->copy()->addDays((int) $this->payment_grace_days)->endOfDay();

        return $today->gte($this->open_date->copy()->startOfDay()) && $today->lte($lastDay);
    }

    /** Masa pendaftaran sudah lewat tetapi periode masih ditayangkan. */
    public function isInProgress(): bool
    {
        return $this->isPublished() && now()->startOfDay()->gt($this->close_date->copy()->endOfDay());
    }

    /** Daftar ulang masih dibuka: ditayangkan dan tenggat kosong atau belum lewat. */
    public function isReRegistrationOpen(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        if (!$this->re_registration_deadline) {
            return true;
        }

        return now()->startOfDay()->lte($this->re_registration_deadline->copy()->endOfDay());
    }

    /**
     * Teks banner publik: [label, tanggal]. Mengikuti fase periode supaya landing tidak
     * mengatakan "dibuka hingga" untuk periode yang sudah tutup atau belum buka.
     *
     * @return array{0: string, 1: string}
     */
    public function publicBanner(): array
    {
        $today = now()->startOfDay();

        if ($this->isOpen()) {
            return ['Pendaftaran dibuka hingga', $this->close_date->translatedFormat('d F Y')];
        }

        if ($today->lt($this->open_date->copy()->startOfDay())) {
            return ['Pendaftaran dibuka pada', $this->open_date->translatedFormat('d F Y')];
        }

        return ['Pendaftaran ditutup pada', $this->close_date->translatedFormat('d F Y')];
    }
    /** Label fase untuk admin, diturunkan dari status dan tanggal. */
    public function phaseLabel(): string
    {
        if ($this->isArchived()) {
            return 'Diarsipkan';
        }
        if (!$this->isPublished()) {
            return 'Draft';
        }
        if ($this->isOpen()) {
            return 'Pendaftaran dibuka';
        }
        if ($this->isPaymentOpen()) {
            return 'Masa bayar';
        }
        if ($this->isInProgress()) {
            return 'Berjalan (pendaftaran tutup)';
        }

        return 'Belum dibuka';
    }

    /**
     * Periode tayang lain yang jendela open_date..close_date-nya beririsan dengan rentang ini.
     * Menjamin satu "periode berjalan" tanpa langkah aktivasi.
     */
    public static function overlapping($open, $close, ?int $exceptId = null)
    {
        return static::active()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->whereDate('open_date', '<=', \Illuminate\Support\Carbon::parse($close)->toDateString())
            ->whereDate('close_date', '>=', \Illuminate\Support\Carbon::parse($open)->toDateString());
    }

    /**
     * Alasan periode ini belum boleh ditayangkan (kosong = boleh). Dipakai tayangkan dan buka-kembali.
     *
     * @return array<int, string>
     */
    public function publishBlockers(): array
    {
        $blockers = [];

        if (!$this->re_registration_deadline) {
            $blockers[] = 'Tenggat daftar ulang wajib diisi sebelum ditayangkan.';
        } elseif ($this->re_registration_deadline->lt($this->close_date)) {
            $blockers[] = 'Tenggat daftar ulang tidak boleh lebih awal dari tanggal tutup.';
        }

        $required = [
            'university_bank_name'         => 'Nama bank',
            'university_bank_account'      => 'Nomor rekening',
            'university_bank_account_name' => 'Atas nama rekening',
            'admin_whatsapp'               => 'Nomor WhatsApp admin',
        ];
        foreach ($required as $column => $label) {
            if (trim((string) $this->{$column}) === '') {
                $blockers[] = $label . ' wajib diisi sebelum ditayangkan (dibaca halaman bayar, SKL, dan daftar ulang).';
            }
        }

        $overlap = static::overlapping($this->open_date, $this->close_date, $this->id)->first();
        if ($overlap) {
            $blockers[] = 'Jendela pendaftaran beririsan dengan periode tayang "' . $overlap->name . '".';
        }

        return $blockers;
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'period_id');
    }

    public function periodPrograms()
    {
        return $this->hasMany(PeriodProgram::class, 'period_id');
    }
}
