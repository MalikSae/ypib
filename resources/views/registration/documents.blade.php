@extends('layouts.landing')
@section('title', 'Pemberkasan Dokumen — PMB YPIB Majalengka')

@section('content')
<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
<div class="pub-container">
<div class="max-w-3xl mx-auto">

    {{-- Page header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-[26px] font-bold mb-1.5 text-neutral-900">Pemberkasan Dokumen</h1>
        <p class="text-sm text-neutral-500">Lengkapi berkas pendaftaran Anda</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="pub-flash-success mb-6">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="pub-flash-error mb-6">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="pub-flash-error flex-col items-start gap-2 mb-6">
            @foreach($errors->all() as $e)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $e }}
                </div>
            @endforeach
        </div>
    @endif

    {{-- Back Link & Progress --}}
    @php
        $mandatoryTypes = \App\Models\RegistrationDocument::MANDATORY_TYPES;
        $approvedMandatory = $registration->documents->whereIn('document_type', $mandatoryTypes)->where('status', 'disetujui')->count();
        $totalMandatory = count($mandatoryTypes);
    @endphp
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('registration.status') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-neutral-500 hover:text-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Status
        </a>
        <div class="bg-white px-4 py-2 rounded-full border border-neutral-200 text-sm font-semibold text-neutral-700 shadow-sm flex items-center gap-2">
            <div class="w-2 h-2 rounded-full {{ $approvedMandatory === $totalMandatory ? 'bg-green-500' : 'bg-orange-500' }}"></div>
            Progress: {{ $approvedMandatory }} dari {{ $totalMandatory }} Dokumen Wajib Disetujui
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
        <div class="text-sm text-blue-900">
            Unggah semua dokumen wajib di bawah ini. Format file yang diizinkan: <strong>JPG, PNG, PDF</strong> dengan ukuran maksimal <strong>15MB</strong>. Dokumen yang sudah disetujui tidak dapat diubah kembali.
        </div>
    </div>

    {{-- DOKUMEN WAJIB & OPSIONAL --}}
    @php
        $mainDocuments = $mandatoryLabels;
        $mainDocuments['sertifikat'] = 'Sertifikat (Opsional)';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        @foreach($mainDocuments as $type => $label)
            @php
                $doc = $registration->documents->where('document_type', $type)->first();
                $isApproved = $doc && $doc->status === 'disetujui';
                $isRevision = $doc && $doc->status === 'perlu_revisi';
                $isPending = $doc && $doc->status === 'menunggu_review';
            @endphp
            <div class="bg-white border {{ $isRevision ? 'border-orange-300' : 'border-neutral-200' }} rounded-xl p-5 shadow-sm relative overflow-hidden flex flex-col h-full">
                @if($isApproved)
                    <div class="absolute top-0 right-0 w-2 h-full bg-green-500"></div>
                @elseif($isRevision)
                    <div class="absolute top-0 right-0 w-2 h-full bg-orange-500"></div>
                @elseif($isPending)
                    <div class="absolute top-0 right-0 w-2 h-full bg-blue-500"></div>
                @else
                    <div class="absolute top-0 right-0 w-2 h-full bg-neutral-200"></div>
                @endif

                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-bold text-neutral-900 text-sm leading-tight pr-4">{{ $label }}</h3>
                    @if($isApproved)
                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Disetujui</span>
                    @elseif($isRevision)
                        <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Revisi</span>
                    @elseif($isPending)
                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Menunggu</span>
                    @else
                        <span class="bg-neutral-100 text-neutral-500 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Belum Ada</span>
                    @endif
                </div>

                @if($isRevision && $doc->review_note)
                    <div class="bg-orange-50 text-orange-800 text-xs p-2.5 rounded-lg mb-3 border border-orange-100">
                        <strong>Catatan Admin:</strong> {{ $doc->review_note }}
                    </div>
                @endif

                @if($doc)
                    <div class="mb-3 flex items-center gap-2 text-xs">
                        <svg class="w-4 h-4 text-neutral-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-primary-600 hover:underline font-medium truncate" title="{{ basename($doc->file_path) }}">{{ basename($doc->file_path) }}</a>
                    </div>
                @endif

                <div class="flex-grow"></div>

                @if(!$isApproved)
                    <form method="POST" action="{{ route('registration.upload-document-file') }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <input type="hidden" name="document_type" value="{{ $type }}">
                        <div class="flex items-center gap-2">
                            <label class="flex-1 flex items-center justify-center gap-2 border border-neutral-300 bg-white rounded-lg px-2 cursor-pointer transition-colors hover:bg-neutral-50 h-8">
                                <span id="filename-{{ $type }}" class="text-[11px] whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500" style="max-width: 100px;">Pilih file...</span>
                                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('filename-{{ $type }}').textContent = this.files[0] ? this.files[0].name : 'Pilih file...'; document.getElementById('filename-{{ $type }}').classList.replace('text-neutral-500','text-neutral-900');">
                            </label>
                            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-[11px] font-bold px-3 rounded-lg h-8 transition-colors shrink-0">
                                Upload
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    {{-- DOKUMEN LAINNYA --}}
    <h2 class="text-lg font-bold text-neutral-900 mb-4">Dokumen Lainnya</h2>
    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm mb-8">
        
        @php
            $otherDocs = $registration->documents->where('document_type', 'lainnya');
        @endphp

        @if($otherDocs->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach($otherDocs as $other)
                    <div class="border border-neutral-200 rounded-lg p-3 flex flex-col">
                        <div class="flex items-start justify-between mb-1">
                            <h4 class="font-bold text-sm text-neutral-800">{{ $other->label }}</h4>
                            @if($other->status === 'disetujui')
                                <span class="bg-green-100 text-green-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Disetujui</span>
                            @elseif($other->status === 'perlu_revisi')
                                <span class="bg-orange-100 text-orange-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Revisi</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Menunggu</span>
                            @endif
                        </div>
                        
                        @if($other->status === 'perlu_revisi' && $other->review_note)
                            <div class="text-[11px] text-orange-700 mb-2 italic">Note: {{ $other->review_note }}</div>
                        @endif

                        <a href="{{ Storage::url($other->file_path) }}" target="_blank" class="text-xs text-primary-600 hover:underline mt-auto flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                            Lihat Dokumen
                        </a>
                    </div>
                @endforeach
            </div>
            <hr class="border-neutral-200 mb-5">
        @endif

        <form method="POST" action="{{ route('registration.upload-document-file') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="document_type" value="lainnya">
            
            <div class="text-sm font-bold text-neutral-900 mb-3">Tambah Dokumen Tambahan (Opsional)</div>
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="label" placeholder="Nama Dokumen (misal: Kartu KIP, Piagam)" class="flex-1 h-10 border border-neutral-300 rounded-lg px-3 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                
                <div class="flex items-center gap-2">
                    <label class="flex items-center justify-center border border-neutral-300 bg-white rounded-lg px-3 cursor-pointer transition-colors hover:bg-neutral-50 h-10 min-w-[120px]">
                        <span id="filename-lainnya" class="text-xs whitespace-nowrap overflow-hidden text-ellipsis text-neutral-500">Pilih file...</span>
                        <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange="document.getElementById('filename-lainnya').textContent = this.files[0] ? this.files[0].name : 'Pilih file...'; document.getElementById('filename-lainnya').classList.replace('text-neutral-500','text-neutral-900');">
                    </label>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold px-4 h-10 rounded-lg transition-colors whitespace-nowrap">
                        Upload
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
</div>
</section>
@endsection
