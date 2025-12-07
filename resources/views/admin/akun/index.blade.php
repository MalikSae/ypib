@extends('layouts.admin')

@section('title', 'Verifikasi Akun')
@section('subtitle', 'Kelola pendaftaran akun calon mahasiswa')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Daftar Akun Baru</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-800 font-semibold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">Tanggal Daftar</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Status Akun</th>
                    <th class="px-6 py-4 text-center">Verifikasi</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        {{ $user->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-800">
                        {{ $user->name }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'aktif' => 'bg-green-100 text-green-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                            ];
                            $statusClass = $statusClasses[$user->status_akun] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst($user->status_akun) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($user->status_akun === 'pending')
                            <div class="flex justify-center space-x-2">
                                <form action="{{ route('admin.akun.verifikasi', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status_akun" value="aktif">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 flex items-center justify-center transition-colors" title="Terima">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.akun.verifikasi', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status_akun" value="ditolak">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 flex items-center justify-center transition-colors" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.akun.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini? Data pendaftaran terkait juga akan terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-user-check text-4xl text-gray-300 mb-3"></i>
                            <p>Belum ada pendaftaran akun baru.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
</div>
@endsection