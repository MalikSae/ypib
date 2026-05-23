<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Faculty;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Referrer;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Main KPI stats ─────────────────────────────────────────
        $stats = [
            'total'            => Registration::count(),
            'pending'          => Registration::whereIn('status', ['menunggu_pembayaran', 'menunggu_konfirmasi'])->count(),
            'terdaftar'        => Registration::where('status', 'terdaftar')->count(),
            'diterima'         => Registration::whereIn('status', ['diterima', 'daftar_ulang_selesai'])->count(),
            'perlu_tindakan'   => Registration::whereIn('status', ['menunggu_konfirmasi', 'menunggu_konfirmasi_daftar_ulang', 'perlu_revisi'])->count(),
            'referrers'        => Referrer::count(),
            'programs'         => Program::where('is_active', true)->count(),
            'faculties'        => Faculty::count(),
        ];

        // ── Breakdown per status (for mini chart/bar) ──────────────
        $statusBreakdown = Registration::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── Top performing referrers ───────────────────────────────
        $topReferrers = Referrer::withCount([
                'registrations as conversions' => fn ($q) => $q->whereNotIn('status', ['menunggu_pembayaran']),
            ])
            ->orderByDesc('conversions')
            ->take(5)
            ->get();

        // ── Recent registrations ───────────────────────────────────
        $recent = Registration::with(['user', 'firstChoiceProgram', 'referrer'])
            ->latest()
            ->take(8)
            ->get();

        // ── Activity feed – last 10 payment actions ────────────────
        $activityFeed = \App\Models\PaymentLog::with(['registration', 'actor'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'statusBreakdown', 'topReferrers', 'recent', 'activityFeed'
        ));
    }
}
