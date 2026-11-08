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

        $finance = $this->finance($pid);

        return view('admin.dashboard', compact(
            'notifications', 'stats', 'statusBreakdown', 'topReferrers', 'recent', 'activityFeed', 'finance'
        ));
    }

    /**
     * Rekap pembayaran terverifikasi per prodi: biaya pendaftaran (tarif saat konfirmasi) dan
     * daftar ulang (jumlah pembayaran terverifikasi), plus sisa tagihan daftar ulang.
     *
     * @return array{rows: \Illuminate\Support\Collection, totals: array<string,int>, unrecorded: int}
     */
    private function finance(?int $pid): array
    {
        $registrationFees = Registration::forPeriod($pid)
            ->whereNotNull('registration_fee_paid')
            ->selectRaw('first_choice_program_id as program_id, sum(registration_fee_paid) as total')
            ->groupBy('first_choice_program_id')
            ->pluck('total', 'program_id');

        $paidPerRegistration = DB::table('registration_payments')
            ->where('type', 're_registration')
            ->where('status', 'terverifikasi')
            ->selectRaw('registration_id, sum(amount) as paid')
            ->groupBy('registration_id');

        $reRegistration = Registration::query()
            ->when($pid, fn ($q) => $q->where('registrations.period_id', $pid))
            ->joinSub($paidPerRegistration, 'p', 'p.registration_id', '=', 'registrations.id')
            ->leftJoin('period_programs as pp', function ($join) {
                $join->on('pp.period_id', '=', 'registrations.period_id')
                    ->on('pp.program_id', '=', 'registrations.first_choice_program_id')
                    ->whereRaw('pp.campus_id <=> registrations.campus_id');
            })
            // Rumus sisa tagihan sama dengan Registration::reRegistrationRemaining() (tarif - potongan
            // form minat - terbayar), ditulis ulang di SQL karena dihitung agregat per prodi.
            ->selectRaw('registrations.first_choice_program_id as program_id, sum(p.paid) as paid, sum(greatest(coalesce(pp.re_registration_fee, 0) - registrations.event_discount_amount - p.paid, 0)) as outstanding')
            ->groupBy('registrations.first_choice_program_id')
            ->get()
            ->keyBy('program_id');

        $programIds = $registrationFees->keys()->merge($reRegistration->keys())->unique()->values();
        $names = Program::whereIn('id', $programIds)->pluck('name', 'id');

        $rows = $programIds->map(fn ($id) => [
            'name'             => $names[$id] ?? 'Prodi dihapus',
            'registration_fee' => (int) ($registrationFees[$id] ?? 0),
            're_registration'  => (int) ($reRegistration[$id]->paid ?? 0),
            'outstanding'      => (int) ($reRegistration[$id]->outstanding ?? 0),
        ])->sortBy('name')->values();

        $unrecorded = Registration::forPeriod($pid)
            ->where('status', 'daftar_ulang_selesai')
            ->whereDoesntHave('payments', fn ($q) => $q->reRegistration()->verified())
            ->count();

        return [
            'rows'       => $rows,
            'totals'     => [
                'registration_fee' => (int) $rows->sum('registration_fee'),
                're_registration'  => (int) $rows->sum('re_registration'),
                'outstanding'      => (int) $rows->sum('outstanding'),
            ],
            'unrecorded' => $unrecorded,
        ];
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
