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

        return view('admin.rewards.index', compact('rewards'));
    }

    public function approve(int $id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya reward berstatus pending yang bisa disetujui.');
        }

        $reward->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Reward berhasil disetujui.');
    }

    public function disburse(int $id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->status !== 'approved') {
            return redirect()->back()->with('error', 'Hanya reward berstatus approved yang bisa dicairkan.');
        }

        $reward->update(['status' => 'disbursed']);

        return redirect()->back()->with('success', 'Reward berhasil dicairkan.');
    }
}
