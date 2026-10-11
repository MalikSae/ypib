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
        // Periode yang dipilih di header admin (null = semua periode).
        $pid = \App\Support\AdminPeriod::id();

        // ── Notifications / Action Required ───────────────────────
        $notifications = [
            'pending_payment' => Registration::forPeriod($pid)->where('status', 'menunggu_konfirmasi')->count(),
            'pending_reregistration' => Registration::forPeriod($pid)->where('status', 'menunggu_konfirmasi_daftar_ulang')->count(),
        ];

        // ── Main KPI stats ─────────────────────────────────────────
        $stats = [
            'total'            => Registration::forPeriod($pid)->count(),
            'pending'          => Registration::forPeriod($pid)->whereIn('status', ['menunggu_pembayaran', 'menunggu_konfirmasi'])->count(),
            'terdaftar'        => Registration::forPeriod($pid)->where('status', 'terdaftar')->count(),
            'diterima'         => Registration::forPeriod($pid)->whereIn('status', ['diterima', 'daftar_ulang_selesai'])->count(),
            'perlu_tindakan'   => Registration::forPeriod($pid)->whereIn('status', ['menunggu_konfirmasi', 'menunggu_konfirmasi_daftar_ulang', 'perlu_revisi'])->count(),
            'referrers'        => Referrer::count(),
            'programs'         => Program::where('is_active', true)->count(),
            'faculties'        => Faculty::count(),
        ];

        // ── Breakdown per status (for mini chart/bar) ──────────────
        $statusBreakdown = Registration::forPeriod($pid)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── Top performing referrers ───────────────────────────────
        $topReferrers = Referrer::withCount([
                'registrations as conversions' => fn ($q) => $q->whereNotIn('status', ['menunggu_pembayaran'])->forPeriod($pid),
            ])
            ->orderByDesc('conversions')
            ->take(5)
            ->get();

        // ── Recent registrations ───────────────────────────────────
        $recent = Registration::forPeriod($pid)->with(['user', 'firstChoiceProgram', 'referrer'])
            ->latest()
            ->take(8)
            ->get();

        // ── Activity feed – last 10 payment actions ────────────────
        $activityFeed = \App\Models\PaymentLog::with(['registration', 'actor'])
            ->when($pid, fn ($q) => $q->whereHas('registration', fn ($r) => $r->where('period_id', $pid)))
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'notifications', 'stats', 'statusBreakdown', 'topReferrers', 'recent', 'activityFeed'
        ));
    }
}
