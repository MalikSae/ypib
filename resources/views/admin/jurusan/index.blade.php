@extends('layouts.admin')

@section('title', 'Data Jurusan')
@section('subtitle', 'Kelola program studi yang tersedia')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Daftar Jurusan</h3>
        <a href="{{ route('admin.jurusan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Jurusan
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-800 font-semibold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">Kode</th>
                    <th class="px-6 py-4">Nama Jurusan</th>
                    <th class="px-6 py-4">Deskripsi</th>
                    <th class="px-6 py-4">Kuota</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($jurusans as $jurusan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-mono font-semibold text-indigo-600">
                        {{ $jurusan->kode_jurusan ?? '-' }}
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-800">
                        {{ $jurusan->nama_jurusan }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm">
                        {{ Str::limit($jurusan->deskripsi, 50) }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <div class="text-sm mb-1">
                                <span class="font-bold text-gray-800" title="Sisa Kuota">{{ max(0, $jurusan->kuota - $jurusan->mahasiswa_diterima_count) }}</span>
                                <span class="text-gray-400 text-xs">/ {{ $jurusan->kuota }} Kursi</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                @php
                                    $terisi = $jurusan->mahasiswa_diterima_count ?? 0;
                                    $percentage = $jurusan->kuota > 0 ? ($terisi / $jurusan->kuota) * 100 : 0;
                                    $colorClass = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-blue-600');
                                @endphp
                                <div class="{{ $colorClass }} h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Terisi: {{ $terisi }} (Diterima)
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($jurusan->status == 'active')
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Non-Aktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.jurusan.edit', $jurusan->id) }}" 
                               class="p-2 text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.jurusan.destroy', $jurusan->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-graduation-cap text-4xl text-gray-300 mb-3 block"></i>
                        Belum ada data jurusan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
