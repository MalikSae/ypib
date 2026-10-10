<?php

namespace App\Livewire;

use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\ReferralClick;
use App\Models\Referrer;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RegistrationForm extends Component
{
    public int $step = 1;
    public $period;

    // Livewire 3 membutuhkan $rules saat menggunakan wire:submit
    // Validasi aktual dihandle manual via rulesForStep()
    public $rules = [];

    public array $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'];

    // Step 1 — Data Diri
    public string $full_name   = '';
    public string $nisn        = '';
    public string $nik         = '';
    public string $birth_place = '';
    public string $birth_date  = '';
    public string $gender      = '';
    public string $religion    = '';
    public string $address     = '';
    public string $phone       = '';

    // Step 2 — Program Studi & Jalur
    public $programs;
    public string $first_choice_program_id  = '';
    public string $admission_path = '';

    // Step 3 — Data Sekolah
    public string $school_name        = '';
    public string $school_major       = '';
    public string $graduation_year    = '';
    public string $certificate_number = '';
    public string $school_grade       = '';

    // Step 4 — Data Orang Tua/Wali
    public string $father_name       = '';
    public string $mother_name       = '';
    public string $father_occupation = '';
    public string $mother_occupation = '';

    // Referral
    // Locked: tidak boleh diubah dari sisi klien. Nilai final di-resolve ulang
    // di server pada submit() lewat resolveReferrer().
    #[Locked]
    public ?int $referrer_id = null;
    #[Locked]
    public string $referrer_code = '';

    protected function rulesForStep(?int $step = null): array
    {
        return match ($step ?? $this->step) {
            1 => [
                'full_name'   => 'required|string|max:255',
                'nisn'        => 'required|string|digits:10',
                'nik'         => 'required|digits:16',
                'birth_place' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($this->getValidCities())],
                'birth_date'  => 'required|date',
                'gender'      => 'required|in:male,female',
                'religion'    => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
                'address'     => 'required|string',
                'phone'       => 'required|string|max:20',
            ],
            2 => [
                'first_choice_program_id' => ['required', \Illuminate\Validation\Rule::exists('programs', 'id')->where('is_active', true)],
                'admission_path'          => 'required|in:umum,prestasi,tahfidz',
            ],
            3 => [
                'school_name'        => 'required|string|max:255',
                'school_major'       => 'required|string|max:255',
                'graduation_year'    => 'required|digits:4',
                'certificate_number' => 'required|string|max:100',
                'school_grade'       => 'nullable|numeric|between:0,100',
            ],
            4 => [
                'father_name'       => 'required|string|max:255',
                'mother_name'       => 'required|string|max:255',
                'father_occupation' => 'required|string|max:255',
                'mother_occupation' => 'required|string|max:255',
            ],
            default => [],
        };
    }

    protected function messagesForStep(): array
    {
        return [
            'full_name.required'              => 'Nama lengkap wajib diisi.',
            'nik.required'                    => 'NIK wajib diisi.',
            'nik.size'                        => 'NIK harus 16 digit.',
            'nik.digits'                      => 'NIK harus 16 digit angka.',
            'school_grade.numeric'            => 'Nilai rata-rata harus berupa angka.',
            'school_grade.between'            => 'Nilai rata-rata harus antara 0 dan 100.',
            'birth_place.required'            => 'Tempat lahir wajib diisi.',
            'birth_place.in'                  => 'Tempat lahir tidak valid. Pilih dari daftar kota yang tersedia.',
            'birth_date.required'             => 'Tanggal lahir wajib diisi.',
            'gender.required'                 => 'Jenis kelamin wajib dipilih.',
            'address.required'                => 'Alamat wajib diisi.',
            'phone.required'                  => 'Nomor HP wajib diisi.',
            'first_choice_program_id.required'=> 'Pilih pilihan prodi pertama.',
            'first_choice_program_id.exists'  => 'Program studi yang dipilih tidak tersedia.',
            'admission_path.required'         => 'Silakan pilih jalur pendaftaran.',
            'school_name.required'            => 'Nama sekolah wajib diisi.',
            'graduation_year.required'        => 'Tahun lulus wajib diisi.',
            'graduation_year.digits'          => 'Tahun lulus harus 4 digit.',
            'nisn.required'                   => 'NISN wajib diisi.',
            'nisn.digits'                     => 'NISN harus 10 digit.',
            'religion.required'               => 'Agama wajib dipilih.',
            'religion.in'                     => 'Pilihan agama tidak valid.',
            'school_major.required'           => 'Jurusan sekolah wajib diisi.',
            'certificate_number.required'     => 'Nomor Ijazah/SKL wajib diisi.',
            'father_name.required'            => 'Nama Ayah wajib diisi.',
            'mother_name.required'            => 'Nama Ibu wajib diisi.',
            'father_occupation.required'      => 'Pekerjaan Ayah wajib diisi.',
            'mother_occupation.required'      => 'Pekerjaan Ibu wajib diisi.',
        ];
    }

    public function getValidCities(): array
    {
        $path = base_path('public/data/cities.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }
        return ["Cirebon","Majalengka","Indramayu","Kuningan","Bandung","Jakarta","Surabaya","Semarang","Yogyakarta","Bekasi","Tangerang","Bogor","Depok"];
    }

    public function setBirthPlace(string $value): void
    {
        $this->birth_place = trim($value);
    }

    public function setBirthDate(string $value): void
    {
        $this->birth_date = trim($value);
    }

    public function mount(): void
    {
        $this->period   = PmbPeriod::active()->first();
        $this->programs = Program::with('faculty')->active()->get();

        // Pre-fill phone from logged-in user
        if ($user = Auth::user()) {
            $this->phone = $user->phone ?? '';
        }

        if ($referrer = $this->resolveReferrer()) {
            $this->referrer_code = $referrer->code;
            $this->referrer_id   = $referrer->id;
        }

        // Set pre-selected prodi from url
        $prodiId = request()->query('prodi');
        if ($prodiId && Program::active()->where('id', $prodiId)->exists()) {
            $this->first_choice_program_id = $prodiId;
        }
    }

    /**
     * Tentukan referrer dari sumber tepercaya di server: akun user (terkunci)
     * lebih dulu, lalu cookie 'ref' (hanya referrer aktif).
     */
    protected function resolveReferrer(): ?Referrer
    {
        $user = Auth::user();

        if ($user && $user->referrer_id) {
            return Referrer::find($user->referrer_id);
        }

        $refCode = Cookie::get('ref', '');
        if ($refCode) {
            return Referrer::where('code', $refCode)->where('status', 'active')->first();
        }

        return null;
    }

    /** Prodi pilihan masih punya kursi pada periode aktif? (quota 0 = tanpa batas) */
    protected function programHasSeat(): bool
    {
        $program = Program::active()->find((int) $this->first_choice_program_id);
        $period = PmbPeriod::active()->first();

        if ($program && $program->isFull($period?->id)) {
            $this->addError('first_choice_program_id', 'Kuota program studi ini sudah penuh. Silakan pilih program studi lain.');

            return false;
        }

        return true;
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep(), $this->messagesForStep());

        if ($this->step === 2 && !$this->programHasSeat()) {
            return;
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect(route('login'));
            return;
        }

        // Reload periode dari DB (hindari masalah serialisasi Livewire model)
        $period = PmbPeriod::active()->first();

        if (!$period || !$period->isOpen()) {
            session()->flash('error', 'Pendaftaran belum dibuka atau sudah ditutup.');
            return;
        }

        // Cek user belum punya registrasi di periode ini
        $existing = Registration::where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->first();

        if ($existing) {
            session()->flash('error', 'Anda sudah memiliki pendaftaran di periode ini.');
            $this->redirect(route('registration.status'));
            return;
        }

        // Validasi ulang semua langkah di server: state Livewire berasal dari klien
        // dan submit() bisa dipanggil tanpa melewati nextStep().
        foreach ([1, 2, 3, 4] as $step) {
            try {
                $this->validate($this->rulesForStep($step), $this->messagesForStep());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->step = $step;
                throw $e;
            }
        }

        try {
            // Kunci baris prodi agar dua pendaftar bersamaan tidak sama-sama merebut kursi terakhir.
            \Illuminate\Support\Facades\DB::beginTransaction();
            $lockedProgram = Program::whereKey((int) $this->first_choice_program_id)->lockForUpdate()->first();
            if ($lockedProgram && $lockedProgram->isFull($period->id)) {
                \Illuminate\Support\Facades\DB::rollBack();
                $this->step = 2;
                $this->addError('first_choice_program_id', 'Kuota program studi ini sudah penuh. Silakan pilih program studi lain.');

                return;
            }

            Registration::create([
                'period_id'                => $period->id,
                'user_id'                  => $user->id,
                'referrer_id'              => $this->resolveReferrer()?->id,
                'registration_number'      => null, // digenerate admin saat konfirmasi bayar
                'admission_path'           => $this->admission_path,
                'first_choice_program_id'  => (int) $this->first_choice_program_id,
                'second_choice_program_id' => null, // removed
                'full_name'                => $this->full_name,
                'nisn'                     => $this->nisn,
                'nik'                      => $this->nik,
                'birth_place'              => $this->birth_place,
                'birth_date'               => $this->birth_date,
                'gender'                   => $this->gender,
                'religion'                 => $this->religion,
                'address'                  => $this->address,
                'phone'                    => $this->phone,
                'school_name'              => $this->school_name,
                'school_major'             => $this->school_major,
                'graduation_year'          => (int) $this->graduation_year,
                'certificate_number'       => $this->certificate_number,
                'school_grade'             => $this->school_grade ?: null,
                'father_name'              => $this->father_name,
                'mother_name'              => $this->mother_name,
                'father_occupation'        => $this->father_occupation,
                'mother_occupation'        => $this->mother_occupation,
                'status'                   => 'menunggu_pembayaran',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            session()->flash('success', 'Formulir berhasil dikirim! Silakan lakukan pembayaran sesuai instruksi berikut.');
            $this->redirect(route('registration.status'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            report($e);
            session()->flash('error', 'Gagal menyimpan pendaftaran. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function render()
    {
        return view('livewire.registration-form');
    }
}
