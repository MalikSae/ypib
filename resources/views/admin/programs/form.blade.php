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
        <form action="{{ isset($program) ? route('admin.programs.update', $program->id) : route('admin.programs.store') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if(isset($program)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <x-input-label for="accreditation" value="Akreditasi" />
                    <x-text-input type="text" id="accreditation" name="accreditation" :value="old('accreditation', $program->accreditation ?? '')"
                                  placeholder="Contoh: B atau Unggul" :error="$errors->has('accreditation')" />
                    <x-input-error :messages="$errors->get('accreditation')" />
                </div>

                <div>
                    <x-input-label for="quota" value="Kuota" required="true" />
                    <x-text-input type="number" id="quota" name="quota" :value="old('quota', $program->quota ?? 0)" required min="0" :error="$errors->has('quota')" />
                    <x-input-error :messages="$errors->get('quota')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <x-input-label for="registration_fee_display" value="Biaya Pendaftaran" required="true" />
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-neutral-400">Rp</div>
                        <x-text-input type="text" id="registration_fee_display" :value="number_format(old('registration_fee', $program->registration_fee ?? 0), 0, '', '.')" required
                                      placeholder="250.000" style="padding-left: 3rem;" :error="$errors->has('registration_fee')" />
                        <input type="hidden" name="registration_fee" id="registration_fee" value="{{ old('registration_fee', $program->registration_fee ?? 0) }}">
                    </div>
                    <div class="text-xs text-neutral-400 mt-1.5">Format ribuan akan muncul otomatis.</div>
                    <x-input-error :messages="$errors->get('registration_fee')" />
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-neutral-900 mb-2">Rincian Biaya Daftar Ulang</label>
                <div class="rounded-lg overflow-hidden border border-neutral-200">
                    <table class="w-full text-left border-collapse" id="fee-table">
                        <thead class="bg-neutral-50 border-b border-neutral-200">
                            <tr>
                                <th class="px-4 py-2 text-sm font-semibold text-neutral-500 w-[60%]">Jenis Biaya</th>
                                <th class="px-4 py-2 text-sm font-semibold text-neutral-500">Nominal (Rp)</th>
                                <th class="px-4 py-2 text-sm font-semibold text-center text-neutral-500 w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="fee-tbody">
                            @php
                                $feeDetails = old('fee_names') ? null : ($program->re_registration_fee_details ?? []);
                                if(old('fee_names')) {
                                    foreach(old('fee_names') as $i => $name) {
                                        $feeDetails[] = ['name' => $name, 'amount' => str_replace('.', '', old('fee_amounts')[$i])];
                                    }
                                }
                            @endphp

                            @if(empty($feeDetails))
                                <tr class="fee-row border-b border-neutral-200">
                                    <td class="px-4 py-3">
                                        <input type="text" name="fee_names[]" class="fee-name w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" required
                                               placeholder="Contoh: Dana Pengembangan Pendidikan">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" class="fee-amount-display w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" required
                                               placeholder="0">
                                        <input type="hidden" name="fee_amounts[]" class="fee-amount" value="0">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" class="remove-fee-btn text-error hover:text-error-700 bg-transparent border-none cursor-pointer p-1 transition-colors" title="Hapus" aria-label="Hapus Baris">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            @else
                                @foreach($feeDetails as $detail)
                                    <tr class="fee-row border-b border-neutral-200">
                                        <td class="px-4 py-3">
                                            <input type="text" name="fee_names[]" class="fee-name w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" value="{{ $detail['name'] }}" required
                                                   placeholder="Contoh: Dana Pengembangan Pendidikan">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" class="fee-amount-display w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" value="{{ number_format($detail['amount'], 0, '', '.') }}" required
                                                   placeholder="0">
                                            <input type="hidden" name="fee_amounts[]" class="fee-amount" value="{{ $detail['amount'] }}">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" class="remove-fee-btn text-error hover:text-error-700 bg-transparent border-none cursor-pointer p-1 transition-colors" title="Hapus" aria-label="Hapus Baris">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="bg-neutral-50 border-t border-neutral-200">
                            <tr>
                                <td colspan="3" class="px-4 py-3">
                                    <button type="button" id="add-fee-btn" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700 bg-transparent border-none cursor-pointer transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        Tambah Rincian
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-t border-neutral-200">
                                <td class="px-4 py-4 text-right text-sm font-bold text-neutral-900">TOTAL BIAYA DAFTAR ULANG:</td>
                                <td colspan="2" class="px-4 py-4 text-lg font-extrabold text-primary-600">
                                    Rp <span id="total-fee-display">0</span>
                                    <input type="hidden" name="re_registration_fee" id="total_re_registration_fee" value="0">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="text-xs text-neutral-400 mt-1.5">Total biaya akan dihitung otomatis dari rincian di atas.</div>
            </div>

            <div class="mb-6">
                <x-input-label for="is_active" value="Status Aktif" />
                <label class="inline-flex items-center cursor-pointer gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $program->is_active ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-primary-600">
                    <span class="text-sm text-neutral-600">Tampilkan di publik</span>
                </label>
                <x-input-error :messages="$errors->get('is_active')" />
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

        // Currency mask
        const formatNumber = (n) => {
            return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };
        
        $('#registration_fee_display').on('input', function() {
            let val = $(this).val();
            let cleanVal = val.replace(/\D/g, "");
            $(this).val(formatNumber(val));
            $('#registration_fee').val(cleanVal);
        });

        // Dynamic Fee Table Logic
        function calculateTotalFee() {
            let total = 0;
            $('.fee-amount').each(function() {
                let val = parseInt($(this).val()) || 0;
                total += val;
            });
            $('#total-fee-display').text(formatNumber(total.toString()));
            $('#total_re_registration_fee').val(total);
        }

        function bindFeeRowEvents(row) {
            row.find('.fee-amount-display').on('input', function() {
                let val = $(this).val();
                let cleanVal = val.replace(/\D/g, "");
                $(this).val(formatNumber(val));
                row.find('.fee-amount').val(cleanVal);
                calculateTotalFee();
            });

            row.find('.remove-fee-btn').on('click', function() {
                if ($('.fee-row').length > 1) {
                    row.remove();
                    calculateTotalFee();
                } else {
                    row.find('input').val('');
                    row.find('.fee-amount').val('0');
                    calculateTotalFee();
                }
            });
        }

        // Bind existing rows
        $('.fee-row').each(function() {
            bindFeeRowEvents($(this));
        });
        calculateTotalFee();

        $('#add-fee-btn').on('click', function() {
            let newRow = `
                <tr class="fee-row border-b border-neutral-200">
                    <td class="px-4 py-3">
                        <input type="text" name="fee_names[]" class="fee-name w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" required
                               placeholder="Contoh: Dana Pengembangan Pendidikan">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" class="fee-amount-display w-full px-3 py-2 border border-neutral-300 rounded-md text-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 outline-none transition-colors" required
                               placeholder="0">
                        <input type="hidden" name="fee_amounts[]" class="fee-amount" value="0">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" class="remove-fee-btn text-error hover:text-error-700 bg-transparent border-none cursor-pointer p-1 transition-colors" title="Hapus" aria-label="Hapus Baris">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </td>
                </tr>
            `;
            let $newRow = $(newRow);
            $('#fee-tbody').append($newRow);
            bindFeeRowEvents($newRow);
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
