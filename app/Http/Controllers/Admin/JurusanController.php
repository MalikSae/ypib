<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::withCount('mahasiswas')->paginate(10);
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan' => 'required|string|unique:jurusans',
            'nama_jurusan' => 'required|string',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:1'
        ]);

        Jurusan::create($request->all());

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function edit($id)
    {
        // PERUBAHAN: Load dengan count mahasiswa untuk validasi kuota
        $jurusan = Jurusan::withCount('mahasiswas')->findOrFail($id);
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, $id)
    {
        // PERUBAHAN: Load dengan count mahasiswa untuk validasi kuota
        $jurusan = Jurusan::withCount('mahasiswas')->findOrFail($id);
        
        $request->validate([
            'kode_jurusan' => 'required|string|unique:jurusans,kode_jurusan,' . $id,
            'nama_jurusan' => 'required|string',
            'deskripsi' => 'nullable|string',
            'kuota' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive'
        ]);

        // Validasi status: Tidak boleh non-active jika ada mahasiswa
        if ($request->status == 'inactive' && $jurusan->mahasiswas_count > 0) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Tidak dapat menonaktifkan jurusan yang masih memiliki mahasiswa terdaftar (' . $jurusan->mahasiswas_count . ' mahasiswa)');
        }

        // PERUBAHAN: Validasi kuota tidak boleh lebih kecil dari mahasiswa terdaftar
        if ($request->kuota < $jurusan->mahasiswas_count) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Kuota tidak boleh lebih kecil dari jumlah mahasiswa yang sudah terdaftar (' . $jurusan->mahasiswas_count . ' mahasiswa)');
        }

        $jurusan->update($request->all());

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil diupdate');
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        
        // OPSIONAL: Cek apakah ada mahasiswa yang terdaftar sebelum hapus
        if ($jurusan->mahasiswas()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Tidak dapat menghapus jurusan yang masih memiliki mahasiswa terdaftar');
        }
        
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus');
    }
}