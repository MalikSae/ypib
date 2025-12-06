@extends('layouts.admin')

@section('title', 'Data Mahasiswa')
@section('subtitle', 'Kelola data pendaftaran mahasiswa baru')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Daftar Pendaftar</h3>
        <!-- Search/Filter could go here -->
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-800 font-semibold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">No. Pendaftaran</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Jurusan</th>
                    <th class="px-6 py-4">Tanggal Daftar</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($mahasiswas as $mahasiswa)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-blue-600">
                        {{ $mahasiswa->no_pendaftaran }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                {{ substr($mahasiswa->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $mahasiswa->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $mahasiswa->user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($mahasiswa->jurusan)
                            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold">
                                {{ $mahasiswa->jurusan->nama_jurusan }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $mahasiswa->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'diverifikasi' => 'bg-blue-100 text-blue-700',
                                'diterima' => 'bg-green-100 text-green-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                            ];
                            $statusClass = $statusClasses[$mahasiswa->status_pendaftaran] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst($mahasiswa->status_pendaftaran) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.mahasiswa.show', $mahasiswa->id) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-xs font-medium">
                            Detail <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-users-slash text-4xl text-gray-300 mb-3"></i>
                            <p>Belum ada data pendaftaran mahasiswa.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $mahasiswas->links() }}
    </div>
</div>
@endsection
