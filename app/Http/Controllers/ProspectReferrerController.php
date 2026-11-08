<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\Referrer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Aksi affiliator atas prospek miliknya: catatan internal dan tandai sudah dihubungi. */
class ProspectReferrerController extends Controller
{
    public function note(Request $request, Prospect $prospect)
    {
        $this->ownProspect($prospect);

        $data = $request->validate([
            'referrer_notes' => ['required', 'string', 'max:1000'],
        ]);

        $prospect->update(['referrer_notes' => $data['referrer_notes']]);

        return back()->with('success', 'Catatan disimpan.');
    }

    /** Affiliator hanya menandai sudah dihubungi; status akhir tetap dari admin. */
    public function contacted(Prospect $prospect)
    {
        $this->ownProspect($prospect);

        if ($prospect->isRegistered()) {
            return back()->with('error', 'Prospek ini sudah mendaftar.');
        }

        $prospect->forceFill([
            'last_contacted_at' => now(),
            'status'            => $prospect->status === Prospect::STATUS_BARU
                ? Prospect::STATUS_DIHUBUNGI
                : $prospect->status,
        ])->save();

        return back()->with('success', 'Prospek ditandai sudah dihubungi.');
    }

    private function ownProspect(Prospect $prospect): Referrer
    {
        $referrer = Referrer::where('user_id', Auth::id())->firstOrFail();
        abort_unless($prospect->referrer_id === $referrer->id, 404);

        return $referrer;
    }
}
