<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'period_id',
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
            'action_label' => 'Lihat Formulir'
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

        // TAHAP 3 - Pemberkasan Dokumen
        $t3State = 'locked';
        $t3ActionUrl = null;
        $t3ActionLabel = null;
        $t3RequiresForm = false;

        if (in_array($status, ['menunggu_tes_tulis', 'menunggu_interview', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t3State = 'completed';
        } elseif ($status === 'terdaftar') {
            $t3State = 'active';
            if (!$this->isFormComplete()) {
                $t3ActionUrl = route('registration.detail');
                $t3ActionLabel = 'Lengkapi Formulir';
                $t3RequiresForm = true;
            } else {
                $t3ActionUrl = route('registration.documents');
                $t3ActionLabel = 'Buka Dokumen';
            }

        } elseif (in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            $t3State = 'locked';
        }
        
        $stages[] = [
            'number' => 3,
            'label' => 'Pemberkasan Dokumen',
            'state' => $t3State,
            'description' => 'Lengkapi dan unggah dokumen pendaftaran Anda.',
            'action_url' => $t3ActionUrl,
            'action_label' => $t3ActionLabel,
            'requires_form_completion' => $t3RequiresForm,
        ];

        // TAHAP 4 - Tes Tulis Online
        $t4State = 'locked';
        $t4ActionUrl = null;
        $t4ActionLabel = null;
        $t4Description = 'Tunggu instruksi selanjutnya untuk mengikuti tes tulis online.';

        if ($this->examSession) {
            if ($status === 'menunggu_tes_tulis') {
                $t4State = 'active';
                $t4Description = 'Kerjakan tes tulis online sekarang. Tidak ada batas waktu, jawaban tersimpan otomatis.';
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Lanjutkan Ujian';
            } elseif ($this->examSession->status === 'completed' && $status !== 'menunggu_tes_tulis') {
                $t4State = 'completed';
                $resultLabelStr = $this->examSession->result_label === 'sangat_baik' ? 'Sangat Baik' : 'Baik';
                $t4Description = "Tes tulis selesai. Hasil: {$resultLabelStr}";
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Lihat Hasil';
            }
        } else {
            if ($status === 'menunggu_tes_tulis') {
                $t4State = 'active';
                $t4Description = 'Kerjakan tes tulis online sekarang. Tidak ada batas waktu, jawaban tersimpan otomatis.';
                $t4ActionUrl = route('registration.exam');
                $t4ActionLabel = 'Mulai Ujian';
            } elseif (in_array($status, ['menunggu_interview', 'diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
                $t4State = 'completed';
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
        $t5Description = 'Tahap wawancara bagi pendaftar yang lolos tes tulis.';
        $t5ActionUrl = null;
        $t5ActionLabel = null;

        if ($status === 'menunggu_interview') {
            $t5State = 'active';
            $t5Description = 'Silakan datang ke kampus untuk melakukan tahap interview dan verifikasi dokumen.';
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
        if ($status === 'ditolak') {
            $t6State = 'rejected';
            $t6Description = 'Mohon maaf, Anda dinyatakan tidak lolos seleksi PMB Universitas YPIB Majalengka tahun ini.';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t6State = 'completed';
            $t6Description = 'Selamat! Anda dinyatakan <strong class="text-success-600">LULUS</strong> seleksi PMB Universitas YPIB Majalengka. Silakan lanjutkan ke tahap Daftar Ulang.';
        }
        $stages[] = [
            'number' => 6,
            'label' => 'Hasil Seleksi',
            'state' => $t6State,
            'description' => $t6Description,
            'action_url' => null,
            'action_label' => null
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

    public function isFormComplete(): bool
    {
        return !empty($this->nisn)
            && !empty($this->religion)
            && !empty($this->school_major)
            && !empty($this->certificate_number)
            && !empty($this->father_name)
            && !empty($this->mother_name)
            && !empty($this->father_occupation)
            && !empty($this->mother_occupation);
    }
}
