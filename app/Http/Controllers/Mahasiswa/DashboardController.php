<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $pengumumans = Pengumuman::where('is_active', true)
                                 ->latest()
                                 ->take(5)
                                 ->get();

        return view('mahasiswa.dashboard', compact('mahasiswa', 'pengumumans'));
    }
}