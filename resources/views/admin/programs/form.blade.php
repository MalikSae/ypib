@extends('layouts.admin')
@section('title', (isset($program) ? 'Edit Program Studi' : 'Tambah Program Studi') . ' — Admin PMB YPIB')
@section('page-title', isset($program) ? 'Edit Program Studi' : 'Tambah Program Studi')

@section('content')

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:32px;max-width:900px;">
    
    <form action="{{ isset($program) ? route('admin.programs.update', $program->id) : route('admin.programs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($program)) @method('PUT') @endif

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Nama Program Studi</label>
                <input type="text" name="name" value="{{ old('name', $program->name ?? '') }}" required
                       style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;"
                       placeholder="Contoh: S1 Ilmu Keperawatan">
                @error('name') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Fakultas</label>
                <select name="faculty_id" required style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;background:#fff;">
                    <option value="">-- Pilih Fakultas --</option>
                    @foreach($faculties as $faculty)
                        <option value="{{ $faculty->id }}" {{ old('faculty_id', $program->faculty_id ?? '') == $faculty->id ? 'selected' : '' }}>
                            {{ $faculty->name }}
                        </option>
                    @endforeach
                </select>
                @error('faculty_id') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Akreditasi</label>
                <input type="text" name="accreditation" value="{{ old('accreditation', $program->accreditation ?? '') }}"
                       style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;"
                       placeholder="Contoh: B atau Unggul">
                @error('accreditation') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Kuota</label>
                <input type="number" name="quota" value="{{ old('quota', $program->quota ?? 0) }}" required min="0"
                       style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;">
                @error('quota') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Biaya Pendaftaran</label>
                <div style="position:relative;">
                    <div style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-weight:600;color:#8595A4;">Rp</div>
                    <input type="text" id="registration_fee_display" value="{{ number_format(old('registration_fee', $program->registration_fee ?? 0), 0, '', '.') }}" required
                           style="width:100%;padding:12px 16px 12px 48px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;"
                           placeholder="250.000">
                    <input type="hidden" name="registration_fee" id="registration_fee" value="{{ old('registration_fee', $program->registration_fee ?? 0) }}">
                </div>
                <div style="font-size:12px;color:#8595A4;margin-top:6px;">Format ribuan akan muncul otomatis.</div>
                @error('registration_fee') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>
            <div></div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Rincian Biaya Daftar Ulang</label>
            <div style="border:1px solid #DEE3E9;border-radius:8px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;" id="fee-table">
                    <thead style="background:#F9FAFB;border-bottom:1px solid #DEE3E9;">
                        <tr>
                            <th style="padding:12px 16px;text-align:left;font-size:13px;font-weight:600;color:#5D6C7B;width:60%;">Jenis Biaya</th>
                            <th style="padding:12px 16px;text-align:left;font-size:13px;font-weight:600;color:#5D6C7B;">Nominal (Rp)</th>
                            <th style="padding:12px 16px;text-align:center;font-size:13px;font-weight:600;color:#5D6C7B;width:60px;">Aksi</th>
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
                            <tr class="fee-row" style="border-bottom:1px solid #DEE3E9;">
                                <td style="padding:12px 16px;">
                                    <input type="text" name="fee_names[]" class="fee-name" required
                                           style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                                           placeholder="Contoh: Dana Pengembangan Pendidikan">
                                </td>
                                <td style="padding:12px 16px;">
                                    <input type="text" class="fee-amount-display" required
                                           style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                                           placeholder="0">
                                    <input type="hidden" name="fee_amounts[]" class="fee-amount" value="0">
                                </td>
                                <td style="padding:12px 16px;text-align:center;">
                                    <button type="button" class="remove-fee-btn" style="color:#C62828;background:none;border:none;cursor:pointer;padding:4px;" title="Hapus">
                                        <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @else
                            @foreach($feeDetails as $detail)
                                <tr class="fee-row" style="border-bottom:1px solid #DEE3E9;">
                                    <td style="padding:12px 16px;">
                                        <input type="text" name="fee_names[]" class="fee-name" value="{{ $detail['name'] }}" required
                                               style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                                               placeholder="Contoh: Dana Pengembangan Pendidikan">
                                    </td>
                                    <td style="padding:12px 16px;">
                                        <input type="text" class="fee-amount-display" value="{{ number_format($detail['amount'], 0, '', '.') }}" required
                                               style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                                               placeholder="0">
                                        <input type="hidden" name="fee_amounts[]" class="fee-amount" value="{{ $detail['amount'] }}">
                                    </td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        <button type="button" class="remove-fee-btn" style="color:#C62828;background:none;border:none;cursor:pointer;padding:4px;" title="Hapus">
                                            <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot style="background:#F9FAFB;">
                        <tr>
                            <td colspan="3" style="padding:12px 16px;">
                                <button type="button" id="add-fee-btn" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#082e8f;background:none;border:none;cursor:pointer;">
                                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Tambah Rincian
                                </button>
                            </td>
                        </tr>
                        <tr style="border-top:2px solid #DEE3E9;">
                            <td style="padding:16px;text-align:right;font-size:14px;font-weight:700;color:#0A1317;">TOTAL BIAYA DAFTAR ULANG:</td>
                            <td colspan="2" style="padding:16px;font-size:18px;font-weight:800;color:#082e8f;">
                                Rp <span id="total-fee-display">0</span>
                                <input type="hidden" name="re_registration_fee" id="total_re_registration_fee" value="0">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="font-size:12px;color:#8595A4;margin-top:6px;">Total biaya akan dihitung otomatis dari rincian di atas.</div>
        </div>

        <div style="display:grid; grid-template-columns:1fr; gap:24px; margin-bottom:24px;">

            <div>
                <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Status Aktif</label>
                <label style="display:inline-flex;align-items:center;cursor:pointer;gap:8px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $program->is_active ?? true) ? 'checked' : '' }} style="width:20px;height:20px;accent-color:#082e8f;">
                    <span style="font-size:14px;color:#444950;">Tampilkan di publik</span>
                </label>
                @error('is_active') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Deskripsi Program Studi</label>
            <textarea name="description" id="description" style="width:100%;padding:12px 16px;border:1px solid #DEE3E9;border-radius:8px;font-size:14px;outline:none;min-height:120px;">{{ old('description', $program->description ?? '') }}</textarea>
            @error('description') <div style="color:#C62828;font-size:13px;margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:14px;font-weight:600;color:#444950;margin-bottom:8px;">Galeri Foto</label>
            <div style="padding:16px;border:1px dashed #DEE3E9;border-radius:8px;background:#F9FAFB;">
                <input type="file" id="gallery-input" name="gallery[]" multiple accept="image/*" style="font-size:14px;color:#444950;">
                <div style="font-size:12px;color:#8595A4;margin-top:8px;">Anda bisa memilih banyak foto sekaligus, atau klik "Choose Files" lagi untuk menambah foto lainnya.</div>
            </div>
            
            <div id="gallery-preview-container" style="display:none; margin-top:16px;">
                <div style="font-size:13px;font-weight:600;color:#0A1317;margin-bottom:8px;">Preview Foto Baru (Akan Diupload):</div>
                <div id="gallery-preview-list" style="display:flex;gap:12px;flex-wrap:wrap;"></div>
            </div>
            
            @if(isset($program) && $program->galleries->count() > 0)
                <div style="margin-top:16px;">
                    <div style="font-size:13px;font-weight:600;color:#444950;margin-bottom:8px;">Foto yang sudah ada:</div>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        @foreach($program->galleries as $gallery)
                            <div class="gallery-item" id="gallery-{{ $gallery->id }}" style="position:relative;width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid #DEE3E9;">
                                <img src="{{ asset('storage/' . $gallery->image_path) }}" style="width:100%;height:100%;object-fit:cover;">
                                <button type="button" onclick="deleteGallery({{ $gallery->id }})" style="position:absolute;top:4px;right:4px;background:#C62828;color:#fff;border:none;border-radius:4px;padding:4px 8px;font-size:11px;cursor:pointer;">Hapus</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div style="display:flex;gap:12px;margin-top:32px;">
            <button type="submit" style="background:#082e8f;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('admin.programs.index') }}" style="background:#F1F4F7;color:#444950;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid #DEE3E9;">
                Batal
            </a>
        </div>
    </form>
</div>

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
                <tr class="fee-row" style="border-bottom:1px solid #DEE3E9;">
                    <td style="padding:12px 16px;">
                        <input type="text" name="fee_names[]" class="fee-name" required
                               style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                               placeholder="Contoh: Dana Pengembangan Pendidikan">
                    </td>
                    <td style="padding:12px 16px;">
                        <input type="text" class="fee-amount-display" required
                               style="width:100%;padding:8px 12px;border:1px solid #CED0D4;border-radius:6px;font-size:14px;outline:none;"
                               placeholder="0">
                        <input type="hidden" name="fee_amounts[]" class="fee-amount" value="0">
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <button type="button" class="remove-fee-btn" style="color:#C62828;background:none;border:none;cursor:pointer;padding:4px;" title="Hapus">
                            <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
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
                        <div style="position:relative;width:100px;height:100px;border-radius:8px;overflow:hidden;border:1px solid #DEE3E9;">
                            <img src="${event.target.result}" style="width:100%;height:100%;object-fit:cover;">
                            <button type="button" onclick="removePreview(${i})" style="position:absolute;top:4px;right:4px;background:#C62828;color:#fff;border:none;border-radius:4px;padding:4px 8px;font-size:10px;cursor:pointer;">X</button>
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
