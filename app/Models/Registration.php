<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    private const YEAR_SEQUENCE_START = [
        '26' => 400, // Tahun 2026: sequence NIM dimulai dari 400
    ];

    protected $fillable = [
        'period_id',
        'campus_id',
        'user_id',
        'referrer_id',
        'registration_number',
        'registration_type',
        'admission_path',
        'first_choice_program_id',
        'second_choice_program_id',
        'full_name',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'phone',
        'school_name',
        'graduation_year',
        'school_grade',
        'nisn',
        'education_level',
        'religion',
        'school_major',
        'certificate_number',
        'father_name',
        'mother_name',
        'father_occupation',
        'mother_occupation',
        'payment_proof',
        'alumni_card_proof',
        'document_proof',
        're_registration_payment_proof',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    const DEFAULT_EDUCATION_LEVEL = 'sma_smk_ma';

    /** Pendidikan terakhir pendaftar: label field dan skala nilai per jenjang. */
    const EDUCATION_LEVELS = [
        'sma_smk_ma' => [
            'label'       => 'SMA/SMK/MA/Sederajat',
            'institution' => 'Nama Sekolah',
            'placeholder' => 'SMA/SMK/MA Negeri/Swasta ...',
            'major'       => 'Jurusan',
            'grade'       => 'Nilai Rata-rata Ijazah',
            'grade_max'   => 100,
        ],
        'd3' => [
            'label'       => 'D3',
            'institution' => 'Nama Perguruan Tinggi',
            'placeholder' => 'Nama universitas/politeknik',
            'major'       => 'Program Studi',
            'grade'       => 'IPK',
            'grade_max'   => 4,
        ],
        's1' => [
            'label'       => 'S1',
            'institution' => 'Nama Perguruan Tinggi',
            'placeholder' => 'Nama universitas/institut',
            'major'       => 'Program Studi',
            'grade'       => 'IPK',
            'grade_max'   => 4,
        ],
        'lainnya' => [
            'label'       => 'Lainnya',
            'institution' => 'Nama Sekolah / Lembaga',
            'placeholder' => 'Nama sekolah atau lembaga pendidikan',
            'major'       => 'Jurusan',
            'grade'       => 'Nilai Rata-rata',
            'grade_max'   => 100,
        ],
    ];

    /** Konfigurasi jenjang; nilai kosong/tak dikenal dianggap SMA/SMK/MA (data lama). */
    public static function educationConfig(?string $level): array
    {
        return self::EDUCATION_LEVELS[$level] ?? self::EDUCATION_LEVELS[self::DEFAULT_EDUCATION_LEVEL];
    }

    public function educationConfigForRegistration(): array
    {
        return self::educationConfig($this->education_level);
    }

    const STATUS_LABELS = [
        'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'color' => 'warning'],
        'menunggu_konfirmasi' => ['label' => 'Menunggu Konfirmasi', 'color' => 'info'],
        'terdaftar' => ['label' => 'Terdaftar', 'color' => 'info'],
        'menunggu_tes_tulis' => ['label' => 'Menunggu Tes Tulis', 'color' => 'warning'],
        'menunggu_interview' => ['label' => 'Menunggu Interview', 'color' => 'warning'],
        'diterima' => ['label' => 'Diterima', 'color' => 'success'],
        'ditolak' => ['label' => 'Ditolak', 'color' => 'error'],
        'menunggu_konfirmasi_daftar_ulang' => ['label' => 'Menunggu Konfirmasi Daftar Ulang', 'color' => 'info'],
        'daftar_ulang_selesai' => ['label' => 'Daftar Ulang Selesai', 'color' => 'primary'],
    ];

    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status]['label']
            ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $this->status));
    }

    public function getStatusColor(): string
    {
        return self::STATUS_LABELS[$this->status]['color'] ?? 'neutral';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }

    public function period()
    {
        return $this->belongsTo(PmbPeriod::class, 'period_id');
    }

    public function firstChoiceProgram()
    {
        return $this->belongsTo(Program::class, 'first_choice_program_id');
    }

    /** Batasi ke satu periode; null = semua periode (filter periode panel admin). */
    public function scopeForPeriod($query, ?int $periodId)
    {
        return $periodId ? $query->where('period_id', $periodId) : $query;
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    /** Pendaftaran tanpa kampus (data lama) dianggap milik kampus bawaan. */
    protected static function booted(): void
    {
        // Staf yang dibatasi ke satu kampus hanya melihat pendaftaran kampusnya.
        static::addGlobalScope(new \App\Models\Scopes\CampusScope());

        static::creating(function (self $registration) {
            $registration->campus_id ??= Campus::defaultId();
        });
    }

    /** Kampus pendaftaran ini; data lama tanpa kampus dianggap kampus bawaan. */
    public function resolvedCampus(): ?Campus
    {
        return $this->campus ?? Campus::default();
    }

    /** WhatsApp admin: milik kampus bila diisi, selain itu milik periode. */
    public function contactWhatsapp(): ?string
    {
        $campus = $this->resolvedCampus();

        return filled($campus?->admin_whatsapp) ? $campus->admin_whatsapp : $this->period?->admin_whatsapp;
    }

    /**
     * Rekening pembayaran: milik kampus bila nama bank dan nomor rekening diisi, selain itu
     * milik periode.
     *
     * @return array{name: ?string, account: ?string, account_name: ?string}
     */
    public function paymentBank(): array
    {
        $campus = $this->resolvedCampus();

        if ($campus?->hasBankDetails()) {
            return [
                'name'         => $campus->university_bank_name,
                'account'      => $campus->university_bank_account,
                'account_name' => $campus->university_bank_account_name,
            ];
        }

        return [
            'name'         => $this->period?->university_bank_name,
            'account'      => $this->period?->university_bank_account,
            'account_name' => $this->period?->university_bank_account_name,
        ];
    }

    /** Biaya, kuota, dan komisi prodi pilihan pada periode dan kampus pendaftaran ini (K6). */
    public function programTerms(): ?PeriodProgram
    {
        return $this->firstChoiceProgram?->termsFor($this->period_id, $this->campus_id);
    }

    public function secondChoiceProgram()
    {
        return $this->belongsTo(Program::class, 'second_choice_program_id');
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class)->orderBy('created_at', 'desc');
    }

    public function examSession()
    {
        return $this->hasOne(ExamSession::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function documents()
    {
        return $this->hasMany(RegistrationDocument::class);
    }

    public function payments()
    {
        return $this->hasMany(RegistrationPayment::class)->latest('id');
    }

    /** Total pembayaran daftar ulang yang sudah diverifikasi admin. */
    public function reRegistrationPaid(): int
    {
        return (int) $this->payments()->reRegistration()->verified()->sum('amount');
    }

    /** Sisa tagihan daftar ulang (0 bila tarif belum diatur atau sudah lunas). */
    public function reRegistrationRemaining(): int
    {
        $fee = (int) ($this->programTerms()?->re_registration_fee ?? 0);

        return max($fee - $this->reRegistrationPaid(), 0);
    }

    public function getTimelineStages()
    {
        $status = $this->status;
        $stages = [];

        // TAHAP 1 - Formulir Pendaftaran
        $stages[] = [
            'number' => 1,
            'label' => 'Formulir Pendaftaran',
            'state' => 'completed',
            'description' => 'Formulir telah berhasil dikirim.',
            'action_url' => route('registration.detail'),
            'action_label' => 'Lihat/Download Formulir'
        ];


        // TAHAP 2 - Pembayaran Registrasi
        $t2State = 'locked';
        if (!in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            $t2State = 'completed';
        } elseif (in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            $t2State = 'active';
        }
        $stages[] = [
            'number' => 2,
            'label' => 'Pembayaran Registrasi',
            'state' => $t2State,
            'description' => 'Lakukan pembayaran biaya pendaftaran.',
            'action_url' => null,
            'action_label' => null
        ];

        // TAHAP 3 - Pemberkasan Dokumen (opsional, tidak menahan tes tulis)
        $t3State = 'locked';
        $t3ActionUrl = null;
        $t3ActionLabel = null;

        if (in_array($status, ['terdaftar', 'menunggu_tes_tulis', 'menunggu_interview', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t3State = 'optional';

            if (!$this->isFormComplete()) {
                $t3ActionUrl = route('registration.detail');
                $t3ActionLabel = 'Lengkapi Formulir';
            } else {
                $t3ActionUrl = route('registration.documents');
                $t3ActionLabel = 'Upload Dokumen';
            }
        }

        $stages[] = [
            'number' => 3,
            'label' => 'Pemberkasan Dokumen (Opsional)',
            'state' => $t3State,
            'description' => 'Unggah dokumen pendaftaran kapan saja. Tidak wajib untuk lanjut ke tes tulis.',
            'action_url' => $t3ActionUrl,
            'action_label' => $t3ActionLabel,
        ];

        // TAHAP 4 - Tes Tulis Online
        $t4State = 'locked';
        $t4ActionUrl = null;
        $t4ActionLabel = null;
        $t4Description = 'Tunggu instruksi selanjutnya untuk mengikuti tes tulis online.';

        if ($this->examSession) {
            if ($this->canTakeExam()) {
                $t4State = 'active';
                $t4Description = 'Kerjakan tes tulis online sekarang. Tidak ada batas waktu, jawaban tersimpan otomatis.';
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Lanjutkan Ujian';
            } elseif ($this->examSession->status === 'completed') {
                $t4State = 'completed';
                $resultLabelStr = $this->examSession->result_label === 'sangat_baik' ? 'Sangat Baik' : 'Baik';
                $t4Description = "Tes tulis selesai. Hasil: {$resultLabelStr}";
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Lihat Hasil';
            }
        } else {
            if ($this->canTakeExam()) {
                $t4State = 'active';
                $t4Description = 'Kerjakan tes tulis online sekarang. Tidak ada batas waktu, jawaban tersimpan otomatis.';
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Mulai Ujian';
            } elseif (in_array($status, ['menunggu_interview', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
                $t4State = 'skipped';
                $t4Description = 'Tidak dikerjakan.';
            }
        }

        $stages[] = [
            'number' => 4,
            'label' => 'Tes Tulis Online',
            'state' => $t4State,
            'description' => $t4Description,
            'action_url' => $t4ActionUrl,
            'action_label' => $t4ActionLabel
        ];

        // TAHAP 5 - Interview
        $t5State = 'locked';
        $t5Description = 'Tahap wawancara dan verifikasi dokumen di kampus.';
        $t5ActionUrl = null;
        $t5ActionLabel = null;

        if ($this->canBeInterviewed()) {
            $t5State = 'active';
            $t5Description = 'Silakan datang ke ' . ($this->resolvedCampus()?->name ?? 'kampus') . ' untuk melakukan tahap interview. Bawa dokumen pendaftaran jika ada.';
            $t5ActionUrl = route('registration.interview');
            $t5ActionLabel = 'Lihat Instruksi';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t5State = 'completed';
            $t5Description = 'Tahap interview telah diselesaikan dengan baik.';
        } elseif ($status === 'ditolak') {
            $t5State = 'rejected';
            $t5Description = 'Mohon maaf, Anda dinyatakan tidak lolos pada tahap ini.';
        }

        $stages[] = [
            'number' => 5,
            'label' => 'Interview',
            'state' => $t5State,
            'description' => $t5Description,
            'action_url' => $t5ActionUrl,
            'action_label' => $t5ActionLabel
        ];

        // TAHAP 6 - Hasil Seleksi
        $t6State = 'locked';
        $t6Description = 'Pengumuman kelulusan calon mahasiswa.';
        $t6ActionUrl = null;
        $t6ActionLabel = null;
        if ($status === 'ditolak') {
            $t6State = 'rejected';
            $t6Description = 'Mohon maaf, Anda dinyatakan tidak lolos seleksi PMB Universitas YPIB Majalengka tahun ini.';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t6State = 'completed';
            $t6Description = 'Selamat! Anda dinyatakan <strong class="text-success-600">LULUS</strong> seleksi PMB Universitas YPIB Majalengka. Silakan lanjutkan ke tahap Daftar Ulang.';
            $t6ActionUrl = route('registration.skl');
            $t6ActionLabel = 'Download Surat Kelulusan';
        }
        $stages[] = [
            'number' => 6,
            'label' => 'Hasil Seleksi',
            'state' => $t6State,
            'description' => $t6Description,
            'action_url' => $t6ActionUrl,
            'action_label' => $t6ActionLabel
        ];

        // TAHAP 7 - Pembayaran Daftar Ulang
        $t7State = 'locked';
        $t7Description = 'Selesaikan pembayaran biaya daftar ulang.';
        $t7ActionUrl = null;
        $t7ActionLabel = null;

        if ($status === 'daftar_ulang_selesai') {
            $t7State = 'completed';
            $t7Description = 'Daftar ulang berhasil dikonfirmasi.';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang'])) {
            $t7State = 'active';
            
            if ($status === 'menunggu_konfirmasi_daftar_ulang' || $this->re_registration_payment_proof) {
                $t7Description = 'Bukti pembayaran sedang diverifikasi admin.';
            } else {
                $t7Description = 'Lengkapi pembayaran daftar ulang sesuai instruksi.';
                $t7ActionUrl = route('registration.re-registration');
                $t7ActionLabel = 'Bayar Daftar Ulang';
            }
        }
        
        $stages[] = [
            'number' => 7,
            'label' => 'Pembayaran Daftar Ulang',
            'state' => $t7State,
            'description' => $t7Description,
            'action_url' => $t7ActionUrl,
            'action_label' => $t7ActionLabel
        ];

        // TAHAP 8 - Selesai
        if ($status === 'daftar_ulang_selesai') {
            $stages[] = [
                'number' => 8,
                'label' => 'Selesai',
                'state' => 'completed',
                'description' => 'Selamat, Anda resmi menjadi mahasiswa YPIB Majalengka!',
                'action_url' => null,
                'action_label' => null
            ];
        }

        return $stages;
    }

    /**
     * Data diri/prodi dikunci setelah keputusan diambil (diterima/ditolak),
     * sepanjang tahap daftar ulang, atau bila NIM sudah terbit.
     */
    public function isDetailLocked(): bool
    {
        return !empty($this->nim)
            || in_array($this->status, [
                'diterima',
                'ditolak',
                'menunggu_konfirmasi_daftar_ulang',
                'daftar_ulang_selesai',
            ], true);
    }

    /** Setelah dapat nomor pendaftaran, tes tulis dan interview boleh dijalani tanpa dokumen. */
    public const EXAM_OPEN_STATUSES = ['terdaftar', 'menunggu_tes_tulis'];

    public const INTERVIEW_OPEN_STATUSES = ['terdaftar', 'menunggu_tes_tulis', 'menunggu_interview'];

    public function canTakeExam(): bool
    {
        return in_array($this->status, self::EXAM_OPEN_STATUSES, true);
    }

    public function canBeInterviewed(): bool
    {
        return in_array($this->status, self::INTERVIEW_OPEN_STATUSES, true);
    }

    public function isFormComplete(): bool
    {
        return !empty($this->religion)
            && !empty($this->school_major)
            && !empty($this->father_name)
            && !empty($this->mother_name)
            && !empty($this->father_occupation)
            && !empty($this->mother_occupation);
    }

    public function generateNim(): ?string
    {
        $program = $this->firstChoiceProgram;
        if (!$program || empty($program->kode_prodi)) {
            return null; // Fallback aman: skip generate, JANGAN error/exception
        }
        $year = $this->period->year ?? date('Y');
        $yearCode = substr((string) $year, -2);
        $trackCode = $program->registration_track === 'non_reguler' ? '2' : '1';
        $prefix = $yearCode . $program->kode_prodi . $trackCode;

        // Urutan dihitung per awalan, bukan global per tahun. withTrashed() + lockForUpdate():
        // NIM milik pendaftar yang dihapus tidak dipakai ulang, dan penerbitan bersamaan
        // pada awalan yang sama berurutan. Wajib dipanggil di dalam DB::transaction.
        $maxSequence = self::withoutGlobalScope(\App\Models\Scopes\CampusScope::class)->withTrashed()
            ->whereNotNull('nim')
            ->where('nim', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(nim, -3) AS UNSIGNED)) as max_seq')
            ->value('max_seq');

        // Lantai urutan: kolom periode; cadangan konstanta lama bila periode belum diatur.
        $periodStart = (int) ($this->period?->nim_sequence_start ?? 0);
        $start = $periodStart > 1 ? $periodStart : (self::YEAR_SEQUENCE_START[$yearCode] ?? 1);
        $nextSequence = max((int) ($maxSequence ?? 0) + 1, $start);

        if ($nextSequence > 999) {
            throw new \RuntimeException(
                "Urutan NIM untuk awalan {$prefix} sudah penuh (maksimum 999). Hubungi pengembang untuk menambah kapasitas."
            );
        }

        return $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @param  \Illuminate\Support\Carbon|null  $at  Tanggal acuan bulan/tahun nomor (default: sekarang).
     */
    public function generateLetterNumber(?\Illuminate\Support\Carbon $at = null): string
    {
        $now = $at ?? now();
        $romanMonths = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $roman = $romanMonths[(int) $now->format('n')];
        $year = $now->format('Y');

        // MAX(urutan) termasuk data soft-delete: count() bisa menerbitkan nomor ganda
        // setelah pendaftar dihapus. Wajib dipanggil di dalam DB::transaction
        // agar lockForUpdate efektif.
        $maxSequence = self::withoutGlobalScope(\App\Models\Scopes\CampusScope::class)->withTrashed()
            ->whereNotNull('letter_number')
            ->where('letter_number', 'like', '%/' . $year)
            ->lockForUpdate()
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(letter_number, '/', 1) AS UNSIGNED)) as max_seq")
            ->value('max_seq');
        $sequence = str_pad(((int) $maxSequence) + 1, 3, '0', STR_PAD_LEFT);

        return "{$sequence}/UNIV-YPIB/SKL/{$roman}/{$year}";
    }
}
