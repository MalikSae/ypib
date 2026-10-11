@extends('layouts.admin')
@section('title', (isset($program) ? 'Edit Program Studi' : 'Tambah Program Studi') . ' — Admin PMB YPIB')
@section('page-title', isset($program) ? 'Edit Program Studi' : 'Tambah Program Studi')

@section('content')

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div class="max-w-4xl">
    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
        <a href="{{ route('admin.programs.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors shrink-0 decoration-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 mb-1">{{ isset($program) ? 'Edit Program Studi' : 'Tambah Program Studi' }}</h1>
            <p class="text-sm text-neutral-500">Lengkapi form di bawah ini.</p>
        </div>
    </div>

    <x-card class="p-6 md:p-8">
        <form action="{{ isset($program) ? route('admin.programs.update', $program->id) : route('admin.programs.store') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false, selectedIcon: '{{ old('icon', $program->icon ?? '') }}' }" @submit="submitting = true">
            @csrf
            @if(isset($program)) @method('PUT') @endif

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- SECTION 1: INFORMASI DASAR                      --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-neutral-900 mb-3">Informasi Dasar</label>
                <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 space-y-5">

                    {{-- Baris 1: Nama | Fakultas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" value="Nama Program Studi" required="true" />
                            <x-text-input type="text" id="name" name="name" :value="old('name', $program->name ?? '')" required
                                          placeholder="Contoh: S1 Ilmu Keperawatan" :error="$errors->has('name')" />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="faculty_id" value="Fakultas" required="true" />
                            <x-select id="faculty_id" name="faculty_id" required :error="$errors->has('faculty_id')">
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" {{ old('faculty_id', $program->faculty_id ?? '') == $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name }}
                                    </option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('faculty_id')" />
                        </div>
                    </div>

                    {{-- Baris 2: Kode Prodi | Tipe Program --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="kode_prodi" value="Kode Prodi" />
                            <x-text-input type="text" id="kode_prodi" name="kode_prodi"
                                          :value="old('kode_prodi', $program->kode_prodi ?? '')"
                                          maxlength="5" inputmode="numeric" pattern="[0-9]{5}"
                                          placeholder="Contoh: 15201"
                                          :error="$errors->has('kode_prodi')" />
                            <div class="text-xs text-neutral-400 mt-1.5">5 digit sesuai kode resmi program studi. Kosongkan jika belum tersedia.</div>
                            <x-input-error :messages="$errors->get('kode_prodi')" />
                        </div>
                        <div>
                            <x-input-label for="registration_track" value="Tipe Program" required="true" />
                            <x-select id="registration_track" name="registration_track" required :error="$errors->has('registration_track')">
                                <option value="reguler" {{ old('registration_track', $program->registration_track ?? 'reguler') == 'reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="non_reguler" {{ old('registration_track', $program->registration_track ?? 'reguler') == 'non_reguler' ? 'selected' : '' }}>Non-Reguler</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('registration_track')" />
                        </div>
                    </div>

                    {{-- Baris 3: Akreditasi | Kuota --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="accreditation" value="Akreditasi" />
                            <x-select id="accreditation" name="accreditation" :error="$errors->has('accreditation')">
                                <option value="">-- Pilih Akreditasi --</option>
                                <option value="Baik" {{ old('accreditation', $program->accreditation ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Baik Sekali" {{ old('accreditation', $program->accreditation ?? '') == 'Baik Sekali' ? 'selected' : '' }}>Baik Sekali</option>
                                <option value="Unggul" {{ old('accreditation', $program->accreditation ?? '') == 'Unggul' ? 'selected' : '' }}>Unggul</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('accreditation')" />
                        </div>
                    </div>

                </div>
            </div>

            {{-- Biaya, kuota, dan komisi tidak lagi diatur per prodi: berbeda tiap periode. --}}
            <div class="mb-8">
                <div class="p-5 rounded-xl border border-info-200 bg-info-50 text-sm text-neutral-700">
                    <strong class="text-neutral-900">Biaya, kuota, dan komisi diatur per periode.</strong>
                    Buka <a href="{{ route('admin.periods.index') }}" class="text-primary-700 font-semibold">Periode PMB</a>
                    lalu pilih <em>Biaya &amp; Kuota</em> pada periode yang dituju. Prodi baru tidak ditawarkan
                    sebelum biayanya diatur di sana.
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- SECTION 4+: STATUS, IKON, DESKRIPSI, GALERI     --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="mb-6">
                <x-input-label for="is_active" value="Status Aktif" />
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $program->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600">
                    <span class="text-sm text-neutral-600">Tampilkan di publik</span>
                </label>
                <x-input-error :messages="$errors->get('is_active')" />
            </div>

            <div class="mb-6">
                <x-input-label for="icon" value="Ikon Program Studi" />
                <div class="text-xs text-neutral-500 mb-2">Pilih ikon yang akan ditampilkan di kartu program studi. Jika tidak dipilih, akan menampilkan inisial huruf pertama.</div>
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 max-h-64 overflow-y-auto p-4 border border-neutral-200 rounded-lg bg-neutral-50">
                    <label class="cursor-pointer relative group" title="Inisial Teks">
                        <input type="radio" name="icon" value="" class="peer hidden" {{ old('icon', $program->icon ?? '') == '' ? 'checked' : '' }}>
                        <div class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent peer-checked:border-primary-500 peer-checked:bg-primary-50 group-hover:bg-neutral-100 peer-checked:group-hover:bg-primary-50 transition-all">
                            <div class="w-8 h-8 flex items-center justify-center text-xs font-bold text-neutral-400 border border-neutral-300 rounded bg-white">Aa</div>
                        </div>
                    </label>
                    @if(isset($icons))
                        @foreach($icons as $iconFile)
                            <label class="cursor-pointer relative group" title="{{ $iconFile }}">
                                <input type="radio" name="icon" value="{{ $iconFile }}" class="peer hidden" {{ old('icon', $program->icon ?? '') == $iconFile ? 'checked' : '' }}>
                                <div class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:shadow-sm group-hover:bg-neutral-100 peer-checked:group-hover:bg-primary-50 transition-all">
                                    <img src="{{ asset('images/icons/' . $iconFile) }}" alt="{{ $iconFile }}" class="w-8 h-8 object-contain">
                                </div>
                            </label>
                        @endforeach
                    @endif
                </div>
                <x-input-error :messages="$errors->get('icon')" />
            </div>

            <div class="mb-6">
                <x-input-label for="description" value="Deskripsi Program Studi" required="true" />
                <x-textarea name="description" id="description" :error="$errors->has('description')">{{ old('description', $program->description ?? '') }}</x-textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="mb-6">
                <label for="gallery-input" class="block text-sm font-semibold text-neutral-900 mb-2">Galeri Foto</label>
                <div class="p-4 rounded-lg bg-neutral-50 border border-neutral-200">
                    <input type="file" id="gallery-input" name="gallery[]" multiple accept="image/*" class="text-sm text-neutral-600 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                    <div class="text-xs text-neutral-500 mt-2">Anda bisa memilih banyak foto sekaligus, atau klik "Choose Files" lagi untuk menambah foto lainnya.</div>
                </div>
                <x-input-error :messages="$errors->get('gallery')" />
                @foreach($errors->get('gallery.*') as $message)
                    <p class="text-sm text-error mt-1">{{ $message[0] }}</p>
                @endforeach

                <div id="gallery-preview-container" style="display:none;" class="mt-4">
                    <div class="text-sm font-semibold text-neutral-900 mb-2">Preview Foto Baru (Akan Diupload):</div>
                    <div id="gallery-preview-list" class="flex flex-wrap gap-3"></div>
                </div>

                @if(isset($program) && $program->galleries && $program->galleries->count() > 0)
                    <div class="mt-6">
                        <div class="text-sm font-semibold text-neutral-600 mb-2">Foto yang sudah ada:</div>
                        <div class="flex flex-wrap gap-3">
                            @foreach($program->galleries as $gallery)
                                <div class="gallery-item relative w-28 h-28 rounded-lg overflow-hidden border border-neutral-200 group" id="gallery-{{ $gallery->id }}">
                                    <img src="{{ asset('storage/' . $gallery->image_path) }}" class="w-full h-full object-cover">
                                    <button type="button" onclick="deleteGallery({{ $gallery->id }})" class="absolute top-1 right-1 bg-error/90 hover:bg-error text-white border-none rounded p-1 text-[10px] cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center w-6 h-6" aria-label="Hapus Foto">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 mt-8">
                <a href="{{ route('admin.programs.index') }}" class="decoration-none">
                    <x-button type="button" color="neutral" variant="ghost">Batal</x-button>
                </a>
                <x-button type="submit" color="primary" ::disabled="submitting">
                    <span x-show="!submitting">Simpan</span>
                    <span x-show="submitting" style="display:none;" class="flex items-center gap-2">
                        <i class="ti ti-loader animate-spin"></i> Menyimpan...
                    </span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>


<!-- (The rest of the JS is left largely untouched, just the HTML elements were styled with x-components and grid adjustments) -->
<script>
    $(document).ready(function() {
        $('#description').summernote({
            placeholder: 'Tulis deskripsi dan keunggulan program studi...',
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });

        // Gallery Accumulation & Preview
        let dt = new DataTransfer();
        
        $('#gallery-input').on('change', function(e) {
            let newFiles = e.target.files;
            for (let i = 0; i < newFiles.length; i++) {
                dt.items.add(newFiles[i]);
            }
            updateGalleryInputAndPreview();
        });

        function updateGalleryInputAndPreview() {
            let input = document.getElementById('gallery-input');
            input.files = dt.files;
            
            let previewList = $('#gallery-preview-list');
            previewList.empty();
            
            if(dt.files.length > 0) {
                $('#gallery-preview-container').show();
            } else {
                $('#gallery-preview-container').hide();
            }
            
            for (let i = 0; i < dt.files.length; i++) {
                let file = dt.files[i];
                let reader = new FileReader();
                reader.onload = function(event) {
                    let html = `
                        <div class="relative w-24 h-24 rounded-lg overflow-hidden border border-neutral-200 group">
                            <img src="${event.target.result}" class="w-full h-full object-cover">
                            <button type="button" onclick="removePreview(${i})" class="absolute top-1 right-1 bg-error/90 hover:bg-error text-white border-none rounded p-1 text-[10px] cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center w-5 h-5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    `;
                    previewList.append(html);
                }
                reader.readAsDataURL(file);
            }
        }

        window.removePreview = function(index) {
            let newDt = new DataTransfer();
            for (let i = 0; i < dt.files.length; i++) {
                if (i !== index) {
                    newDt.items.add(dt.files[i]);
                }
            }
            dt = newDt;
            updateGalleryInputAndPreview();
        }
    });

    function deleteGallery(id) {
        if(confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
            $.ajax({
                url: '/admin/programs/gallery/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.success) {
                        $('#gallery-' + id).remove();
                    }
                }
            });
        }
    }
</script>
@endsection
