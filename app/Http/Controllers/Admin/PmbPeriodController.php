<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Kelola periode PMB: buat, ubah, salin, tayangkan, arsipkan.
 * Fase pendaftaran diturunkan dari tanggal (PmbPeriod); yang diatur di sini hanya `status`.
 */
class PmbPeriodController extends Controller
{
    /** Status pendaftaran yang dianggap sudah final (tidak lagi berjalan). */
    private const FINAL_STATUSES = ['daftar_ulang_selesai', 'ditolak'];

    public function index()
    {
        $periods = PmbPeriod::withCount([
            'registrations',
            'registrations as running_registrations_count' => fn ($q) => $q->whereNotIn('status', self::FINAL_STATUSES),
        ])->orderByDesc('open_date')->orderByDesc('id')->get();

        return view('admin.periods.index', compact('periods'));
    }

    /** Pemilih periode global di header admin: hanya memengaruhi tampilan dan ekspor staf ini. */
    public function select(Request $request)
    {
        $data = $request->validate([
            'period' => ['required', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value !== \App\Support\AdminPeriod::ALL && !PmbPeriod::whereKey((int) $value)->exists()) {
                    $fail('Periode tidak ditemukan.');
                }
            }],
        ]);

        \App\Support\AdminPeriod::choose($data['period']);

        return redirect()->back();
    }

    public function create()
    {
        $year = (int) now()->year;

        $period = new PmbPeriod([
            'name'               => 'PMB ' . $year . '/' . ($year + 1),
            'year'               => $year,
            'payment_grace_days' => 3,
            'nim_sequence_start' => 1,
        ]);

        return view('admin.periods.form', compact('period'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $period = PmbPeriod::create($validated + ['status' => PmbPeriod::STATUS_DRAFT]);
        PeriodProgram::seedFromPrograms($period);

        $this->logTransition($period, null, PmbPeriod::STATUS_DRAFT, 'created');

        return redirect()->route('admin.periods.index')
            ->with('success', 'Periode "' . $period->name . '" dibuat sebagai draft. Tayangkan setelah data lengkap.');
    }

    public function edit(int $id)
    {
        $period = PmbPeriod::withCount('registrations')->findOrFail($id);

        return view('admin.periods.form', compact('period'));
    }

    public function update(Request $request, int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->isArchived()) {
            return redirect()->route('admin.periods.index')
                ->with('error', 'Periode yang diarsipkan hanya baca. Buka kembali periode ini sebelum mengubahnya.');
        }

        $validated = $request->validate($this->rules());

        $errors = [];

        // Tahun menjadi bagian nomor pendaftaran dan NIM: kunci setelah ada pendaftar (termasuk yang dihapus).
        if ((int) $validated['year'] !== (int) $period->year
            && $period->registrations()->withTrashed()->exists()) {
            $errors['year'] = 'Tahun tidak bisa diubah karena periode ini sudah punya pendaftar.';
        }

        if ($period->isPublished()) {
            if (empty($validated['re_registration_deadline'])) {
                $errors['re_registration_deadline'] = 'Tenggat daftar ulang wajib diisi pada periode yang sedang tayang.';
            }

            $overlap = PmbPeriod::overlapping($validated['open_date'], $validated['close_date'], $period->id)->first();
            if ($overlap) {
                $errors['open_date'] = 'Jendela pendaftaran beririsan dengan periode tayang "' . $overlap->name . '".';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $period->update($validated);

        return redirect()->route('admin.periods.index')->with('success', 'Periode "' . $period->name . '" berhasil diperbarui.');
    }

    public function publish(int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->status !== PmbPeriod::STATUS_DRAFT) {
            return redirect()->route('admin.periods.index')->with('error', 'Hanya periode draft yang bisa ditayangkan.');
        }

        return $this->transitionToPublished($period, 'published');
    }

    public function reopen(int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if (!$period->isArchived()) {
            return redirect()->route('admin.periods.index')->with('error', 'Hanya periode yang diarsipkan yang bisa dibuka kembali.');
        }

        return $this->transitionToPublished($period, 'reopened');
    }

    public function unpublish(int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if (!$period->isPublished()) {
            return redirect()->route('admin.periods.index')->with('error', 'Periode ini tidak sedang tayang.');
        }

        if ($period->registrations()->withTrashed()->exists()) {
            return redirect()->route('admin.periods.index')
                ->with('error', 'Periode sudah punya pendaftar, jadi tidak bisa dikembalikan ke draft. Gunakan Arsipkan.');
        }

        $period->update(['status' => PmbPeriod::STATUS_DRAFT, 'published_at' => null]);
        $this->logTransition($period, PmbPeriod::STATUS_PUBLISHED, PmbPeriod::STATUS_DRAFT, 'unpublished');

        return redirect()->route('admin.periods.index')->with('success', 'Periode "' . $period->name . '" dikembalikan ke draft.');
    }

    /** Halaman konfirmasi arsip: menampilkan pendaftaran yang masih berjalan. */
    public function archiveConfirm(int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->isArchived()) {
            return redirect()->route('admin.periods.index')->with('error', 'Periode ini sudah diarsipkan.');
        }

        return view('admin.periods.archive', [
            'period'  => $period,
            'summary' => $this->archiveSummary($period),
        ]);
    }

    public function archive(Request $request, int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->isArchived()) {
            return redirect()->route('admin.periods.index')->with('error', 'Periode ini sudah diarsipkan.');
        }

        $summary = $this->archiveSummary($period);

        // Mengarsipkan memutus pendaftar yang masih menunggu konfirmasi daftar ulang: wajib disetujui eksplisit.
        if ($summary['awaiting_re_registration'] > 0 && !$request->boolean('acknowledge')) {
            return redirect()->route('admin.periods.archive.confirm', $period->id)
                ->with('error', 'Centang pernyataan persetujuan: masih ada pendaftar yang menunggu konfirmasi daftar ulang.');
        }

        $from = $period->status;
        $period->update(['status' => PmbPeriod::STATUS_ARCHIVED]);
        $this->logTransition($period, $from, PmbPeriod::STATUS_ARCHIVED, 'archived', [
            'running_registrations'    => $summary['running'],
            'awaiting_re_registration' => $summary['awaiting_re_registration'],
        ]);

        return redirect()->route('admin.periods.index')->with('success', 'Periode "' . $period->name . '" diarsipkan.');
    }

    /**
     * Salin periode sebagai draft tahun berikutnya: tanggal digeser satu tahun, rekening dan
     * WhatsApp ikut disalin (dibaca halaman bayar, SKL, dan daftar ulang dari periode pendaftaran).
     */
    public function duplicate(int $id)
    {
        $source = PmbPeriod::findOrFail($id);

        $copy = PmbPeriod::create([
            'name'                         => $source->name . ' (salinan)',
            'year'                         => (int) $source->year + 1,
            'open_date'                    => $source->open_date->copy()->addYear()->toDateString(),
            'close_date'                   => $source->close_date->copy()->addYear()->toDateString(),
            're_registration_deadline'     => $source->re_registration_deadline?->copy()->addYear()->toDateString(),
            'payment_grace_days'           => $source->payment_grace_days,
            'nim_sequence_start'           => 1,
            'university_bank_name'         => $source->university_bank_name,
            'university_bank_account'      => $source->university_bank_account,
            'university_bank_account_name' => $source->university_bank_account_name,
            'admin_whatsapp'               => $source->admin_whatsapp,
            'status'                       => PmbPeriod::STATUS_DRAFT,
        ]);

        // Biaya, kuota, dan komisi ikut disalin; admin menyesuaikannya di "Biaya & Kuota".
        PeriodProgram::copyBetween($source, $copy);

        $this->logTransition($copy, null, PmbPeriod::STATUS_DRAFT, 'duplicated', ['source_period_id' => $source->id]);

        return redirect()->route('admin.periods.edit', $copy->id)
            ->with('success', 'Salinan dibuat sebagai draft. Periksa tanggal, rekening, dan tenggat sebelum ditayangkan.');
    }

    private function transitionToPublished(PmbPeriod $period, string $event)
    {
        $blockers = $period->publishBlockers();

        if ($blockers) {
            return redirect()->route('admin.periods.index')
                ->with('error', 'Periode "' . $period->name . '" belum bisa ditayangkan: ' . implode(' ', $blockers));
        }

        $from = $period->status;
        $period->update(['status' => PmbPeriod::STATUS_PUBLISHED, 'archived_at' => null]);
        $this->logTransition($period, $from, PmbPeriod::STATUS_PUBLISHED, $event);

        return redirect()->route('admin.periods.index')->with('success', 'Periode "' . $period->name . '" ditayangkan.');
    }

    /** @return array{total:int, running:int, awaiting_re_registration:int} */
    private function archiveSummary(PmbPeriod $period): array
    {
        $registrations = $period->registrations();

        return [
            'total'                    => (clone $registrations)->count(),
            'running'                  => (clone $registrations)->whereNotIn('status', self::FINAL_STATUSES)->count(),
            'awaiting_re_registration' => (clone $registrations)->where('status', 'menunggu_konfirmasi_daftar_ulang')->count(),
        ];
    }

    private function rules(): array
    {
        return [
            'name'                         => 'required|string|max:255',
            'year'                         => 'required|integer|between:2000,2100',
            'open_date'                    => 'required|date',
            'close_date'                   => 'required|date|after_or_equal:open_date',
            're_registration_deadline'     => 'nullable|date|after_or_equal:close_date',
            'payment_grace_days'           => 'required|integer|between:0,30',
            'nim_sequence_start'           => 'required|integer|between:1,999',
            'university_bank_name'         => 'nullable|string|max:255',
            'university_bank_account'      => 'nullable|string|max:255',
            'university_bank_account_name' => 'nullable|string|max:255',
            'admin_whatsapp'               => 'nullable|string|max:20',
        ];
    }

    private function logTransition(PmbPeriod $period, ?string $from, string $to, string $event, array $context = []): void
    {
        Log::info('PMB period ' . $event, array_merge([
            'period_id' => $period->id,
            'name'      => $period->name,
            'from'      => $from,
            'to'        => $to,
            'by_user'   => Auth::id(),
        ], $context));
    }
}
