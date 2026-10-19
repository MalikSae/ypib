<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $campus->slug = $data['slug'];
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
        $campus->slug = $data['slug'];
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

    /** Segmen pertama semua rute terdaftar, ditambah path statis yang dilayani web server. */
    private function reservedSlugs(): array
    {
        return collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
            ->map(fn ($route) => explode('/', trim($route->uri(), '/'))[0])
            ->filter(fn ($segment) => $segment !== '' && !str_starts_with($segment, '{'))
            ->merge(['storage', 'build', 'images', 'vendor', 'up', 'sanctum'])
            ->unique()->values()->all();
    }

    private function validated(Request $request, ?Campus $campus = null): array
    {
        // Teks hero wajib untuk kampus non-bawaan (landing /{slug}); kampus bawaan memakai landing utama.
        $heroRequired = ($campus?->is_default) ? 'nullable' : 'required';

        $data = $request->validate([
            'name'                         => ['required', 'string', 'max:255', Rule::unique('campuses', 'name')->ignore($campus?->id)],
            'slug'                         => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'code'                         => ['required', 'string', 'max:20', Rule::unique('campuses', 'code')->ignore($campus?->id)],
            'hero_headline'                => [$heroRequired, 'string', 'min:10', 'max:90', 'regex:/^[^<>]*$/'],
            'hero_highlight'               => ['nullable', 'string', 'max:40', 'regex:/^[^<>]*$/'],
            'hero_subtitle'                => [$heroRequired, 'string', 'min:20', 'max:180', 'regex:/^[^<>]*$/'],
            'city'                         => 'nullable|string|max:255',
            'address'                      => 'nullable|string|max:1000',
            'admin_whatsapp'               => 'nullable|string|max:20',
            'university_bank_name'         => 'nullable|string|max:255',
            'university_bank_account'      => 'nullable|string|max:255',
            'university_bank_account_name' => 'nullable|string|max:255',
        ], [
            'hero_headline.required' => 'Judul hero wajib diisi untuk kampus ini.',
            'hero_headline.min'      => 'Judul hero minimal :min karakter.',
            'hero_headline.max'      => 'Judul hero maksimal :max karakter.',
            'hero_headline.regex'    => 'Judul hero tidak boleh memuat tanda < atau >.',
            'hero_highlight.max'     => 'Kata sorotan maksimal :max karakter.',
            'hero_highlight.regex'   => 'Kata sorotan tidak boleh memuat tanda < atau >.',
            'hero_subtitle.required' => 'Deskripsi hero wajib diisi untuk kampus ini.',
            'hero_subtitle.min'      => 'Deskripsi hero minimal :min karakter.',
            'hero_subtitle.max'      => 'Deskripsi hero maksimal :max karakter.',
            'hero_subtitle.regex'    => 'Deskripsi hero tidak boleh memuat tanda < atau >.',
        ]);

        // Kata sorotan harus benar-benar ada di judul (huruf besar/kecil dibedakan).
        if (!empty($data['hero_highlight']) && !str_contains((string) ($data['hero_headline'] ?? ''), $data['hero_highlight'])) {
            throw ValidationException::withMessages(['hero_highlight' => 'Kata sorotan harus ada persis di dalam judul hero.']);
        }

        // Slug = alamat landing (/cirebon). Kosong: pertahankan slug lama, atau turunkan dari kota/nama.
        $slug = $data['slug'] ?? null;
        $slug = $slug ?: ($campus?->slug ?? Str::slug($data['city'] ?: $data['name']));

        if ($slug === '') {
            throw ValidationException::withMessages(['slug' => 'Alamat halaman tidak valid.']);
        }

        if (in_array($slug, $this->reservedSlugs(), true)) {
            throw ValidationException::withMessages(['slug' => 'Alamat ini dicadangkan sistem. Gunakan alamat lain.']);
        }

        $taken = Campus::where('slug', $slug)
            ->when($campus, fn ($q) => $q->where('id', '!=', $campus->id))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['slug' => 'Alamat halaman sudah dipakai kampus lain.']);
        }

        $data['slug'] = $slug;
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
