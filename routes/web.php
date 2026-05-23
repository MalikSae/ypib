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
Route::get('/landing-preview', [LandingController::class, 'preview'])->name('landing.preview');

// ── Temporary Brand Visualization ──────────────────────────────────────────
Route::get('/brand', function () {
    return view('brand');
});
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
    Route::post('/pendaftaran/upload-daftar-ulang-bukti', [RegistrationController::class, 'uploadReRegistrationProof'])->name('registration.upload-re-registration-proof');
});

// ── Referrer Area (Afiliasi) ────────────────────────────────────────────────
Route::prefix('afiliasi')->name('referrer.')->group(function () {
    Route::get('/', [ReferrerController::class, 'index'])->name('index');
    Route::post('/', [ReferrerController::class, 'register'])->name('register');

    Route::middleware('auth')->group(function () {
        Route::post('/aktifkan', [ReferrerController::class, 'store'])->name('store');
        Route::get('/dashboard', [ReferrerController::class, 'dashboard'])->name('dashboard');
        Route::post('/bank', [ReferrerController::class, 'updateBank'])->name('bank.update');
    });
});

// ── Admin & Operator ────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Pengaturan PMB
        Route::get('/pengaturan', [\App\Http\Controllers\Admin\PmbPeriodController::class, 'index'])->name('periods.index');
        Route::put('/pengaturan/{id}', [\App\Http\Controllers\Admin\PmbPeriodController::class, 'update'])->name('periods.update');

        // Master Data
        Route::resource('faculties', \App\Http\Controllers\Admin\FacultyController::class)->except(['show']);
        Route::resource('programs', \App\Http\Controllers\Admin\ProgramController::class)->except(['show']);
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)->except(['show']);
        Route::patch('partners/{partner}/toggle', [\App\Http\Controllers\Admin\PartnerController::class, 'toggleActive'])->name('partners.toggle');
        Route::delete('programs/gallery/{id}', [\App\Http\Controllers\Admin\ProgramController::class, 'destroyGallery'])->name('programs.gallery.destroy');
        Route::resource('facilities', \App\Http\Controllers\Admin\FacilityController::class)->except(['show']);
        Route::patch('facilities/{facility}/toggle', [\App\Http\Controllers\Admin\FacilityController::class, 'toggleActive'])->name('facilities.toggle');

        Route::prefix('pendaftar')->name('registrations.')->group(function () {
            Route::get('/', [AdminRegistrationController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminRegistrationController::class, 'show'])->name('show');
            Route::post('/{id}/konfirmasi-bayar', [AdminRegistrationController::class, 'confirmPayment'])->name('confirm-payment');
            Route::post('/{id}/konfirmasi-daftar-ulang', [AdminRegistrationController::class, 'confirmReRegistration'])->name('confirm-re-registration');
            Route::post('/{id}/upload-bukti', [AdminRegistrationController::class, 'uploadBukti'])->name('upload-bukti');
            Route::post('/{id}/status', [AdminRegistrationController::class, 'updateStatus'])->name('update-status');
            Route::post('/{id}/catatan', [AdminRegistrationController::class, 'addNote'])->name('add-note');
        });

        // Referrer management
        Route::prefix('afiliasi')->name('referrers.')->group(function () {
            Route::get('/', [AdminReferrerController::class, 'index'])->name('index');
            Route::post('/{id}/toggle', [AdminReferrerController::class, 'toggle'])->name('toggle');
        });

        // Reward management
        Route::prefix('reward')->name('rewards.')->group(function () {
            Route::get('/', [AdminRewardController::class, 'index'])->name('index');
            Route::post('/mass-disburse', [AdminRewardController::class, 'massDisburse'])->name('mass-disburse');
            Route::post('/export', [AdminRewardController::class, 'exportCsv'])->name('export');
            Route::post('/{id}/approve', [AdminRewardController::class, 'approve'])->name('approve');
            Route::post('/{id}/disburse', [AdminRewardController::class, 'disburse'])->name('disburse');
            Route::post('/referrer/{referrer_id}/disburse', [AdminRewardController::class, 'disburseByReferrer'])->name('disburse.referrer');
            Route::post('/referrers/mass-disburse', [AdminRewardController::class, 'massDisburseReferrers'])->name('referrers.mass-disburse');
            Route::post('/referrers/export', [AdminRewardController::class, 'exportCsvReferrers'])->name('referrers.export');
        });
    });

require __DIR__.'/auth.php';
