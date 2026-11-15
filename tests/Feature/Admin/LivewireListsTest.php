<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ExamQuestionList;
use App\Livewire\Admin\ProgramList;
use App\Livewire\Admin\RegistrationList;
use App\Livewire\Admin\RegistrationTrashList;
use App\Livewire\Admin\RewardDetailList;
use App\Livewire\Admin\StaffList;
use App\Livewire\Panitia\InterviewHistory;
use App\Models\Campus;
use App\Models\ExamQuestion;
use App\Models\Faculty;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Halaman daftar admin dan panitia yang filternya memakai Livewire (tanpa tombol Terapkan/Cari). */
class LivewireListsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeRegistration(string $name, string $status, Program $program, PmbPeriod $period, string $nik): Registration
    {
        return Registration::create([
            'period_id' => $period->id, 'user_id' => User::factory()->create()->id,
            'first_choice_program_id' => $program->id, 'full_name' => $name, 'nik' => $nik,
            'birth_place' => 'Majalengka', 'birth_date' => '2008-01-01', 'gender' => 'male',
            'address' => 'Jl. Uji', 'phone' => '081200000000', 'school_name' => 'SMA Uji', 'graduation_year' => 2026,
            'status' => $status,
        ]);
    }

    public function test_pages_render_livewire_components(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.staff.index'))->assertOk()->assertSeeLivewire(StaffList::class);
        $this->get(route('admin.programs.index'))->assertOk()->assertSeeLivewire(ProgramList::class);
        $this->get(route('admin.bank-soal.index'))->assertOk()->assertSeeLivewire(ExamQuestionList::class);
        $this->get(route('admin.registrations.trash'))->assertOk()->assertSeeLivewire(RegistrationTrashList::class);
        $this->get(route('admin.rewards.index'))->assertOk()->assertSeeLivewire(RewardDetailList::class);
        $this->get(route('admin.registrations.index'))->assertOk()->assertSeeLivewire(RegistrationList::class)
            ->assertDontSee('>Cari</button>', false)
            ->assertSee('id="filter_status"', false);
    }

    public function test_staff_list_filters_live(): void
    {
        User::factory()->create(['role' => 'operator', 'name' => 'Operator Satu']);
        User::factory()->create(['role' => 'panitia', 'name' => 'Panitia Dua']);

        Livewire::actingAs($this->admin)->test(StaffList::class)
            ->set('role', 'panitia')
            ->assertSee('Panitia Dua')->assertDontSee('Operator Satu')
            ->call('clearFilters')
            ->set('search', 'operator')
            ->assertSee('Operator Satu')->assertDontSee('Panitia Dua');
    }

    public function test_exam_question_search_is_live(): void
    {
        ExamQuestion::create(['question_text' => 'Ibukota Jawa Barat adalah', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_option' => 'a', 'is_active' => true]);
        ExamQuestion::create(['question_text' => 'Hasil dua tambah dua', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_option' => 'b', 'is_active' => true]);

        Livewire::actingAs($this->admin)->test(ExamQuestionList::class)
            ->set('search', 'Ibukota')
            ->assertSee('Ibukota Jawa Barat')->assertDontSee('dua tambah dua');
    }

    public function test_registration_list_filters_by_search_status_and_program(): void
    {
        $period = PmbPeriod::create([
            'name' => 'PMB Uji', 'year' => 2027, 'open_date' => now()->subDay()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(), 'is_active' => true, 'status' => PmbPeriod::STATUS_PUBLISHED,
        ]);
        $faculty = Faculty::create(['name' => 'Fakultas Uji', 'slug' => 'fakultas-uji']);
        $nursing = Program::create(['name' => 'S1 Keperawatan', 'slug' => 's1-kep', 'kode_prodi' => '14201', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id, 'is_active' => true]);
        $midwife = Program::create(['name' => 'D3 Kebidanan', 'slug' => 'd3-keb', 'kode_prodi' => '15401', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id, 'is_active' => true]);

        $this->makeRegistration('Siti Aminah', 'terdaftar', $nursing, $period, '3210000000000001');
        $this->makeRegistration('Budi Santoso', 'menunggu_pembayaran', $midwife, $period, '3210000000000002');

        Livewire::actingAs($this->admin)->test(RegistrationList::class)
            ->assertSee('Siti Aminah')->assertSee('Budi Santoso')
            ->set('search', 'siti')
            ->assertSee('Siti Aminah')->assertDontSee('Budi Santoso')
            ->call('clearFilters')
            ->set('status', 'menunggu_pembayaran')
            ->assertSee('Budi Santoso')->assertDontSee('Siti Aminah')
            // Hapus filter ikut mengosongkan status (status sekarang dropdown, bukan tab).
            ->call('clearFilters')
            ->assertSet('status', '')
            ->set('prodi', (string) $nursing->id)
            ->assertSee('Siti Aminah')->assertDontSee('Budi Santoso')
            // Status yang tidak dikenal diabaikan.
            ->set('prodi', '')
            ->set('status', 'status-palsu')
            ->assertSee('Siti Aminah')->assertSee('Budi Santoso');
    }

    public function test_panitia_dashboard_renders_history_component(): void
    {
        $panitia = User::factory()->create(['role' => 'panitia']);

        $this->actingAs($panitia)->get(route('panitia.dashboard'))
            ->assertOk()
            ->assertSeeLivewire(InterviewHistory::class)
            ->assertSee('Anda belum memproses interview apapun.');

        Livewire::actingAs($panitia)->test(InterviewHistory::class)
            ->set('search', 'tidak ada')
            ->assertSee('Tidak ditemukan riwayat yang cocok');
    }

    public function test_access_is_checked_inside_components(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $campusAdmin = User::factory()->create(['role' => 'admin', 'campus_id' => Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka', 'is_active' => true,
        ])->id]);

        foreach ([StaffList::class, ProgramList::class, ExamQuestionList::class, RewardDetailList::class] as $component) {
            Livewire::actingAs($campusAdmin)->test($component)->assertForbidden();
        }
        foreach ([RegistrationList::class, RegistrationTrashList::class, InterviewHistory::class] as $component) {
            Livewire::actingAs($student)->test($component)->assertForbidden();
        }
        Livewire::actingAs($campusAdmin)->test(RegistrationList::class)->assertOk();
    }
}
