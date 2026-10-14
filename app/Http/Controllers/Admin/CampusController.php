<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::withCount(['registrations', 'periodPrograms'])
            ->orderByDesc('is_default')->orderBy('name')->paginate(10);

        return view('admin.campuses.index', compact('campuses'));
    }

    public function create()
    {
        return view('admin.campuses.form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $campus = new Campus($data);
        $campus->slug = Str::slug($data['name']);
        $campus->is_default = false;
        $campus->save();

        return redirect()->route('admin.campuses.index')->with('success', 'Kampus berhasil ditambahkan.');
    }

    public function edit(Campus $campus)
    {
        return view('admin.campuses.form', compact('campus'));
    }

    public function update(Request $request, Campus $campus)
    {
        $data = $this->validated($request, $campus);

        // Kampus bawaan memegang data lama dan tidak boleh dinonaktifkan.
        if ($campus->is_default) {
            $data['is_active'] = true;
        }

        $campus->fill($data);
        $campus->slug = Str::slug($data['name']);
        $campus->save();

        return redirect()->route('admin.campuses.index')->with('success', 'Kampus berhasil diperbarui.');
    }

    public function destroy(Campus $campus)
    {
        if ($campus->is_default) {
            return redirect()->route('admin.campuses.index')->with('error', 'Kampus bawaan tidak bisa dihapus.');
        }

        if ($campus->registrations()->exists() || $campus->periodPrograms()->exists()) {
            return redirect()->route('admin.campuses.index')
                ->with('error', 'Kampus tidak bisa dihapus karena sudah dipakai pendaftaran atau pengaturan biaya. Nonaktifkan saja.');
        }

        $campus->delete();

        return redirect()->route('admin.campuses.index')->with('success', 'Kampus berhasil dihapus.');
    }

    private function validated(Request $request, ?Campus $campus = null): array
    {
        $data = $request->validate([
            'name'                         => ['required', 'string', 'max:255', function (string $attr, $value, \Closure $fail) use ($campus) {
                $exists = Campus::where('slug', Str::slug((string) $value))
                    ->when($campus, fn ($q) => $q->where('id', '!=', $campus->id))
                    ->exists();

                if ($exists) {
                    $fail('Nama kampus sudah digunakan (atau terlalu mirip dengan kampus lain).');
                }
            }],
            'code'                         => ['required', 'string', 'max:20', Rule::unique('campuses', 'code')->ignore($campus?->id)],
            'city'                         => 'nullable|string|max:255',
            'address'                      => 'nullable|string|max:1000',
            'admin_whatsapp'               => 'nullable|string|max:20',
            'university_bank_name'         => 'nullable|string|max:255',
            'university_bank_account'      => 'nullable|string|max:255',
            'university_bank_account_name' => 'nullable|string|max:255',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
