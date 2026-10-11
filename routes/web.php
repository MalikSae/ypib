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
Route::get('/', [LandingController::class, 'preview'])->name('landing');

Route::get('/prodi/{slug}', [LandingController::class, 'prodi'])->name('prodi.show');

// ── Referral Tracking (public, tanpa auth) ──────────────────────────────────
Route::get('/ref/{code}', [ReferralController::class, 'track'])
    ->middleware('throttle:60,1')
    ->name('referral.track');

// ── Breeze Dashboard (keep for compatibility) ───────────────────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (in_array($user->role, ['admin', 'operator'])) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'panitia') {
        return redirect()->route('panitia.dashboard');
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
    Route::post('/pendaftaran/upload-kartu-alumni', [RegistrationController::class, 'uploadAlumniCard'])->name('registration.upload-alumni-card');
    Route::post('/pendaftaran/upload-daftar-ulang-bukti', [RegistrationController::class, 'uploadReRegistrationProof'])->name('registration.upload-re-registration-proof');
    Route::get('/pendaftaran/dokumen', [RegistrationController::class, 'documents'])->name('registration.documents');
    Route::get('/pendaftaran/daftar-ulang', [RegistrationController::class, 'reRegistration'])->name('registration.re-registration');
    Route::post('/pendaftaran/dokumen/upload', [RegistrationController::class, 'uploadDocumentFile'])->name('registration.upload-document-file');
    Route::get('/pendaftaran/detail', [RegistrationController::class, 'detail'])->name('registration.detail');
    Route::post('/pendaftaran/detail/update', [RegistrationController::class, 'updateDetail'])->name('registration.detail.update');
    Route::get('/pendaftaran/cetak', [RegistrationController::class, 'downloadPdf'])->name('registration.pdf');
    Route::get('/pendaftaran/surat-kelulusan', [RegistrationController::class, 'downloadSkl'])->name('registration.skl');
    Route::get('/pendaftaran/e-ktm/download', [RegistrationController::class, 'downloadEktm'])->name('registration.ektm');
    Route::get('/pendaftaran/tes-tulis', [RegistrationController::class, 'exam'])->name('registration.exam');
    Route::get('/pendaftaran/interview', [RegistrationController::class, 'interview'])->name('registration.interview');
    Route::post('/pendaftaran/tes-tulis/jawab', [RegistrationController::class, 'saveExamAnswer'])->name('registration.exam.save-answer');
    Route::post('/pendaftaran/tes-tulis/selesai', [RegistrationController::class, 'submitExam'])->name('registration.exam.submit');
});

// ── Berkas Pendaftar (terotorisasi) ────────────────────────────────────────
Route::middleware('auth')->prefix('berkas')->name('files.')->group(function () {
    Route::get('/registrasi/{id}/{field}', [\App\Http\Controllers\FileController::class, 'registration'])
        ->whereNumber('id')->name('registration');
    Route::get('/dokumen/{id}', [\App\Http\Controllers\FileController::class, 'document'])
        ->whereNumber('id')->name('document');
});

