<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectActivity;
use App\Support\ProspectService;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    /** Status yang bisa diatur admin. "Sudah daftar" hanya terisi otomatis saat registrasi. */
    private const ADMIN_STATUSES = [
        Prospect::STATUS_BARU,
        Prospect::STATUS_DIHUBUNGI,
        Prospect::STATUS_TERTARIK,
        Prospect::STATUS_TIDAK_TERTARIK,
    ];

    /** Daftar, pencarian, dan filter ditangani komponen Livewire App\Livewire\Admin\ProspectList. */
    public function index()
    {
        return view('admin.prospek.index');
    }

    public function updateStatus(Request $request, Prospect $prospect)
    {
        if ($prospect->isRegistered()) {
            return back()->with('error', 'Prospek yang sudah mendaftar tidak bisa diubah statusnya di sini.');
        }

        // Catatan admin dicatat di riwayat (form catatan di detail), bukan lewat form status.
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::ADMIN_STATUSES)],
        ]);

        $changes = ['status' => $data['status']];
        if ($data['status'] !== Prospect::STATUS_BARU && !$prospect->last_contacted_at) {
            $changes['last_contacted_at'] = now();
        }

        $oldStatus = $prospect->status;
        $prospect->forceFill($changes)->save();

        if ($oldStatus !== $prospect->status) {
            $this->logActivity($prospect, ProspectActivity::TYPE_STATUS,
                'Status: ' . (Prospect::STATUS_LABELS[$oldStatus]['label'] ?? $oldStatus) . ' → ' . Prospect::STATUS_LABELS[$prospect->status]['label']);
        }

        return back()->with('success', 'Status prospek diperbarui.');
    }

    public function show(Prospect $prospect)
    {
        $prospect->load(['campus', 'program', 'referrer.user', 'sources.event', 'registration', 'activities.user']);

        return view('admin.prospek.show', [
            'prospect'      => $prospect,
            'statuses'      => Prospect::STATUS_LABELS,
            'adminStatuses' => self::ADMIN_STATUSES,
            'chatUrl'       => ProspectService::prospectChatUrl($prospect, $prospect->campus),
        ]);
    }

    /**
     * Satu form untuk catatan. Dengan "sudah dihubungi" dicentang: catat kontak dan ubah status baru
     * menjadi sudah dihubungi. Tanpa centang: hanya catatan.
     */
    public function contact(Request $request, Prospect $prospect)
    {
        if ($prospect->isRegistered()) {
            return back()->with('error', 'Prospek ini sudah mendaftar.');
        }

        $data = $request->validate([
            'note'      => ['nullable', 'string', 'max:1000'],
            'dihubungi' => ['nullable', 'boolean'],
        ]);
        $note = $data['note'] ?? null;
        $contacted = $request->boolean('dihubungi');

        if (!$contacted && blank($note)) {
            return back()->withErrors(['note' => 'Isi catatan atau centang sudah dihubungi.'])->withInput();
        }

        if ($contacted) {
            $prospect->markContacted();

            $this->logActivity($prospect, ProspectActivity::TYPE_KONTAK, $note);

            return back()->with('success', 'Kontak dicatat.');
        }

        $this->logActivity($prospect, ProspectActivity::TYPE_CATATAN, $note);

        return back()->with('success', 'Catatan disimpan.');
    }

    private function logActivity(Prospect $prospect, string $type, ?string $note): void
    {
        ProspectActivity::create([
            'prospect_id' => $prospect->id,
            'user_id'     => auth()->id(),
            'type'        => $type,
            'note'        => $note,
        ]);
    }

    /** Kirim ulang link daftar: pakai link yang masih berlaku atau buat baru, lalu tampilkan tautan WA. */
    public function resendLink(Prospect $prospect)
    {
        if ($prospect->isRegistered()) {
            return back()->with('error', 'Prospek ini sudah mendaftar. Link tidak diperlukan lagi.');
        }

        $event = $prospect->sources()->first()?->event;
        $link = ProspectService::linkFor($prospect, $event);
        $linkUrl = route('prospect.link', $link->token);

        return back()->with('success', 'Link daftar siap dikirim.')
            ->with('prospect_link', [
                'name' => $prospect->full_name,
                'url'  => $linkUrl,
                'wa'   => ProspectService::prospectWhatsappUrl($prospect, $prospect->campus, $linkUrl),
            ]);
    }
}
