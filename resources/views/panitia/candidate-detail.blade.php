@extends('layouts.panitia')

@section('content')
<!-- BREADCRUMB (Luar Tab) -->
<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('panitia.dashboard') }}" class="inline-flex items-center text-sm font-medium text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg border border-primary-200">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
    </a>
    <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider bg-neutral-200 px-2 py-1 rounded-md">{{ $registration->registration_number }}</span>
</div>

<!-- HEADER RINGKAS KANDIDAT -->
<x-card class="mb-4 p-4 border-l-4 border-l-primary-600 shadow-sm">
    <div class="flex flex-col">
        <span class="text-xs text-neutral-500 uppercase font-bold tracking-wider mb-1">Kandidat</span>
        <span class="text-lg font-black text-neutral-900 leading-tight">{{ $registration->full_name }}</span>
        <span class="text-sm font-medium text-primary-600 mt-1">{{ $registration->firstChoiceProgram->name ?? '-' }}</span>
    </div>
</x-card>

<!-- FORM KEPUTUSAN (Selalu Terlihat di Luar Tab) -->
<x-card class="border-2 border-primary-600 mb-6 p-0 overflow-hidden shadow-sm">
    <div class="p-5 border-b border-neutral-200 bg-white">
        <h2 class="font-bold text-neutral-900 text-lg flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            Keputusan Interview
        </h2>
        <p class="text-xs text-neutral-500 mt-1">Status akan langsung diupdate dan disinkronkan ke akun pendaftar.</p>
    </div>
    <div class="p-5 bg-white">
        <form id="interview-form" action="{{ route('panitia.scan.complete', $registration->registration_number) }}" method="POST" x-data @submit.prevent="$dispatch('open-modal', 'confirm-interview')">
            @csrf
            
            <div class="grid grid-cols-2 gap-4 mb-5">
                <label class="cursor-pointer relative">
                    <input type="radio" name="hasil" value="diterima" class="peer sr-only" required>
                    <div class="rounded-xl border-2 border-neutral-200 bg-white px-4 py-4 hover:bg-neutral-50 peer-checked:border-success-500 peer-checked:bg-success-50 transition text-center">
                        <div class="font-bold text-neutral-900 mb-1">Lulus</div>
                        <div class="text-xs text-success-600">Penuhi Syarat</div>
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-success-500">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                </label>
                
                <label class="cursor-pointer relative">
                    <input type="radio" name="hasil" value="ditolak" class="peer sr-only" required>
                    <div class="rounded-xl border-2 border-neutral-200 bg-white px-4 py-4 hover:bg-neutral-50 peer-checked:border-error-500 peer-checked:bg-error-50 transition text-center">
                        <div class="font-bold text-neutral-900 mb-1">Tidak Lulus</div>
                        <div class="text-xs text-error-600">Gagal Syarat</div>
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-error-500">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    </div>
                </label>
            </div>
            
            <div class="mb-5">
                <x-input-label for="catatan" value="Catatan Interview (Opsional)" />
                <x-textarea name="catatan" id="catatan" rows="3" placeholder="Tambahkan keterangan jika perlu..."></x-textarea>
            </div>
            
            <x-button type="submit" color="primary" size="lg" class="w-full flex items-center justify-center">
                Selesaikan Interview
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </x-button>
        </form>
    </div>
</x-card>

