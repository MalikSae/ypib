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
        $programs = Program::with('faculty')->active()->get();
        $period   = PmbPeriod::active()->first();
        $faculties = Faculty::with(['programs' => fn($q) => $q->active()])->get();
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();

        return view('landing.index', compact('programs', 'period', 'faculties', 'partners', 'facilities'));
    }

    public function preview()
    {
        $programs  = Program::with('faculty', 'galleries')->active()->get();
        $faculties = Faculty::with(['programs' => fn($q) => $q->active()])->get();
        $partners  = Partner::where('is_active', true)->orderBy('id')->get();
        $facilities = Facility::active()->get();
        $period    = PmbPeriod::active()->first();

        return view('landing.preview', compact(
            'programs', 'faculties', 'partners', 'facilities', 'period'
        ));
    }

    public function prodi(string $slug)
    {
        $program = Program::with(['faculty', 'galleries'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $period  = PmbPeriod::active()->first();

        return view('landing.prodi', compact('program', 'period'));
    }
}
