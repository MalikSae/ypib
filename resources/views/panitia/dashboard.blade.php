@extends('layouts.panitia')

@section('content')
<x-card class="mb-6 p-0 overflow-hidden">
    <div class="p-5 border-b border-neutral-100 flex items-center justify-between">
        <h2 class="font-bold text-lg text-neutral-800">Scan QR Pendaftar</h2>
        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
    </div>
    
    <div class="p-5">
        <p class="text-sm text-neutral-600 mb-4 text-center">Arahkan kamera ke QR code pada layar HP/dokumen pendaftar.</p>
        
        <div id="reader-container" class="w-full bg-neutral-50 rounded-xl overflow-hidden relative border-2 border-dashed border-neutral-300 min-h-[250px] flex flex-col items-center justify-center">
            
            {{-- Placeholder --}}
            <div id="reader-placeholder" class="text-center p-6 w-full flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-neutral-200 rounded-full flex items-center justify-center text-neutral-400 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <p class="text-neutral-500 font-medium mb-4">Kamera belum aktif</p>
                <x-button type="button" color="primary" id="startScanBtn">Mulai Scan</x-button>
            </div>

            {{-- Reader --}}
            <div id="reader" width="100%" class="hidden w-full"></div>
            
            {{-- Error Message --}}
            <div id="camera-error" class="hidden p-4 text-xs text-error-600 bg-error-50 border-t border-error-200 w-full text-center">
                Gagal mengakses kamera: <span id="camera-error-text"></span>. Pastikan Anda mengizinkan akses kamera, atau gunakan pencarian manual di bawah.
            </div>

            {{-- Stop Button --}}
            <div id="stop-scan-container" class="hidden p-3 bg-white border-t border-neutral-200 w-full text-center">
                <x-button type="button" color="neutral" variant="outline" size="sm" id="stopScanBtn">Hentikan Scan</x-button>
            </div>
        </div>
    </div>
</x-card>

<x-card class="p-0 overflow-hidden">
    <div class="p-5 border-b border-neutral-100">
        <h3 class="font-bold text-neutral-800">Cari Manual</h3>
    </div>
    <div class="p-5">
        <p class="text-xs text-neutral-500 mb-3">Atau masukkan nomor pendaftaran manual jika kamera bermasalah.</p>
        <form onsubmit="handleManualSearch(event)" class="flex gap-2">
            <x-text-input type="text" id="manual_number" placeholder="PMB-202X-XXXX" class="flex-1" required />
            <x-button type="submit" color="primary">Cari</x-button>
        </form>
    </div>
</x-card>

<x-card class="mt-6 p-0 overflow-hidden">
    <div class="p-5 border-b border-neutral-100 flex items-center justify-between">
        <h3 class="font-bold text-neutral-800">Riwayat Interview Saya</h3>
    </div>
    
    <div class="p-5 bg-neutral-50 border-b border-neutral-100">
        <form action="{{ route('panitia.dashboard') }}" method="GET" class="flex gap-2">
            <x-text-input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau nomor pendaftaran..." class="flex-1" />
            <x-button type="submit" color="neutral" variant="outline">Cari</x-button>
        </form>
    </div>

    <div class="p-5 bg-white">
        @if($riwayatInterview->isEmpty())
            <div class="text-center py-6 text-neutral-500 text-sm">
                @if(request('search'))
                    Tidak ditemukan riwayat yang cocok dengan pencarian Anda.
                @else
                    Anda belum memproses interview apapun.
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach($riwayatInterview as $log)
                    <div class="p-4 bg-white border border-neutral-200 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-mono font-bold text-neutral-500 mb-1">{{ $log->registration->registration_number }}</div>
                            <div class="font-bold text-neutral-900">{{ $log->registration->full_name }}</div>
                            <div class="text-xs font-medium text-primary-600 mt-1">{{ $log->registration->firstChoiceProgram->name ?? '-' }}</div>
                            <div class="text-[10px] text-neutral-400 mt-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                        <div>
                            @if($log->registration->status === 'diterima' || $log->registration->status === 'menunggu_konfirmasi_daftar_ulang' || $log->registration->status === 'daftar_ulang_selesai')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-success-50 text-success-700 border border-success-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Lulus
                                </span>
                            @elseif($log->registration->status === 'ditolak')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-error-50 text-error-700 border border-error-200">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Tidak Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">
                                    {{ ucfirst(str_replace('_', ' ', $log->registration->status)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $riwayatInterview->links() }}
            </div>
        @endif
    </div>
</x-card>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const html5QrCode = new Html5Qrcode("reader");
        
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        const placeholder = document.getElementById('reader-placeholder');
        const readerDiv = document.getElementById('reader');
        const stopContainer = document.getElementById('stop-scan-container');
        const errorContainer = document.getElementById('camera-error');
        const errorText = document.getElementById('camera-error-text');

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            // Stop scanning once success
            html5QrCode.stop().then(ignore => {
                // redirect
                const url = "{{ route('panitia.scan.show', ':nomor') }}".replace(':nomor', decodedText);
                window.location.href = url;
            }).catch(err => {
                console.error("Stop failed", err);
            });
        };
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

        startBtn.addEventListener('click', () => {
            startBtn.disabled = true;
            startBtn.textContent = 'Memulai...';
            errorContainer.classList.add('hidden');
            
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .then(() => {
                    placeholder.classList.add('hidden');
                    readerDiv.classList.remove('hidden');
                    stopContainer.classList.remove('hidden');
                    startBtn.disabled = false;
                    startBtn.textContent = 'Mulai Scan';
                })
                .catch(err => {
                    errorText.textContent = err;
                    errorContainer.classList.remove('hidden');
                    startBtn.disabled = false;
                    startBtn.textContent = 'Mulai Scan';
                });
        });

        stopBtn.addEventListener('click', () => {
            html5QrCode.stop().then(() => {
                placeholder.classList.remove('hidden');
                readerDiv.classList.add('hidden');
                stopContainer.classList.add('hidden');
            }).catch(err => {
                console.error("Stop failed", err);
            });
        });
    });

    function handleManualSearch(e) {
        e.preventDefault();
        const number = document.getElementById('manual_number').value.trim();
        if(number) {
            const url = "{{ route('panitia.scan.show', ':nomor') }}".replace(':nomor', number);
            window.location.href = url;
        }
    }
</script>
@endpush
