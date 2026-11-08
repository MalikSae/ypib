<?php

namespace App\Livewire;

use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Prospect;
use App\Models\ProspectLink;
use App\Models\ReferralClick;
use App\Models\Referrer;
use App\Models\Registration;
use App\Models\Scopes\CampusScope;
use App\Support\ProspectService;
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
    public string $nik         = '';
    public string $birth_place = '';
    public string $birth_date  = '';
    public string $gender      = '';
    public string $religion    = '';
    public string $address     = '';
    public string $phone       = '';

    // Step 2 — Program Studi & Jalur
    public $programs;
    public $campuses;
    public string $campus_id = '';
    public string $first_choice_program_id  = '';
    public string $admission_path = 'umum';

    // Step 3 — Pendidikan Terakhir
    public string $education_level    = Registration::DEFAULT_EDUCATION_LEVEL;
    public string $nisn               = '';
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

    // Link daftar dari form minat: nama dan WA tidak bisa diubah.
    #[Locked]
    public bool $prospectLocked = false;
    #[Locked]
    public string $prospectEventName = '';

    protected function rulesForStep(?int $step = null): array
    {
        return match ($step ?? $this->step) {
            1 => [
                'full_name'   => 'required|string|max:255',
                'nik'         => 'required|digits:16',
                'birth_place' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($this->getValidCities())],
                'birth_date'  => 'required|date',
                'gender'      => 'required|in:male,female',
                'religion'    => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
                'address'     => 'required|string',
                'phone'       => 'required|string|max:20',
            ],
            2 => [
                'campus_id' => [
                    'required',
                    \Illuminate\Validation\Rule::exists('campuses', 'id')->where('is_active', true),
                ],
                'first_choice_program_id' => [
                    'required',
                    \Illuminate\Validation\Rule::exists('programs', 'id')->where('is_active', true),
                    // Prodi harus ditawarkan pada periode berjalan di kampus yang dipilih (period_programs).
                    function (string $attribute, mixed $value, \Closure $fail) {
                        $period = PmbPeriod::displayPeriod();
                        if (!Program::offeredIn($period, (int) $this->campus_id)->whereKey((int) $value)->exists()) {
                            $fail('Program studi yang dipilih tidak ditawarkan pada periode ini.');
                        }
                    },
                ],
                'admission_path'          => 'required|in:umum,prestasi,tahfidz',
            ],
            3 => [
                'education_level'    => ['required', \Illuminate\Validation\Rule::in(array_keys(Registration::EDUCATION_LEVELS))],
                'nisn'               => 'nullable|string|digits:10',
                'school_name'        => 'required|string|max:255',
                'school_major'       => 'required|string|max:255',
                'graduation_year'    => 'required|digits:4',
                'certificate_number' => 'nullable|string|max:100',
                'school_grade'       => 'nullable|numeric|between:0,' . Registration::educationConfig($this->education_level)['grade_max'],
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
            'school_grade.between'            => 'Nilai harus antara 0 dan ' . Registration::educationConfig($this->education_level)['grade_max'] . '.',
            'education_level.required'        => 'Pendidikan terakhir wajib dipilih.',
            'education_level.in'              => 'Pilihan pendidikan terakhir tidak valid.',
            'birth_place.required'            => 'Tempat lahir wajib diisi.',
            'birth_place.in'                  => 'Tempat lahir tidak valid. Pilih dari daftar kota yang tersedia.',
            'birth_date.required'             => 'Tanggal lahir wajib diisi.',
            'gender.required'                 => 'Jenis kelamin wajib dipilih.',
            'address.required'                => 'Alamat wajib diisi.',
            'phone.required'                  => 'Nomor HP wajib diisi.',
            'campus_id.required'              => 'Pilih kampus terlebih dahulu.',
            'campus_id.exists'                => 'Kampus yang dipilih tidak tersedia.',
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
        $this->period = PmbPeriod::displayPeriod();

        // Hanya kampus aktif yang menawarkan minimal satu prodi pada periode ini.
        $this->campuses = Campus::active()->orderByDesc('is_default')->orderBy('name')->get()
            ->filter(fn (Campus $campus) => Program::offeredIn($this->period, $campus)->exists())
            ->values();

        // Link daftar dari form minat: kampus mengikuti event prospek.
        $prospectLink = ProspectService::sessionLink();

        // ?kampus=slug memilih kampus; satu-satunya kampus dipilih otomatis.
        $slug = \App\Support\QueryInput::string(request(), 'kampus');
        $chosen = $prospectLink
            ? $this->campuses->firstWhere('id', $prospectLink->event?->campus_id)
            : ($slug ? $this->campuses->firstWhere('slug', $slug) : null);
        $chosen ??= $this->campuses->count() === 1 ? $this->campuses->first() : null;

        // Prodi dari URL (?prodi=) tanpa kampus eksplisit: pakai kampus pertama yang menawarkannya.
        $prodiId = \App\Support\QueryInput::string(request(), 'prodi');
        if (!$chosen && $prodiId) {
            $chosen = $this->campuses->first(
                fn (Campus $campus) => Program::offeredIn($this->period, $campus)->whereKey((int) $prodiId)->exists()
            );
        }

        $this->campus_id = $chosen ? (string) $chosen->id : '';
        $this->loadPrograms();

        // Pre-fill phone from logged-in user
        if ($user = Auth::user()) {
            $this->phone = $user->phone ?? '';
        }

        // Link prospek: nama dan WA dikunci ke data form minat (ditegakkan ulang di submit()).
        if ($prospectLink) {
            $this->lockToProspect($prospectLink);
        }

        if ($referrer = $this->resolveReferrer()) {
            $this->referrer_code = $referrer->code;
            $this->referrer_id   = $referrer->id;
        }

        // Set pre-selected prodi from url
        if ($prodiId && $this->campus_id && $this->programs->contains('id', (int) $prodiId)) {
            $this->first_choice_program_id = (string) $prodiId;
        }
    }

    /** Daftar prodi mengikuti kampus terpilih; tanpa kampus daftar kosong. */
    protected function loadPrograms(): void
    {
        $campusId = $this->campus_id !== '' ? (int) $this->campus_id : null;

        $this->programs = $campusId
            ? Program::with('faculty')->offeredIn($this->period, $campusId)->get()
            : collect();

        if ($campusId) {
            Program::preloadTerms($this->programs, $this->period, $campusId);
        }
    }

    /** Ganti kampus: daftar prodi dimuat ulang dan pilihan prodi lama dibuang. */
    public function updatedCampusId(): void
    {
        $this->first_choice_program_id = '';
        $this->resetErrorBag(['campus_id', 'first_choice_program_id']);
        $this->loadPrograms();
    }

    /** "Bukan kamu?": lepas link form minat dari sesi, lalu muat ulang form tanpa data terkunci. */
    public function releaseProspect(): void
    {
        session()->forget(ProspectService::SESSION_KEY);
        session()->flash('success', 'Data form minat tidak dipakai. Silakan isi data kamu sendiri.');

        $this->redirect(route('registration.create'));
    }

    protected function lockToProspect(ProspectLink $link): void
    {
        $prospect = $link->prospect;

        $this->full_name = $prospect->full_name;
        $this->phone = $prospect->phone_normalized;
        $this->prospectLocked = true;

        // Pendidikan dan sekolah dari form minat dipakai sebagai isian awal (tetap bisa diubah).
        if ($prospect->education_level) {
            $this->education_level = $prospect->education_level;
        }
        if ($prospect->school_name) {
            $this->school_name = $prospect->school_name;
        }
        $this->prospectEventName = $link->event?->name ?? '';

        if ($prospect->program_id && $this->campus_id !== '') {
            $offered = Program::offeredIn($this->period, (int) $this->campus_id)->whereKey($prospect->program_id)->exists();
            if ($offered) {
                $this->first_choice_program_id = (string) $prospect->program_id;
            }
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
        $period = PmbPeriod::displayPeriod();
        $campusId = (int) $this->campus_id;
        $program = Program::offeredIn($period, $campusId)->find((int) $this->first_choice_program_id);

        if ($program && $program->isFull($period?->id, $campusId)) {
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

        // Reload periode dari DB (hindari masalah serialisasi Livewire model).
        // Pendaftaran baru hanya masuk ke periode berjalan.
        $period = PmbPeriod::current();

        if (!$period || !$period->isOpen()) {
            session()->flash('error', 'Pendaftaran belum dibuka atau sudah ditutup.');
            return;
        }

        // Link prospek: nama, WA, dan kampus dipaksa dari data prospek, bukan dari input klien.
        $prospectLink = ProspectService::sessionLink();
        if ($prospectLink) {
            $this->full_name = $prospectLink->prospect->full_name;
            $this->phone = $prospectLink->prospect->phone_normalized;
            $this->campus_id = (string) $prospectLink->event->campus_id;
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

        // Satu proses aktif per orang (K7). Dicek di sini karena submit() sudah wajib login,
        // dengan pesan generik; langkah-langkah form (terbuka untuk tamu) tidak mengecek NIK.
        if (\App\Support\NikGuard::blocks($this->nik, (int) $this->first_choice_program_id)) {
            $this->step = 1;
            $this->addError('nik', \App\Support\NikGuard::message($period));

            return;
        }

        try {
            // Kunci baris prodi agar dua pendaftar bersamaan tidak sama-sama merebut kursi terakhir.
            \Illuminate\Support\Facades\DB::beginTransaction();
            $lockedProgram = Program::whereKey((int) $this->first_choice_program_id)->lockForUpdate()->first();
            if ($lockedProgram && $lockedProgram->isFull($period->id, (int) $this->campus_id)) {
                \Illuminate\Support\Facades\DB::rollBack();
                $this->step = 2;
                $this->addError('first_choice_program_id', 'Kuota program studi ini sudah penuh. Silakan pilih program studi lain.');

                return;
            }

            // Link sekali pakai: kunci baris link agar tidak dipakai dua kali bersamaan.
            $lockedLink = null;
            if ($prospectLink) {
                $lockedLink = ProspectLink::whereKey($prospectLink->id)->lockForUpdate()->first();
                if (!$lockedLink || !$lockedLink->isUsable()) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    session()->forget(ProspectService::SESSION_KEY);
                    session()->flash('error', 'Link pendaftaran ini sudah dipakai atau tidak berlaku.');

                    return;
                }
            }

            // Prospek terkait: dari link terkunci, atau nomor WA yang cocok dengan prospek yang
            // belum mendaftar (prospek yang sudah terhubung ke registrasi lain tidak diambil alih).
            $prospect = $lockedLink
                ? $prospectLink->prospect
                : Prospect::withoutGlobalScope(CampusScope::class)
                    ->where('phone_normalized', Prospect::normalizePhone($this->phone))
                    ->where('status', '!=', Prospect::STATUS_SUDAH_DAFTAR)
                    ->lockForUpdate()
                    ->first();

            // Lewat link: referrer selalu pemilik prospek, cookie diabaikan (prospek event kampus
            // tanpa pemilik = tanpa referrer). Tanpa link: pemilik prospek mengalahkan cookie.
            $referrerId = $lockedLink
                ? $prospect->referrer_id
                : ($prospect?->referrer_id ?? $this->resolveReferrer()?->id);

            // Potongan daftar ulang hanya dari link form minat, memakai snapshot saat link dibuat.
            $discount = $lockedLink ? (int) $lockedLink->discount_amount : 0;

            $registration = Registration::create([
                'period_id'                => $period->id,
                'campus_id'                => (int) $this->campus_id,
                'user_id'                  => $user->id,
                'referrer_id'              => $referrerId,
                'prospect_id'              => $prospect?->id,
                'event_discount_amount'    => $discount,
                'registration_number'      => null, // digenerate admin saat konfirmasi bayar
                'admission_path'           => $this->admission_path,
                'first_choice_program_id'  => (int) $this->first_choice_program_id,
                'second_choice_program_id' => null, // removed
                'full_name'                => $this->full_name,
                'nisn'                     => $this->nisn ?: null,
                'education_level'          => $this->education_level,
                'nik'                     => $this->nik,
                'birth_place'              => $this->birth_place,
                'birth_date'               => $this->birth_date,
                'gender'                   => $this->gender,
                'religion'                 => $this->religion,
                'address'                  => $this->address,
                'phone'                    => $this->phone,
                'school_name'              => $this->school_name,
                'school_major'             => $this->school_major,
                'graduation_year'          => (int) $this->graduation_year,
                'certificate_number'       => $this->certificate_number ?: null,
                'school_grade'             => $this->school_grade ?: null,
                'father_name'              => $this->father_name,
                'mother_name'              => $this->mother_name,
                'father_occupation'        => $this->father_occupation,
                'mother_occupation'        => $this->mother_occupation,
                'status'                   => 'menunggu_pembayaran',
            ]);

            if ($prospect) {
                $prospect->forceFill([
                    'status'          => Prospect::STATUS_SUDAH_DAFTAR,
                    'registration_id' => $registration->id,
                ])->save();
            }
            if ($lockedLink) {
                $lockedLink->forceFill(['used_at' => now()])->save();
                session()->forget(ProspectService::SESSION_KEY);
            }

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
