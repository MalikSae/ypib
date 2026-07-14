@extends('layouts.landing')
@section('title', 'Formulir Pendaftaran — PMB YPIB Majalengka')

@section('content')
<section class="py-16 bg-[#F1F4F7] min-h-[calc(100vh-64px)]">
<div class="pub-container">
<div class="max-w-3xl mx-auto">

    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('registration.status') }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-500 hover:text-primary-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Status Pendaftaran
        </a>
    </div>

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-xl font-semibold text-neutral-900 mb-1">Formulir Pendaftaran Saya</h1>
            <p class="text-sm text-neutral-500">Data diri, program studi, asal sekolah, dan orang tua</p>
        </div>
        <div class="flex-shrink-0 w-full sm:w-auto">
            <a href="{{ route('registration.pdf') }}" target="_blank" class="btn-secondary w-full sm:w-auto justify-center">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download Formulir (PDF)
            </a>
        </div>
    </div>

    @if(!$registration->isFormComplete())
    <div class="pub-flash-warning">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <strong>Data Anda belum lengkap.</strong> Mohon lengkapi seluruh field yang ditandai wajib sebelum melanjutkan ke tahap Pemberkasan Dokumen.
        </div>
    </div>
    @elseif(in_array($registration->status, ['terdaftar', 'menunggu_review_berkas', 'perlu_revisi_berkas']))
    <div class="mb-6 bg-green-50/80 rounded-xl border border-green-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 text-center sm:text-left">
            <div class="flex-shrink-0 bg-green-100 p-3 rounded-full hidden sm:block">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-green-900 mb-1">Data Formulir Sudah Lengkap</h3>
                <p class="text-xs sm:text-sm text-green-700">Semua isian wajib telah terisi. Anda dapat melanjutkan ke tahap pengunggahan dokumen.</p>
            </div>
        </div>
        <div class="w-full sm:w-auto shrink-0 mt-2 sm:mt-0">
            <a href="{{ route('registration.documents') }}" class="btn-primary w-full sm:w-auto justify-center">
                Lanjut Pemberkasan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>
    @endif

    @if(!$canEdit)
    <div class="pub-flash-warning">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            Data sudah tidak bisa diedit karena pendaftaran sudah diproses (status: <strong>{{ str_replace('_', ' ', Str::title($registration->status)) }}</strong>).
        </div>
    </div>
    @endif

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

    <div class="space-y-6">
        {{-- Card: Data Diri --}}
        @php $dataDiriErrors = $errors->hasAny(['full_name', 'nisn', 'nik', 'birth_place', 'birth_date', 'gender', 'religion', 'address', 'phone']); @endphp
        <div id="data-diri" class="bg-white border border-neutral-200 rounded-xl shadow-sm" x-data="{ editing: {{ $dataDiriErrors ? 'true' : 'false' }} }">
            <div class="p-6 flex items-start justify-between gap-3 border-b border-neutral-200">
                <h3 class="text-sm font-semibold text-neutral-900 flex-1">Data Diri</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="p-6">
                {{-- View Mode --}}
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">NISN</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->nisn }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">NIK</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->nik }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Tempat, Tanggal Lahir</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->birth_place }}, {{ $registration->birth_date ? $registration->birth_date->format('d-m-Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Jenis Kelamin</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Agama</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->religion }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">No. HP (WhatsApp)</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->phone }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-neutral-500">Alamat Lengkap</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->address }}</dd>
                    </div>
                </div>

                {{-- Edit Mode --}}
                <div x-show="editing">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_diri">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nama Lengkap</label>
                                <input type="text" name="full_name" value="{{ old('full_name', $registration->full_name) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">NISN</label>
                                <input type="text" name="nisn" value="{{ old('nisn', $registration->nisn) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">NIK</label>
                                <input type="text" name="nik" value="{{ old('nik', $registration->nik) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Tempat Lahir</label>
                                <input type="text" name="birth_place" value="{{ old('birth_place', $registration->birth_place) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('birth_place') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date', $registration->birth_date ? $registration->birth_date->format('Y-m-d') : '') }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Jenis Kelamin</label>
                                <select name="gender" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    <option value="male" {{ old('gender', $registration->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="female" {{ old('gender', $registration->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Agama</label>
                                <select name="religion" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $rel)
                                        <option value="{{ $rel }}" {{ old('religion', $registration->religion) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                                @error('religion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">No. HP (WhatsApp)</label>
                                <input type="text" name="phone" value="{{ old('phone', $registration->phone) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-neutral-700">Alamat Lengkap</label>
                                <textarea name="address" rows="3" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">{{ old('address', $registration->address) }}</textarea>
                                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Program Studi & Jalur --}}
        @php $programErrors = $errors->hasAny(['first_choice_program_id', 'admission_path']); @endphp
        <div id="program-jalur" class="bg-white border border-neutral-200 rounded-xl shadow-sm" x-data="{ editing: {{ $programErrors ? 'true' : 'false' }} }">
            <div class="p-6 flex items-start justify-between gap-3 border-b border-neutral-200">
                <h3 class="text-sm font-semibold text-neutral-900 flex-1">Program Studi & Jalur</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Program Studi</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->firstChoiceProgram ? $registration->firstChoiceProgram->name : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Jalur Pendaftaran</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ str_replace('_', ' ', Str::title($registration->admission_path)) }}</dd>
                    </div>
                </div>

                <div x-show="editing">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="program_jalur">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Program Studi</label>
                                <select name="first_choice_program_id" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    <option value="">Pilih Program Studi</option>
                                    @php $programs = \App\Models\Program::where('is_active', true)->get(); @endphp
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('first_choice_program_id', $registration->first_choice_program_id) == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('first_choice_program_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Jalur Pendaftaran</label>
                                <select name="admission_path" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                    <option value="umum" {{ old('admission_path', $registration->admission_path) == 'umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="prestasi" {{ old('admission_path', $registration->admission_path) == 'prestasi' ? 'selected' : '' }}>Prestasi</option>
                                    <option value="tahfidz" {{ old('admission_path', $registration->admission_path) == 'tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                                </select>
                                @error('admission_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Data Sekolah --}}
        @php $sekolahErrors = $errors->hasAny(['school_name', 'school_major', 'graduation_year', 'certificate_number', 'school_grade']); @endphp
        <div id="data-sekolah" class="bg-white border border-neutral-200 rounded-xl shadow-sm" x-data="{ editing: {{ $sekolahErrors ? 'true' : 'false' }} }">
            <div class="p-6 flex items-start justify-between gap-3 border-b border-neutral-200">
                <h3 class="text-sm font-semibold text-neutral-900 flex-1">Data Sekolah Asal</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nama Sekolah</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->school_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Jurusan</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->school_major }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Tahun Lulus</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->graduation_year }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nomor Ijazah / SKL</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->certificate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nilai Rata-rata Rapor (Opsional)</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->school_grade ?? '-' }}</dd>
                    </div>
                </div>

                <div x-show="editing">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_sekolah">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nama Sekolah</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $registration->school_name) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('school_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Jurusan</label>
                                <input type="text" name="school_major" value="{{ old('school_major', $registration->school_major) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('school_major') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Tahun Lulus</label>
                                <input type="text" name="graduation_year" value="{{ old('graduation_year', $registration->graduation_year) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('graduation_year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nomor Ijazah / SKL</label>
                                <input type="text" name="certificate_number" value="{{ old('certificate_number', $registration->certificate_number) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('certificate_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nilai Rata-rata Rapor (Opsional)</label>
                                <input type="number" step="0.01" name="school_grade" value="{{ old('school_grade', $registration->school_grade) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('school_grade') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Data Orang Tua --}}
        @php $ortuErrors = $errors->hasAny(['father_name', 'mother_name', 'father_occupation', 'mother_occupation']); @endphp
        <div id="data-ortu" class="bg-white border border-neutral-200 rounded-xl shadow-sm" x-data="{ editing: {{ $ortuErrors ? 'true' : 'false' }} }">
            <div class="p-6 flex items-start justify-between gap-3 border-b border-neutral-200">
                <h3 class="text-sm font-semibold text-neutral-900 flex-1">Data Orang Tua / Wali</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nama Ayah</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->father_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Pekerjaan Ayah</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->father_occupation }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Nama Ibu</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->mother_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-neutral-500">Pekerjaan Ibu</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $registration->mother_occupation }}</dd>
                    </div>
                </div>

                <div x-show="editing">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_ortu">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nama Ayah</label>
                                <input type="text" name="father_name" value="{{ old('father_name', $registration->father_name) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('father_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Pekerjaan Ayah</label>
                                <input type="text" name="father_occupation" value="{{ old('father_occupation', $registration->father_occupation) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('father_occupation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Nama Ibu</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name', $registration->mother_name) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('mother_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700">Pekerjaan Ibu</label>
                                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $registration->mother_occupation) }}" class="mt-1 block w-full border-neutral-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                @error('mother_occupation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</section>
@endsection
