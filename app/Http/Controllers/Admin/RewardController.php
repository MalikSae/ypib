<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $query = Reward::with(['referrer.user', 'registration.user', 'approvedBy']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $rewards = $query->latest()->paginate(20)->withQueryString();

        // Data for Tab 1: Rekap per Afiliasi (Hanya yang Siap Cair / approved)
        $readyToDisburse = Reward::with(['referrer.user'])
            ->where('status', 'approved')
            ->get();
            
        $recapByReferrer = $readyToDisburse->groupBy('referrer_id')->map(function ($rewards) {
            return [
                'referrer' => $rewards->first()->referrer,
                'total_amount' => $rewards->sum('amount'),
                'total_registrations' => $rewards->count(),
                'reward_ids' => $rewards->pluck('id')->toArray(),
            ];
        });

        // Data for Tab 3: Riwayat Pencairan (Hanya yang Sudah Cair / disbursed)
        $alreadyDisbursed = Reward::with(['referrer.user'])
            ->where('status', 'disbursed')
            ->get();
            
        $historyByReferrer = $alreadyDisbursed->groupBy('referrer_id')->map(function ($rewards) {
            return [
                'referrer' => $rewards->first()->referrer,
                'total_amount' => $rewards->sum('amount'),
                'total_registrations' => $rewards->count(),
                'last_disbursed_at' => $rewards->max('updated_at'),
            ];
        });

        return view('admin.rewards.index', compact('rewards', 'recapByReferrer', 'historyByReferrer'));
    }

    public function approve(int $id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya reward berstatus pending yang bisa disetujui.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reward) {
            $reward->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            \App\Models\ReferrerLog::create([
                'referrer_id' => $reward->referrer_id,
                'acted_by' => Auth::id(),
                'action' => 'approved',
                'note' => 'Komisi disetujui senilai Rp ' . number_format($reward->amount, 0, ',', '.'),
            ]);
        });

        return redirect()->back()->with('success', 'Reward berhasil disetujui.');
    }

    private function guardBankComplete(\Illuminate\Support\Collection $referrerIds): void
    {
        $referrers = \App\Models\Referrer::with('user')->whereIn('id', $referrerIds->unique())->get();
        
        $incompleteReferrers = $referrers->filter(function ($referrer) {
            return trim((string)$referrer->bank_name) === '' || 
                   trim((string)$referrer->bank_account_number) === '' || 
                   trim((string)$referrer->bank_account_name) === '';
        });

        if ($incompleteReferrers->isNotEmpty()) {
            $names = $incompleteReferrers->map(fn($r) => $r->user?->name ?? 'Unknown')->implode(', ');
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->back()->with('error', 'Pencairan dibatalkan. Data rekening belum lengkap untuk: ' . $names)
            );
        }
    }

    public function disburse(int $id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->status !== 'approved') {
            return redirect()->back()->with('error', 'Hanya reward berstatus approved yang bisa dicairkan.');
        }

        $this->guardBankComplete(collect([$reward->referrer_id]));

        \Illuminate\Support\Facades\DB::transaction(function () use ($reward) {
            $reward->update(['status' => 'disbursed']);
            
            \App\Models\ReferrerLog::create([
                'referrer_id' => $reward->referrer_id,
                'acted_by' => Auth::id(),
                'action' => 'disbursed',
                'note' => 'Pencairan komisi senilai Rp ' . number_format($reward->amount, 0, ',', '.'),
            ]);
        });

        return redirect()->back()->with('success', 'Reward berhasil dicairkan.');
    }

    public function massDisburse(Request $request)
    {
        $request->validate([
            'reward_ids' => 'required|array',
            'reward_ids.*' => 'exists:rewards,id',
        ]);

        $rewards = Reward::whereIn('id', $request->reward_ids)
            ->where('status', 'approved')
            ->get();

        if ($rewards->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada reward berstatus "Siap Cair" yang dipilih.');
        }

        $this->guardBankComplete($rewards->pluck('referrer_id'));

        \Illuminate\Support\Facades\DB::transaction(function () use ($rewards) {
            foreach ($rewards as $reward) {
                $reward->update(['status' => 'disbursed']);
            }
            
            $byReferrer = $rewards->groupBy('referrer_id');
            foreach ($byReferrer as $referrerId => $group) {
                \App\Models\ReferrerLog::create([
                    'referrer_id' => $referrerId,
                    'acted_by' => Auth::id(),
                    'action' => 'disbursed',
                    'note' => 'Pencairan massal ' . $group->count() . ' komisi dengan total Rp ' . number_format($group->sum('amount'), 0, ',', '.'),
                ]);
            }
        });

        return redirect()->back()->with('success', $rewards->count() . ' reward berhasil dicairkan serentak.');
    }

    public function exportCsv(Request $request)
    {
        $query = Reward::with(['referrer.user', 'registration.user'])->where('status', 'approved');
        
        if ($request->has('reward_ids')) {
            $query->whereIn('id', $request->reward_ids);
        }

        $rewards = $query->get();

        if ($rewards->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        $filename = 'Pencairan_Afiliasi_' . date('Ymd_His') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID Reward', 'Nama Afiliasi', 'Kode', 'Bank', 'No Rekening', 'Atas Nama', 'Nominal', 'Pendaftar'];

        $callback = function() use($rewards, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($rewards as $reward) {
                $row = [
                    $reward->id,
                    $reward->referrer?->user?->name,
                    $reward->referrer?->code,
                    $reward->referrer?->bank_name,
                    $reward->referrer?->bank_account_number,
                    $reward->referrer?->bank_account_name,
                    $reward->amount,
                    $reward->registration?->full_name,
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function disburseByReferrer(int $referrerId)
    {
        $rewards = Reward::where('referrer_id', $referrerId)
            ->where('status', 'approved')
            ->get();

        if ($rewards->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada komisi yang Siap Cair untuk afiliasi ini.');
        }

        $this->guardBankComplete(collect([$referrerId]));

        \Illuminate\Support\Facades\DB::transaction(function () use ($rewards, $referrerId) {
            foreach ($rewards as $reward) {
                $reward->update(['status' => 'disbursed']);
            }
            
            \App\Models\ReferrerLog::create([
                'referrer_id' => $referrerId,
                'acted_by' => Auth::id(),
                'action' => 'disbursed',
                'note' => 'Pencairan komisi senilai Rp ' . number_format($rewards->sum('amount'), 0, ',', '.') . ' (' . $rewards->count() . ' transaksi)',
            ]);
        });

        return redirect()->back()->with('success', 'Berhasil mencairkan ' . $rewards->count() . ' komisi untuk afiliasi ini.');
    }

    public function massDisburseReferrers(Request $request)
    {
        $request->validate([
            'referrer_ids' => 'required|array',
            'referrer_ids.*' => 'exists:referrers,id',
        ]);

        $rewards = Reward::whereIn('referrer_id', $request->referrer_ids)
            ->where('status', 'approved')
            ->get();

        if ($rewards->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada komisi yang Siap Cair untuk afiliasi yang dipilih.');
        }

        $this->guardBankComplete(collect($request->referrer_ids));

        \Illuminate\Support\Facades\DB::transaction(function () use ($rewards) {
            foreach ($rewards as $reward) {
                $reward->update(['status' => 'disbursed']);
            }
            
            $byReferrer = $rewards->groupBy('referrer_id');
            foreach ($byReferrer as $referrerId => $group) {
                \App\Models\ReferrerLog::create([
                    'referrer_id' => $referrerId,
                    'acted_by' => Auth::id(),
                    'action' => 'disbursed',
                    'note' => 'Pencairan komisi senilai Rp ' . number_format($group->sum('amount'), 0, ',', '.') . ' (' . $group->count() . ' transaksi)',
                ]);
            }
        });

        return redirect()->back()->with('success', $rewards->count() . ' komisi dari afiliasi yang dipilih berhasil dicairkan serentak.');
    }

    public function exportCsvReferrers(Request $request)
    {
        $query = Reward::with(['referrer.user'])->where('status', 'approved');
        
        if ($request->has('referrer_ids')) {
            $query->whereIn('referrer_id', $request->referrer_ids);
        }

        $readyToDisburse = $query->get();

        if ($readyToDisburse->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        $recapByReferrer = $readyToDisburse->groupBy('referrer_id')->map(function ($rewards) {
            return [
                'referrer' => $rewards->first()->referrer,
                'total_amount' => $rewards->sum('amount'),
                'total_registrations' => $rewards->count(),
            ];
        });

        $filename = 'Rekap_Pencairan_Afiliasi_' . date('Ymd_His') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID Afiliasi', 'Nama Afiliasi', 'Kode', 'Bank', 'No Rekening', 'Atas Nama', 'Jumlah Pendaftar', 'Total Pencairan (Rp)'];

        $callback = function() use($recapByReferrer, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($recapByReferrer as $referrerId => $recap) {
                $row = [
                    $referrerId,
                    $recap['referrer']?->user?->name,
                    $recap['referrer']?->code,
                    $recap['referrer']?->bank_name,
                    $recap['referrer']?->bank_account_number,
                    $recap['referrer']?->bank_account_name,
                    $recap['total_registrations'],
                    $recap['total_amount'],
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approvedRewardsByReferrer(Request $request, $referrer_id)
    {
        $referrer = \App\Models\Referrer::findOrFail($referrer_id);

        $rewards = Reward::where('referrer_id', $referrer_id)
            ->where('status', 'approved')
            ->with('registration')
            ->orderByDesc('created_at')
            ->get();

        $data = $rewards->map(function ($reward) {
            return [
                'id' => $reward->id,
                'nama_pendaftar' => $reward->registration?->full_name ?? '—',
                'reward_type' => $reward->reward_type === 'registration' ? 'Pendaftaran' : 'Daftar Ulang',
                'amount' => $reward->amount,
                'created_at' => $reward->created_at->translatedFormat('d M Y, H:i'),
            ];
        });

        return response()->json($data);
    }

    public function disburseSelected(Request $request)
    {
        $request->validate([
            'reward_ids' => 'required|array|min:1',
            'reward_ids.*' => 'integer|exists:rewards,id',
        ]);

        $rewards = Reward::whereIn('id', $request->reward_ids)->get();

        abort_if($rewards->contains(fn($r) => $r->status !== 'approved'), 422, 'Ada komisi yang statusnya sudah berubah, silakan refresh halaman.');

        $this->guardBankComplete($rewards->pluck('referrer_id'));

        \Illuminate\Support\Facades\DB::transaction(function () use ($rewards) {
            foreach ($rewards as $reward) {
                $reward->update(['status' => 'disbursed']);
            }
            
            $byReferrer = $rewards->groupBy('referrer_id');
            foreach ($byReferrer as $referrerId => $group) {
                \App\Models\ReferrerLog::create([
                    'referrer_id' => $referrerId,
                    'acted_by' => auth()->id(),
                    'action' => 'disbursed',
                    'note' => 'Pencairan komisi senilai Rp ' . number_format($group->sum('amount'), 0, ',', '.') . ' (' . $group->count() . ' transaksi terpilih)',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'total_disbursed' => $rewards->count(),
            'total_amount' => $rewards->sum('amount'),
        ]);
    }
}
