<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Biaya, kuota, dan komisi per prodi pada satu periode (PLAN_MULTI_PERIODE.md T3b, K6).
 * Periode arsip hanya baca supaya SKL dan halaman bayar pendaftar lama tidak berubah.
 */
class PeriodProgramController extends Controller
{
    private const MAX_FEE_DETAILS = 20;

    public function edit(int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        $programs = Program::with('faculty')->orderBy('name')->get();
        Program::preloadTerms($programs, $period);

        $otherPeriods = PmbPeriod::where('id', '!=', $period->id)
            ->orderByDesc('open_date')->orderByDesc('id')->get();

        return view('admin.periods.pricing', [
            'period'       => $period,
            'programs'     => $programs,
            'otherPeriods' => $otherPeriods,
            'readOnly'     => $period->isArchived(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->isArchived()) {
            return redirect()->route('admin.periods.pricing', $period->id)
                ->with('error', 'Periode yang diarsipkan hanya baca. Buka kembali periode ini sebelum mengubah biaya.');
        }

        $request->validate([
            'programs'                                   => 'required|array|min:1',
            'programs.*.quota'                           => 'required|integer|min:0|max:100000',
            'programs.*.registration_fee'                => 'required|integer|min:0|max:2000000000',
            'programs.*.re_registration_minimum_payment' => 'required|integer|min:0|max:2000000000',
            'programs.*.referral_reward_amount'          => 'required|integer|min:0|max:2000000000',
            'programs.*.re_registration_reward_amount'   => 'required|integer|min:0|max:2000000000',
            'programs.*.fee_names'                       => 'nullable|array|max:' . self::MAX_FEE_DETAILS,
            'programs.*.fee_names.*'                     => 'nullable|string|max:255',
            'programs.*.fee_amounts'                     => 'nullable|array|max:' . self::MAX_FEE_DETAILS,
            'programs.*.fee_amounts.*'                   => 'nullable|string|max:20',
        ]);

        $input = $request->input('programs');
        $knownIds = Program::whereIn('id', array_map('intval', array_keys($input)))->pluck('id')->all();

        DB::transaction(function () use ($period, $input, $knownIds) {
            foreach ($knownIds as $programId) {
                $row = $input[$programId];

                [$details, $total] = $this->feeDetails($row['fee_names'] ?? [], $row['fee_amounts'] ?? []);

                PeriodProgram::updateOrCreate(
                    ['period_id' => $period->id, 'program_id' => $programId],
                    [
                        'is_offered'                      => !empty($row['is_offered']),
                        'quota'                           => (int) $row['quota'],
                        'registration_fee'                => (int) $row['registration_fee'],
                        're_registration_fee'             => $total,
                        're_registration_fee_details'     => $details,
                        're_registration_minimum_payment' => (int) $row['re_registration_minimum_payment'],
                        'referral_reward_amount'          => (int) $row['referral_reward_amount'],
                        're_registration_reward_amount'   => (int) $row['re_registration_reward_amount'],
                    ]
                );
            }
        });

        Log::info('PMB period pricing updated', [
            'period_id' => $period->id,
            'programs'  => count($knownIds),
            'by_user'   => Auth::id(),
        ]);

        return redirect()->route('admin.periods.pricing', $period->id)
            ->with('success', 'Biaya, kuota, dan komisi periode "' . $period->name . '" disimpan.');
    }

    public function copy(Request $request, int $id)
    {
        $period = PmbPeriod::findOrFail($id);

        if ($period->isArchived()) {
            return redirect()->route('admin.periods.pricing', $period->id)
                ->with('error', 'Periode yang diarsipkan hanya baca.');
        }

        $data = $request->validate([
            'source_period_id' => 'required|integer|exists:pmb_periods,id|different:' . $period->id,
        ]);

        $source = PmbPeriod::findOrFail($data['source_period_id']);
        $copied = PeriodProgram::copyBetween($source, $period, true);

        Log::info('PMB period pricing copied', [
            'period_id' => $period->id,
            'source_id' => $source->id,
            'programs'  => $copied,
            'by_user'   => Auth::id(),
        ]);

        return redirect()->route('admin.periods.pricing', $period->id)
            ->with('success', "Nilai {$copied} prodi disalin dari \"{$source->name}\". Periksa lalu simpan bila ada penyesuaian.");
    }

    /**
     * Rincian biaya daftar ulang dari baris form (nama + nominal). Baris tanpa nama dibuang;
     * total biaya daftar ulang selalu dihitung dari rincian.
     *
     * @return array{0: array<int, array{name: string, amount: int}>, 1: int}
     */
    private function feeDetails(array $names, array $amounts): array
    {
        $details = [];
        $total = 0;

        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            if ($name === '' || !isset($amounts[$index])) {
                continue;
            }

            $amount = (int) preg_replace('/\D/', '', (string) $amounts[$index]);
            $details[] = ['name' => $name, 'amount' => $amount];
            $total += $amount;
        }

        return [$details, $total];
    }
}
