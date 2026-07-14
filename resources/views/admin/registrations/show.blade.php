@extends('layouts.admin')
@section('title', 'Detail Pendaftar — ' . $registration->full_name)
@section('page-title', 'Detail Pendaftar')

@section('content')

{{-- Back link --}}
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.registrations.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:500;text-decoration:none;" class="text-neutral-500">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Daftar
    </a>
</div>

{{-- ── 2-Column Layout ── --}}
<div class="detail-grid">

    {{-- ══════ KOLOM KIRI (Konten Utama) ══════ --}}
    <div class="min-w-0" style="display:flex;flex-direction:column;gap:20px;">

        {{-- Card 1: Header Pendaftar --}}
        <div style="border-radius:16px;padding:24px;" class="bg-primary-600">
            <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-bottom:6px;">
                {{ $registration->registration_number ? 'Nomor Pendaftaran' : 'Belum terdaftar' }}
            </div>
            @if($registration->registration_number)
                <div style="font-size:20px;font-weight:700;font-family:monospace;letter-spacing:0.05em;margin-bottom:8px;" class="text-white">
                    {{ $registration->registration_number }}
                </div>
            @endif
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="font-size:24px;font-weight:700;" class="text-white">{{ $registration->full_name }}</div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="border:1.5px solid rgba(255,255,255,0.6);font-size:12px;font-weight:700;border-radius:9999px;padding:4px 14px;display:inline-block;background:rgba(0,0,0,0.15);" class="text-white">
                        Jalur: {{ $registration->registration_type === 'alumni' ? 'Alumni YPIB' : 'Reguler' }}
                    </span>
                    <span style="border:1.5px solid rgba(255,255,255,0.6);font-size:12px;font-weight:700;border-radius:9999px;padding:4px 14px;display:inline-block;" class="text-white">
                        {{ $registration->getStatusLabel() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Data Diri --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Data Diri</div>
            <div class="field-grid">
                @php
                $fields = [
                    'NIK'           => $registration->nik,
                    'Tempat Lahir'  => $registration->birth_place,
                    'Tanggal Lahir' => $registration->birth_date ? \Carbon\Carbon::parse($registration->birth_date)->isoFormat('D MMMM Y') : null,
                    'Jenis Kelamin' => $registration->gender === 'male' ? 'Laki-laki' : ($registration->gender === 'female' ? 'Perempuan' : $registration->gender),
                    'No. HP'        => $registration->phone,
                    'Email'         => $registration->user?->email,
                ];
                @endphp
                @foreach($fields as $label => $value)
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">{{ $label }}</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $value ?? '—' }}</div>
                </div>
                @endforeach
                {{-- Alamat full width --}}
                <div class="field-full" style="grid-column:1/-1;">
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Alamat</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->address ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Card 3: Pilihan Prodi & Asal Sekolah --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Pilihan Prodi &amp; Asal Sekolah</div>
            <div class="field-grid">
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Program Studi</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Jalur Pendaftaran</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">
                        @php
                            $pathLabels = ['umum' => 'Jalur Reguler', 'prestasi' => 'Jalur Prestasi', 'tahfidz' => 'Jalur Tahfidz'];
                        @endphp
                        <span style="display:inline-flex;align-items:center;gap:6px;background:#e6edfc;color:#082e8f;font-size:13px;font-weight:700;padding:4px 14px;border-radius:9999px;">
                            {{ $pathLabels[$registration->admission_path] ?? $registration->admission_path ?? '—' }}
                        </span>
                    </div>
                </div>
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Asal Sekolah</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->school_name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Tahun Lulus</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->graduation_year ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Nilai Rata-rata</div>
                    <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->school_grade ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Card 4: Bukti Pembayaran / Kartu Alumni --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">
                {{ $registration->registration_type === 'alumni' ? 'Kartu Alumni YPIB' : 'Bukti Pembayaran' }}
            </div>

            @if($registration->registration_type === 'alumni')
                @if($registration->alumni_card_proof)
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <a href="{{ Storage::url($registration->alumni_card_proof) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:8px;background:#e6edfc;color:#082e8f;font-size:14px;font-weight:700;padding:10px 20px;border-radius:9999px;text-decoration:none;border:1px solid #DEE3E9;transition:background 0.12s;"
                           onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#e6edfc'">
                            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Lihat Kartu Alumni
                        </a>
                        <span style="font-size:12px;" class="text-neutral-400">{{ basename($registration->alumni_card_proof) }}</span>
                    </div>
                @else
                    <p style="font-size:14px;margin:0 0 16px 0;" class="text-neutral-400">Belum ada kartu alumni diupload.</p>
                @endif
            @else
                @if($registration->payment_proof)
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <a href="{{ Storage::url($registration->payment_proof) }}" target="_blank"
                           style="display:inline-flex;align-items:center;gap:8px;background:#e6edfc;color:#082e8f;font-size:14px;font-weight:700;padding:10px 20px;border-radius:9999px;text-decoration:none;border:1px solid #DEE3E9;transition:background 0.12s;"
                           onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#e6edfc'">
                            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Lihat Bukti Bayar
                        </a>
                        <span style="font-size:12px;" class="text-neutral-400">{{ basename($registration->payment_proof) }}</span>
                    </div>
                @else
                    <p style="font-size:14px;margin:0 0 16px 0;" class="text-neutral-400">Belum ada bukti bayar diupload.</p>
                @endif

                {{-- Form upload admin --}}
                @if(!in_array($registration->status, ['terdaftar','diterima','ditolak']))
                    <form method="POST"
                          action="{{ route('admin.registrations.upload-bukti', $registration->id) }}"
                          enctype="multipart/form-data"
                          style="border-top:1px solid #DEE3E9;padding-top:16px;margin-top:4px;">
                        @csrf
                        <div style="font-size:12px;font-weight:700;margin-bottom:10px;" class="text-neutral-500">Upload Bukti Bayar (Admin)</div>
                        <div style="display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;">
                            <div style="flex:1;min-width:200px;">
                                <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf"
                                       style="display:block;width:100%;font-size:13px;border-radius:8px;padding:8px 12px;font-family:inherit;cursor:pointer;" class="text-neutral-500 border-neutral-300">
                                <span style="font-size:12px;margin-top:4px;display:block;" class="text-neutral-400">Format: JPG, PNG, PDF. Maks. 2MB</span>
                            </div>
                            <button type="submit"
                                    style="height:40px;border-radius:9999px;padding:0 20px;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;flex-shrink:0;transition:background 0.15s;"
                                    onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'" class="bg-primary-600 text-white">
                                Upload
                            </button>
                        </div>
                    </form>
                @endif
            @endif
        </div>

        {{-- Card 4.3: Dokumen Pendaftaran --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200"
             x-data="{
                selected: [],
                allIds: [{{ $registration->documents ? $registration->documents->pluck('id')->implode(',') : '' }}],
                get allSelected() { return this.allIds.length > 0 && this.selected.length === this.allIds.length },
                get someSelected() { return this.selected.length > 0 && this.selected.length < this.allIds.length },
                toggleAll() {
                    if (this.allSelected) { this.selected = [] }
                    else { this.selected = [...this.allIds] }
                },
                toggle(id) {
                    const idx = this.selected.indexOf(id);
                    if (idx > -1) { this.selected.splice(idx, 1) }
                    else { this.selected.push(id) }
                }
             }">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;" class="text-neutral-400">Dokumen Pendaftaran</div>
                @php
                    $mandatoryTypes = \App\Models\RegistrationDocument::MANDATORY_TYPES;
                    $uploadedDocs = $registration->documents ? $registration->documents->keyBy('document_type') : collect();
                    $approvedCount = $uploadedDocs->whereIn('document_type', $mandatoryTypes)->where('status', 'disetujui')->count();
                    $totalMandatory = count($mandatoryTypes);
                    
                    $allDocsToDisplay = [];
                    foreach ($mandatoryTypes as $type) {
                        if ($uploadedDocs->has($type)) {
                            $allDocsToDisplay[] = (object) ['is_uploaded' => true, 'doc' => $uploadedDocs->get($type)];
                        } else {
                            $allDocsToDisplay[] = (object) [
                                'is_uploaded' => false,
                                'type' => $type,
                                'label' => strtoupper(str_replace('_', ' ', $type)),
                            ];
                        }
                    }
                    foreach ($uploadedDocs as $type => $doc) {
                        if (!in_array($type, $mandatoryTypes)) {
                            $allDocsToDisplay[] = (object) ['is_uploaded' => true, 'doc' => $doc];
                        }
                    }
                @endphp
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:9999px;background:#F1F4F7;" class="text-neutral-600">
                    {{ $approvedCount }}/{{ $totalMandatory }} Wajib Disetujui
                </span>
            </div>

            {{-- Select All header (only when status allows review AND there are uploaded docs) --}}
            @if($registration->status === 'terdaftar' && $uploadedDocs->count() > 0)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:#F1F4F7;border-radius:8px;margin-bottom:12px;">
                <input type="checkbox" :checked="allSelected" :indeterminate="someSelected" @click="toggleAll()"
                       class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500 cursor-pointer">
                <span style="font-size:12px;font-weight:600;" class="text-neutral-600">
                    Pilih Semua Dokumen Upload
                </span>
                <span x-show="selected.length > 0" x-cloak
                      style="font-size:11px;font-weight:700;padding:1px 8px;border-radius:9999px;background:#082e8f;color:white;margin-left:auto;">
                    <span x-text="selected.length"></span> dipilih
                </span>
            </div>
            @endif

            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($allDocsToDisplay as $item)
                    @if($item->is_uploaded)
                        @php $doc = $item->doc; @endphp
                        <div class="p-4 rounded-xl border transition-colors duration-200"
                             :class="selected.includes({{ $doc->id }}) ? 'border-primary-600 bg-primary-50' : 'border-neutral-200 bg-white'">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                                @if($registration->status === 'terdaftar')
                                <input type="checkbox" :checked="selected.includes({{ $doc->id }})" @click="toggle({{ $doc->id }})"
                                       class="w-4 h-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500 cursor-pointer shrink-0 mt-0.5">
                                @endif
                                <div style="font-size:14px;font-weight:700;flex:1;" class="text-neutral-900">
                                    {{ $doc->document_type === 'lainnya' ? $doc->label : strtoupper(str_replace('_', ' ', $doc->document_type)) }}
                                    @if(in_array($doc->document_type, \App\Models\RegistrationDocument::MANDATORY_TYPES))
                                        <span style="color:#ef4444;">*</span>
                                    @endif
                                </div>
                                <div>
                                    @if($doc->status === 'disetujui')
                                        <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:4px 10px;border-radius:9999px;">DISETUJUI</span>
                                    @elseif($doc->status === 'perlu_revisi')
                                        <span style="background:#ffedd5;color:#c2410c;font-size:10px;font-weight:700;padding:4px 10px;border-radius:9999px;">REVISI</span>
                                    @else
                                        <span style="background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;padding:4px 10px;border-radius:9999px;">MENUNGGU</span>
                                    @endif
                                </div>
                            </div>

                            <div style="padding-left: {{ $registration->status === 'terdaftar' ? '28px' : '0' }};">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:#0B41CB;font-weight:600;text-decoration:none;">
                                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                    Lihat Dokumen
                                </a>

                                @if($doc->status === 'perlu_revisi' && $doc->review_note)
                                    <div style="font-size:12px;color:#c2410c;margin-top:8px;font-style:italic;background:#fff7ed;padding:8px 12px;border-radius:6px;border:1px solid #ffedd5;">
                                        <strong>Catatan Revisi:</strong> {{ $doc->review_note }}
                                    </div>
                                @endif

                                @if($doc->document_type !== 'lainnya')
                                <form method="POST" action="{{ route('admin.registrations.upload-document', $registration->id) }}" enctype="multipart/form-data" class="mt-3 pt-3" style="border-top:1px dashed #E5E7EB;">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $doc->document_type }}">
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required
                                               style="flex:1;font-size:11px;">
                                        <button type="submit"
                                                onclick="return confirm('Ganti dokumen ini? File lama akan dihapus dan status otomatis jadi Disetujui.')"
                                                style="height:32px;background:#082e8f;color:white;border:none;border-radius:6px;font-size:11px;font-weight:700;padding:0 12px;cursor:pointer;white-space:nowrap;">
                                            Ganti
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-xl border border-dashed border-neutral-300 bg-neutral-50/70">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                                @if($registration->status === 'terdaftar')
                                <input type="checkbox" disabled class="w-4 h-4 rounded border-neutral-300 bg-neutral-200 cursor-not-allowed shrink-0 mt-0.5 opacity-50">
                                @endif
                                <div style="font-size:14px;font-weight:700;flex:1;" class="text-neutral-500">
                                    {{ $item->label }} <span style="color:#ef4444;">*</span>
                                </div>
                                <div>
                                    <span style="background:#F1F2F4;color:#646E87;font-size:10px;font-weight:700;padding:4px 10px;border-radius:9999px;">BELUM DIUPLOAD</span>
                                </div>
                            </div>
                            <div style="padding-left: {{ $registration->status === 'terdaftar' ? '28px' : '0' }}; font-size:13px;color:#9A9FAC;display:flex;align-items:center;gap:6px;">
                                <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                Pendaftar belum mengunggah dokumen ini.
                            </div>

                            <div style="padding-left: {{ $registration->status === 'terdaftar' ? '28px' : '0' }};">
                                @if($item->type !== 'lainnya')
                                <form method="POST" action="{{ route('admin.registrations.upload-document', $registration->id) }}" enctype="multipart/form-data" class="mt-3 pt-3" style="border-top:1px dashed #E5E7EB;">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $item->type }}">
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required
                                               style="flex:1;font-size:11px;">
                                        <button type="submit"
                                                onclick="return confirm('Upload dokumen ini atas nama pendaftar? Status akan langsung Disetujui.')"
                                                style="height:32px;background:#082e8f;color:white;border:none;border-radius:6px;font-size:11px;font-weight:700;padding:0 12px;cursor:pointer;white-space:nowrap;">
                                            Upload
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Bulk Action Bar (inline) --}}
            @if($registration->status === 'terdaftar')
            <div x-show="selected.length > 0" x-cloak x-transition.opacity
                 style="margin-top:16px;padding:16px;background:#F8FAFF;border:1.5px solid #082e8f;border-radius:12px;">
                <form method="POST" action="{{ route('admin.registrations.bulk-review-documents', $registration->id) }}">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="document_ids[]" :value="id">
                    </template>

                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                        <svg style="width:16px;height:16px;color:#082e8f;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span style="font-size:13px;font-weight:700;color:#082e8f;">
                            Aksi untuk <span x-text="selected.length"></span> dokumen terpilih
                        </span>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                        <div style="flex:1;min-width:200px;">
                            <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Catatan (wajib jika revisi)</label>
                            <input type="text" name="bulk_note" placeholder="Catatan untuk dokumen terpilih..."
                                   style="width:100%;height:36px;border-radius:6px;font-size:12px;border:1px solid #D1D5DB;padding:0 10px;box-sizing:border-box;">
                        </div>
                        <button type="submit" name="bulk_status" value="disetujui"
                                onclick="return confirm('Setujui ' + document.querySelectorAll('input[name=\'document_ids[]\']').length + ' dokumen terpilih?')"
                                style="height:36px;background:#15803d;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;padding:0 16px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background 0.15s;"
                                onmouseover="this.style.background='#166534'" onmouseout="this.style.background='#15803d'">
                            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Setujui Terpilih
                        </button>
                        <button type="submit" name="bulk_status" value="perlu_revisi"
                                onclick="return confirm('Minta revisi ' + document.querySelectorAll('input[name=\'document_ids[]\']').length + ' dokumen terpilih?')"
                                style="height:36px;background:#c2410c;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;padding:0 16px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background 0.15s;"
                                onmouseover="this.style.background='#9a3412'" onmouseout="this.style.background='#c2410c'">
                            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            Minta Revisi
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>

        {{-- Card 4.35: Hasil Tes Tulis Online --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Hasil Tes Tulis Online</div>
            
            @php $examSession = $registration->examSession; @endphp
            
            @if($examSession)
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                    <div>
                        <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Status Ujian</div>
                        <div style="font-size:14px;font-weight:700;" class="{{ $examSession->status === 'completed' ? 'text-success-600' : 'text-warning-600' }}">
                            {{ $examSession->status === 'completed' ? 'Selesai' : 'Sedang Dikerjakan' }}
                        </div>
                    </div>
                    
                    @if($examSession->status === 'completed')
                        <div>
                            <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Skor Ujian</div>
                            <div style="font-size:14px;font-weight:700;" class="text-neutral-900">
                                {{ $examSession->score }}% 
                                <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:9999px;margin-left:6px;background:{{ $examSession->result_label === 'sangat_baik' ? '#dcfce7' : '#dbeafe' }};color:{{ $examSession->result_label === 'sangat_baik' ? '#166534' : '#1e40af' }};">
                                    {{ $examSession->result_label === 'sangat_baik' ? 'Sangat Baik' : 'Baik' }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Tanggal Selesai</div>
                            <div style="font-size:14px;font-weight:500;" class="text-neutral-900">
                                {{ \Carbon\Carbon::parse($examSession->completed_at)->isoFormat('D MMM Y, HH:mm') }}
                            </div>
                        </div>
                        
                        <div>
                            <form action="{{ route('admin.registrations.reset-exam', $registration->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset sesi tes tulis peserta ini? Seluruh jawaban akan dihapus dan peserta harus mengerjakan ulang.')" style="margin:0;">
                                @csrf
                                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:#fee2e2;color:#b91c1c;font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Reset Tes
                                </button>
                            </form>
                        </div>
                    @else
                        <div>
                            <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Tanggal Mulai</div>
                            <div style="font-size:14px;font-weight:500;" class="text-neutral-900">
                                {{ \Carbon\Carbon::parse($examSession->started_at)->isoFormat('D MMM Y, HH:mm') }}
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <p style="font-size:14px;margin:0;" class="text-neutral-400">Pendaftar belum memulai tes tulis.</p>
            @endif
        </div>

        {{-- Card 4.4: Tagihan Daftar Ulang --}}
        @if(in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']))
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Tagihan Daftar Ulang</div>
            
            <div style="margin-bottom: 16px;">
                <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Program Studi</div>
                <div style="font-size:14px;font-weight:700;" class="text-neutral-900">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Total Tagihan</div>
                <div style="font-size:20px;font-weight:700;color:#BF360C;">Rp {{ number_format($registration->firstChoiceProgram?->re_registration_fee ?? 0, 0, ',', '.') }}</div>
            </div>

            @if($registration->firstChoiceProgram && !empty($registration->firstChoiceProgram->re_registration_fee_details) && count($registration->firstChoiceProgram->re_registration_fee_details) > 0)
                <div style="border:1px solid #E5E7EB;border-radius:8px;overflow:hidden;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <tbody>
                            @foreach($registration->firstChoiceProgram->re_registration_fee_details as $detail)
                            <tr style="border-bottom:1px solid #E5E7EB;">
                                <td style="padding:10px 16px;" class="text-neutral-600">{{ $detail['name'] }}</td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;" class="text-neutral-900">Rp {{ number_format($detail['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="font-size:13px;margin:0;" class="text-neutral-400">Tidak ada rincian biaya.</p>
            @endif
        </div>
        @endif

        {{-- Card 4.5: Bukti Pembayaran Daftar Ulang --}}
        @if(in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']))
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Bukti Daftar Ulang</div>

            @if($registration->re_registration_payment_proof)
                <div style="display:flex;align-items:center;gap:12px;">
                    <a href="{{ Storage::url($registration->re_registration_payment_proof) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:8px;background:#e6edfc;color:#082e8f;font-size:14px;font-weight:700;padding:10px 20px;border-radius:9999px;text-decoration:none;border:1px solid #DEE3E9;transition:background 0.12s;"
                       onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#e6edfc'">
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Lihat Bukti Daftar Ulang
                    </a>
                    <span style="font-size:12px;" class="text-neutral-400">{{ basename($registration->re_registration_payment_proof) }}</span>
                </div>
            @else
                <p style="font-size:14px;margin:0;" class="text-neutral-400">Belum ada bukti daftar ulang diupload.</p>
            @endif
        </div>
        @endif

        {{-- Card 5: Informasi Referral --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Informasi Referral</div>
            @if($registration->referrer)
                <div class="field-grid">
                    <div>
                        <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Kode Referral</div>
                        <div>
                            <span style="background:#e6edfc;font-size:13px;font-weight:700;padding:4px 12px;border-radius:9999px;font-family:monospace;" class="text-primary-600">{{ $registration->referrer->code }}</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:12px;margin-bottom:4px;" class="text-neutral-400">Nama Referrer</div>
                        <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $registration->referrer->user?->name ?? '—' }}</div>
                    </div>
                </div>
            @else
                <p style="font-size:14px;margin:0;" class="text-neutral-400">Pendaftar langsung (tanpa referral)</p>
            @endif
        </div>

        {{-- Card 6: Riwayat Aktivitas --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px;" class="text-neutral-400">Riwayat Aktivitas</div>

            @if($registration->paymentLogs->isEmpty())
                <p style="font-size:14px;margin:0;" class="text-neutral-400">Belum ada aktivitas tercatat.</p>
            @else
                <div style="position:relative;padding-left:20px;">
                    {{-- Garis kiri --}}
                    <div style="position:absolute;left:7px;top:0;bottom:0;width:1px;" class="bg-neutral-200"></div>

                    <div style="display:flex;flex-direction:column;gap:16px;">
                        @foreach($registration->paymentLogs->sortByDesc('created_at') as $log)
                        <div style="display:flex;align-items:flex-start;gap:12px;position:relative;">
                            {{-- Dot --}}
                            <div style="width:8px;height:8px;border-radius:9999px;position:absolute;left:-20px;top:5px;flex-shrink:0;" class="bg-primary-600"></div>
                            <div style="flex:1;">
                                <div style="font-size:14px;font-weight:500;" class="text-neutral-900">{{ $log->note ?? $log->action }}</div>
                                <div style="font-size:12px;margin-top:2px;" class="text-neutral-400">
                                    {{ $log->actor?->name }} &middot; {{ $log->created_at->isoFormat('D MMM Y HH:mm') }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>{{-- end kolom kiri --}}

    {{-- ══════ KOLOM KANAN ══════ --}}
    <div class="detail-sidebar-col" style="position:sticky;top:88px;display:flex;flex-direction:column;gap:16px;">

        {{-- Card Aksi 1: Konfirmasi Pembayaran --}}
        @if(in_array($registration->status, ['menunggu_pembayaran', 'menunggu_konfirmasi']))
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">
                {{ $registration->registration_type === 'alumni' ? 'Konfirmasi Alumni' : 'Konfirmasi Pembayaran' }}
            </h3>
            <p style="font-size:13px;margin:0 0 16px 0;" class="text-neutral-500">
                {{ $registration->registration_type === 'alumni' ? 'Verifikasi kartu alumni pendaftar.' : 'Konfirmasi pembayaran dan generate nomor pendaftaran.' }}
            </p>

            @if ($errors->any())
                <div style="background:#FEE2E2;border:1px solid #F87171;color:#B91C1C;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.registrations.confirm-payment', $registration->id) }}" enctype="multipart/form-data">
                @csrf
                
                @if($registration->registration_type !== 'alumni' && !$registration->payment_proof)
                    <div style="margin-bottom: 12px;">
                        <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Upload Bukti (Opsional jika ada catatan)</label>
                        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;font-size:13px;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;cursor:pointer;" class="text-neutral-500 border-neutral-300">
                    </div>
                @endif
                
                @if(!$registration->payment_proof && $registration->registration_type !== 'alumni' || $registration->registration_type === 'alumni' && !$registration->alumni_card_proof)
                    <div style="margin-bottom: 16px;">
                        <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Catatan (Wajib jika tidak upload bukti/kartu)</label>
                        <textarea name="note" rows="2" style="width:100%;font-size:13px;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;resize:vertical;outline:none;transition:border 0.15s;" placeholder="Misal: Diverifikasi manual..." onfocus="this.style.border='1px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'" class="border-neutral-300">{{ old('note') }}</textarea>
                    </div>
                @endif

                <button type="submit"
                        onclick="return confirm('{{ $registration->registration_type === 'alumni' ? 'Verifikasi alumni dan generate nomor pendaftaran?' : 'Konfirmasi pembayaran dan generate nomor pendaftaran?' }}')"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'" class="bg-primary-600 text-white">
                    {{ $registration->registration_type === 'alumni' ? 'Verifikasi & Konfirmasi Alumni' : 'Konfirmasi Pembayaran' }}
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi: Konfirmasi Daftar Ulang --}}
        @if(in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang']))
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Konfirmasi Daftar Ulang</h3>
            <p style="font-size:13px;margin:0 0 16px 0;" class="text-neutral-500">Konfirmasi pembayaran daftar ulang pendaftar.</p>

            @if ($errors->has('bukti_daftar_ulang') || $errors->has('note'))
                <div style="background:#FEE2E2;border:1px solid #F87171;color:#B91C1C;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                    <ul style="margin:0;padding-left:20px;">
                        @if($errors->has('bukti_daftar_ulang')) <li>{{ $errors->first('bukti_daftar_ulang') }}</li> @endif
                        @if($errors->has('note')) <li>{{ $errors->first('note') }}</li> @endif
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.registrations.confirm-re-registration', $registration->id) }}" enctype="multipart/form-data">
                @csrf

                @if(!$registration->re_registration_payment_proof)
                    <div style="margin-bottom: 12px;">
                        <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Upload Bukti Daftar Ulang (Opsional jika ada catatan)</label>
                        <input type="file" name="bukti_daftar_ulang" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;font-size:13px;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;cursor:pointer;" class="text-neutral-500 border-neutral-300">
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Catatan (Wajib jika tidak upload bukti)</label>
                        <textarea name="note" rows="2" style="width:100%;font-size:13px;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;resize:vertical;outline:none;transition:border 0.15s;" placeholder="Misal: Bayar tunai untuk daftar ulang..." onfocus="this.style.border='1px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'" class="border-neutral-300">{{ old('note') }}</textarea>
                    </div>
                @endif

                <button type="submit"
                        onclick="return confirm('Konfirmasi pembayaran daftar ulang?')"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'" class="bg-primary-600 text-white">
                    Konfirmasi Daftar Ulang
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi 3: Catatan Internal --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Catatan Internal</h3>
            <p style="font-size:13px;margin:0 0 12px 0;" class="text-neutral-500">Catatan untuk tim internal — tidak terlihat pendaftar.</p>
            <form method="POST" action="{{ route('admin.registrations.add-note', $registration->id) }}">
                @csrf
                <textarea name="note" rows="4"
                          style="width:100%;border-radius:8px;padding:12px;font-size:14px;font-family:inherit;resize:vertical;min-height:100px;outline:none;box-sizing:border-box;margin-bottom:12px;transition:border 0.15s;"
                          onfocus="this.style.border='2px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'"
                          placeholder="Tulis catatan untuk tim internal..." class="border-neutral-300 text-neutral-900">{{ $registration->internal_notes }}</textarea>
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#e6edfc'" onmouseout="this.style.background='#F1F4F7'" class="bg-neutral-100 text-primary-600 border-neutral-300">
                    Simpan Catatan
                </button>
            </form>
        </div>

        {{-- Card Aksi 4: Reset Password --}}
        @if($registration->user_id)
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Reset Password</h3>
            <p style="font-size:13px;margin:0 0 16px 0;" class="text-neutral-500">Atur ulang password pengguna jika lupa atau kesulitan login.</p>
            
            @if ($errors->has('password') || $errors->has('password_confirmation'))
                <div style="background:#FEE2E2;border:1px solid #F87171;color:#B91C1C;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px;">
                    <ul style="margin:0;padding-left:20px;">
                        @if($errors->has('password')) <li>{{ $errors->first('password') }}</li> @endif
                        @if($errors->has('password_confirmation')) <li>{{ $errors->first('password_confirmation') }}</li> @endif
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.reset-password', $registration->user_id) }}" onsubmit="return confirm('Anda yakin ingin mereset password akun ini?')">
                @csrf
                <div style="margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label style="display:block;font-size:12px;font-weight:600;" class="text-neutral-500">Password Baru</label>
                        <button type="button" onclick="useStandardPassword('reg_new_pwd', 'reg_conf_pwd')" class="text-primary-600 bg-primary-50" style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; border:none; cursor:pointer; font-family:inherit;">
                            Gunakan pass standar
                        </button>
                    </div>
                    <div style="position:relative;">
                        <input type="password" id="reg_new_pwd" name="password" required minlength="8" 
                               style="width:100%;height:44px;border-radius:8px;padding:0 40px 0 12px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;border:1px solid #CED0D4;" 
                               class="text-neutral-900 bg-white" placeholder="Masukkan password baru">
                        <button type="button" onclick="toggleRegPassword('reg_new_pwd', 'reg_eye_1')" style="position:absolute;right:0;top:0;bottom:0;padding:0 12px;background:none;border:none;cursor:pointer;display:flex;align-items:center;" class="text-neutral-400">
                            <svg id="reg_eye_1" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;" class="text-neutral-500">Konfirmasi Password</label>
                    <div style="position:relative;">
                        <input type="password" id="reg_conf_pwd" name="password_confirmation" required minlength="8" 
                               style="width:100%;height:44px;border-radius:8px;padding:0 40px 0 12px;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box;border:1px solid #CED0D4;" 
                               class="text-neutral-900 bg-white" placeholder="Ulangi password baru">
                        <button type="button" onclick="toggleRegPassword('reg_conf_pwd', 'reg_eye_2')" style="position:absolute;right:0;top:0;bottom:0;padding:0 12px;background:none;border:none;cursor:pointer;display:flex;align-items:center;" class="text-neutral-400">
                            <svg id="reg_eye_2" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;background-color:#EF4444;color:white;"
                        onmouseover="this.style.backgroundColor='#DC2626'" onmouseout="this.style.backgroundColor='#EF4444'">
                    Simpan Password Baru
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi 5: Ubah Afiliator --}}
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-neutral-900">Ubah Afiliator</h3>
            <p style="font-size:13px;margin:0 0 16px 0;" class="text-neutral-500">Ubah atau hapus afiliator untuk pendaftar ini.</p>
            
            <form method="POST" action="{{ route('admin.registrations.update-referral', $registration->id) }}" onsubmit="return confirm('Anda yakin ingin mengubah afiliator pendaftar ini?')">
                @csrf
                <select name="referrer_id"
                        style="width:100%;height:44px;border-radius:8px;padding:0 12px;font-size:14px;outline:none;font-family:inherit;margin-bottom:12px;" class="border-neutral-300 text-neutral-900 bg-white">
                    <option value="">— Tidak Ada (Langsung) —</option>
                    @foreach($referrers as $ref)
                        <option value="{{ $ref->id }}" {{ $registration->referrer_id == $ref->id ? 'selected' : '' }}>
                            {{ $ref->user->name ?? 'User Dihapus' }} ({{ $ref->code }})
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#e6edfc'" onmouseout="this.style.background='#F1F4F7'" class="bg-neutral-100 text-primary-600 border-neutral-300">
                    Simpan Afiliator
                </button>
            </form>
        </div>

        {{-- Reward Info --}}
        @if($registration->rewards && $registration->rewards->count() > 0)
        <div style="border-radius:16px;padding:24px;" class="bg-white border-neutral-200 mb-6">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;" class="text-neutral-400">Reward Referral</div>
            
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($registration->rewards as $reward)
                <div style="border-radius:8px;padding:12px;" class="border-neutral-200 bg-neutral-50">
                    <div style="font-size:12px;margin-bottom:4px;font-weight:600;" class="text-neutral-400">
                        {{ $reward->reward_type === 'registration' ? 'Komisi Pendaftaran' : 'Komisi Daftar Ulang' }}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="font-size:13px;" class="text-neutral-500">Jumlah</span>
                        <span style="font-size:14px;font-weight:700;" class="text-neutral-900">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;" class="text-neutral-500">Status</span>
                        <span style="font-size:12px;font-weight:600;text-transform:capitalize;padding:2px 8px;border-radius:4px;" class="{{ $reward->status === 'cancelled' ? 'bg-error-50 text-error-600' : 'text-neutral-600 bg-neutral-200' }}">{{ $reward->status }}</span>
                    </div>
                    @if($reward->notes)
                    <div style="font-size:11px;margin-top:8px;font-style:italic;" class="text-neutral-400">
                        {{ $reward->notes }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Card Aksi 6: Hapus Pendaftar --}}
        <div style="border-radius:16px;padding:24px;border:1px solid #FCA5A5;" class="bg-red-50">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 6px 0;" class="text-red-700">Hapus Pendaftar</h3>
            <p style="font-size:13px;margin:0 0 16px 0;" class="text-red-600">Arsipkan data pendaftar ini. Komisi terkait akan otomatis dibatalkan dan nilai konversi afiliasi disesuaikan.</p>
            
            <form method="POST" action="{{ route('admin.registrations.destroy', $registration->id) }}" onsubmit="return confirm('Yakin ingin menghapus pendaftar ini? Data akan diarsipkan dan komisi terkait akan disesuaikan otomatis.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:background 0.15s;border:none;background-color:#DC2626;color:#ffffff;"
                        onmouseover="this.style.backgroundColor='#B91C1C'" onmouseout="this.style.backgroundColor='#DC2626'" class="shadow-sm">
                    Hapus Pendaftar
                </button>
            </form>
        </div>

    </div>{{-- end kolom kanan --}}

</div>{{-- end 2-column grid --}}

<script>
function useStandardPassword(newId, confId) {
    document.getElementById(newId).value = 'ypib2026';
    document.getElementById(confId).value = 'ypib2026';
}

function toggleRegPassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}
</script>

@endsection
