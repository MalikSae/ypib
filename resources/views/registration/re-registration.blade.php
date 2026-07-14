@extends('layouts.landing')
@section('title', 'Pembayaran Daftar Ulang — PMB YPIB Majalengka')
@section('content')

<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
    <div class="pub-container">
        <div class="max-w-3xl mx-auto">

            {{-- Breadcrumb --}}
            <div class="mb-8">
                <a href="{{ route('registration.status') }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Status Pendaftaran
                </a>
            </div>

            {{-- Header --}}
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-neutral-200/60 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-neutral-900 mb-1">Pembayaran Daftar Ulang</h1>
                    <p class="text-sm text-neutral-500">Selesaikan pembayaran untuk mengkonfirmasi status pendaftaran Anda.</p>
                </div>
            </div>

            @if($registration->status === 'diterima' && !$registration->re_registration_payment_proof)
                {{-- Info rekening daftar ulang --}}
                <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 mb-6">
                    <div class="text-[13px] font-bold text-amber-900 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        Informasi Pembayaran Daftar Ulang
                    </div>
                    <div class="grid grid-cols-[100px_1fr] gap-2 text-[13px] text-amber-900/80">
                        <span>Bank</span><strong class="text-amber-950">{{ $registration->period->university_bank_name ?? '-' }}</strong>
                        <span>No. Rekening</span>
                        <div class="flex items-center gap-1.5">
                            <strong class="font-mono text-amber-950">{{ $registration->period->university_bank_account ?? '-' }}</strong>
                            @if(!empty($registration->period->university_bank_account))
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $registration->period->university_bank_account }}').then(() => alert('Nomor rekening berhasil disalin!'))" class="bg-transparent border-none p-0.5 cursor-pointer text-amber-800 flex items-center justify-center transition-colors hover:text-primary-700" title="Salin nomor rekening">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
                                </button>
                            @endif
                        </div>
                        <span>Atas Nama</span><strong class="text-amber-950">{{ $registration->period->university_bank_account_name ?? '-' }}</strong>
                        <span>Nominal</span><strong class="text-orange-700 text-base">Rp {{ number_format($registration->firstChoiceProgram?->re_registration_fee ?? 0, 0, ',', '.') }}</strong>
                    </div>


                    @if($registration->firstChoiceProgram && !empty($registration->firstChoiceProgram->re_registration_fee_details) && count($registration->firstChoiceProgram->re_registration_fee_details) > 0)
                    <div class="mt-4 border-t border-dashed border-amber-300 pt-3">
                        <div class="text-xs font-bold text-amber-900 mb-2">Rincian Tagihan:</div>
                        <table class="w-full border-collapse text-xs">
                            <tbody>
                                @foreach($registration->firstChoiceProgram->re_registration_fee_details as $detail)
                                <tr>
                                    <td class="py-1 text-amber-900/80">{{ $detail['name'] }}</td>
                                    <td class="py-1 text-right font-semibold text-amber-900/80">Rp {{ number_format($detail['amount'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                {{-- Upload bukti daftar ulang --}}
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-neutral-200/60">
                    <h2 class="text-[15px] font-bold text-neutral-900 mb-4">Upload Bukti Pembayaran</h2>
                    
                    @if(($registration->firstChoiceProgram?->re_registration_minimum_payment ?? 0) > 0)
                    <div class="bg-amber-50 border border-amber-300 rounded-lg p-3.5 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-3">
                            <span class="text-xs font-bold text-amber-900">Minimal Pembayaran Awal (DP)</span>
                            <span class="text-lg font-bold text-orange-700">Rp {{ number_format($registration->firstChoiceProgram->re_registration_minimum_payment, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[11px] text-amber-900/70 mt-1.5 leading-relaxed">
                            Anda boleh membayar minimal sejumlah ini terlebih dahulu. Sisa tagihan dapat dilunasi menyusul sesuai ketentuan kampus.
                        </p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('registration.upload-re-registration-proof') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-col sm:flex-row items-start gap-3">
                            <div class="w-full sm:flex-1">
                                <label class="flex items-center gap-2.5 border border-neutral-300 bg-white rounded-xl py-2.5 px-3.5 cursor-pointer transition-colors hover:bg-neutral-50 w-full">
                                    <svg class="w-[18px] h-[18px] shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                    <span id="file-name-re" class="text-[13px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file bukti bayar...</span>
                                    <input type="file" name="re_registration_payment_proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('file-name-re').textContent = this.files[0] ? this.files[0].name : 'Pilih file bukti bayar...'; document.getElementById('file-name-re').classList.remove('text-neutral-500'); document.getElementById('file-name-re').classList.add('text-neutral-900');">
                                </label>
                            </div>
                            <button type="submit" class="btn-primary w-full sm:w-auto h-[46px] px-6 text-sm shrink-0">Kirim Bukti Pembayaran</button>
                        </div>
                        <div class="text-[11px] mt-2.5 leading-relaxed text-neutral-400">
                            <div>Format file: JPG, PNG, PDF. Maksimal ukuran 2MB.</div>
                        </div>
                    </form>
                </div>
            @elseif(in_array($registration->status, ['menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']) && $registration->re_registration_payment_proof)
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-neutral-200/60 mb-6">
                    <div class="flex items-start sm:items-center flex-col sm:flex-row gap-4 sm:gap-6">
                        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-neutral-900 mb-1">
                                @if($registration->status === 'daftar_ulang_selesai')
                                    Daftar Ulang Berhasil Dikonfirmasi
                                @else
                                    Bukti Pembayaran Sedang Diverifikasi
                                @endif
                            </h3>
                            <p class="text-sm text-neutral-500 mb-3">
                                @if($registration->status === 'daftar_ulang_selesai')
                                    Pembayaran daftar ulang Anda telah diverifikasi oleh admin.
                                @else
                                    Mohon menunggu sementara admin mengecek bukti pembayaran Anda.
                                @endif
                            </p>
                            <a href="{{ Storage::url($registration->re_registration_payment_proof) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                Lihat File yang Diunggah
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>
@endsection
