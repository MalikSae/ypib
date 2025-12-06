<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Admin Statistics
        $totalMahasiswa = Mahasiswa::count();
        $pendingVerifikasi = Mahasiswa::where('status_pendaftaran', 'pending')->count();
        $mahasiswaDiterima = Mahasiswa::where('status_pendaftaran', 'diterima')->count();
        $pendingPembayaran = Pembayaran::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalMahasiswa',
            'pendingVerifikasi',
            'mahasiswaDiterima',
            'pendingPembayaran'
        ));
    }
}