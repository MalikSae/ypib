<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MahasiswaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jurusan_id' => 'required|exists:jurusan,id',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string|max:50',
            'alamat' => 'required|string',
            'asal_sekolah' => 'required|string|max:255',
            'tahun_lulus' => 'required|string|max:4',
            'nilai_rata_rata' => 'required|numeric|min:0|max:100',
        ]);

        // Generate nomor pendaftaran
        $noPendaftaran = 'REG' . date('Y') . str_pad(Mahasiswa::count() + 1, 5, '0', STR_PAD_LEFT);

        $mahasiswa = Mahasiswa::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'no_pendaftaran' => $noPendaftaran,
            'status_pendaftaran' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pendaftaran berhasil disimpan',
            'data' => $mahasiswa->load('jurusan')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($mahasiswa->status_pendaftaran !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak dapat diubah karena sudah disubmit'
            ], 403);
        }

        $validated = $request->validate([
            'jurusan_id' => 'sometimes|exists:jurusan,id',
            'nama_lengkap' => 'sometimes|string|max:255',
            'tempat_lahir' => 'sometimes|string|max:255',
            'tanggal_lahir' => 'sometimes|date',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'agama' => 'sometimes|string|max:50',
            'alamat' => 'sometimes|string',
            'asal_sekolah' => 'sometimes|string|max:255',
            'tahun_lulus' => 'sometimes|string|max:4',
            'nilai_rata_rata' => 'sometimes|numeric|min:0|max:100',
        ]);

        $mahasiswa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $mahasiswa->load('jurusan')
        ]);
    }

    public function submit(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($mahasiswa->status_pendaftaran !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah pernah disubmit'
            ], 403);
        }

        $mahasiswa->update(['status_pendaftaran' => 'submitted']);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil disubmit'
        ]);
    }

    public function show(Request $request)
    {
        $mahasiswa = Mahasiswa::where('user_id', $request->user()->id)
            ->with(['jurusan', 'pembayaran'])
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mahasiswa
        ]);
    }
}