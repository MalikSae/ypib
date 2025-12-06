<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::where('is_active', true)
                                 ->latest()
                                 ->paginate(10);

        return view('mahasiswa.pengumuman.index', compact('pengumumans'));
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('mahasiswa.pengumuman.show', compact('pengumuman'));
    }
}