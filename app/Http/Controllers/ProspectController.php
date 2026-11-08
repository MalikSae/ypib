<?php

namespace App\Http\Controllers;

use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectLink;
use App\Models\Referrer;
use App\Models\Scopes\CampusScope;
use App\Support\ProspectService;
use App\Support\QueryInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Form minat publik (calon mahasiswa yang belum memutuskan daftar) dan link daftar terkunci. */
class ProspectController extends Controller
{
    private const SESSION_THANKS = 'prospect_thanks';

    public function show(Request $request, string $eventSlug)
    {
        $event = $this->openEvent($eventSlug);

        if (!$event) {
            return redirect()->route('landing')->with('error', 'Form minat untuk kegiatan ini tidak tersedia.');
        }

        $period = $event->period ?? PmbPeriod::displayPeriod();
        $programs = Program::offeredIn($period, $event->campus_id)->orderBy('name')->get();

        return view('prospek.minat', [
            'event'    => $event,
            'campus'   => $event->campus,
            'programs' => $programs,
            // Affiliator hanya lewat kampanye online. Event kampus tidak pernah punya pemilik.
            'ref'      => $event->isOnline() ? $this->activeReferrer($request)?->code : null,
        ]);
    }

    public function store(Request $request, string $eventSlug)
    {
        $event = $this->openEvent($eventSlug);

        if (!$event) {
            return redirect()->route('landing')->with('error', 'Form minat untuk kegiatan ini tidak tersedia.');
        }

        $period = $event->period ?? PmbPeriod::displayPeriod();

        $validator = Validator::make($request->all(), [
            'full_name'  => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'education_level' => ['required', Rule::in(array_keys(\App\Models\Registration::EDUCATION_LEVELS))],
            'phone'      => ['required', 'string', 'max:20'],
            // Kosong = "belum menentukan prodi".
            'program_id' => [
                'nullable', 'integer',
                Rule::exists('programs', 'id')->where('is_active', true),
                function (string $attribute, mixed $value, \Closure $fail) use ($period, $event) {
                    if ($value && !Program::offeredIn($period, $event->campus_id)->whereKey((int) $value)->exists()) {
                        $fail('Program studi yang dipilih tidak ditawarkan pada kegiatan ini.');
                    }
                },
            ],
        ], [
            'full_name.required'   => 'Nama lengkap wajib diisi.',
            'school_name.required' => 'Nama sekolah atau kampus wajib diisi.',
            'education_level.required' => 'Pendidikan terakhir wajib dipilih.',
            'education_level.in'       => 'Pilihan pendidikan terakhir tidak valid.',
            'phone.required'       => 'Nomor WhatsApp wajib diisi.',
        ]);

        $validator->after(function ($v) use ($request) {
            if (!Prospect::normalizePhone($request->input('phone'))) {
                $v->errors()->add('phone', 'Nomor WhatsApp tidak valid. Contoh: 081234567890.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['program_id'] = $data['program_id'] ?? null;

        $referrer = $event->isOnline() ? $this->activeReferrer($request) : null;
        $prospect = ProspectService::capture($event, $data, $referrer);
        $link = ProspectService::linkFor($prospect, $event);

        session()->put(self::SESSION_THANKS, [
            'prospect_id' => $prospect->id,
            'link_token'  => $link->token,
            'event'       => $event->name,
        ]);

        // Langsung terhubung ke WA admin. Tanpa nomor admin, tampilkan halaman terima kasih.
        $waUrl = ProspectService::whatsappUrl($prospect, $event->campus, route('prospect.link', $link->token));
        if ($waUrl) {
            $prospect->forceFill(['wa_clicked_at' => now()])->save();

            return redirect()->away($waUrl);
        }

        return redirect()->route('prospect.thanks');
    }

    public function thanks()
    {
        [$prospect, $data] = $this->thanksContext();

        if (!$prospect) {
            return redirect()->route('landing');
        }

        $linkUrl = route('prospect.link', $data['link_token']);

        return view('prospek.selesai', [
            'prospect' => $prospect,
            'linkUrl'  => $linkUrl,
            'event'    => $data['event'],
            'hasWa'    => (bool) ProspectService::whatsappUrl($prospect, $prospect->campus, $linkUrl),
        ]);
    }

    /** Klik tombol WA: catat waktu klik, lalu teruskan ke WA admin kampus. */
    public function whatsapp()
    {
        [$prospect, $data] = $this->thanksContext();

        if (!$prospect) {
            return redirect()->route('landing');
        }

        $url = ProspectService::whatsappUrl($prospect, $prospect->campus, route('prospect.link', $data['link_token']));

        if (!$url) {
            return back()->with('error', 'Nomor WhatsApp admin belum tersedia. Simpan link pendaftaran di bawah.');
        }

        $prospect->forceFill(['wa_clicked_at' => now()])->save();

        return redirect()->away($url);
    }

    /** Link daftar terkunci: validasi, simpan token di sesi, lalu arahkan ke form pendaftaran. */
    public function openLink(string $token)
    {
        $link = ProspectLink::where('token', $token)->with(['prospect.referrer', 'event'])->first();

        // Link prospek ikut mati bila sudah dipakai, kedaluwarsa, atau affiliator pemiliknya nonaktif.
        if (!ProspectService::isLinkOpen($link)) {
            return redirect()->route('landing')->with('error', 'Link pendaftaran ini sudah tidak berlaku. Hubungi admin kampus.');
        }

        session()->put(ProspectService::SESSION_KEY, $token);

        // Tamu harus login atau daftar akun dulu; setelahnya kembali ke form pendaftaran.
        session()->put('url.intended', route('registration.create'));

        return redirect()->route('registration.create');
    }

    /**
     * "Bukan kamu?": lepaskan link form minat dari sesi ini. Pendaftaran lanjut seperti biasa,
     * tanpa nama/WA terkunci dan tanpa potongan dari link. Link tetap berlaku untuk pemiliknya.
     */
    public function release()
    {
        session()->forget(ProspectService::SESSION_KEY);

        return back()->with('success', 'Data form minat tidak dipakai. Silakan isi data kamu sendiri.');
    }

    /** Prospek dan data sesi halaman terima kasih, atau [null, null]. */
    private function thanksContext(): array
    {
        $data = session(self::SESSION_THANKS);
        $prospect = $data
            ? Prospect::withoutGlobalScope(CampusScope::class)->with('campus')->find($data['prospect_id'])
            : null;

        return [$prospect, $data];
    }

    /** Event aktif yang masih menerima form minat, atau null. */
    private function openEvent(string $slug): ?ProspectEvent
    {
        $event = ProspectEvent::withoutGlobalScope(CampusScope::class)->with(['campus', 'period'])
            ->where('slug', $slug)->first();

        return $event && $event->campus && $event->acceptsInterest() ? $event : null;
    }

    /** Affiliator dari ?ref=, hanya bila aktif. Selain itu prospek tanpa pemilik. */
    private function activeReferrer(Request $request): ?Referrer
    {
        $code = QueryInput::string($request, 'ref');

        return $code ? Referrer::where('code', $code)->where('status', 'active')->first() : null;
    }
}
