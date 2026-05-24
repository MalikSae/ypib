<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('order')->get();
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if ($request->hasFile('image')) {
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $path = 'facilities/' . $filename;
            
            $image = $manager->read($request->file('image'));
            $image->scaleDown(width: 1200);
            $encoded = $image->toWebp(80);
            
            Storage::disk('public')->put($path, (string) $encoded);
            $validated['image_path'] = $path;
        }

        Facility::create($validated);

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($facility->image_path) {
                Storage::disk('public')->delete($facility->image_path);
            }
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $path = 'facilities/' . $filename;
            
            $image = $manager->read($request->file('image'));
            $image->scaleDown(width: 1200);
            $encoded = $image->toWebp(80);
            
            Storage::disk('public')->put($path, (string) $encoded);
            $validated['image_path'] = $path;
        }

        $facility->update($validated);

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        if ($facility->image_path) {
            Storage::disk('public')->delete($facility->image_path);
        }
        $facility->delete();
        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil dihapus.');
    }

    public function toggleActive(Facility $facility)
    {
        $facility->update(['is_active' => !$facility->is_active]);
        return back()->with('success', 'Status fasilitas berhasil diubah.');
    }
}
