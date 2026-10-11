<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('faculty', function($q) use ($searchTerm) {
                      $q->where('name', 'like', '%' . $searchTerm . '%');
                  });
        }

        $perPage = $request->input('per_page', 10);
        $programs = $query->paginate($perPage)->appends($request->query());

        // Kuota dan biaya ditampilkan dari periode berjalan (nilainya diatur per periode).
        $displayPeriod = \App\Models\PmbPeriod::displayPeriod();
        Program::preloadTerms($programs->getCollection(), $displayPeriod);

        return view('admin.programs.index', compact('programs', 'displayPeriod'));
    }

    public function create()
    {
        $faculties = Faculty::orderBy('name')->get();
        $icons = [];
        $iconPath = public_path('images/icons');
        if (File::exists($iconPath)) {
            $icons = array_map('basename', File::files($iconPath));
        }
        return view('admin.programs.form', compact('faculties', 'icons'));
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
        ], [
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
        return view('admin.programs.form', compact('program', 'faculties', 'icons'));
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
        ], [
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
