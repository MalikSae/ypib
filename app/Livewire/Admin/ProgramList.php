<?php

namespace App\Livewire\Admin;

use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\Program;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Daftar program studi. Cari, kampus, dan jumlah per halaman langsung berlaku dan tersimpan di URL. */
class ProgramList extends Component
{
    use WithPagination;

    private const PER_PAGE = [5, 10, 25, 50, 100];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $campus = '';

    #[Url(as: 'per_page', except: 10)]
    public int $perPage = 10;

    /** Sama dengan middleware global.admin: admin atau operator tanpa batasan kampus. */
    public function boot(): void
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['admin', 'operator'], true) && ! $user->campus_id, 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'campus', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Program::with('faculty')->orderBy('name');

        // Kuota dan biaya ditampilkan dari periode berjalan (nilainya diatur per periode).
        $displayPeriod = PmbPeriod::displayPeriod();

        $campuses = Campus::orderByDesc('is_default')->orderBy('name')->get();
        $campusFilter = $campuses->firstWhere('id', (int) $this->campus);

        $term = trim($this->search);
        if ($term !== '') {
            // Dikelompokkan agar OR tidak lolos dari filter kampus.
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('faculty', fn ($f) => $f->where('name', 'like', "%{$term}%")));
        }

        if ($campusFilter) {
            $query->inCampus($displayPeriod, $campusFilter);
        }

        $perPage = in_array($this->perPage, self::PER_PAGE, true) ? $this->perPage : 10;
        $programs = $query->paginate($perPage);

        // Kolom Kampus, lalu biaya/kuota kampus yang difilter (tanpa filter: kampus bawaan).
        Program::attachOfferingCampuses($programs->getCollection(), $displayPeriod);
        $termsCampus = $campusFilter ?? Campus::default();
        Program::preloadTerms($programs->getCollection(), $displayPeriod, $termsCampus);

        return view('livewire.admin.program-list', [
            'programs'       => $programs,
            'displayPeriod'  => $displayPeriod,
            'campusOptions'  => $campuses->pluck('name', 'id')->all(),
            'termsCampus'    => $termsCampus,
            'perPageOptions' => array_combine(self::PER_PAGE, array_map('strval', self::PER_PAGE)),
        ]);
    }
}
