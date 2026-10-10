<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function mail()
    {
        // Nilai API key tidak pernah dikirim ke browser; view hanya tahu apakah sudah tersimpan.
        $hasResendKey = !empty(Setting::getSecret('resend_api_key'));
$fromAddress = Setting::get('mail_from_address', 'no-reply@' . request()->getHost());
        $fromName = Setting::get('mail_from_name', config('app.name'));

        return view('admin.settings.mail', compact('hasResendKey', 'fromAddress', 'fromName'));
    }

    public function updateMail(Request $request)
    {
        $request->validate([
            'resend_api_key' => 'nullable|string|max:255',
            'clear_resend_api_key' => 'nullable|boolean',
'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // Kosong = pertahankan key lama; hapus hanya lewat opsi eksplisit.
        if ($request->boolean('clear_resend_api_key')) {
            Setting::setSecret('resend_api_key', null);
        } elseif ($request->filled('resend_api_key')) {
            Setting::setSecret('resend_api_key', trim($request->resend_api_key));
        }
Setting::set('mail_from_address', $request->mail_from_address);
        Setting::set('mail_from_name', $request->mail_from_name);

        return redirect()->back()->with('success', 'Pengaturan email berhasil disimpan.');
    }
}
