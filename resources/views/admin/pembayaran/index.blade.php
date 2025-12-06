@extends('layouts.admin')

@section('title', 'Data Pembayaran')
@section('subtitle', 'Kelola data pembayaran mahasiswa')

@section('content')
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Pembayaran</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-800 font-semibold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">No. Pembayaran</th>
                    <th class="px-6 py-4">Mahasiswa</th>
                    <th class="px-6 py-4">Jumlah</th>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pembayarans as $pembayaran)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-indigo-600">
                        {{ $pembayaran->no_pembayaran }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">{{ $pembayaran->mahasiswa->nama_lengkap ?? $pembayaran->mahasiswa->user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $pembayaran->mahasiswa->no_pendaftaran ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 font-medium">
                        Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4">
                        {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                         @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'terverifikasi' => 'bg-green-100 text-green-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                            ];
                            $statusClass = $statusClasses[$pembayaran->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst($pembayaran->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.pembayaran.show', $pembayaran->id) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors text-xs font-medium">
                            Detail <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-money-bill-wave-alt text-4xl text-gray-300 mb-3 block"></i>
                        Belum ada data pembayaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $pembayarans->links() }}
    </div>
</div>
@endsection
