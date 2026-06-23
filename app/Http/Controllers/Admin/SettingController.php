<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function mail()
    {
        $resendKey = Setting::get('resend_api_key', '');
        $fromAddress = Setting::get('mail_from_address', 'no-reply@' . request()->getHost());
        $fromName = Setting::get('mail_from_name', config('app.name'));

        return view('admin.settings.mail', compact('resendKey', 'fromAddress', 'fromName'));
    }

    public function updateMail(Request $request)
    {
        $request->validate([
            'resend_api_key' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        Setting::set('resend_api_key', $request->resend_api_key);
        Setting::set('mail_from_address', $request->mail_from_address);
        Setting::set('mail_from_name', $request->mail_from_name);

        return redirect()->back()->with('success', 'Pengaturan email berhasil disimpan.');
    }
}
