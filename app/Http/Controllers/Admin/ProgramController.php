<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Support\AdminPeriod;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\ProgramGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with('faculty')->orderBy('name');

        // Kuota dan biaya ditampilkan dari periode berjalan (nilainya diatur per periode).
        $displayPeriod = \App\Models\PmbPeriod::displayPeriod();

        $campuses = Campus::orderByDesc('is_default')->orderBy('name')->get();
        $campusFilter = $campuses->firstWhere('id', (int) $request->input('campus'));

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            // Dikelompokkan agar OR tidak lolos dari filter kampus.
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('faculty', function ($f) use ($searchTerm) {
                      $f->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($campusFilter) {
            $query->inCampus($displayPeriod, $campusFilter);
        }

        $perPage = $request->input('per_page', 10);
        $programs = $query->paginate($perPage)->appends($request->query());

        // Kolom Kampus, lalu biaya/kuota kampus yang difilter (tanpa filter: kampus bawaan).
        Program::attachOfferingCampuses($programs->getCollection(), $displayPeriod);
        $termsCampus = $campusFilter ?? Campus::default();
        Program::preloadTerms($programs->getCollection(), $displayPeriod, $termsCampus);

        return view('admin.programs.index', compact('programs', 'displayPeriod', 'campuses', 'campusFilter', 'termsCampus'));
    }

    public function create()
    {
        $faculties = Faculty::orderBy('name')->get();
        $icons = [];
        $iconPath = public_path('images/icons');
        if (File::exists($iconPath)) {
            $icons = array_map('basename', File::files($iconPath));
        }
        $offers = $this->offerData(null);

        return view('admin.programs.form', compact('faculties', 'icons', 'offers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                            => 'required|string|max:255',
            'kode_prodi'                      => [
                'nullable', 'string', 'size:5', 'regex:/^[0-9]{5}$/',
                \Illuminate\Validation\Rule::unique('programs')->where(function ($query) use ($request) {
                    return $query->where('registration_track', $request->registration_track);
                }),
            ],
            'registration_track'              => 'required|in:reguler,non_reguler',
            'faculty_id'                      => 'required|exists:faculties,id',
            'accreditation'                   => 'nullable|string|max:50',
            'is_active'                       => 'boolean',
            'icon'                            => 'nullable|string|max:255',
            'description'                     => 'nullable|string',
            'gallery.*'                       => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'offers'                          => 'nullable|array',
            'offers.*.fee'                    => 'nullable|integer|min:0|max:2000000000',
            'offers.*.quota'                  => 'nullable|integer|min:0|max:100000',
        ], [
            'offers.*.fee.integer'   => 'Biaya pendaftaran harus berupa angka.',
            'offers.*.fee.min'       => 'Biaya pendaftaran tidak boleh negatif.',
            'offers.*.fee.max'       => 'Biaya pendaftaran terlalu besar.',
            'offers.*.quota.integer' => 'Kuota harus berupa angka bulat.',
            'offers.*.quota.min'     => 'Kuota tidak boleh negatif (0 = tanpa batas).',
            'offers.*.quota.max'     => 'Kuota terlalu besar.',
            'kode_prodi.size'   => 'Kode Prodi harus tepat 5 karakter.',
            'kode_prodi.regex'  => 'Kode Prodi harus berupa 5 digit angka.',
            'kode_prodi.unique' => 'Kode Prodi ini sudah digunakan oleh program studi lain pada jalur pendaftaran yang sama.',
        ]);

        $createdPaths = [];
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $program = new Program();
            $program->fill($request->only(['name', 'kode_prodi', 'registration_track', 'faculty_id', 'accreditation', 'icon']));
            $program->slug = Program::uniqueSlug($request->name);
            $program->description = \App\Support\HtmlSanitizer::clean($request->description);
            $program->is_active = $request->has('is_active');
            $program->save();

            // Prodi baru belum punya biaya dan kuota per periode: tidak ditawarkan sampai admin
            // mengaturnya di "Biaya & Kuota" (mencegah prodi tampil dengan harga bawaan).
            foreach (\App\Models\PmbPeriod::where('status', '!=', \App\Models\PmbPeriod::STATUS_ARCHIVED)->get() as $period) {
                \App\Models\PeriodProgram::seedFromPrograms($period, false, $program->id);
            }

            $this->syncCampusOffers($program, $request);

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                $manager = new ImageManager(new Driver());
                foreach ($request->file('gallery') as $file) {
                    $filename = Str::uuid() . '.webp';
                    $path = 'programs/' . $filename;
                    
                    $image = $manager->decode($file);
                    $image->scaleDown(width: 1200);
                    $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80));
                    
                    Storage::disk('public')->put($path, (string) $encoded);
                    $createdPaths[] = $path;

                    ProgramGallery::create([
                        'program_id' => $program->id,
                        'image_path' => $path
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.programs.index')->with('success', 'Program Studi berhasil ditambahkan. Atur biaya dan kuotanya di menu Periode PMB > Biaya & Kuota agar prodi ini ditawarkan.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            foreach ($createdPaths as $createdPath) {
                Storage::disk('public')->delete($createdPath);
            }
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data. Periksa isian Anda lalu coba lagi; bila berulang hubungi pengembang.');
        }
    }

    public function edit($id)
    {
        $program = Program::with('galleries')->findOrFail($id);
        $faculties = Faculty::all();
        $icons = [];
        $iconPath = public_path('images/icons');
        if (File::exists($iconPath)) {
            $icons = array_map('basename', File::files($iconPath));
        }
        $offers = $this->offerData($program);

        return view('admin.programs.form', compact('program', 'faculties', 'icons', 'offers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'                            => 'required|string|max:255',
            'kode_prodi'                      => [
                'nullable', 'string', 'size:5', 'regex:/^[0-9]{5}$/',
                \Illuminate\Validation\Rule::unique('programs')->ignore($id)->where(function ($query) use ($request) {
                    return $query->where('registration_track', $request->registration_track);
                }),
            ],
            'registration_track'              => 'required|in:reguler,non_reguler',
            'faculty_id'                      => 'required|exists:faculties,id',
            'accreditation'                   => 'nullable|string|max:50',
            'is_active'                       => 'boolean',
            'icon'                            => 'nullable|string|max:255',
            'description'                     => 'nullable|string',
            'gallery.*'                       => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'offers'                          => 'nullable|array',
            'offers.*.fee'                    => 'nullable|integer|min:0|max:2000000000',
            'offers.*.quota'                  => 'nullable|integer|min:0|max:100000',
        ], [
            'offers.*.fee.integer'   => 'Biaya pendaftaran harus berupa angka.',
            'offers.*.fee.min'       => 'Biaya pendaftaran tidak boleh negatif.',
            'offers.*.fee.max'       => 'Biaya pendaftaran terlalu besar.',
            'offers.*.quota.integer' => 'Kuota harus berupa angka bulat.',
            'offers.*.quota.min'     => 'Kuota tidak boleh negatif (0 = tanpa batas).',
            'offers.*.quota.max'     => 'Kuota terlalu besar.',
            'kode_prodi.size'   => 'Kode Prodi harus tepat 5 karakter.',
            'kode_prodi.regex'  => 'Kode Prodi harus berupa 5 digit angka.',
            'kode_prodi.unique' => 'Kode Prodi ini sudah digunakan oleh program studi lain pada jalur pendaftaran yang sama.',
        ]);

        $createdPaths = [];
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $program = Program::findOrFail($id);

            $program->fill($request->only(['name', 'kode_prodi', 'registration_track', 'faculty_id', 'accreditation', 'icon']));
            if ($program->isDirty('name') || empty($program->slug)) {
                $program->slug = Program::uniqueSlug($request->name, $program->id);
            }
            $program->description = \App\Support\HtmlSanitizer::clean($request->description);
            $program->is_active = $request->has('is_active');
            $program->save();

            $this->syncCampusOffers($program, $request);

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                $manager = new ImageManager(new Driver());
                foreach ($request->file('gallery') as $file) {
                    $filename = Str::uuid() . '.webp';
                    $path = 'programs/' . $filename;

                    $image = $manager->decode($file);
                    $image->scaleDown(width: 1200);
                    $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80));

                    Storage::disk('public')->put($path, (string) $encoded);
                    $createdPaths[] = $path;

                    ProgramGallery::create([
                        'program_id' => $program->id,
                        'image_path' => $path
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.programs.index')->with('success', 'Program Studi berhasil diperbarui.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            foreach ($createdPaths as $createdPath) {
                Storage::disk('public')->delete($createdPath);
            }
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data. Periksa isian Anda lalu coba lagi; bila berulang hubungi pengembang.');
        }
    }

    /** Periode yang dikelola kartu Kampus: pilihan di header admin, atau periode tampilan bila "Semua periode". */
    private function offerPeriod(): ?PmbPeriod
    {
        return AdminPeriod::current() ?? PmbPeriod::displayPeriod();
    }

    /**
     * Data kartu "Kampus": per kampus, apakah prodi ditawarkan pada periode terpilih beserta
     * biaya pendaftaran dan kuotanya. Kampus yang belum punya nilai diisi awal dari kampus bawaan.
     *
     * @return array{period: ?PmbPeriod, readOnly: bool, rows: array<int, array<string, mixed>>}
     */
    private function offerData(?Program $program): array
    {
        $period = $this->offerPeriod();
        $defaultId = Campus::defaultId();
        $campuses = Campus::orderByDesc('is_default')->orderBy('name')->get();

        $rows = [];

        foreach ($campuses as $campus) {
            $row = ($program && $period)
                ? PeriodProgram::where('period_id', $period->id)->where('program_id', $program->id)->where('campus_id', $campus->id)->first()
                : null;

            // Kampus bawaan tanpa baris dianggap menawarkan prodi (perilaku lama); kampus lain tidak.
            $offered = $row ? (bool) $row->is_offered : ($program && $campus->id === $defaultId);

            $fee = (int) ($row?->registration_fee ?? 0);
            $quota = (int) ($row?->quota ?? 0);

            if (!$offered && $fee === 0 && $quota === 0 && $program && $period) {
                $defaults = $program->termsFor($period, $defaultId);
                $fee = (int) $defaults->registration_fee;
                $quota = (int) $defaults->quota;
            }

            $rows[] = [
                'campus'     => $campus,
                'offered'    => $offered,
                'fee'        => $fee,
                'quota'      => $quota,
                'seats'      => ($program && $period) ? $program->occupiedSeats($period->id, $campus) : 0,
            ];
        }

        return [
            'period'   => $period,
            'readOnly' => !$period || $period->isArchived(),
            'rows'     => $rows,
        ];
    }

    /**
     * Simpan kartu "Kampus" ke period_programs untuk periode terpilih. Hanya biaya pendaftaran
     * dan kuota yang diubah di sini; baris baru meminjam nilai lain (daftar ulang, komisi) dari
     * kampus bawaan sebagai titik awal, dan dirapikan di Biaya & Kuota.
     * Periode arsip dan form tanpa kartu (tanda `offers_present`) tidak disentuh.
     */
    private function syncCampusOffers(Program $program, Request $request): void
    {
        $period = $this->offerPeriod();

        if (!$period || $period->isArchived() || !$request->boolean('offers_present')) {
            return;
        }

        $offers = (array) $request->input('offers', []);
        $defaultId = Campus::defaultId();
        $defaultRow = PeriodProgram::where('period_id', $period->id)
            ->where('program_id', $program->id)->where('campus_id', $defaultId)->first();

        foreach (Campus::all() as $campus) {
            $input = (array) ($offers[$campus->id] ?? []);
            $offered = !empty($input['offered']);

            $row = PeriodProgram::where('period_id', $period->id)
                ->where('program_id', $program->id)->where('campus_id', $campus->id)->first();

            if ($offered) {
                if (!$row) {
                    $row = new PeriodProgram(['period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $campus->id]);

                    if ($defaultRow) {
                        foreach (PeriodProgram::TERM_COLUMNS as $column) {
                            $row->{$column} = $defaultRow->{$column};
                        }
                    }
                }

                $row->is_offered = true;
                $row->registration_fee = (int) ($input['fee'] ?? $row->registration_fee ?? 0);
                $row->quota = (int) ($input['quota'] ?? $row->quota ?? 0);
                $row->save();
            } elseif ($row) {
                $row->is_offered = false;
                $row->save();
            } elseif ($campus->id === $defaultId) {
                // Kampus bawaan tanpa baris = ditawarkan, jadi "tidak" harus dicatat eksplisit.
                PeriodProgram::create([
                    'period_id' => $period->id, 'program_id' => $program->id,
                    'campus_id' => $campus->id, 'is_offered' => false,
                ]);
            }
        }
    }
    public function destroy(Program $program)
    {
        // Pendaftar (termasuk yang di-soft-delete) masih mereferensikan prodi ini lewat FK.
        $inUse = \App\Models\Registration::withTrashed()
            ->where(fn ($q) => $q->where('first_choice_program_id', $program->id)
                ->orWhere('second_choice_program_id', $program->id))
            ->exists();

        if ($inUse) {
            return redirect()->route('admin.programs.index')
                ->with('error', 'Prodi tidak bisa dihapus karena sudah dipakai oleh data pendaftar. Nonaktifkan prodi ini sebagai gantinya.');
        }

if ($program->galleries) {
            foreach ($program->galleries as $gallery) {
                Storage::disk('public')->delete($gallery->image_path);
            }
        }
        $program->delete();
        return redirect()->route('admin.programs.index')->with('success', 'Prodi berhasil dihapus.');
    }

    public function destroyGallery($id)
    {
        $gallery = ProgramGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();

        return response()->json(['success' => true]);
    }
}
