<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'           => Registration::count(),
            'pending_payment' => Registration::where('status', 'payment_pending')->count(),
            'pending_verify'  => Registration::whereIn('status', ['payment_confirmed', 'document_pending'])->count(),
            'accepted'        => Registration::where('status', 'accepted')->count(),
        ];

        $recent = Registration::with(['user', 'firstChoiceProgram'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent'));
    }
}
