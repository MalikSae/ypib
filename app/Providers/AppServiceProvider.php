<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Pendaftaran akun / afiliasi / lupa password: batasi per IP. Angka cukup longgar
        // untuk satu kelas di jaringan sekolah (NAT), tetapi menghentikan pembuatan akun massal.
        \Illuminate\Support\Facades\RateLimiter::for('public-signup', function (\Illuminate\Http\Request $request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perHour(60)->by($request->ip()),
            ];
        });

        try {
            if (Schema::hasTable('settings')) {
                $resendKey = \App\Models\Setting::getSecret('resend_api_key');
                if (!empty($resendKey)) {
                    \Illuminate\Support\Facades\Config::set('services.resend.key', $resendKey);
                    \Illuminate\Support\Facades\Config::set('mail.default', 'resend');
                    
                    $fromAddress = \App\Models\Setting::get('mail_from_address');
                    if (!empty($fromAddress)) {
                        \Illuminate\Support\Facades\Config::set('mail.from.address', $fromAddress);
                        \Illuminate\Support\Facades\Config::set('mail.from.name', \App\Models\Setting::get('mail_from_name', config('app.name')));
                    }
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika database belum disiapkan
        }
    }
}
