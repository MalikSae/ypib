<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::orderBy('name')->paginate(10);
        return view('admin.faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('admin.faculties.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueSlugRule()],
            'description' => 'nullable|string',
        ]);

        $faculty = new Faculty($request->only('name', 'description'));
        $faculty->slug = Str::slug($request->name);
        $faculty->save();

        return redirect()->route('admin.faculties.index')->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function edit(Faculty $faculty)
    {
        return view('admin.faculties.form', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueSlugRule($faculty->id)],
            'description' => 'nullable|string',
        ]);

        $faculty->fill($request->only('name', 'description'));
        $faculty->slug = Str::slug($request->name);
        $faculty->save();

        return redirect()->route('admin.faculties.index')->with('success', 'Fakultas berhasil diperbarui.');
    }

    /** Slug dibentuk dari nama dan bersifat unik; nama yang menghasilkan slug sama ditolak. */
    private function uniqueSlugRule(?int $ignoreId = null): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) use ($ignoreId) {
            $exists = Faculty::where('slug', Str::slug((string) $value))
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $fail('Nama fakultas sudah digunakan (atau terlalu mirip dengan fakultas lain).');
            }
        };
    }

    public function destroy(Faculty $faculty)
    {
        if ($faculty->programs()->count() > 0) {
            return redirect()->route('admin.faculties.index')->with('error', 'Fakultas tidak bisa dihapus karena masih memiliki Program Studi.');
        }

        $faculty->delete();
        return redirect()->route('admin.faculties.index')->with('success', 'Fakultas berhasil dihapus.');
    }
}
