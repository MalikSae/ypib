<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrer;

use Illuminate\Http\Request;

class ReferrerController extends Controller
{
    public function index(Request $request)
    {
        $query = Referrer::with(['user', 'rewards', 'clicks'])->latest();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('code', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function($q) use ($searchTerm) {
                      $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%');
                  });
        }

        $perPage = $request->input('per_page', 20);
        $referrers = $query->paginate($perPage)->appends($request->query());

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
