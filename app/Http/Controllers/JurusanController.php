<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::withCount('mahasiswa')->get();
        return view('admin.jurusan.index', compact('jurusan'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10|unique:jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Jurusan::create($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan,'.$jurusan->id,
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($jurusan->mahasiswa()->count() > 0) {
            return back()->with('error', 'Jurusan tidak dapat dihapus karena masih ada mahasiswa yang terdaftar.');
        }

        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}