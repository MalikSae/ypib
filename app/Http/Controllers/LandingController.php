<?php

namespace App\Http\Controllers;

use App\Models\PmbPeriod;
use App\Models\Program;

class LandingController extends Controller
{
    public function index()
    {
        $programs = Program::active()->get();
        $period   = PmbPeriod::active()->first();

        return view('landing.index', compact('programs', 'period'));
    }

    public function prodi(string $slug)
    {
        $program = Program::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $period  = PmbPeriod::active()->first();

        return view('landing.prodi', compact('program', 'period'));
    }
}
