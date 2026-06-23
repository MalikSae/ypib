@extends('layouts.admin')
@section('title', 'Detail Afiliasi — ' . ($referrer->user?->name ?? 'Tanpa Nama'))
@section('page-title', 'Detail Afiliasi')

@section('content')

{{-- Back link --}}
<div class="mb-5">
    <a href="{{ route('admin.referrers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-neutral-500 hover:text-neutral-900 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Daftar Afiliasi
    </a>
</div>

{{-- ── 2-Column Layout ── --}}
<div class="detail-grid">

    {{-- ══════ KOLOM KIRI (Konten Utama: Riwayat) ══════ --}}
    <div class="min-w-0" style="display:flex;flex-direction:column;gap:20px;">

        {{-- Section: Pendaftar Bawaan --}}
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Riwayat Pendaftar Bawaan</h3>
            
            @if($referrer->registrations->isEmpty())
                <p class="text-sm text-neutral-500 py-4 text-center bg-neutral-50 rounded-xl border border-neutral-100">
                    Belum ada pendaftar yang menggunakan kode referral ini.
                </p>
            @else
                <div class="overflow-x-auto border border-neutral-100 rounded-xl">
                    <table class="min-w-full divide-y divide-neutral-100">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tanggal Daftar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-100">
                            @foreach($referrer->registrations as $reg)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-neutral-900">{{ $reg->full_name }}</div>
                                    <div class="text-xs text-neutral-500 font-mono">{{ $reg->registration_number ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 whitespace-nowrap">
                                    {{ $reg->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-neutral-100 text-neutral-700 text-xs font-bold capitalize">
                                        {{ str_replace('_', ' ', $reg->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.registrations.show', $reg->id) }}" class="text-primary-600 hover:text-primary-800 text-xs font-bold inline-flex items-center gap-1">
                                        Detail
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Section: Riwayat Komisi --}}
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Riwayat Komisi (Rewards)</h3>
            
            @if($referrer->rewards->isEmpty())
                <p class="text-sm text-neutral-500 py-4 text-center bg-neutral-50 rounded-xl border border-neutral-100">
                    Belum ada riwayat komisi.
                </p>
            @else
                <div class="overflow-x-auto border border-neutral-100 rounded-xl">
                    <table class="min-w-full divide-y divide-neutral-100">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tipe / Pendaftar</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Nominal</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-100">
                            @foreach($referrer->rewards as $reward)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-neutral-600 whitespace-nowrap">
                                    {{ $reward->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-neutral-900">{{ $reward->reward_type === 'registration' ? 'Komisi Pendaftaran' : 'Komisi Daftar Ulang' }}</div>
                                    <div class="text-xs text-neutral-500">Dari: {{ $reward->registration?->full_name ?? 'Pendaftar Tidak Diketahui' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-bold text-neutral-900">Rp {{ number_format($reward->amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-warning/10 text-warning-700 border-warning-200',
                                            'approved' => 'bg-info/10 text-info-700 border-info-200',
                                            'disbursed' => 'bg-success/10 text-success-700 border-success-200',
                                            'rejected' => 'bg-error/10 text-error-700 border-error-200',
                                        ];
                                        $colorClass = $statusColors[$reward->status] ?? 'bg-neutral-100 text-neutral-700 border-neutral-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs font-bold capitalize {{ $colorClass }}">
                                        {{ $reward->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>{{-- end kolom kiri --}}

    {{-- ══════ KOLOM KANAN (Sidebar: Profil & Aksi) ══════ --}}
    <div class="detail-sidebar-col" style="position:sticky;top:88px;display:flex;flex-direction:column;gap:16px;">

        {{-- Card: Profil Afiliasi --}}
        <div class="bg-primary-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10 blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="text-xs text-primary-200 mb-1 font-semibold uppercase tracking-wider">Profil Afiliasi</div>
                <h2 class="text-2xl font-bold mb-4">{{ $referrer->user?->name ?? 'Tanpa Nama' }}</h2>
                
                <div class="flex flex-col">
                    <div class="flex justify-between items-center border-b border-primary-500/50 py-3">
                        <span class="text-sm text-primary-100">Kode Referral</span>
                        <span class="font-mono text-sm font-bold bg-white/20 px-2 py-1 rounded">{{ $referrer->code }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-primary-500/50 py-3">
                        <span class="text-sm text-primary-100">Status</span>
                        @if($referrer->status === 'active')
                            <span class="text-xs font-bold bg-success text-white px-2.5 py-1 rounded-full">Aktif</span>
                        @else
                            <span class="text-xs font-bold bg-neutral-400 text-white px-2.5 py-1 rounded-full">Nonaktif</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center border-b border-primary-500/50 py-3">
                        <span class="text-sm text-primary-100">Bergabung</span>
                        <span class="text-sm font-semibold">{{ $referrer->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="pt-3">
                        <div class="text-xs text-primary-200 mb-1">Email</div>
                        <div class="text-sm font-medium mb-3">{{ $referrer->user?->email ?? '-' }}</div>
                        
                        <div class="text-xs text-primary-200 mb-1">No. HP / WhatsApp</div>
                        <div class="text-sm font-medium">
                            @if($referrer->user?->phone)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $referrer->user->phone)) }}" target="_blank" class="hover:text-white transition-colors flex items-center gap-1.5">
                                    {{ $referrer->user->phone }}
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Statistik Performa --}}
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Kinerja Referral</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-neutral-50 rounded-xl p-4 border border-neutral-100">
                    <div class="text-xs text-neutral-500 mb-1 font-semibold">Total Klik Link</div>
                    <div class="text-2xl font-black text-neutral-900">{{ number_format($referrer->total_clicks) }}</div>
                </div>
                <div class="bg-neutral-50 rounded-xl p-4 border border-neutral-100">
                    <div class="text-xs text-neutral-500 mb-1 font-semibold">Pendaftar Sukses</div>
                    <div class="text-2xl font-black text-primary-600">{{ number_format($referrer->total_conversions) }}</div>
                </div>
            </div>
            @if($referrer->total_clicks > 0)
            <div class="mt-4 pt-4 border-t border-neutral-100">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-neutral-500 font-medium">Conversion Rate</span>
                    <span class="font-bold text-neutral-900">{{ number_format(($referrer->total_conversions / $referrer->total_clicks) * 100, 0) }}%</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-neutral-100 rounded-full h-1.5 mt-2">
                    <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ min(($referrer->total_conversions / max($referrer->total_clicks, 1)) * 100, 100) }}%"></div>
                </div>
            </div>
            @endif
        </div>

        {{-- Card: Keuangan & Pencairan --}}
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Informasi Komisi</h3>
            
            <div class="space-y-4 mb-6">
                <div>
                    <div class="text-xs text-neutral-500 font-semibold mb-1">Total Pendapatan (Keseluruhan)</div>
                    <div class="text-lg font-bold text-neutral-900">Rp {{ number_format($referrer->rewards->sum('amount'), 0, ',', '.') }}</div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-neutral-100">
                    <div>
                        <div class="text-[10px] text-neutral-400 font-bold uppercase mb-1">Sudah Cair</div>
                        <div class="text-sm font-bold text-success-600">Rp {{ number_format($referrer->rewards->where('status', 'disbursed')->sum('amount'), 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-neutral-400 font-bold uppercase mb-1">Menunggu Cair</div>
                        <div class="text-sm font-bold text-warning-600">Rp {{ number_format($referrer->rewards->whereIn('status', ['pending', 'approved'])->sum('amount'), 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-neutral-50 rounded-xl p-4 border border-neutral-100">
                <div class="text-[10px] text-neutral-400 font-bold uppercase tracking-wider mb-2">Rekening Pencairan</div>
                @if($referrer->bank_name)
                    <div class="text-sm font-bold text-neutral-900">{{ $referrer->bank_name }}</div>
                    <div class="text-sm font-mono text-neutral-700 my-0.5">{{ $referrer->bank_account_number }}</div>
                    <div class="text-xs text-neutral-500">a.n {{ $referrer->bank_account_name }}</div>
                @else
                    <div class="text-sm text-neutral-400 italic">Belum mengatur informasi rekening bank.</div>
                @endif
            </div>
        </div>

        {{-- Card: Aksi Lanjutan --}}
        <div class="bg-white rounded-2xl p-6 border border-neutral-200 shadow-sm">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Aksi Lanjutan</h3>
            
            <div class="flex flex-col gap-3">
                <form method="POST" action="{{ route('admin.referrers.toggle', $referrer->id) }}" class="m-0">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Toggle status afiliasi ini?')"
                            class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold transition-colors border {{ $referrer->status === 'active' ? 'border-neutral-200 bg-white text-error-600 hover:bg-error-50' : 'border-neutral-200 bg-white text-success-600 hover:bg-success-50' }}">
                        {{ $referrer->status === 'active' ? 'Nonaktifkan Akun Afiliasi' : 'Aktifkan Akun Afiliasi' }}
                    </button>
                </form>

                @if($referrer->user_id)
                <button type="button" onclick="openResetModal('{{ $referrer->user_id }}', '{{ addslashes($referrer->user?->name) }}')"
                        class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold transition-colors border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50">
                    Reset Password Akun
                </button>
                @endif
            </div>
        </div>

    </div>{{-- end kolom kanan --}}

</div>{{-- end grid --}}


<!-- Modal Reset Password (Copied from Index) -->
@if($referrer->user_id)
<div id="resetPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full p-6" style="max-width: 450px;">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-neutral-900">Reset Password</h3>
            <button onclick="closeResetModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>
        <p class="text-sm text-neutral-500 mb-6">Set password baru secara manual untuk <span id="resetModalName" class="font-bold text-neutral-900"></span></p>
        
        <form id="resetPasswordForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-neutral-700">Password Baru</label>
                    <button type="button" onclick="useStandardPassword('show_new_pwd', 'show_conf_pwd')" class="text-xs font-bold text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-1 rounded-md transition-colors">
                        Gunakan pass standar
                    </button>
                </div>
                <div class="relative">
                    <input type="password" id="show_new_pwd" name="password" required minlength="8" 
                           class="w-full pl-4 pr-10 py-2 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all" 
                           placeholder="Masukkan password baru">
                    <button type="button" onclick="togglePasswordVisibility('show_new_pwd', 'show_eye_1')" class="absolute inset-y-0 right-0 px-3 flex items-center text-neutral-400 hover:text-neutral-600">
                        <svg id="show_eye_1" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-neutral-700 mb-2">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password" id="show_conf_pwd" name="password_confirmation" required minlength="8" 
                           class="w-full pl-4 pr-10 py-2 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all" 
                           placeholder="Ulangi password baru">
                    <button type="button" onclick="togglePasswordVisibility('show_conf_pwd', 'show_eye_2')" class="absolute inset-y-0 right-0 px-3 flex items-center text-neutral-400 hover:text-neutral-600">
                        <svg id="show_eye_2" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeResetModal()" class="px-5 py-2.5 text-sm font-bold text-neutral-600 bg-neutral-100 rounded-xl hover:bg-neutral-200 transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-opacity" style="background-color:#EF4444;">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function useStandardPassword(newId, confId) {
        document.getElementById(newId).value = 'ypib2026';
        document.getElementById(confId).value = 'ypib2026';
    }

    function togglePasswordVisibility(inputId, iconId) {
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
    
    function openResetModal(userId, userName) {
        document.getElementById('resetModalName').textContent = userName;
        document.getElementById('resetPasswordForm').action = '/admin/users/' + userId + '/reset-password';
        document.getElementById('resetPasswordModal').classList.remove('hidden');
        document.getElementById('resetPasswordModal').classList.add('flex');
    }
    
    function closeResetModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
        document.getElementById('resetPasswordModal').classList.remove('flex');
    }
</script>
@endif

@endsection
