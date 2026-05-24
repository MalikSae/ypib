<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_active' => 'boolean'
        ]);

        $partner = new Partner();
        $partner->name = $request->name;
        $partner->is_active = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $path = 'partners/' . $filename;
            
            $image = $manager->read($request->file('logo'));
            $image->scaleDown(width: 400); // Logo tak perlu terlalu besar
            $encoded = $image->toWebp(80);
            
            Storage::disk('public')->put($path, (string) $encoded);
            $partner->logo_path = $path;
        }

        $partner->save();

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil ditambahkan.');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.form', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_active' => 'boolean'
        ]);

        $partner->name = $request->name;
        $partner->is_active = $request->has('is_active');

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $path = 'partners/' . $filename;
            
            $image = $manager->read($request->file('logo'));
            $image->scaleDown(width: 400);
            $encoded = $image->toWebp(80);
            
            Storage::disk('public')->put($path, (string) $encoded);
            $partner->logo_path = $path;
        }

        $partner->save();

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil diperbarui.');
    }

    public function destroy(Partner $partner)
    {
        if ($partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
        }
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil dihapus.');
    }

    public function toggleActive(Partner $partner)
    {
        $partner->is_active = !$partner->is_active;
        $partner->save();

        return redirect()->route('admin.partners.index')->with('success', 'Status partner berhasil diubah.');
    }
}
