<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrer;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReferrerController extends Controller
{
    public function index(Request $request)
    {
        $totalAfiliator = Referrer::count();
        $totalKlik = Referrer::sum('total_clicks');
        $totalKonversi = Referrer::sum('total_conversions');
        $persentaseKonversi = $totalKlik > 0 ? ($totalKonversi / $totalKlik) * 100 : 0;
        $query = Referrer::with(['user', 'rewards', 'clicks'])
            ->withSum(['rewards as total_reward' => function($q) {
                $q->whereIn('status', ['approved', 'disbursed']);
            }], 'amount');

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('code', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function($q) use ($searchTerm) {
                      $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%');
                  });
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            $dir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';

            if ($sort === 'klik') {
                $query->orderBy('total_clicks', $dir);
            } elseif ($sort === 'konversi') {
                $query->orderBy('total_conversions', $dir);
            } elseif ($sort === 'rate') {
                $query->orderByRaw('(CASE WHEN total_clicks = 0 THEN 0 ELSE total_conversions / total_clicks END) ' . $dir);
            } elseif ($sort === 'reward') {
                $query->orderBy('total_reward', $dir);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $perPage = $request->input('per_page', 20);
        $referrers = $query->paginate($perPage)->appends($request->query());

        return view('admin.referrers.index', compact('referrers', 'totalAfiliator', 'totalKlik', 'totalKonversi', 'persentaseKonversi'));
    }

    public function toggle(int $id)
    {
        $referrer = Referrer::findOrFail($id);
        $referrer->update([
            'status' => $referrer->status === 'active' ? 'inactive' : 'active',
        ]);

        $referrer->logs()->create([
            'acted_by' => auth()->id(),
            'action' => $referrer->status === 'active' ? 'toggled_active' : 'toggled_inactive',
            'note' => $referrer->status === 'active' ? 'Akun diaktifkan' : 'Akun dinonaktifkan',
        ]);

        $label = $referrer->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Referrer berhasil {$label}.");
    }

    public function updateBankAccount(Request $request, int $id)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ]);

        $referrer = Referrer::findOrFail($id);

        $old = [
            'bank_name' => $referrer->bank_name,
            'bank_account_number' => $referrer->bank_account_number,
            'bank_account_name' => $referrer->bank_account_name,
        ];

        $referrer->update([
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ]);

        $oldBankText = $old['bank_name'] || $old['bank_account_number'] || $old['bank_account_name'] 
            ? "{$old['bank_name']} {$old['bank_account_number']} a.n {$old['bank_account_name']}"
            : "belum ada data";

        $newBankText = "{$request->bank_name} {$request->bank_account_number} a.n {$request->bank_account_name}";

        $referrer->logs()->create([
            'acted_by' => auth()->id(),
            'action' => 'bank_updated',
            'note' => "Rekening diubah dari {$oldBankText} menjadi {$newBankText}",
        ]);

        return redirect()->back()->with('success', 'Rekening pencairan berhasil diperbarui.');
    }

    public function show(int $id)
    {
        $referrer = Referrer::with([
            'user', 
            'registrations' => function($query) {
                $query->latest();
            },
            'rewards' => function($query) {
                $query->with('registration')->latest();
            }
        ])->findOrFail($id);

        $logs = $referrer->logs()->with('actedBy')->paginate(15);

        return view('admin.referrers.show', compact('referrer', 'logs'));
    }

    public function export(Request $request)
    {
        $periodId = $request->query('period_id');
        $status   = $request->query('status');
        $filename = 'Data_Referrer_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\ReferrerExport($periodId, $status), $filename);
    }
}
