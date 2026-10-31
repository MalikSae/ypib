<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class EducationLevelTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function fill($component, int $programId, array $override = [])
    {
        $data = array_merge([
            'full_name' => 'Calon Uji', 'nik' => '3210000000000001', 'birth_place' => 'Majalengka',
            'birth_date' => '2000-01-01', 'gender' => 'male', 'religion' => 'Islam', 'address' => 'Jl. Uji', 'phone' => '081234567890',
            'first_choice_program_id' => (string) $programId, 'admission_path' => 'umum',
            'education_level' => 'sma_smk_ma', 'nisn' => '1234567890',
            'school_name' => 'SMA Uji', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'IJ-1',
            'father_name' => 'Ayah', 'mother_name' => 'Ibu', 'father_occupation' => 'Petani', 'mother_occupation' => 'IRT',
        ], $override);

        foreach ($data as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    private function openForm()
    {
        $this->period();

        return Livewire::actingAs($this->makeUser())->test(RegistrationForm::class);
    }

    public function test_nisn_is_required_only_for_sma_smk_ma(): void
    {
        $program = $this->program();

        $this->fill($this->openForm(), $program->id, ['nisn' => ''])->call('submit')->assertHasErrors(['nisn']);
        $this->assertSame(0, Registration::count());

        foreach (['d3', 's1', 'lainnya'] as $level) {
            Registration::query()->forceDelete();
            $this->fill($this->openForm(), $program->id, ['education_level' => $level, 'nisn' => ''])->call('submit')->assertHasNoErrors();

            $registration = Registration::first();
            $this->assertSame($level, $registration->education_level);
            $this->assertNull($registration->nisn);
        }
    }

    public function test_nisn_when_filled_must_still_be_ten_digits(): void
    {
        $program = $this->program();

        $this->fill($this->openForm(), $program->id, ['education_level' => 's1', 'nisn' => '123'])->call('submit')->assertHasErrors(['nisn']);
    }

    public function test_grade_scale_depends_on_level(): void
    {
        $program = $this->program();

        // D3/S1: IPK 0-4
        foreach (['d3', 's1'] as $level) {
            $this->fill($this->openForm(), $program->id, ['education_level' => $level, 'nisn' => '', 'school_grade' => '85'])
                ->call('submit')->assertHasErrors(['school_grade']);
            $this->assertSame(0, Registration::count());

            $this->fill($this->openForm(), $program->id, ['education_level' => $level, 'nisn' => '', 'school_grade' => '3.45'])
                ->call('submit')->assertHasNoErrors();
            $this->assertSame('3.45', (string) Registration::first()->school_grade);
            Registration::query()->forceDelete();
        }

        // SMA/SMK/MA dan Lainnya: 0-100
        foreach (['sma_smk_ma', 'lainnya'] as $level) {
            $this->fill($this->openForm(), $program->id, ['education_level' => $level, 'school_grade' => '85.5'])
                ->call('submit')->assertHasNoErrors();
            Registration::query()->forceDelete();
        }
    }

    public function test_unknown_level_is_rejected(): void
    {
        $program = $this->program();

        $this->fill($this->openForm(), $program->id, ['education_level' => 's3'])->call('submit')->assertHasErrors(['education_level']);
        $this->assertSame(0, Registration::count());
    }

    public function test_form_completeness_follows_level_nisn_rule(): void
    {
        [, $sma] = $this->student('terdaftar', ['education_level' => 'sma_smk_ma', 'nisn' => null]);
        [, $legacy] = $this->student('terdaftar', ['education_level' => null, 'nisn' => null]);
        [, $s1] = $this->student('terdaftar', ['education_level' => 's1', 'nisn' => null]);

        $this->assertFalse($sma->isFormComplete());
        $this->assertFalse($legacy->isFormComplete(), 'data lama tanpa jenjang dianggap SMA/SMK/MA');
        $this->assertTrue($s1->isFormComplete());
    }

    public function test_labels_follow_level_and_fall_back_to_sma_for_legacy_rows(): void
    {
        $this->assertSame('IPK', Registration::educationConfig('s1')['grade']);
        $this->assertSame('Program Studi', Registration::educationConfig('d3')['major']);
        $this->assertSame('Nama Sekolah', Registration::educationConfig(null)['institution']);
        $this->assertSame(100, Registration::educationConfig('tidak-dikenal')['grade_max']);
    }

    public function test_detail_page_section_validates_by_level(): void
    {
        [$user, $registration] = $this->student('terdaftar', ['education_level' => 'sma_smk_ma']);

        $payload = [
            'section' => 'data_sekolah', 'education_level' => 's1', 'nisn' => '', 'school_name' => 'Universitas Uji',
            'school_major' => 'Informatika', 'graduation_year' => '2023', 'certificate_number' => 'IJ-9', 'school_grade' => '3.5',
        ];

        $this->actingAs($user)->post(route('registration.detail.update'), $payload)->assertSessionHasNoErrors();
        $registration->refresh();
        $this->assertSame('s1', $registration->education_level);
        $this->assertNull($registration->nisn);

        $this->actingAs($user)->post(route('registration.detail.update'), array_merge($payload, ['school_grade' => '85']))
            ->assertSessionHasErrors(['school_grade']);

        $this->actingAs($user)->post(route('registration.detail.update'), array_merge($payload, ['education_level' => 'sma_smk_ma']))
            ->assertSessionHasErrors(['nisn']);
    }
}
