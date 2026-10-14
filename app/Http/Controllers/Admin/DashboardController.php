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

        // Satu query: hitungan per status (ditutup indeks) menjadi sumber seluruh KPI di bawah.
        $statusBreakdown = Registration::forPeriod($pid)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->toArray();

        $sum = fn (array $statuses) => array_sum(array_map(fn ($status) => $statusBreakdown[$status] ?? 0, $statuses));

        // ── Notifications / Action Required ───────────────────────
        $notifications = [
            'pending_payment'        => $sum(['menunggu_konfirmasi']),
            'pending_reregistration' => $sum(['menunggu_konfirmasi_daftar_ulang']),
        ];

        // ── Main KPI stats ─────────────────────────────────────────
        $stats = [
            'total'            => array_sum($statusBreakdown),
            'pending'          => $sum(['menunggu_pembayaran', 'menunggu_konfirmasi']),
            'terdaftar'        => $sum(['terdaftar']),
            'diterima'         => $sum(['diterima', 'daftar_ulang_selesai']),
            'perlu_tindakan'   => $sum(['menunggu_konfirmasi', 'menunggu_konfirmasi_daftar_ulang', 'perlu_revisi']),
            'referrers'        => Referrer::count(),
            'programs'         => Program::where('is_active', true)->count(),
            'faculties'        => Faculty::count(),
        ];

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
        // Dengan filter periode, optimizer memilih memindai registrasi lalu filesort ribuan log
        // (ratusan ms); memaksa urutan indeks created_at hanya membaca log terbaru sampai 8 cocok.
        $activityFeed = \App\Models\PaymentLog::with(['registration', 'actor'])
            ->when($pid, function ($q) use ($pid) {
                // Petunjuk indeks hanya dipakai bila indeksnya benar-benar ada: nama indeks yang
                // tidak ada membuat query gagal (galat 1176) dan dashboard menjadi 500.
                if ($this->hasCreatedAtIndex()) {
                    $q->from(DB::raw('`payment_logs` force index (`payment_logs_created_at_index`)'));
                }

                $q->whereHas('registration', fn ($r) => $r->where('period_id', $pid));
            })
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'notifications', 'stats', 'statusBreakdown', 'topReferrers', 'recent', 'activityFeed'
        ));
    }

    /** Indeks created_at pada payment_logs ada? Di-cache sebentar agar tidak memeriksa tiap permintaan. */
    private function hasCreatedAtIndex(): bool
    {
        return (bool) \Illuminate\Support\Facades\Cache::remember(
            'perf.index.payment_logs_created_at',
            600,
            fn () => \Illuminate\Support\Facades\Schema::hasIndex('payment_logs', 'payment_logs_created_at_index')
        );
    }
}
