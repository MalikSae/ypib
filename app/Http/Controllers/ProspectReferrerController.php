<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\ProspectActivity;
use App\Models\Referrer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Aksi affiliator atas prospek miliknya: catatan dan tandai sudah dihubungi. Keduanya tercatat
 * di riwayat prospek (sama dengan riwayat admin). Status akhir tetap diatur admin.
 */
class ProspectReferrerController extends Controller
{
    public function note(Request $request, Prospect $prospect)
    {
        $this->ownProspect($prospect);

        $data = $request->validate([
            'referrer_notes' => ['required', 'string', 'max:1000'],
        ]);

        // referrer_notes menyimpan catatan terakhir untuk tampilan cepat; riwayat lengkap di activities.
        $prospect->update(['referrer_notes' => $data['referrer_notes']]);
        $this->log($prospect, ProspectActivity::TYPE_CATATAN, $data['referrer_notes']);

        return back()->with('success', 'Catatan disimpan.');
    }

    public function contacted(Prospect $prospect)
    {
        $this->ownProspect($prospect);

        if ($prospect->isRegistered()) {
            return back()->with('error', 'Prospek ini sudah mendaftar.');
        }

        $prospect->markContacted();
        $this->log($prospect, ProspectActivity::TYPE_KONTAK, 'Dihubungi oleh affiliator.');

        return back()->with('success', 'Prospek ditandai sudah dihubungi.');
    }

    private function ownProspect(Prospect $prospect): Referrer
    {
        $referrer = Referrer::where('user_id', Auth::id())->firstOrFail();
        abort_unless((int) $prospect->referrer_id === (int) $referrer->id, 404);

        return $referrer;
    }

    private function log(Prospect $prospect, string $type, ?string $note): void
    {
        ProspectActivity::create([
            'prospect_id' => $prospect->id,
            'user_id'     => Auth::id(),
            'type'        => $type,
            'note'        => $note,
        ]);
    }
}
