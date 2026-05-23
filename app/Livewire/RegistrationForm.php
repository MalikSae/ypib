<?php

namespace App\Livewire;

use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\ReferralClick;
use App\Models\Referrer;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class RegistrationForm extends Component
{
    public int $step = 1;
    public $period;

    // Livewire 3 membutuhkan $rules saat menggunakan wire:submit
    // Validasi aktual dihandle manual via rulesForStep()
    public $rules = [];

    // Step 1 — Data Diri
    public string $full_name   = '';
    public string $nik         = '';
    public string $birth_place = '';
    public string $birth_date         = '';
    public string $gender      = '';
    public string $address     = '';
    public string $phone       = '';

    // Step 2 — Pilih Prodi
    public $programs;
    public string $first_choice_program_id  = '';

    // Step 3 — Asal Sekolah
    public string $school_name      = '';
    public string $graduation_year  = '';
    public string $school_grade     = '';

    // Referral
    public ?int $referrer_id = null;
    public string $referrer_code = '';

    protected function rulesForStep(): array
    {
        return match ($this->step) {
            1 => [
                'full_name'   => 'required|string|max:255',
                'nik'         => 'required|string|size:16',
                'birth_place' => 'required|string|max:100',
                'birth_date'  => 'required|date',
                'gender'      => 'required|in:male,female',
                'address'     => 'required|string',
                'phone'       => 'required|string|max:20',
                'first_choice_program_id' => 'required|exists:programs,id',
            ],
            2 => [
                'school_name'     => 'required|string|max:255',
                'graduation_year' => 'required|digits:4',
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
            'birth_place.required'            => 'Tempat lahir wajib diisi.',
            'birth_date.required'             => 'Tanggal lahir wajib diisi.',
            'gender.required'                 => 'Jenis kelamin wajib dipilih.',
            'address.required'                => 'Alamat wajib diisi.',
            'phone.required'                  => 'Nomor HP wajib diisi.',
            'first_choice_program_id.required'=> 'Pilih pilihan prodi pertama.',
            'school_name.required'            => 'Nama sekolah wajib diisi.',
            'graduation_year.required'        => 'Tahun lulus wajib diisi.',
            'graduation_year.digits'          => 'Tahun lulus harus 4 digit.',
        ];
    }

    public function mount(): void
    {
        $this->period   = PmbPeriod::active()->first();
        $this->programs = Program::with('faculty')->active()->get();

        // Pre-fill phone from logged-in user
        if ($user = Auth::user()) {
            $this->phone = $user->phone ?? '';
        }

        // Check referral cookie & resolve referrer_id
        $refCode = Cookie::get('ref', '');
        if ($refCode) {
            $referrer = Referrer::where('code', $refCode)->where('status', 'active')->first();
            if ($referrer) {
                $this->referrer_code = $refCode;
                $this->referrer_id   = $referrer->id;
            }
        }

        // Set pre-selected prodi from url
        $prodiId = request()->query('prodi');
        if ($prodiId && Program::where('id', $prodiId)->exists()) {
            $this->first_choice_program_id = $prodiId;
        }
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep(), $this->messagesForStep());
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
        // Step 3 (Konfirmasi) tidak punya validation rules khusus — langsung submit
        $user = Auth::user();

        // Reload periode dari DB (hindari masalah serialisasi Livewire model)
        $period = PmbPeriod::active()->first();

        if (!$period) {
            session()->flash('error', 'Tidak ada periode pendaftaran aktif saat ini.');
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


        try {
            Registration::create([
                'period_id'                => $period->id,
                'user_id'                  => $user->id,
                'referrer_id'              => $this->referrer_id,
                'registration_number'      => null, // digenerate admin saat konfirmasi bayar
                'first_choice_program_id'  => (int) $this->first_choice_program_id,
                'second_choice_program_id' => null, // removed
                'full_name'                => $this->full_name,
                'nik'                      => $this->nik,
                'birth_place'              => $this->birth_place,
                'birth_date'               => $this->birth_date,
                'gender'                   => $this->gender,
                'address'                  => $this->address,
                'phone'                    => $this->phone,
                'school_name'              => $this->school_name,
                'graduation_year'          => (int) $this->graduation_year,
                'school_grade'             => $this->school_grade ?: null,
                'status'                   => 'menunggu_pembayaran',
            ]);

            session()->flash('success', 'Formulir berhasil dikirim! Silakan lakukan pembayaran sesuai instruksi berikut.');
            $this->redirect(route('registration.status'));

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan pendaftaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registration-form');
    }
}
