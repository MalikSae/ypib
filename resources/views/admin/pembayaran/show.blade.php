@extends('layouts.admin')

@section('title', 'Detail Pembayaran')
@section('subtitle', 'Verifikasi pembayaran mahasiswa')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Info Pembayaran -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
            <h3 class="font-bold text-indigo-900">Informasi Pembayaran</h3>
            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                {{ $pembayaran->status === 'terverifikasi' ? 'bg-green-100 text-green-700' : 
                  ($pembayaran->status === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                {{ ucfirst($pembayaran->status) }}
            </span>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">No. Pembayaran</p>
                    <p class="font-bold text-gray-800">{{ $pembayaran->no_pembayaran }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Tanggal Bayar</p>
                    <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Jumlah</p>
                    <p class="font-bold text-indigo-600 text-lg">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Waktu Upload</p>
                    <p class="text-sm text-gray-800">{{ $pembayaran->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Mahasiswa</p>
                <div class="flex items-center space-x-3 mt-2">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $pembayaran->mahasiswa->nama_lengkap ?? $pembayaran->mahasiswa->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $pembayaran->mahasiswa->no_pendaftaran }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bukti & Verifikasi -->
    <div class="space-y-6">
        <!-- Bukti Pembayaran -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Bukti Pembayaran</h3>
            </div>
            <div class="p-6 text-center">
                @if($pembayaran->bukti_pembayaran)
                    @php
                        $extension = pathinfo($pembayaran->bukti_pembayaran, PATHINFO_EXTENSION);
                    @endphp
                    
                    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                        <img src="{{ Storage::url($pembayaran->bukti_pembayaran) }}" 
                             alt="Bukti Pembayaran" 
                             class="max-w-full max-h-96 mx-auto rounded shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                             onclick="window.open(this.src, '_blank')">
                        <p class="text-xs text-gray-400 mt-2">Klik gambar untuk memperbesar</p>
                    @elseif(strtolower($extension) === 'pdf')
                        <div class="py-8">
                            <i class="fas fa-file-pdf text-red-500 text-5xl mb-3"></i>
                            <p class="mb-4">{{ basename($pembayaran->bukti_pembayaran) }}</p>
                            <a href="{{ Storage::url($pembayaran->bukti_pembayaran) }}" target="_blank"
                               class="inline-block px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 transition">
                                Lihat PDF
                            </a>
                        </div>
                    @else
                        <a href="{{ Storage::url($pembayaran->bukti_pembayaran) }}" target="_blank" class="text-blue-600 hover:underline">
                            Download Bukti
                        </a>
                    @endif
                @else
                    <p class="text-red-500">Tidak ada bukti pembayaran diupload.</p>
                @endif
            </div>
        </div>

        <!-- Form Action -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="font-bold text-gray-800 mb-4">Verifikasi</h3>
            <form action="{{ route('admin.pembayaran.verifikasi', $pembayaran->id) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="status" value="terverifikasi" 
                                   {{ $pembayaran->status == 'terverifikasi' ? 'checked' : '' }}
                                   class="text-green-600 focus:ring-green-500">
                            <span class="text-green-700 font-medium">Terverifikasi</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="status" value="ditolak" 
                                   {{ $pembayaran->status == 'ditolak' ? 'checked' : '' }}
                                   class="text-red-600 focus:ring-red-500">
                            <span class="text-red-700 font-medium">Tolak</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" 
                              class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Alasan penolakan atau catatan tambahan...">{{ $pembayaran->catatan }}</textarea>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-lg shadow-indigo-200">
                    Simpan Verifikasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