// ── Referrer Area (Afiliasi) ────────────────────────────────────────────────
Route::prefix('afiliasi')->name('referrer.')->group(function () {
    Route::get('/', [ReferrerController::class, 'index'])->name('index');
    Route::post('/', [ReferrerController::class, 'register'])
        ->middleware('throttle:public-signup')
        ->name('register');

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

        // Periode PMB
        Route::redirect('/pengaturan', '/admin/periode');
        Route::prefix('periode')->name('periods.')->group(function () {
            $c = \App\Http\Controllers\Admin\PmbPeriodController::class;

            Route::get('/', [$c, 'index'])->name('index');
            Route::get('/buat', [$c, 'create'])->name('create');
            Route::post('/', [$c, 'store'])->name('store');
            Route::post('/pilih', [$c, 'select'])->name('select');
            Route::get('/{id}/edit', [$c, 'edit'])->whereNumber('id')->name('edit');
            Route::put('/{id}', [$c, 'update'])->whereNumber('id')->name('update');
            Route::post('/{id}/tayangkan', [$c, 'publish'])->whereNumber('id')->name('publish');
            Route::post('/{id}/batalkan-tayang', [$c, 'unpublish'])->whereNumber('id')->name('unpublish');
            Route::get('/{id}/arsip', [$c, 'archiveConfirm'])->whereNumber('id')->name('archive.confirm');
            Route::post('/{id}/arsip', [$c, 'archive'])->whereNumber('id')->name('archive');
            Route::post('/{id}/buka-kembali', [$c, 'reopen'])->whereNumber('id')->name('reopen');
            Route::post('/{id}/salin', [$c, 'duplicate'])->whereNumber('id')->name('duplicate');

            // Biaya, kuota, dan komisi per prodi pada periode ini (K6).
            $p = \App\Http\Controllers\Admin\PeriodProgramController::class;
            Route::get('/{id}/biaya', [$p, 'edit'])->whereNumber('id')->name('pricing');
            Route::put('/{id}/biaya', [$p, 'update'])->whereNumber('id')->name('pricing.update');
            Route::post('/{id}/biaya/salin', [$p, 'copy'])->whereNumber('id')->name('pricing.copy');
        });

        // Pengaturan Email
        Route::get('/pengaturan-email', [\App\Http\Controllers\Admin\SettingController::class, 'mail'])->name('settings.mail');
        Route::put('/pengaturan-email', [\App\Http\Controllers\Admin\SettingController::class, 'updateMail'])->name('settings.mail.update');

        // Master Data
        Route::resource('faculties', \App\Http\Controllers\Admin\FacultyController::class)->except(['show']);
        Route::resource('programs', \App\Http\Controllers\Admin\ProgramController::class)->except(['show']);
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)->except(['show']);
        Route::patch('partners/{partner}/toggle', [\App\Http\Controllers\Admin\PartnerController::class, 'toggleActive'])->name('partners.toggle');
        Route::delete('programs/gallery/{id}', [\App\Http\Controllers\Admin\ProgramController::class, 'destroyGallery'])->name('programs.gallery.destroy');
        Route::resource('facilities', \App\Http\Controllers\Admin\FacilityController::class)->except(['show']);
        Route::patch('facilities/{facility}/toggle', [\App\Http\Controllers\Admin\FacilityController::class, 'toggleActive'])->name('facilities.toggle');
        Route::resource('bank-soal', \App\Http\Controllers\Admin\ExamQuestionController::class)->except(['show']);
        Route::resource('panitia', \App\Http\Controllers\Admin\PanitiaController::class)->except(['show']);

        Route::prefix('pendaftar')->name('registrations.')->group(function () {
            Route::get('/', [AdminRegistrationController::class, 'index'])->name('index');
            Route::get('/export', [AdminRegistrationController::class, 'export'])->name('export');
            Route::get('/rekap', [AdminRegistrationController::class, 'recap'])->name('recap');
            Route::get('/rekap/export', [AdminRegistrationController::class, 'exportRecap'])->name('recap.export');
            Route::get('/sampah', [AdminRegistrationController::class, 'trash'])->name('trash');
            Route::get('/{registration}', [AdminRegistrationController::class, 'show'])->whereNumber('registration')->name('show');
            Route::post('/{id}/update-data', [AdminRegistrationController::class, 'updateData'])->name('update-data');
            Route::post('/{id}/reset-tes-tulis', [AdminRegistrationController::class, 'resetExam'])->name('reset-exam');
            Route::delete('/{id}', [AdminRegistrationController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [AdminRegistrationController::class, 'restore'])->name('restore');
            Route::post('/{id}/konfirmasi-bayar', [AdminRegistrationController::class, 'confirmPayment'])->name('confirm-payment');
            Route::post('/{id}/konfirmasi-daftar-ulang', [AdminRegistrationController::class, 'confirmReRegistration'])->name('confirm-re-registration');
            Route::post('/{id}/override-status', [AdminRegistrationController::class, 'overrideStatus'])->name('override-status');
            Route::post('/{id}/upload-bukti', [AdminRegistrationController::class, 'uploadBukti'])->name('upload-bukti');
            Route::post('/{id}/catatan', [AdminRegistrationController::class, 'addNote'])->name('add-note');
            Route::post('/{id}/referral', [AdminRegistrationController::class, 'updateReferral'])->name('update-referral');
            Route::post('/document/{documentId}/review', [AdminRegistrationController::class, 'reviewDocument'])->name('review-document');
            Route::post('/{id}/documents/bulk-review', [AdminRegistrationController::class, 'bulkReviewDocuments'])->name('bulk-review-documents');
            Route::post('/{id}/document/upload', [AdminRegistrationController::class, 'uploadDocument'])->name('upload-document');
        });

        // Referrer management
        Route::prefix('afiliasi')->name('referrers.')->group(function () {
            Route::get('/', [AdminReferrerController::class, 'index'])->name('index');
            Route::get('/export', [AdminReferrerController::class, 'export'])->name('export');
            Route::get('/{id}', [AdminReferrerController::class, 'show'])->name('show');
            Route::post('/{id}/toggle', [AdminReferrerController::class, 'toggle'])->name('toggle');
            Route::post('/{id}/rekening', [AdminReferrerController::class, 'updateBankAccount'])->name('update-bank');
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
            Route::get('/referrer/{referrer_id}/approved-rewards', [AdminRewardController::class, 'approvedRewardsByReferrer'])->name('approved-rewards');
            Route::post('/disburse-selected', [AdminRewardController::class, 'disburseSelected'])->name('disburse-selected');
        });

        // User management
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
        
        // Komponen Showcase
        Route::get('/komponen', [\App\Http\Controllers\Admin\ComponentShowcaseController::class, 'index'])->name('komponen');
    });

// ── Panitia PMB ─────────────────────────────────────────────────────────────
Route::prefix('panitia')
    ->middleware(['auth', 'role:panitia'])
    ->name('panitia.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Panitia\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/scan/{registrationNumber}', [\App\Http\Controllers\Panitia\DashboardController::class, 'show'])->name('scan.show');
        Route::post('/scan/{registrationNumber}/selesai', [\App\Http\Controllers\Panitia\DashboardController::class, 'complete'])->name('scan.complete');
    });

require __DIR__.'/auth.php';
