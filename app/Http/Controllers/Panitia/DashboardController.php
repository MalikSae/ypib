<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** Riwayat interview dan pencariannya ditangani komponen Livewire App\Livewire\Panitia\InterviewHistory. */
    public function index()
    {
        return view('panitia.dashboard');
    }

    public function show($registrationNumber)
    {
        $registration = Registration::with(['firstChoiceProgram', 'documents', 'examSession.answers.question'])
            ->where('registration_number', $registrationNumber)
            ->first();

        if (!$registration) {
            return redirect()->route('panitia.dashboard')->with('error', 'Nomor pendaftaran tidak ditemukan.');
        }

        if (!$registration->canBeInterviewed()) {
            return view('panitia.invalid-status', [
                'registration' => $registration,
                'statusLabel' => $registration->getStatusLabel()
            ]);
        }

        return view('panitia.candidate-detail', compact('registration'));
    }

    public function complete(Request $request, $registrationNumber)
    {
        $request->validate([
            'hasil' => 'required|in:diterima,ditolak',
            'catatan' => 'nullable|string|max:1000'
        ]);

        $registration = Registration::where('registration_number', $registrationNumber)->firstOrFail();

        if (!$registration->canBeInterviewed()) {
            return redirect()->route('panitia.dashboard')->with('error', 'Pendaftar ini sudah diproses atau tidak valid.');
        }

        $catatanNote = $request->catatan ? $request->catatan : '-';

        \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $request, $catatanNote) {
            $registration->status = $request->hasil;

            if ($request->hasil === 'diterima' && empty($registration->letter_number)) {
                $registration->letter_number = $registration->generateLetterNumber();
            }
            
            $internalNotes = $registration->internal_notes ? $registration->internal_notes . "\n\n" : "";
            $internalNotes .= "[Interview] Diproses oleh " . Auth::user()->name . " pada " . now()->format('d M Y H:i:s') . "\nCatatan: " . $catatanNote;
            
            $registration->internal_notes = $internalNotes;
            $registration->save();

            PaymentLog::create([
                'registration_id' => $registration->id,
                'acted_by' => Auth::id(),
                'action' => 'interview_completed',
                'note' => 'Interview diputuskan: ' . strtoupper($request->hasil) . '. Catatan: ' . $catatanNote
            ]);
        });

        return redirect()->route('panitia.dashboard')->with('success', 'Interview untuk ' . $registration->full_name . ' selesai diproses: ' . strtoupper($request->hasil) . '.');
    }
}
