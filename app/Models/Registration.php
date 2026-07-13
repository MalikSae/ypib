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
        return $this->hasMany(PaymentLog::class);
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
            'action_label' => 'Lihat/Edit Formulir'
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
        if (in_array($status, ['menunggu_tes_tulis', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t3State = 'completed';
        } elseif (in_array($status, ['terdaftar', 'menunggu_review_berkas', 'perlu_revisi_berkas'])) {
            $t3State = 'active';
            $t3ActionUrl = route('registration.documents');
        } elseif (in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            $t3State = 'locked';
        }
        $stages[] = [
            'number' => 3,
            'label' => 'Pemberkasan Dokumen',
            'state' => $t3State,
            'description' => 'Lengkapi dan unggah dokumen pendaftaran Anda.',
            'action_url' => $t3ActionUrl,
            'action_label' => 'Buka Halaman Dokumen'
        ];

        // TAHAP 4 - Tes Tulis Online
        $t4State = 'locked';
        if ($status === 'menunggu_tes_tulis') {
            $t4State = 'active';
        }
        $stages[] = [
            'number' => 4,
            'label' => 'Tes Tulis Online',
            'state' => $t4State,
            'description' => 'Fitur Tes Tulis Online sedang dalam persiapan. Admin akan menghubungi Anda melalui WhatsApp/Email untuk instruksi selanjutnya.',
            'action_url' => null,
            'action_label' => null
        ];

        // TAHAP 5 - Interview
        $stages[] = [
            'number' => 5,
            'label' => 'Interview',
            'state' => 'locked',
            'description' => 'Tahap wawancara bagi pendaftar yang lolos tes tulis.',
            'action_url' => null,
            'action_label' => null
        ];

        // TAHAP 6 - Hasil Seleksi
        $t6State = 'locked';
        if ($status === 'ditolak') {
            $t6State = 'rejected';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            $t6State = 'completed';
        }
        $stages[] = [
            'number' => 6,
            'label' => 'Hasil Seleksi',
            'state' => $t6State,
            'description' => 'Pengumuman kelulusan calon mahasiswa.',
            'action_url' => null,
            'action_label' => null
        ];

        // TAHAP 7 - Pembayaran Daftar Ulang
        $t7State = 'locked';
        if ($status === 'daftar_ulang_selesai') {
            $t7State = 'completed';
        } elseif (in_array($status, ['diterima', 'menunggu_konfirmasi_daftar_ulang'])) {
            $t7State = 'active';
        }
        $stages[] = [
            'number' => 7,
            'label' => 'Pembayaran Daftar Ulang',
            'state' => $t7State,
            'description' => 'Selesaikan pembayaran biaya daftar ulang.',
            'action_url' => null,
            'action_label' => null
        ];

        // TAHAP 8 - Selesai
        $t8State = 'locked';
        if ($status === 'daftar_ulang_selesai') {
            $t8State = 'completed';
        }
        $stages[] = [
            'number' => 8,
            'label' => 'Selesai',
            'state' => $t8State,
            'description' => 'Selamat, Anda resmi menjadi mahasiswa YPIB Majalengka!',
            'action_url' => null,
            'action_label' => null
        ];

        return $stages;
    }
}
