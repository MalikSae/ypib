<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmbPeriod;
use Illuminate\Http\Request;

class PmbPeriodController extends Controller
{
    public function index()
    {
        $period = PmbPeriod::active()->first();

        // Jika belum ada periode aktif, buatkan satu yang kosong untuk diisi
        if (!$period) {
            $period = PmbPeriod::create([
                'name'                  => 'PMB Baru',
                'year'                  => date('Y'),
                'open_date'             => date('Y-m-d'),
                'close_date'            => date('Y-m-t'),
                'is_active'             => true,
            ]);
        }

        return view('admin.periods.index', compact('period'));
    }

    public function update(Request $request, $id)
    {
        $period = PmbPeriod::findOrFail($id);

        $request->validate([
            'name'                          => 'required|string|max:255',
            'year'                          => 'required|integer',
            'open_date'                     => 'required|date',
            'close_date'                    => 'required|date|after_or_equal:open_date',
            'university_bank_name'          => 'nullable|string|max:255',
            'university_bank_account'       => 'nullable|string|max:255',
            'university_bank_account_name'  => 'nullable|string|max:255',
            'admin_whatsapp'                => 'nullable|string|max:20',
        ]);

        $period->update($request->all());

        return redirect()->route('admin.periods.index')->with('success', 'Pengaturan PMB berhasil diperbarui.');
    }
}
