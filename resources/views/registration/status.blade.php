@extends('layouts.landing')
@section('title', 'Status Pendaftaran — PMB YPIB Majalengka')

@section('content')
<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
<div class="pub-container">
<div class="max-w-2xl mx-auto">

    {{-- Page header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-[26px] font-bold mb-1.5 text-neutral-900">Status Pendaftaran</h1>
        <p class="text-sm text-neutral-500">PMB YPIB Majalengka 2025/2026</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="pub-flash-success">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="pub-flash-error">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="pub-flash-error flex-col items-start gap-2">
            @foreach($errors->all() as $e)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $e }}
                </div>
            @endforeach
        </div>
    @endif

    @if(!$registration)
    {{-- Belum daftar --}}
    <div class="pub-card text-center py-12 px-6">
        <div class="w-14 h-14 rounded-2xl bg-primary-600/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold mb-2 text-neutral-900">Belum Ada Pendaftaran</h2>
        <p class="text-sm mb-6 text-neutral-500">Anda belum mengisi formulir pendaftaran PMB YPIB.</p>
        <a href="{{ route('landing') }}#program-studi" class="btn-primary inline-flex items-center gap-2">
            <i class="ti ti-book text-lg"></i>
            Pilih Program Studi
        </a>
    </div>

    @else
    @php
        $status = $registration->status;
    @endphp

    {{-- Timeline Card --}}
    <div class="pub-card mb-4">
        <div class="text-[11px] font-bold uppercase tracking-wider mb-5 text-neutral-400">Tahapan Pendaftaran</div>

        <div class="relative pl-14">
            <div class="absolute left-[19px] top-0 bottom-0 w-0.5 bg-[#DEE3E9] z-0"></div>

            @foreach($timelineStages as $index => $stage)
            <div class="relative {{ !$loop->last ? 'mb-6' : '' }}">
                @php
                    $circleClass = 'bg-[#DEE3E9] text-[#8595A4]'; // locked
                    $textClass = 'text-[#8595A4]';
                    if ($stage['state'] === 'completed') {
                        $circleClass = 'bg-[#082e8f] text-white border-[3px] border-white';
                        $textClass = 'text-neutral-900';
                    } elseif ($stage['state'] === 'active') {
                        $circleClass = 'bg-orange-600 text-white border-[3px] border-white';
                        $textClass = 'text-orange-600';
                    } elseif ($stage['state'] === 'rejected') {
                        $circleClass = 'bg-red-600 text-white border-[3px] border-white';
                        $textClass = 'text-red-600';
                    }
                @endphp

                <div class="absolute -left-14 top-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold z-10 {{ $circleClass }}">
                    @if($stage['state'] === 'completed')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @elseif($stage['state'] === 'rejected')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        {{ $stage['number'] }}
                    @endif
                </div>

                <div class="text-sm font-semibold flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 {{ $textClass }}">
                    <span>{{ $stage['label'] }}</span>
                    @if($stage['number'] == 2 && $status === 'menunggu_konfirmasi')
                        <span class="bg-[#e6edfc] text-primary-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Menunggu Konfirmasi</span>
                    @elseif($stage['number'] == 2 && $status === 'menunggu_pembayaran')
                        <span class="bg-orange-50 text-orange-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Belum Dibayar</span>
                    @elseif($stage['number'] == 3 && $status === 'menunggu_review_berkas')
                        <span class="bg-[#e6edfc] text-primary-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Menunggu Review</span>
                    @elseif($stage['number'] == 3 && $status === 'perlu_revisi_berkas')
                        <span class="bg-orange-50 text-orange-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Perlu Revisi</span>
                    @elseif($stage['number'] == 7 && $status === 'menunggu_konfirmasi_daftar_ulang' && $registration->re_registration_payment_proof)
                        <span class="bg-[#e6edfc] text-primary-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Menunggu Konfirmasi</span>
                    @elseif($stage['number'] == 7 && $status === 'diterima')
                        <span class="bg-orange-50 text-orange-600 text-[11px] font-bold py-0.5 px-2.5 rounded-full leading-tight shrink-0">Belum Dibayar</span>
                    @endif
                </div>

                {{-- Custom Content per Stage --}}
                @if($stage['number'] == 1)
                    <div class="text-xs mt-0.5 text-neutral-400">{{ $registration->created_at->isoFormat('D MMMM Y, HH:mm') }}</div>
                    @if(!empty($stage['action_url']))
                        <div class="mt-3">
                            <a href="{{ $stage['action_url'] }}" class="btn-primary inline-flex items-center gap-2 h-10 px-5 text-sm">
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                {{ $stage['action_label'] }}
                            </a>
                        </div>
                    @endif
                @elseif($stage['number'] == 2)
                    @if($stage['state'] === 'active')
                        <div x-data="{ isAlumni: {{ $registration->registration_type === 'alumni' ? 'true' : 'false' }} }">
                            <div class="mt-2 mb-3">
                                <button type="button" @click="isAlumni = !isAlumni" class="text-xs text-primary-600 font-semibold hover:underline bg-transparent border-none p-0 cursor-pointer text-left">
                                    <span x-show="!isAlumni">Saya Alumni YPIB, klik di sini</span>
                                    <span x-show="isAlumni" style="display:none;">Kembali ke Pembayaran Reguler</span>
                                </button>
                            </div>

                            <div x-show="!isAlumni">
                                {{-- Info rekening --}}
                                <div class="bg-amber-50 border border-amber-300 rounded-xl p-5">
                                    <div class="text-[13px] font-bold text-amber-900 mb-3 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Informasi Pembayaran
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
                                        <span>Nominal</span><strong class="text-orange-700">Rp {{ number_format($registration->firstChoiceProgram?->registration_fee ?? 0, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                {{-- Upload bukti --}}
                                <div class="mt-3">
                                    @if($registration->payment_proof)
                                        <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 rounded-lg py-3 px-3.5 text-[13px] mb-2.5">
                                            <svg class="w-4 h-4 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                                            <div>
                                                <div class="font-semibold text-green-700">Bukti sudah diunggah</div>
                                                <div class="text-[11px] text-green-600">{{ basename($registration->payment_proof) }}</div>
                                            </div>
                                            <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank" class="ml-auto text-xs text-green-700 underline hover:text-green-800">Lihat</a>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('registration.upload-proof') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-wrap items-start gap-2.5">
                                            <div class="flex-1 min-w-[200px]">
                                                <label class="flex items-center gap-2.5 border border-neutral-300 bg-white rounded-lg py-2 px-3 cursor-pointer transition-colors hover:bg-neutral-50">
                                                    <svg class="w-[18px] h-[18px] shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                                    <span id="file-name" class="text-[13px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file bukti bayar...</span>
                                                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'Pilih file bukti bayar...'; document.getElementById('file-name').classList.remove('text-neutral-500'); document.getElementById('file-name').classList.add('text-neutral-900');">
                                                </label>
                                            </div>
                                            <button type="submit" class="btn-primary h-10 px-5 text-sm shrink-0">Upload</button>
                                        </div>
                                        <div class="text-[11px] mt-2 leading-relaxed text-neutral-400">
                                            <div>Format file: JPG, PNG, PDF. Maks: 2MB.</div>
                                            <div class="mt-0.5">Atau konfirmasi ke admin: <strong>{{ $registration->period->admin_whatsapp ?? '(0233) 123456' }}</strong></div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div x-show="isAlumni" style="display:none;">
                                {{-- Info Alumni --}}
                                <div class="bg-blue-50 border border-blue-300 rounded-xl p-5">
                                    <div class="text-[13px] font-bold text-blue-900 mb-2 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                                        Verifikasi Alumni YPIB
                                    </div>
                                    <div class="text-[13px] text-blue-900/80 leading-relaxed">
                                        Sebagai alumni YPIB, Anda tidak perlu membayar biaya pendaftaran. Silakan upload kartu siswa/mahasiswa YPIB yang masih berlaku sebagai gantinya.
                                    </div>
                                </div>

                                {{-- Upload bukti alumni --}}
                                <div class="mt-3">
                                    @if($registration->alumni_card_proof)
                                        <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 rounded-lg py-3 px-3.5 text-[13px] mb-2.5">
                                            <svg class="w-4 h-4 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                                            <div>
                                                <div class="font-semibold text-green-700">Kartu Alumni sudah diunggah</div>
                                                <div class="text-[11px] text-green-600">{{ basename($registration->alumni_card_proof) }}</div>
                                            </div>
                                            <a href="{{ Storage::url($registration->alumni_card_proof) }}" target="_blank" class="ml-auto text-xs text-green-700 underline hover:text-green-800">Lihat</a>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('registration.upload-alumni-card') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-wrap items-start gap-2.5">
                                            <div class="flex-1 min-w-[200px]">
                                                <label class="flex items-center gap-2.5 border border-neutral-300 bg-white rounded-lg py-2 px-3 cursor-pointer transition-colors hover:bg-neutral-50">
                                                    <svg class="w-[18px] h-[18px] shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                                    <span id="file-name-alumni" class="text-[13px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file kartu alumni...</span>
                                                    <input type="file" name="alumni_card_proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('file-name-alumni').textContent = this.files[0] ? this.files[0].name : 'Pilih file kartu alumni...'; document.getElementById('file-name-alumni').classList.remove('text-neutral-500'); document.getElementById('file-name-alumni').classList.add('text-neutral-900');">
                                                </label>
                                            </div>
                                            <button type="submit" class="btn-primary h-10 px-5 text-sm shrink-0">Upload Kartu</button>
                                        </div>
                                        <div class="text-[11px] mt-2 leading-relaxed text-neutral-400">
                                            <div>Format file: JPG, PNG, PDF. Maks: 2MB.</div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif($stage['state'] === 'completed')
                        @if($registration->payment_proof)
                            <div class="mt-1">
                                <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank" class="text-xs text-primary-700 hover:underline">Lihat bukti bayar</a>
                            </div>
                        @elseif($registration->alumni_card_proof)
                            <div class="mt-1">
                                <a href="{{ Storage::url($registration->alumni_card_proof) }}" target="_blank" class="text-xs text-primary-700 hover:underline">Lihat kartu alumni</a>
                            </div>
                        @endif

                        @if($registration->registration_number)
                            <div class="mt-2.5 bg-primary-50 border border-primary-200 rounded-xl px-[18px] py-[14px] inline-block">
                                <div class="text-[11px] font-bold mb-1 text-primary-600">NOMOR PENDAFTARAN RESMI</div>
                                <div class="text-2xl font-bold font-mono tracking-wider text-neutral-900">{{ $registration->registration_number }}</div>
                            </div>
                            <div class="text-xs mt-2 text-neutral-400">Simpan nomor ini untuk keperluan administrasi.</div>
                        @endif
                    @else
                        <div class="text-xs mt-0.5 text-neutral-400">{{ $stage['description'] }}</div>
                    @endif
                @elseif($stage['number'] == 3)
                    @if($stage['state'] === 'active')
                        @if(in_array($status, ['menunggu_review_berkas', 'perlu_revisi_berkas']))
                            {{-- Legacy Document Upload Logic --}}
                            @if($status === 'perlu_revisi_berkas')
                                <div class="bg-orange-50 border border-orange-300 rounded-xl p-3.5 mt-2.5 mb-3">
                                    <div class="font-bold text-orange-600 text-[15px] flex items-center gap-1.5">
                                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                        Berkas Perlu Direvisi
                                    </div>
                                    <div class="text-[13px] text-orange-700 mt-1">Silakan upload ulang dokumen Ijazah atau SKL yang lebih jelas/sesuai.</div>
                                </div>
                            @endif

                            @if($registration->document_proof)
                                <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 rounded-lg py-3 px-3.5 text-[13px] mb-2.5 mt-2.5">
                                    <svg class="w-4 h-4 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                                    <div>
                                        <div class="font-semibold text-green-700">Dokumen sudah diunggah</div>
                                        <div class="text-[11px] text-green-600">{{ basename($registration->document_proof) }}</div>
                                    </div>
                                    <a href="{{ Storage::url($registration->document_proof) }}" target="_blank" class="ml-auto text-xs text-green-700 underline hover:text-green-800">Lihat</a>
                                </div>
                            @endif

                            @if($status !== 'menunggu_review_berkas')
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('registration.upload-document') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex flex-wrap items-start gap-2.5">
                                            <div class="flex-1 min-w-[200px]">
                                                <label class="flex items-center gap-2.5 border border-neutral-300 bg-white rounded-lg py-2 px-3 cursor-pointer transition-colors hover:bg-neutral-50">
                                                    <svg class="w-[18px] h-[18px] shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                                    <span id="file-name-doc" class="text-[13px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file Ijazah/SKL...</span>
                                                    <input type="file" name="document_proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('file-name-doc').textContent = this.files[0] ? this.files[0].name : 'Pilih file Ijazah/SKL...'; document.getElementById('file-name-doc').classList.remove('text-neutral-500'); document.getElementById('file-name-doc').classList.add('text-neutral-900');">
                                                </label>
                                            </div>
                                            <button type="submit" class="btn-primary h-10 px-5 text-sm shrink-0">Upload</button>
                                        </div>
                                        <div class="text-[11px] mt-2 leading-relaxed text-neutral-400">
                                            <div>Format file: JPG, PNG, PDF. Maks: 15MB.</div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @else
                            {{-- New Multi-Document Logic for 'terdaftar' --}}
                            <div class="mt-3">
                                <a href="{{ $stage['action_url'] }}" class="btn-primary inline-flex items-center gap-2 h-10 px-5 text-sm">
                                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    {{ $stage['action_label'] }}
                                </a>
                            </div>
                        @endif
                    @elseif($stage['state'] === 'completed')
                        <div class="text-xs mt-0.5 text-green-600 font-semibold">Berkas Disetujui</div>
                    @else
                        <div class="text-xs mt-0.5 text-neutral-400">{{ $stage['description'] }}</div>
                    @endif
                @elseif($stage['number'] == 4)
                    @if($stage['state'] === 'active')
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-2.5">
                            <div class="text-[13px] text-blue-800 leading-relaxed">{{ $stage['description'] }}</div>
                        </div>
                    @else
                        <div class="text-xs mt-0.5 text-neutral-400">{{ $stage['description'] }}</div>
                    @endif
                @elseif($stage['number'] == 7)
                    @if($stage['state'] === 'active' && !$registration->re_registration_payment_proof)
                        {{-- Info rekening daftar ulang --}}
                        <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 mt-3">
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
                        <div class="mt-3">
                            <form method="POST" action="{{ route('registration.upload-re-registration-proof') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="flex flex-wrap items-start gap-2.5">
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="flex items-center gap-2.5 border border-neutral-300 bg-white rounded-lg py-2 px-3 cursor-pointer transition-colors hover:bg-neutral-50">
                                            <svg class="w-[18px] h-[18px] shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                            <span id="file-name-re" class="text-[13px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file bukti bayar...</span>
                                            <input type="file" name="re_registration_payment_proof" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('file-name-re').textContent = this.files[0] ? this.files[0].name : 'Pilih file bukti bayar...'; document.getElementById('file-name-re').classList.remove('text-neutral-500'); document.getElementById('file-name-re').classList.add('text-neutral-900');">
                                        </label>
                                    </div>
                                    <button type="submit" class="btn-primary h-10 px-5 text-sm shrink-0">Upload</button>
                                </div>
                                <div class="text-[11px] mt-2 leading-relaxed text-neutral-400">
                                    <div>Format file: JPG, PNG, PDF. Maks: 2MB.</div>
                                </div>
                            </form>
                        </div>
                    @elseif(($stage['state'] === 'active' || $stage['state'] === 'completed') && $registration->re_registration_payment_proof)
                        <div class="flex items-center gap-2.5 bg-green-50 border border-green-200 rounded-lg py-3 px-3.5 text-[13px] mb-2.5 mt-3">
                            <svg class="w-4 h-4 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                            <div>
                                <div class="font-semibold text-green-700">Bukti Daftar Ulang sudah diunggah</div>
                                <div class="text-[11px] text-green-600">{{ basename($registration->re_registration_payment_proof) }}</div>
                            </div>
                            <a href="{{ Storage::url($registration->re_registration_payment_proof) }}" target="_blank" class="ml-auto text-xs text-green-700 underline hover:text-green-800">Lihat</a>
                        </div>
                        @if($stage['state'] === 'completed')
                            <div class="text-xs font-semibold mt-1 text-green-700">Daftar ulang berhasil dikonfirmasi.</div>
                        @endif
                    @else
                        <div class="text-xs mt-0.5 text-neutral-400">{{ $stage['description'] }}</div>
                    @endif
                @else
                    <div class="text-xs mt-0.5 text-neutral-400">{{ $stage['description'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Ringkasan Pendaftaran --}}
    <div class="pub-card">
        <div class="text-[11px] font-bold uppercase tracking-wider mb-4 text-neutral-400">Ringkasan Pendaftaran</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
            <div><div class="text-xs mb-1 text-neutral-400">Nama</div><div class="text-sm font-medium text-neutral-900">{{ $registration->full_name }}</div></div>
            <div><div class="text-xs mb-1 text-neutral-400">Tanggal Daftar</div><div class="text-sm font-medium text-neutral-900">{{ $registration->created_at->isoFormat('D MMM Y') }}</div></div>
            <div><div class="text-xs mb-1 text-neutral-400">Program Studi</div><div class="text-sm font-medium text-neutral-900">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div></div>
        </div>
    </div>
    @endif

</div>
</div>
</section>
@endsection

