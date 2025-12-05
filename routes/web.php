<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\Admin\JurusanController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Calon Mahasiswa Routes
    Route::middleware('role:calon_mahasiswa')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/pendaftaran', [MahasiswaController::class, 'create'])->name('pendaftaran.create');
        Route::post('/pendaftaran', [MahasiswaController::class, 'store'])->name('pendaftaran.store');
        Route::get('/pendaftaran/{mahasiswa}/edit', [MahasiswaController::class, 'edit'])->name('pendaftaran.edit');
        Route::put('/pendaftaran/{mahasiswa}', [MahasiswaController::class, 'update'])->name('pendaftaran.update');
        Route::post('/pendaftaran/{mahasiswa}/submit', [MahasiswaController::class, 'submit'])->name('pendaftaran.submit');
        
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
        
        Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    });
    
  
});