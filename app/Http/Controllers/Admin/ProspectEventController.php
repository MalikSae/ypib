<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\ProspectEvent;
use Illuminate\Http\Request;

/** Event sosialisasi dan potongan biaya daftar ulang untuk link form minat. */
class ProspectEventController extends Controller
{
    public function index()
    {
        $events = ProspectEvent::with(['campus', 'period'])
            ->withCount('sources')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.prospek.event-index', compact('events'));
    }

    public function create()
    {
        return view('admin.prospek.event-form', $this->formData(new ProspectEvent()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = ProspectEvent::makeSlug($data['name']);

        ProspectEvent::create($data);

        return redirect()->route('admin.prospects.events.index')
            ->with('success', 'Event berhasil dibuat. Link form minat siap dibagikan.');
    }

    public function edit(ProspectEvent $event)
    {
        return view('admin.prospek.event-form', $this->formData($event));
    }

    public function update(Request $request, ProspectEvent $event)
    {
        $event->update($this->validated($request));

        return redirect()->route('admin.prospects.events.index')
            ->with('success', 'Event diperbarui. Potongan baru berlaku untuk link yang dibuat setelah ini.');
    }

    /** Unduh QR code berisi link form minat (PNG, untuk dicetak di materi sosialisasi). */
    public function qr(ProspectEvent $event)
    {
        $qrWriter = new \Endroid\QrCode\Writer\PngWriter();
        $qrCode = \Endroid\QrCode\QrCode::create(route('prospect.show', $event->slug))
            ->setSize(600)
            ->setMargin(20);

        return response($qrWriter->write($qrCode)->getString(), 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-' . $event->slug . '.png"',
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'campus_id'       => ['required', 'exists:campuses,id'],
            'kind'            => ['required', 'in:' . implode(',', array_keys(ProspectEvent::KIND_LABELS))],
            'period_id'       => ['nullable', 'exists:pmb_periods,id'],
            'name'            => ['required', 'string', 'max:255'],
            'school_name'     => ['nullable', 'string', 'max:255'],
            'event_date'      => ['nullable', 'date'],
            'discount_amount' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'is_active'       => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Sekolah dan tanggal hanya relevan untuk event kampus.
        if ($data['kind'] === ProspectEvent::KIND_ONLINE) {
            $data['school_name'] = null;
            $data['event_date'] = null;
        }
        $data['period_id'] = $data['period_id'] ?? null;

        return $data;
    }

    private function formData(ProspectEvent $event): array
    {
        return [
            'event'     => $event,
            'kinds'     => ProspectEvent::KIND_LABELS,
            'campuses'  => Campus::active()->orderByDesc('is_default')->orderBy('name')->get(),
            'periods'   => PmbPeriod::active()->orderByDesc('open_date')->get(),
        ];
    }
}
