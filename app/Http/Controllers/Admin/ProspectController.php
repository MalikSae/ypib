<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectActivity;
use App\Models\ProspectEvent;
use App\Models\Referrer;
use App\Support\ProspectService;
use App\Support\QueryInput;
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

    public function index(Request $request)
    {
        $query = Prospect::query()
            ->with(['campus', 'program', 'referrer.user', 'sources.event', 'registration'])
            ->latest('id');

        $q = QueryInput::string($request, 'q');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('school_name', 'like', "%{$q}%")
                    ->orWhere('phone_normalized', 'like', "%{$q}%");
            });
        }

        $status = QueryInput::string($request, 'status');
        if (array_key_exists($status, Prospect::STATUS_LABELS)) {
            $query->where('status', $status);
        }

        $eventId = (int) QueryInput::string($request, 'event');
        if ($eventId) {
            $query->whereHas('sources', fn ($s) => $s->where('prospect_event_id', $eventId));
        }

        // Filter affiliator memakai id yang dipilih dari daftar saran (bukan teks bebas).
        $referrerId = (int) QueryInput::string($request, 'referrer');
        $selectedAffiliator = $referrerId ? Referrer::with('user')->find($referrerId) : null;
        if ($selectedAffiliator) {
            $query->where('referrer_id', $selectedAffiliator->id);
        }

        $prospects = $query->paginate(25)->withQueryString();

        $counts = Prospect::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.prospek.index', [
            'prospects' => $prospects,
            'counts'    => $counts,
            'events'    => ProspectEvent::orderByDesc('id')->get(),
            'selectedAffiliator' => $selectedAffiliator,
            'statuses'  => Prospect::STATUS_LABELS,
        ]);
    }

    /** Saran affiliator untuk filter: cocok nama atau kode, maksimal 10. */
    public function searchAffiliators(Request $request)
    {
        $q = QueryInput::string($request, 'q');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $items = Referrer::with('user')
            ->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            })
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->map(fn (Referrer $r) => [
                'id'    => $r->id,
                'label' => ($r->user?->name ?? 'Tanpa nama') . ' (' . $r->code . ')',
            ]);

        return response()->json($items);
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
