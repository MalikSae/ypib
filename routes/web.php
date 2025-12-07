<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Controllers Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MahasiswaController as AdminMahasiswaController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PengumumanController as AdminPengumumanController;
use App\Http\Controllers\Admin\JurusanController as AdminJurusanController;
use App\Http\Controllers\Admin\AkunController as AdminAkunController;

// Controllers Mahasiswa
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\PendaftaranController;
use App\Http\Controllers\Mahasiswa\PembayaranController as MahasiswaPembayaranController;
use App\Http\Controllers\Mahasiswa\PengumumanController as MahasiswaPengumumanController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
use App\Models\Mahasiswa;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('mahasiswa.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Kelola Akun User (Verifikasi Pendaftaran Akun)
    Route::get('/akun', [AdminAkunController::class, 'index'])->name('akun.index');
    Route::post('/akun/{id}/verifikasi', [AdminAkunController::class, 'verifikasi'])->name('akun.verifikasi');
    Route::delete('/akun/{id}', [AdminAkunController::class, 'destroy'])->name('akun.destroy');
    
    // Kelola Mahasiswa
    Route::get('/mahasiswa', [AdminMahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{id}', [AdminMahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::post('/mahasiswa/{id}/verifikasi', [AdminMahasiswaController::class, 'verifikasi'])->name('mahasiswa.verifikasi');
    Route::patch('/mahasiswa/{id}/status', [AdminMahasiswaController::class, 'updateStatus'])->name('mahasiswa.updateStatus');
    Route::delete('/mahasiswa/{id}', [AdminMahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    
    // Kelola Pembayaran
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('/pembayaran/{id}', [AdminPembayaranController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{id}/verifikasi', [AdminPembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::delete('/pembayaran/{id}', [AdminPembayaranController::class, 'destroy'])->name('pembayaran.destroy');
    
    // Kelola Pengumuman
    Route::get('/pengumuman', [AdminPengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [AdminPengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [AdminPengumumanController::class, 'store'])->name('pengumuman.store');
    Route::get('/pengumuman/{id}/edit', [AdminPengumumanController::class, 'edit'])->name('pengumuman.edit');
    Route::patch('/pengumuman/{id}', [AdminPengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{id}', [AdminPengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    
    // Kelola Jurusan
    Route::get('/jurusan', [AdminJurusanController::class, 'index'])->name('jurusan.index');
    Route::get('/jurusan/create', [AdminJurusanController::class, 'create'])->name('jurusan.create');
    Route::post('/jurusan', [AdminJurusanController::class, 'store'])->name('jurusan.store');
    Route::get('/jurusan/{id}/edit', [AdminJurusanController::class, 'edit'])->name('jurusan.edit');
    Route::patch('/jurusan/{id}', [AdminJurusanController::class, 'update'])->name('jurusan.update');
    Route::delete('/jurusan/{id}', [AdminJurusanController::class, 'destroy'])->name('jurusan.destroy');
});

/*
|--------------------------------------------------------------------------
| Mahasiswa Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    
    // Dashboard Mahasiswa
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    
    // Pendaftaran
    Route::get('/pendaftaran', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    Route::get('/pendaftaran/status', [PendaftaranController::class, 'status'])->name('pendaftaran.status');
    
    // Pembayaran
    Route::get('/pembayaran', [MahasiswaPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('/pembayaran/create', [MahasiswaPembayaranController::class, 'create'])->name('pembayaran.create');
    Route::post('/pembayaran', [MahasiswaPembayaranController::class, 'store'])->name('pembayaran.store');
    Route::get('/pembayaran/{id}', [MahasiswaPembayaranController::class, 'show'])->name('pembayaran.show');
    
    // Pengumuman
    Route::get('/pengumuman', [MahasiswaPengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/{id}', [MahasiswaPengumumanController::class, 'show'])->name('pengumuman.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';