<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Faculty;
use App\Models\Partner;
use App\Models\Facility;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $period   = PmbPeriod::displayPeriod();
        $programs = Program::with('faculty')->offeredInAny($period)->get();
        $faculties = Faculty::with(['programs' => fn($q) => $q->offeredInAny($period)])->get();
        Program::attachOfferingCampuses($programs->concat($faculties->pluck('programs')->flatten()), $period);
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();

        return view('landing.index', compact('programs', 'period', 'faculties', 'partners', 'facilities'));
    }

    public function preview()
    {
        $period    = PmbPeriod::displayPeriod();
        $programs  = Program::with('faculty', 'galleries')->offeredInAny($period)->get();
        $faculties = Faculty::with(['programs' => fn($q) => $q->offeredInAny($period)])->get();
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();
        Program::attachOfferingCampuses($programs->concat($faculties->pluck('programs')->flatten()), $period);

        // Biaya pendaftaran terendah di antara prodi yang ditawarkan pada periode ini (hero).
        $startingFee = $programs
            ->map(fn ($program) => (int) $program->landingTerms($period)->registration_fee)
            ->filter(fn ($fee) => $fee > 0)
            ->min();

        return view('landing.preview', compact(
            'programs', 'faculties', 'partners', 'facilities', 'period', 'startingFee'
        ));
    }

    /**
     * Landing satu kampus (/cirebon). Kampus bawaan diarahkan ke landing utama; kampus nonaktif
     * atau slug tidak dikenal 404. Template sama dengan landing utama.
     */
    public function campus(string $campusSlug)
    {
        $campus = Campus::where('slug', $campusSlug)->first();

        if (!$campus) {
            // Slug lama (kampus diganti slug-nya): alihkan permanen ke alamat baru.
            $current = Campus::findActiveBySlug($campusSlug);
            abort_unless($current, 404);

            return redirect($current->landingUrl(), 301);
        }

        abort_unless($campus->is_active, 404);

        if ($campus->is_default) {
            return redirect()->route('landing', [], 301);
        }

        $period   = PmbPeriod::displayPeriod();
        $programs = Program::with('faculty', 'galleries')->offeredIn($period, $campus)->get();
        $faculties = Faculty::with(['programs' => fn ($q) => $q->offeredIn($period, $campus)])->get()
            ->filter(fn ($faculty) => $faculty->programs->isNotEmpty())->values();

        Program::attachCampus($programs->concat($faculties->pluck('programs')->flatten()), $period, $campus);

        $startingFee = $programs
            ->map(fn ($program) => (int) $program->landingTerms($period)->registration_fee)
            ->filter(fn ($fee) => $fee > 0)
            ->min();

        // Galeri dan mitra bersifat global (belum per kampus), jadi tidak ditampilkan di sini.
        $partners   = collect();
        $facilities = collect();

        return view('landing.preview', compact(
            'programs', 'faculties', 'partners', 'facilities', 'period', 'startingFee', 'campus'
        ));
    }

    public function prodi(Request $request, string $slug)
    {
        $period  = PmbPeriod::displayPeriod();
        $program = Program::with(['faculty', 'galleries'])->offeredInAny($period)->where('slug', $slug)->firstOrFail();
        Program::attachOfferingCampuses([$program], $period);

        // ?kampus=slug: tampilkan biaya dan kuota kampus itu (bila prodi ditawarkan di sana).
        $campus = $program->offeringCampuses->firstWhere('slug', \App\Support\QueryInput::string($request, 'kampus'));

        if ($campus) {
            $program->setRelation('offeringCampuses', $program->offeringCampuses
                ->sortBy(fn ($c) => $c->id === $campus->id ? 0 : 1)->values());
        }

        return view('landing.prodi', compact('program', 'period', 'campus'));
    }
}
