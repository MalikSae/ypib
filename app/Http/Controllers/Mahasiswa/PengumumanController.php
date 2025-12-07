<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengumuman::where('is_active', true);

        // Filter berdasarkan kategori jika ada
        if ($request->has('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        $pengumumans = $query->latest()->paginate(10);

        return view('mahasiswa.pengumuman.index', compact('pengumumans'));
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('mahasiswa.pengumuman.show', compact('pengumuman'));
    }
}