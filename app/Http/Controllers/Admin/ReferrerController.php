<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referrer;

use Illuminate\Http\Request;

class ReferrerController extends Controller
{
    public function index(Request $request)
    {
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

        return view('admin.referrers.show', compact('referrer'));
    }
}
