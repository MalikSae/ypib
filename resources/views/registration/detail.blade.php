@extends('layouts.landing')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('registration.status') }}" class="text-blue-600 hover:underline flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Status Pendaftaran
        </a>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Formulir Pendaftaran Saya</h1>
        <a href="{{ route('registration.pdf') }}" target="_blank" class="shrink-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Download PDF
        </a>
    </div>

    @if(!$canEdit)
    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Data sudah tidak bisa diedit karena pendaftaran sudah diproses (status: <strong>{{ str_replace('_', ' ', Str::title($registration->status)) }}</strong>).
                </p>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-md">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif
    
    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-md">
        <p class="text-sm text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    <div class="space-y-6">
        {{-- Card: Data Diri --}}
        @php $dataDiriErrors = $errors->hasAny(['full_name', 'nisn', 'nik', 'birth_place', 'birth_date', 'gender', 'religion', 'address', 'phone']); @endphp
        <div id="data-diri" class="bg-white shadow sm:rounded-lg" x-data="{ editing: {{ $dataDiriErrors ? 'true' : 'false' }} }">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Data Diri</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                {{-- View Mode --}}
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">NISN</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->nisn }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">NIK</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->nik }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tempat, Tanggal Lahir</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->birth_place }}, {{ $registration->birth_date ? $registration->birth_date->format('d-m-Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Kelamin</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agama</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->religion }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">No. HP (WhatsApp)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->phone }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Alamat Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->address }}</dd>
                    </div>
                </div>

                {{-- Edit Mode --}}
                <div x-show="editing" style="display: none;">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_diri">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="full_name" value="{{ old('full_name', $registration->full_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NISN</label>
                                <input type="text" name="nisn" value="{{ old('nisn', $registration->nisn) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIK</label>
                                <input type="text" name="nik" value="{{ old('nik', $registration->nik) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                                <input type="text" name="birth_place" value="{{ old('birth_place', $registration->birth_place) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('birth_place') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date', $registration->birth_date ? $registration->birth_date->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                                <select name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="male" {{ old('gender', $registration->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="female" {{ old('gender', $registration->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Agama</label>
                                <select name="religion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $rel)
                                        <option value="{{ $rel }}" {{ old('religion', $registration->religion) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                                @error('religion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">No. HP (WhatsApp)</label>
                                <input type="text" name="phone" value="{{ old('phone', $registration->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                                <textarea name="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('address', $registration->address) }}</textarea>
                                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Program Studi & Jalur --}}
        @php $programErrors = $errors->hasAny(['first_choice_program_id', 'admission_path']); @endphp
        <div id="program-jalur" class="bg-white shadow sm:rounded-lg" x-data="{ editing: {{ $programErrors ? 'true' : 'false' }} }">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Program Studi & Jalur</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Program Studi</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->firstChoiceProgram ? $registration->firstChoiceProgram->name : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jalur Pendaftaran</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ str_replace('_', ' ', Str::title($registration->admission_path)) }}</dd>
                    </div>
                </div>

                <div x-show="editing" style="display: none;">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="program_jalur">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Program Studi</label>
                                <select name="first_choice_program_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
                                <label class="block text-sm font-medium text-gray-700">Jalur Pendaftaran</label>
                                <select name="admission_path" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="umum" {{ old('admission_path', $registration->admission_path) == 'umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="prestasi" {{ old('admission_path', $registration->admission_path) == 'prestasi' ? 'selected' : '' }}>Prestasi</option>
                                    <option value="tahfidz" {{ old('admission_path', $registration->admission_path) == 'tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                                </select>
                                @error('admission_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Data Sekolah --}}
        @php $sekolahErrors = $errors->hasAny(['school_name', 'school_major', 'graduation_year', 'certificate_number', 'school_grade']); @endphp
        <div id="data-sekolah" class="bg-white shadow sm:rounded-lg" x-data="{ editing: {{ $sekolahErrors ? 'true' : 'false' }} }">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Data Sekolah Asal</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Sekolah</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->school_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jurusan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->school_major }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tahun Lulus</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->graduation_year }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Ijazah / SKL</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->certificate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nilai Rata-rata Rapor (Opsional)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->school_grade ?? '-' }}</dd>
                    </div>
                </div>

                <div x-show="editing" style="display: none;">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_sekolah">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                                <input type="text" name="school_name" value="{{ old('school_name', $registration->school_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('school_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                                <input type="text" name="school_major" value="{{ old('school_major', $registration->school_major) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('school_major') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tahun Lulus</label>
                                <input type="text" name="graduation_year" value="{{ old('graduation_year', $registration->graduation_year) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('graduation_year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Ijazah / SKL</label>
                                <input type="text" name="certificate_number" value="{{ old('certificate_number', $registration->certificate_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('certificate_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai Rata-rata Rapor (Opsional)</label>
                                <input type="number" step="0.01" name="school_grade" value="{{ old('school_grade', $registration->school_grade) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('school_grade') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card: Data Orang Tua --}}
        @php $ortuErrors = $errors->hasAny(['father_name', 'mother_name', 'father_occupation', 'mother_occupation']); @endphp
        <div id="data-ortu" class="bg-white shadow sm:rounded-lg" x-data="{ editing: {{ $ortuErrors ? 'true' : 'false' }} }">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Data Orang Tua / Wali</h3>
                @if($canEdit)
                <button @click="editing = !editing" type="button" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    <span x-show="!editing">Edit</span>
                    <span x-show="editing">Batal</span>
                </button>
                @endif
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <div x-show="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Ayah</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->father_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Pekerjaan Ayah</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->father_occupation }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Ibu</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->mother_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Pekerjaan Ibu</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $registration->mother_occupation }}</dd>
                    </div>
                </div>

                <div x-show="editing" style="display: none;">
                    <form action="{{ route('registration.detail.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="section" value="data_ortu">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Ayah</label>
                                <input type="text" name="father_name" value="{{ old('father_name', $registration->father_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('father_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pekerjaan Ayah</label>
                                <input type="text" name="father_occupation" value="{{ old('father_occupation', $registration->father_occupation) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('father_occupation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Ibu</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name', $registration->mother_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('mother_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pekerjaan Ibu</label>
                                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $registration->mother_occupation) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('mother_occupation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
