<?php

namespace App\Http\Controllers;

use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Faculty;
use App\Models\Partner;
use App\Models\Facility;

class LandingController extends Controller
{
    public function index()
    {
        $period   = PmbPeriod::displayPeriod();
        $programs = Program::with('faculty')->offeredIn($period)->get();
        $faculties = Faculty::with(['programs' => fn($q) => $q->offeredIn($period)])->get();
        Program::preloadTerms($programs->concat($faculties->pluck('programs')->flatten()), $period);
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();

        return view('landing.index', compact('programs', 'period', 'faculties', 'partners', 'facilities'));
    }

    public function preview()
    {
        $period    = PmbPeriod::displayPeriod();
        $programs  = Program::with('faculty', 'galleries')->offeredIn($period)->get();
        $faculties = Faculty::with(['programs' => fn($q) => $q->offeredIn($period)])->get();
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();
        Program::preloadTerms($programs->concat($faculties->pluck('programs')->flatten()), $period);

        // Biaya pendaftaran terendah di antara prodi yang ditawarkan pada periode ini (hero).
        $startingFee = $programs
            ->map(fn ($program) => (int) $program->termsFor($period)->registration_fee)
            ->filter(fn ($fee) => $fee > 0)
            ->min();

        return view('landing.preview', compact(
            'programs', 'faculties', 'partners', 'facilities', 'period', 'startingFee'
        ));
    }

    public function prodi(string $slug)
    {
        $period  = PmbPeriod::displayPeriod();
        $program = Program::with(['faculty', 'galleries'])->offeredIn($period)->where('slug', $slug)->firstOrFail();

        return view('landing.prodi', compact('program', 'period'));
    }
}
