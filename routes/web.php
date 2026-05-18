<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\ReferrerController as AdminReferrerController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferrerController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// ── Public Landing ─────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/prodi/{slug}', [LandingController::class, 'prodi'])->name('prodi.show');

// ── Referral Tracking (public, tanpa auth) ──────────────────────────────────
Route::get('/ref/{code}', [ReferralController::class, 'track'])->name('referral.track');

// ── Breeze Dashboard (keep for compatibility) ───────────────────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (in_array($user->role, ['admin', 'operator'])) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->is_referrer) {
        return redirect()->route('referrer.dashboard');
    }
    return redirect()->route('registration.status');
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Auth Profile ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Mahasiswa ───────────────────────────────────────────────────────────────
Route::get('/daftar', [RegistrationController::class, 'create'])->name('registration.create');

Route::middleware('auth')->group(function () {
    Route::get('/pendaftaran', [RegistrationController::class, 'index'])->name('registration.index');
    Route::get('/pendaftaran/status', [RegistrationController::class, 'status'])->name('registration.status');
    Route::post('/pendaftaran/upload-bukti', [RegistrationController::class, 'uploadProof'])->name('registration.upload-proof');
});

// ── Referrer Area (auth, semua role bisa daftar jadi referrer) ───────────────
Route::middleware('auth')->prefix('referrer')->name('referrer.')->group(function () {
    Route::get('/daftar', [ReferrerController::class, 'create'])->name('create');
    Route::post('/daftar', [ReferrerController::class, 'store'])->name('store');
    Route::get('/dashboard', [ReferrerController::class, 'dashboard'])->name('dashboard');
});

// ── Admin & Operator ────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('pendaftar')->name('registrations.')->group(function () {
            Route::get('/', [AdminRegistrationController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminRegistrationController::class, 'show'])->name('show');
            Route::post('/{id}/konfirmasi-bayar', [AdminRegistrationController::class, 'confirmPayment'])->name('confirm-payment');
            Route::post('/{id}/upload-bukti', [AdminRegistrationController::class, 'uploadBukti'])->name('upload-bukti');
            Route::post('/{id}/status', [AdminRegistrationController::class, 'updateStatus'])->name('update-status');
            Route::post('/{id}/catatan', [AdminRegistrationController::class, 'addNote'])->name('add-note');
        });

        // Referrer management
        Route::prefix('referrer')->name('referrers.')->group(function () {
            Route::get('/', [AdminReferrerController::class, 'index'])->name('index');
            Route::post('/{id}/toggle', [AdminReferrerController::class, 'toggle'])->name('toggle');
        });

        // Reward management
        Route::prefix('reward')->name('rewards.')->group(function () {
            Route::get('/', [AdminRewardController::class, 'index'])->name('index');
            Route::post('/{id}/approve', [AdminRewardController::class, 'approve'])->name('approve');
            Route::post('/{id}/disburse', [AdminRewardController::class, 'disburse'])->name('disburse');
        });
    });

require __DIR__.'/auth.php';
