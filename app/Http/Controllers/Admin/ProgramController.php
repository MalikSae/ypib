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

        return view('admin.programs.index', compact('programs'));
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
            'name' => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
            'accreditation' => 'nullable|string|max:50',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'required|integer|min:0',
            'referral_reward_amount' => 'required|integer|min:0',
            're_registration_reward_amount' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:255',
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
            $program->fill($request->except(['gallery', 'is_active', 'fee_names', 'fee_amounts', 're_registration_fee', 'referral_reward_amount', 're_registration_reward_amount']));
            $program->slug = Str::slug($request->name);
            $program->is_active = $request->has('is_active');
            $program->re_registration_fee_details = $feeDetails;
            $program->re_registration_fee = $totalReRegistrationFee;
            $program->referral_reward_amount = $request->referral_reward_amount;
            $program->re_registration_reward_amount = $request->re_registration_reward_amount;
            $program->save();

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                $manager = new ImageManager(new Driver());
                foreach ($request->file('gallery') as $file) {
                    $filename = Str::uuid() . '.webp';
                    $path = 'programs/' . $filename;
                    
                    $image = $manager->read($file);
                    $image->scaleDown(width: 1200);
                    $encoded = $image->toWebp(80);
                    
                    Storage::disk('public')->put($path, (string) $encoded);
                    
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
            'name' => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
            'accreditation' => 'nullable|string|max:50',
            'quota' => 'required|integer|min:0',
            'registration_fee' => 'required|integer|min:0',
            'referral_reward_amount' => 'required|integer|min:0',
            're_registration_reward_amount' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:255',
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

            $program->fill($request->except(['gallery', 'is_active', 'fee_names', 'fee_amounts', 're_registration_fee', 'referral_reward_amount', 're_registration_reward_amount']));
            $program->slug = Str::slug($request->name);
            $program->is_active = $request->has('is_active');
            $program->re_registration_fee_details = $feeDetails;
            $program->re_registration_fee = $totalReRegistrationFee;
            $program->referral_reward_amount = $request->referral_reward_amount;
            $program->re_registration_reward_amount = $request->re_registration_reward_amount;
            $program->save();

            // Handle Galleries
            if ($request->hasFile('gallery')) {
                $manager = new ImageManager(new Driver());
                foreach ($request->file('gallery') as $file) {
                    $filename = Str::uuid() . '.webp';
                    $path = 'programs/' . $filename;
                    
                    $image = $manager->read($file);
                    $image->scaleDown(width: 1200);
                    $encoded = $image->toWebp(80);
                    
                    Storage::disk('public')->put($path, (string) $encoded);
                    
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
