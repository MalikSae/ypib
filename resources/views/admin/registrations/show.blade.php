@extends('layouts.admin')
@section('title', 'Detail Pendaftar — ' . $registration->full_name)
@section('page-title', 'Detail Pendaftar')

@section('content')

{{-- Back link --}}
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.registrations.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:500;color:#5D6C7B;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Daftar
    </a>
</div>

{{-- ── 2-Column Layout ── --}}
<div class="detail-grid">

    {{-- ══════ KOLOM KIRI ══════ --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Card 1: Header Pendaftar --}}
        <div style="background:#082e8f;border-radius:16px;padding:24px;">
            <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-bottom:6px;">
                {{ $registration->registration_number ? 'Nomor Pendaftaran' : 'Belum terdaftar' }}
            </div>
            @if($registration->registration_number)
                <div style="font-size:20px;font-weight:700;color:#FFFFFF;font-family:monospace;letter-spacing:0.05em;margin-bottom:8px;">
                    {{ $registration->registration_number }}
                </div>
            @endif
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="font-size:24px;font-weight:700;color:#FFFFFF;">{{ $registration->full_name }}</div>
                <span style="border:1.5px solid rgba(255,255,255,0.6);color:#FFFFFF;font-size:12px;font-weight:700;border-radius:9999px;padding:4px 14px;display:inline-block;">
                    @php
                    $labelsMap = [
                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                        'terdaftar'           => 'Terdaftar',
                        'diterima'            => 'Diterima (Menunggu Daftar Ulang)',
                        'menunggu_konfirmasi_daftar_ulang' => 'Menunggu Konfirmasi Daftar Ulang',
                        'daftar_ulang_selesai'=> 'Daftar Ulang Selesai',
                        'ditolak'             => 'Ditolak',
                        'perlu_revisi'        => 'Perlu Revisi',
                    ];
                    @endphp
                    {{ $labelsMap[$registration->status] ?? $registration->status }}
                </span>
            </div>
        </div>

        {{-- Card 2: Data Diri --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Data Diri</div>
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
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">{{ $label }}</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $value ?? '—' }}</div>
                </div>
                @endforeach
                {{-- Alamat full width --}}
                <div class="field-full" style="grid-column:1/-1;">
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Alamat</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->address ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Card 3: Pilihan Prodi & Asal Sekolah --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Pilihan Prodi &amp; Asal Sekolah</div>
            <div class="field-grid">
                <div>
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Program Studi</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->firstChoiceProgram?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Asal Sekolah</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->school_name ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Tahun Lulus</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->graduation_year ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Nilai Rata-rata</div>
                    <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->school_grade ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Card 4: Bukti Pembayaran --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Bukti Pembayaran</div>

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
                    <span style="font-size:12px;color:#8595A4;">{{ basename($registration->payment_proof) }}</span>
                </div>
            @else
                <p style="font-size:14px;color:#8595A4;margin:0 0 16px 0;">Belum ada bukti bayar diupload.</p>
            @endif

            {{-- Form upload admin --}}
            @if(!in_array($registration->status, ['terdaftar','diterima','ditolak']))
                <form method="POST"
                      action="{{ route('admin.registrations.upload-bukti', $registration->id) }}"
                      enctype="multipart/form-data"
                      style="border-top:1px solid #DEE3E9;padding-top:16px;margin-top:4px;">
                    @csrf
                    <div style="font-size:12px;font-weight:700;color:#5D6C7B;margin-bottom:10px;">Upload Bukti Bayar (Admin)</div>
                    <div style="display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;">
                        <div style="flex:1;min-width:200px;">
                            <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf"
                                   style="display:block;width:100%;font-size:13px;color:#5D6C7B;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;font-family:inherit;cursor:pointer;">
                            <span style="font-size:12px;color:#8595A4;margin-top:4px;display:block;">Format: JPG, PNG, PDF. Maks. 2MB</span>
                        </div>
                        <button type="submit"
                                style="height:40px;border-radius:9999px;padding:0 20px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;flex-shrink:0;transition:background 0.15s;"
                                onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
                            Upload
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Card 4.5: Bukti Pembayaran Daftar Ulang --}}
        @if(in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai']))
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Bukti Daftar Ulang</div>

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
                    <span style="font-size:12px;color:#8595A4;">{{ basename($registration->re_registration_payment_proof) }}</span>
                </div>
            @else
                <p style="font-size:14px;color:#8595A4;margin:0;">Belum ada bukti daftar ulang diupload.</p>
            @endif
        </div>
        @endif

        {{-- Card 5: Informasi Referral --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Informasi Referral</div>
            @if($registration->referrer)
                <div class="field-grid">
                    <div>
                        <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Kode Referral</div>
                        <div>
                            <span style="background:#e6edfc;color:#082e8f;font-size:13px;font-weight:700;padding:4px 12px;border-radius:9999px;font-family:monospace;">{{ $registration->referrer->code }}</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#8595A4;margin-bottom:4px;">Nama Referrer</div>
                        <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $registration->referrer->user?->name ?? '—' }}</div>
                    </div>
                </div>
            @else
                <p style="font-size:14px;color:#8595A4;margin:0;">Pendaftar langsung (tanpa referral)</p>
            @endif
        </div>

        {{-- Card 6: Riwayat Aktivitas --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px;">Riwayat Aktivitas</div>

            @if($registration->paymentLogs->isEmpty())
                <p style="font-size:14px;color:#8595A4;margin:0;">Belum ada aktivitas tercatat.</p>
            @else
                <div style="position:relative;padding-left:20px;">
                    {{-- Garis kiri --}}
                    <div style="position:absolute;left:7px;top:0;bottom:0;width:1px;background:#DEE3E9;"></div>

                    <div style="display:flex;flex-direction:column;gap:16px;">
                        @foreach($registration->paymentLogs->sortByDesc('created_at') as $log)
                        <div style="display:flex;align-items:flex-start;gap:12px;position:relative;">
                            {{-- Dot --}}
                            <div style="width:8px;height:8px;border-radius:9999px;background:#082e8f;position:absolute;left:-20px;top:5px;flex-shrink:0;"></div>
                            <div style="flex:1;">
                                <div style="font-size:14px;font-weight:500;color:#1C1E21;">{{ $log->note ?? $log->action }}</div>
                                <div style="font-size:12px;color:#8595A4;margin-top:2px;">
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
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <h3 style="font-size:16px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Konfirmasi Pembayaran</h3>
            <p style="font-size:13px;color:#5D6C7B;margin:0 0 16px 0;">Konfirmasi pembayaran dan generate nomor pendaftaran.</p>

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
                
                @if(!$registration->payment_proof)
                    <div style="margin-bottom: 12px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#5D6C7B;margin-bottom:4px;">Upload Bukti (Opsional jika ada catatan)</label>
                        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;font-size:13px;color:#5D6C7B;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;cursor:pointer;">
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#5D6C7B;margin-bottom:4px;">Catatan (Wajib jika tidak upload bukti)</label>
                        <textarea name="note" rows="2" style="width:100%;font-size:13px;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;resize:vertical;outline:none;transition:border 0.15s;" placeholder="Misal: Bayar tunai di kampus..." onfocus="this.style.border='1px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'">{{ old('note') }}</textarea>
                    </div>
                @endif

                <button type="submit"
                        onclick="return confirm('Konfirmasi pembayaran dan generate nomor pendaftaran?')"
                        style="width:100%;height:44px;border-radius:9999px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
                    Konfirmasi Pembayaran
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi 2: Update Status Seleksi --}}
        @if(in_array($registration->status, ['terdaftar', 'diterima', 'ditolak', 'perlu_revisi']))
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <h3 style="font-size:16px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Update Status Seleksi</h3>
            <p style="font-size:13px;color:#5D6C7B;margin:0 0 16px 0;">Tetapkan atau ubah hasil seleksi pendaftar.</p>
            <form method="POST" action="{{ route('admin.registrations.update-status', $registration->id) }}">
                @csrf
                <select name="status"
                        style="width:100%;height:44px;border-radius:8px;border:1px solid #CED0D4;padding:0 12px;font-size:14px;color:#1C1E21;background:#fff;outline:none;font-family:inherit;margin-bottom:12px;">
                    <option value="">— Pilih Hasil —</option>
                    <option value="diterima"     {{ $registration->status === 'diterima'     ? 'selected' : '' }}>Diterima</option>
                    <option value="ditolak"      {{ $registration->status === 'ditolak'      ? 'selected' : '' }}>Ditolak</option>
                    <option value="perlu_revisi" {{ $registration->status === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                </select>
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
                    Perbarui Status
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi: Konfirmasi Daftar Ulang --}}
        @if(in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang']))
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <h3 style="font-size:16px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Konfirmasi Daftar Ulang</h3>
            <p style="font-size:13px;color:#5D6C7B;margin:0 0 16px 0;">Konfirmasi pembayaran daftar ulang pendaftar.</p>

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
                        <label style="display:block;font-size:12px;font-weight:600;color:#5D6C7B;margin-bottom:4px;">Upload Bukti Daftar Ulang (Opsional jika ada catatan)</label>
                        <input type="file" name="bukti_daftar_ulang" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;font-size:13px;color:#5D6C7B;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;cursor:pointer;">
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#5D6C7B;margin-bottom:4px;">Catatan (Wajib jika tidak upload bukti)</label>
                        <textarea name="note" rows="2" style="width:100%;font-size:13px;border:1px solid #CED0D4;border-radius:8px;padding:8px 12px;box-sizing:border-box;font-family:inherit;resize:vertical;outline:none;transition:border 0.15s;" placeholder="Misal: Bayar tunai untuk daftar ulang..." onfocus="this.style.border='1px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'">{{ old('note') }}</textarea>
                    </div>
                @endif

                <button type="submit"
                        onclick="return confirm('Konfirmasi pembayaran daftar ulang?')"
                        style="width:100%;height:44px;border-radius:9999px;background:#082e8f;color:#FFFFFF;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#052066'" onmouseout="this.style.background='#082e8f'">
                    Konfirmasi Daftar Ulang
                </button>
            </form>
        </div>
        @endif

        {{-- Card Aksi 3: Catatan Internal --}}
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <h3 style="font-size:16px;font-weight:700;color:#0A1317;margin:0 0 6px 0;">Catatan Internal</h3>
            <p style="font-size:13px;color:#5D6C7B;margin:0 0 12px 0;">Catatan untuk tim internal — tidak terlihat pendaftar.</p>
            <form method="POST" action="{{ route('admin.registrations.add-note', $registration->id) }}">
                @csrf
                <textarea name="note" rows="4"
                          style="width:100%;border:1px solid #CED0D4;border-radius:8px;padding:12px;font-size:14px;color:#1C1E21;font-family:inherit;resize:vertical;min-height:100px;outline:none;box-sizing:border-box;margin-bottom:12px;transition:border 0.15s;"
                          onfocus="this.style.border='2px solid #082e8f'" onblur="this.style.border='1px solid #CED0D4'"
                          placeholder="Tulis catatan untuk tim internal...">{{ $registration->internal_notes }}</textarea>
                <button type="submit"
                        style="width:100%;height:44px;border-radius:9999px;background:#F1F4F7;color:#082e8f;font-size:14px;font-weight:700;border:1px solid #CED0D4;cursor:pointer;font-family:inherit;transition:background 0.15s;"
                        onmouseover="this.style.background='#e6edfc'" onmouseout="this.style.background='#F1F4F7'">
                    Simpan Catatan
                </button>
            </form>
        </div>

        {{-- Reward Info --}}
        @if($registration->rewards && $registration->rewards->count() > 0)
        <div style="background:#FFFFFF;border-radius:16px;border:1px solid #DEE3E9;padding:24px;">
            <div style="font-size:11px;font-weight:700;color:#8595A4;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px;">Reward Referral</div>
            
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($registration->rewards as $reward)
                <div style="border:1px solid #DEE3E9;border-radius:8px;padding:12px;">
                    <div style="font-size:12px;color:#8595A4;margin-bottom:4px;font-weight:600;">
                        {{ $reward->reward_type === 'registration' ? 'Komisi Pendaftaran' : 'Komisi Daftar Ulang' }}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="font-size:13px;color:#5D6C7B;">Jumlah</span>
                        <span style="font-size:14px;font-weight:700;color:#0A1317;">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;color:#5D6C7B;">Status</span>
                        <span style="font-size:12px;font-weight:600;color:#444950;text-transform:capitalize;background:#F1F4F7;padding:2px 8px;border-radius:4px;">{{ $reward->status }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- end kolom kanan --}}

</div>{{-- end 2-column grid --}}

@endsection
