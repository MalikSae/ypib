<?php

namespace Tests\Feature\Prospek;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectLink;
use App\Models\ProspectSource;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Corong per event di halaman Event dan Kampanye: prospek, daftar, daftar ulang. */
class ProspectEventFunnelTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;
    private PmbPeriod $period;
    private Program $program;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka',
            'admin_whatsapp' => '081200000001', 'is_active' => true,
        ]);
        $this->period = $this->makePeriod('PMB Uji');
        $faculty = Faculty::create(['name' => 'Fakultas Uji', 'slug' => 'fakultas-uji']);
        $this->program = Program::create([
            'name' => 'S1 Uji', 'slug' => 's1-uji', 'kode_prodi' => '13201', 'registration_track' => 'reguler',
            'faculty_id' => $faculty->id, 'is_active' => true,
        ]);
    }

    private function makePeriod(string $name): PmbPeriod
    {
        return PmbPeriod::create([
            'name' => $name, 'year' => 2027,
            'open_date' => now()->subDay()->toDateString(), 'close_date' => now()->addMonth()->toDateString(),
            'is_active' => true, 'status' => PmbPeriod::STATUS_PUBLISHED,
        ]);
    }

    private function makeEvent(string $name, array $overrides = []): ProspectEvent
    {
        return ProspectEvent::create(array_merge([
            'campus_id' => $this->campus->id, 'period_id' => $this->period->id, 'kind' => ProspectEvent::KIND_EVENT,
            'name' => $name, 'slug' => Str::slug($name) . '-' . uniqid(), 'discount_amount' => 0, 'is_active' => true,
        ], $overrides));
    }

    /**
     * Prospek yang ikut event sesuai urutan $events. $status = status registrasi (null: belum daftar).
     * $linkEvent = event pada link daftar yang terpakai (null: daftar tanpa link).
     */
    private function makeProspect(array $events, ?string $status = null, ?ProspectEvent $linkEvent = null, bool $fraud = false): Prospect
    {
        $n = ++$this->seq;
        $phone = '0812000000' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);

        $prospect = Prospect::create([
            'full_name' => 'Prospek ' . $n, 'phone' => $phone, 'phone_normalized' => $phone,
            'campus_id' => $this->campus->id, 'status' => Prospect::STATUS_BARU, 'fraud_flag' => $fraud,
        ]);

        foreach (array_values($events) as $i => $event) {
            ProspectSource::create([
                'prospect_id' => $prospect->id, 'prospect_event_id' => $event->id,
                'created_at' => now()->subDays(10)->addDays($i),
            ]);
        }

        if ($status !== null) {
            $registration = Registration::create([
                'period_id' => $this->period->id, 'campus_id' => $this->campus->id,
                'user_id' => User::factory()->create()->id, 'prospect_id' => $prospect->id,
                'first_choice_program_id' => $this->program->id, 'full_name' => $prospect->full_name,
                'nik' => '32100000000000' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'birth_place' => 'Majalengka', 'birth_date' => '2008-01-01', 'gender' => 'male',
                'address' => 'Jl. Uji', 'phone' => $phone, 'school_name' => 'SMA Uji', 'graduation_year' => 2026,
                'status' => $status,
            ]);
            $prospect->forceFill(['status' => Prospect::STATUS_SUDAH_DAFTAR, 'registration_id' => $registration->id])->save();

            if ($linkEvent) {
                ProspectLink::create([
                    'prospect_id' => $prospect->id, 'prospect_event_id' => $linkEvent->id,
                    'token' => Str::random(40), 'expires_at' => now()->addWeek(), 'used_at' => now(),
                ]);
            }
        }

        return $prospect;
    }

    private function funnel(ProspectEvent $event): array
    {
        $row = ProspectEvent::withFunnel()->findOrFail($event->id);

        return [(int) $row->prospects_count, (int) $row->registered_count, (int) $row->reregistered_count];
    }

    public function test_counts_prospects_paid_registrations_and_completed_re_registrations(): void
    {
        $event = $this->makeEvent('Sosialisasi A');

        $this->makeProspect([$event]);                               // belum daftar
        $this->makeProspect([$event], 'menunggu_pembayaran');        // belum bayar: bukan "daftar"
        $this->makeProspect([$event], 'menunggu_konfirmasi');        // belum dikonfirmasi: bukan "daftar"
        $this->makeProspect([$event], 'terdaftar');
        $this->makeProspect([$event], 'ditolak');                    // sudah bayar: tetap "daftar"
        $this->makeProspect([$event], 'daftar_ulang_selesai');

        $this->assertSame([6, 3, 1], $this->funnel($event));
    }

    public function test_fraud_prospects_are_excluded_from_every_number(): void
    {
        $event = $this->makeEvent('Sosialisasi A');

        $this->makeProspect([$event], 'daftar_ulang_selesai');
        $this->makeProspect([$event], 'daftar_ulang_selesai', fraud: true);

        $this->assertSame([1, 1, 1], $this->funnel($event));
    }

    public function test_multi_event_prospect_counts_everywhere_but_registration_credits_one_event(): void
    {
        $first = $this->makeEvent('Sosialisasi A');
        $second = $this->makeEvent('Sosialisasi B');

        // Tanpa link: kredit ke event pertama yang diikuti.
        $this->makeProspect([$first, $second], 'terdaftar');
        // Dengan link event kedua: kredit ke event pada link, bukan event pertama.
        $this->makeProspect([$first, $second], 'daftar_ulang_selesai', $second);

        $this->assertSame([2, 1, 0], $this->funnel($first));
        $this->assertSame([2, 1, 1], $this->funnel($second));
    }

    public function test_soft_deleted_registration_is_not_counted(): void
    {
        $event = $this->makeEvent('Sosialisasi A');
        $prospect = $this->makeProspect([$event], 'terdaftar');
        Registration::findOrFail($prospect->registration_id)->delete();

        $this->assertSame([1, 0, 0], $this->funnel($event));
    }

    public function test_event_without_prospects_counts_zero(): void
    {
        $event = $this->makeEvent('Sosialisasi Kosong');

        $this->assertSame([0, 0, 0], $this->funnel($event));
    }

    public function test_index_page_shows_funnel_and_applies_filters_and_sort(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherPeriod = $this->makePeriod('PMB Lain');

        $small = $this->makeEvent('Sosialisasi Kecil');
        $big = $this->makeEvent('Sosialisasi Besar');
        $online = $this->makeEvent('Kampanye Daring', ['kind' => ProspectEvent::KIND_ONLINE, 'period_id' => $otherPeriod->id]);
        $noPeriod = $this->makeEvent('Tanpa Periode', ['period_id' => null]);

        $this->makeProspect([$small]);
        $this->makeProspect([$big], 'terdaftar');
        $this->makeProspect([$big]);

        $this->actingAs($admin)->get(route('admin.prospects.events.index'))
            ->assertOk()
            ->assertSee('Daftar ulang')
            // Persen konversi tidak ditampilkan lagi, hanya jumlah.
            ->assertDontSee('>50%<', false)
            ->assertSee(route('admin.prospects.index', ['event' => $big->id]), false)
            // Filter memakai dropdown kustom, bukan <select> bawaan.
            ->assertDontSee('<select', false)
            ->assertSee('role="combobox"', false)
            ->assertSeeInOrder([$noPeriod->name, $online->name, $big->name, $small->name]);

        $this->actingAs($admin)->get(route('admin.prospects.events.index', ['urut' => 'prospek']))
            ->assertOk()
            ->assertSeeInOrder([$big->name, $small->name]);

        $this->actingAs($admin)->get(route('admin.prospects.events.index', ['jenis' => ProspectEvent::KIND_ONLINE]))
            ->assertOk()
            ->assertSee($online->name)
            ->assertDontSee($big->name);

        $this->actingAs($admin)->get(route('admin.prospects.events.index', ['periode' => $otherPeriod->id]))
            ->assertOk()
            ->assertSee($online->name)
            ->assertDontSee($big->name)
            ->assertDontSee($noPeriod->name);
    }
}