<!-- ALPINE WRAPPER UNTUK TABS -->
<div x-data="{ tab: 'ringkasan' }">

    <!-- TAB BAR (Sticky) -->
    <div class="flex border-b border-neutral-200 mb-6 bg-gray-100/90 backdrop-blur-md sticky top-16 z-40 overflow-x-auto" style="margin-left: -1rem; margin-right: -1rem; padding-left: 1rem; padding-right: 1rem; scrollbar-width: none;">
        <button @click="tab = 'ringkasan'" class="px-4 py-3 whitespace-nowrap text-sm transition-colors border-b-2" :class="tab === 'ringkasan' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-neutral-500 hover:text-neutral-700'">Ringkasan</button>
        <button @click="tab = 'dokumen'" class="px-4 py-3 whitespace-nowrap text-sm transition-colors border-b-2" :class="tab === 'dokumen' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-neutral-500 hover:text-neutral-700'">Dokumen</button>
        <button @click="tab = 'tes_tulis'" class="px-4 py-3 whitespace-nowrap text-sm transition-colors border-b-2" :class="tab === 'tes_tulis' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-neutral-500 hover:text-neutral-700'">Tes Tulis</button>
    </div>

    <!-- KONTEN TAB: RINGKASAN -->
    <div x-show="tab === 'ringkasan'">
        <x-card class="overflow-hidden p-0 shadow-sm mb-4">
            <div class="divide-y divide-neutral-100">
                
                <!-- DATA DIRI -->
                <div x-data="{ open: true }">
                    <div class="p-4 flex items-center justify-between cursor-pointer select-none hover:bg-neutral-50 transition-colors" @click="open = !open">
                        <h3 class="font-bold text-neutral-900 flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Data Diri
                        </h3>
                        <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div x-show="open" x-transition class="p-4 pt-0 space-y-3 bg-white">
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Nama Lengkap</div>
                            <div class="text-neutral-900 font-medium">{{ $registration->full_name }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">NIK</div>
                                <div class="text-neutral-900">{{ $registration->nik }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">NISN</div>
                                <div class="text-neutral-900">{{ $registration->nisn }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Tempat/Tgl Lahir</div>
                                <div class="text-neutral-900">{{ $registration->birth_place }}, {{ $registration->birth_date ? \Carbon\Carbon::parse($registration->birth_date)->format('d M Y') : '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Jenis Kelamin</div>
                                <div class="text-neutral-900">{{ $registration->gender === 'male' ? 'Laki-Laki' : 'Perempuan' }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Agama</div>
                            <div class="text-neutral-900">{{ $registration->religion }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Alamat</div>
                            <div class="text-neutral-900 text-sm">{{ $registration->address }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">No HP/WA</div>
                            <div class="text-neutral-900">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $registration->phone) }}" target="_blank" class="text-success-600 font-medium hover:underline flex items-center">
                                    {{ $registration->phone }}
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PROGRAM STUDI & JALUR -->
                <div x-data="{ open: false }">
                    <div class="p-4 flex items-center justify-between cursor-pointer select-none hover:bg-neutral-50 transition-colors" @click="open = !open">
                        <h3 class="font-bold text-neutral-900 flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Program Studi & Jalur
                        </h3>
                        <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div x-show="open" x-transition class="p-4 pt-0 space-y-3 bg-white">
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Program Studi Pilihan</div>
                            <div class="text-neutral-900 font-bold">{{ $registration->firstChoiceProgram->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Jalur Pendaftaran</div>
                            <div class="inline-block mt-1 bg-primary-50 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold border border-primary-200">
                                {{ ucfirst($registration->admission_path) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATA SEKOLAH -->
                <div x-data="{ open: false }">
                    <div class="p-4 flex items-center justify-between cursor-pointer select-none hover:bg-neutral-50 transition-colors" @click="open = !open">
                        <h3 class="font-bold text-neutral-900 flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 8h5"></path></svg>
                            Data Sekolah
                        </h3>
                        <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div x-show="open" x-transition class="p-4 pt-0 space-y-3 bg-white">
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Asal Sekolah</div>
                            <div class="text-neutral-900 font-medium">{{ $registration->school_name }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 uppercase font-semibold">Jurusan</div>
                            <div class="text-neutral-900">{{ $registration->school_major }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Tahun Lulus</div>
                                <div class="text-neutral-900">{{ $registration->graduation_year }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Nilai Rata-rata</div>
                                <div class="text-neutral-900 font-bold">{{ $registration->school_grade ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATA ORTU -->
                <div x-data="{ open: false }">
                    <div class="p-4 flex items-center justify-between cursor-pointer select-none hover:bg-neutral-50 transition-colors" @click="open = !open">
                        <h3 class="font-bold text-neutral-900 flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Data Orang Tua
                        </h3>
                        <svg class="w-5 h-5 text-neutral-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div x-show="open" x-transition class="p-4 pt-0 space-y-3 bg-white">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Nama Ayah</div>
                                <div class="text-neutral-900">{{ $registration->father_name }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Pekerjaan</div>
                                <div class="text-neutral-900">{{ $registration->father_occupation }}</div>
                            </div>
                        </div>
                        <hr class="border-neutral-100">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Nama Ibu</div>
                                <div class="text-neutral-900">{{ $registration->mother_name }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 uppercase font-semibold">Pekerjaan</div>
                                <div class="text-neutral-900">{{ $registration->mother_occupation }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </x-card>
    </div>

    <!-- KONTEN TAB: DOKUMEN -->
    <div x-show="tab === 'dokumen'" style="display: none;">
        <x-card class="overflow-hidden p-0 shadow-sm mb-4">
            <div class="bg-primary-600 p-4">
                <h3 class="font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Dokumen Persyaratan
                </h3>
            </div>
            <div class="p-0 bg-white">
                <ul class="divide-y divide-neutral-100">
                    @forelse($registration->documents as $doc)
                        <li class="p-4 flex items-center justify-between hover:bg-neutral-50 transition">
                            <div>
                                <div class="font-medium text-neutral-900">
                                    {{ $doc->document_type === 'lainnya' ? ($doc->label ?? 'Dokumen Lainnya') : ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                </div>
                                <div class="text-xs {{ $doc->status === 'disetujui' ? 'text-success-600' : 'text-warning-600' }} font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="px-3 py-1.5 bg-white border border-neutral-300 text-neutral-700 text-sm font-medium rounded-lg hover:bg-neutral-50 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Lihat
                            </a>
                        </li>
                    @empty
                        <li class="p-4 text-center text-neutral-500 text-sm">Tidak ada dokumen.</li>
                    @endforelse
                </ul>
            </div>
        </x-card>
    </div>

    <!-- KONTEN TAB: TES TULIS -->
    <div x-show="tab === 'tes_tulis'" style="display: none;">
        @if($registration->examSession)
        <x-card class="overflow-hidden p-0 mb-4 shadow-sm">
            <div class="bg-primary-600 p-4">
                <h3 class="font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Hasil Tes Tulis
                </h3>
            </div>
            <div class="p-4 bg-primary-50 border-b border-primary-200 flex items-center justify-between">
                <div>
                    <div class="text-xs text-primary-600 font-bold uppercase mb-1">Skor Akhir</div>
                    <div class="text-3xl font-black text-primary-900">{{ rtrim(rtrim($registration->examSession->score, '0'), '.') }}</div>
                </div>
                <div class="px-4 py-2 bg-white rounded-xl shadow-sm border border-primary-200 font-bold text-primary-700">
                    {{ $registration->examSession->result_label === 'sangat_baik' ? 'Sangat Baik' : 'Baik' }}
                </div>
            </div>
        </x-card>
        
        <div x-data="{ showAnswers: false }" class="mb-6">
            <button @click="showAnswers = !showAnswers" type="button" class="w-full flex items-center justify-center p-3 text-sm font-semibold text-primary-700 bg-primary-50 rounded-xl border border-primary-200 hover:bg-primary-100 transition shadow-sm">
                <span x-text="showAnswers ? 'Sembunyikan Rincian Jawaban' : 'Lihat Rincian Jawaban ({{ count($registration->examSession->answers) }} soal)'"></span>
                <svg class="w-4 h-4 ml-1 transition-transform" :class="showAnswers ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            
            <div x-show="showAnswers" x-transition class="mt-4 bg-white rounded-2xl shadow-sm border border-neutral-200 overflow-hidden">
                <ul class="divide-y divide-neutral-100">
                    @foreach($registration->examSession->answers as $index => $answer)
                        <li class="p-4 text-sm">
                            <div class="flex items-start mb-2">
                                <span class="font-bold text-neutral-500 w-6">{{ $index + 1 }}.</span>
                                <p class="font-medium text-neutral-900 flex-1">{!! nl2br(e($answer->question->question_text)) !!}</p>
                            </div>
                            
                            <div class="pl-6 space-y-1.5 mt-2">
                                @php
                                    $options = [
                                        'a' => $answer->question->option_a,
                                        'b' => $answer->question->option_b,
                                        'c' => $answer->question->option_c,
                                        'd' => $answer->question->option_d,
                                    ];
                                @endphp
                                
                                @foreach($options as $key => $text)
                                    @php
                                        $isSelected = $answer->selected_option === $key;
                                        $isCorrect = $answer->question->correct_option === $key;
                                        
                                        $bgClass = 'bg-neutral-50 border-neutral-200 text-neutral-600';
                                        $icon = '';
                                        
                                        if ($isSelected && $isCorrect) {
                                            $bgClass = 'bg-success-50 border-success-300 text-success-800 font-medium';
                                            $icon = '<svg class="w-4 h-4 text-success-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                        } elseif ($isSelected && !$isCorrect) {
                                            $bgClass = 'bg-error-50 border-error-300 text-error-800 font-medium';
                                            $icon = '<svg class="w-4 h-4 text-error-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                        } elseif (!$isSelected && $isCorrect) {
                                            $bgClass = 'bg-success-50 border-success-300 text-success-800 font-medium border-dashed';
                                            $icon = '<svg class="w-4 h-4 text-success-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                        }
                                    @endphp
                                    
                                    <div class="flex items-start px-3 py-2 rounded-lg border {{ $bgClass }} text-xs">
                                        <span class="font-bold mr-2 uppercase">{{ $key }}.</span>
                                        <span class="flex-1">{{ $text }}</span>
                                        @if($icon) {!! $icon !!} @endif
                                    </div>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @else
        <x-card class="p-6 text-center text-neutral-500 text-sm shadow-sm">
            Kandidat belum mengikuti tes tulis.
        </x-card>
        @endif
    </div>

</div> <!-- Akhir Alpine Tab Wrapper -->

<x-modal name="confirm-interview" maxWidth="md">
    <div class="p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center text-warning-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-neutral-900">Konfirmasi Keputusan</h3>
            </div>
        </div>
        <p class="text-sm text-neutral-600 mb-6 pl-14">
            Yakin? Keputusan ini akan langsung mengubah status pendaftar dan tidak bisa diubah dari sini lagi.
        </p>
        <div class="flex justify-end gap-3">
            <x-button type="button" color="neutral" variant="ghost" x-on:click="$dispatch('close')">Batal</x-button>
            <x-button type="button" color="primary" onclick="document.getElementById('interview-form').submit()">Ya, Selesaikan</x-button>
        </div>
    </div>
</x-modal>

@endsection
