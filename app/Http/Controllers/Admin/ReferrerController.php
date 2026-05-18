<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrer;

class ReferrerController extends Controller
{
    public function index()
    {
        $referrers = Referrer::with(['user', 'rewards', 'clicks'])
            ->latest()
            ->paginate(20);

        return view('admin.referrers.index', compact('referrers'));
    }

    public function toggle(int $id)
    {
        $referrer = Referrer::findOrFail($id);
        $referrer->update([
            'status' => $referrer->status === 'active' ? 'inactive' : 'active',
        ]);

        $label = $referrer->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Referrer berhasil {$label}.");
    }
}
