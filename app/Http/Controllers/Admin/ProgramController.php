<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\ProgramGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('faculty')->orderBy('name')->paginate(10);
        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        $faculties = Faculty::orderBy('name')->get();
        return view('admin.programs.form', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
            'accreditation' => 'nullable|string|max:50',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'gallery.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $feeDetails = [];
            $totalReRegistrationFee = 0;
            if ($request->has('fee_names') && is_array($request->fee_names)) {
                foreach ($request->fee_names as $index => $name) {
                    if (!empty($name) && isset($request->fee_amounts[$index])) {
                        $amount = (int) preg_replace('/\D/', '', $request->fee_amounts[$index]);
                        $feeDetails[] = [
                            'name' => $name,
                            'amount' => $amount
                        ];
                        $totalReRegistrationFee += $amount;
                    }
                }
            }

            $program = new Program();
            $program->fill($request->except(['gallery', 'is_active', 'fee_names', 'fee_amounts', 're_registration_fee']));
            $program->slug = Str::slug($request->name);
            $program->is_active = $request->has('is_active');
            $program->re_registration_fee_details = $feeDetails;
            $program->re_registration_fee = $totalReRegistrationFee;
            $program->save();

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $path = $file->store('programs', 'public');
                    ProgramGallery::create([
                        'program_id' => $program->id,
                        'image_path' => $path
                    ]);
                }
            }

            return redirect()->route('admin.programs.index')->with('success', 'Program Studi berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $program = Program::with('galleries')->findOrFail($id);
        $faculties = Faculty::all();
        return view('admin.programs.form', compact('program', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
            'accreditation' => 'nullable|string|max:50',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'gallery.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $program = Program::findOrFail($id);

            $feeDetails = [];
            $totalReRegistrationFee = 0;
            if ($request->has('fee_names') && is_array($request->fee_names)) {
                foreach ($request->fee_names as $index => $name) {
                    if (!empty($name) && isset($request->fee_amounts[$index])) {
                        $amount = (int) preg_replace('/\D/', '', $request->fee_amounts[$index]);
                        $feeDetails[] = [
                            'name' => $name,
                            'amount' => $amount
                        ];
                        $totalReRegistrationFee += $amount;
                    }
                }
            }

            $program->fill($request->except(['gallery', 'is_active', 'fee_names', 'fee_amounts', 're_registration_fee']));
            $program->slug = Str::slug($request->name);
            $program->is_active = $request->has('is_active');
            $program->re_registration_fee_details = $feeDetails;
            $program->re_registration_fee = $totalReRegistrationFee;
            $program->save();

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $path = $file->store('programs', 'public');
                    ProgramGallery::create([
                        'program_id' => $program->id,
                        'image_path' => $path
                    ]);
                }
            }

            return redirect()->route('admin.programs.index')->with('success', 'Program Studi berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Program $program)
    {
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
