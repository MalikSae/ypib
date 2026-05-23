@extends('layouts.landing')
@section('title', 'Program Afiliasi — PMB YPIB Majalengka')

@section('content')
<section style="padding:64px 0;min-height:calc(100vh - 64px);" class="bg-neutral-100">
<div class="pub-container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:48px;align-items:start;">
        
        {{-- KIRI: Informasi & Benefit --}}
        <div>
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-6">
                <svg style="width:28px;height:28px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-primary-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                </svg>
            </div>
            <h1 style="font-size:36px;font-weight:700;margin:0 0 16px 0;line-height:1.2;" class="text-neutral-900">Ajak teman kuliah,<br>dapatkan reward!</h1>
            <p style="font-size:16px;margin:0 0 32px 0;line-height:1.6;" class="text-neutral-500">Program Afiliasi YPIB memberikan keuntungan untuk kamu dan temanmu. Cukup bagikan link unikmu.</p>
            
            <div style="display:flex;flex-direction:column;gap:24px;">
                {{-- Point 1 --}}
                <div style="display:flex;align-items:flex-start;gap:16px;">
                    <div style="width:48px;height:48px;border-radius:16px;background:#e8f0fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-primary-700"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:700;margin-bottom:4px;" class="text-neutral-900">Kamu dapat Rp 50.000</div>
                        <div style="font-size:14px;line-height:1.5;" class="text-neutral-500">Untuk setiap pendaftar yang berhasil membayar biaya pendaftaran melalui link referral-mu.</div>
                    </div>
                </div>
                {{-- Point 2 --}}
                <div style="display:flex;align-items:flex-start;gap:16px;">
                    <div style="width:48px;height:48px;border-radius:16px;background:#e6f4ea;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-success"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:700;margin-bottom:4px;" class="text-neutral-900">Mudah & Transparan</div>
                        <div style="font-size:14px;line-height:1.5;" class="text-neutral-500">Pantau jumlah klik, pendaftar, dan komisi yang kamu kumpulkan langsung melalui Dashboard Afiliasi.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: Form Pendaftaran / Aktivasi --}}
        <div>
            <div class="pub-card" style="padding:32px;">
                @auth
                    {{-- State: Sudah Login tapi belum aktif afiliasi --}}
                    <div style="text-align:center;margin-bottom:24px;">
                        <h2 style="font-size:24px;font-weight:700;margin:0 0 8px 0;" class="text-neutral-900">Halo, {{ auth()->user()->name }}!</h2>
                        <p style="font-size:14px;margin:0;" class="text-neutral-500">Satu langkah lagi untuk menjadi mitra afiliasi kami.</p>
                    </div>

                    <form method="POST" action="{{ route('referrer.store') }}">
                        @csrf
                        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;height:48px;font-size:15px;border-radius:100px;">
                            Aktifkan Akun Afiliasi Saya
                        </button>
                    </form>
                @else
                    {{-- State: Belum Login (Form Registrasi Publik) --}}
                    <div style="margin-bottom:24px;">
                        <h2 style="font-size:24px;font-weight:700;margin:0 0 8px 0;" class="text-neutral-900">Daftar Sekarang</h2>
                        <p style="font-size:14px;margin:0;" class="text-neutral-500">Buat akun untuk mendapatkan link unikmu.</p>
                    </div>

                    <form id="referrer_form" method="POST" action="{{ route('referrer.register') }}">
                        @csrf
                        
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nama Lengkap</label>
                            <input type="text" name="name" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required autofocus>
                            @error('name')<div style="font-size:12px;margin-top:4px;" class="text-error">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Email</label>
                            <input type="email" id="reg_email" name="email" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" value="{{ old('email') }}" placeholder="Contoh: budi@gmail.com" onblur="validateLiveEmail(this)" oninput="clearLiveError(this, 'email_live_error')" required>
                            <div id="email_live_error" style="display:none;font-size:12px;margin-top:4px;" class="text-error">Format email tidak valid (harus menggunakan domain lengkap seperti .com, .id, dll).</div>
                            @error('email')<div style="font-size:12px;margin-top:4px;" class="text-error">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Nomor HP / WhatsApp</label>
                            <input type="tel" id="reg_phone" name="phone" class="w-full h-11 px-3 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" onblur="validateLivePhone(this)" oninput="this.value = this.value.replace(/[^0-9]/g, ''); clearLiveError(this, 'phone_live_error');" required>
                            <div id="phone_live_error" style="display:none;font-size:12px;margin-top:4px;" class="text-error">Nomor HP harus berupa angka dan minimal 10 digit.</div>
                            @error('phone')<div style="font-size:12px;margin-top:4px;" class="text-error">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Password</label>
                            <div style="position:relative;">
                                <input type="password" id="reg_password" name="password" class="w-full h-11 px-3 pr-10 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" placeholder="Minimal 8 karakter" required>
                                <button type="button" onclick="togglePassword('reg_password', 'icon_reg_password')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;" class="text-neutral-400">
                                    <svg id="icon_reg_password" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')<div style="font-size:12px;margin-top:4px;" class="text-error">{{ $message }}</div>@enderror
                        </div>

                        <div style="margin-bottom:24px;">
                            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;" class="text-neutral-900">Konfirmasi Password</label>
                            <div style="position:relative;">
                                <input type="password" id="reg_password_confirmation" name="password_confirmation" class="w-full h-11 px-3 pr-10 rounded-lg border border-[#ced0d4] text-[15px] focus:outline-none focus:border-[#1a43a8] focus:ring-1 focus:ring-[#1a43a8]" placeholder="Ulangi password" required>
                                <button type="button" onclick="togglePassword('reg_password_confirmation', 'icon_reg_password_confirmation')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;" class="text-neutral-400">
                                    <svg id="icon_reg_password_confirmation" style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;height:48px;font-size:15px;border-radius:100px;">
                            Daftar & Dapatkan Link
                        </button>
                        
                        <div style="margin-top:20px;text-align:center;font-size:14px;" class="text-neutral-500">
                            Sudah punya akun? <a href="{{ route('referrer.dashboard') }}" style="font-weight:600;text-decoration:none;" class="text-primary-700">Login</a>
                        </div>
                    </form>
                @endauth
            </div>
        </div>

    </div>
</div>
</section>

<script>
function togglePassword(inputId, iconSvgId) {
    const input = document.getElementById(inputId);
    const svg = document.getElementById(iconSvgId);
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
    } else {
        input.type = 'password';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
    }
}

function validateLiveEmail(input) {
    const errorDiv = document.getElementById('email_live_error');
    const regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    // Only show error if they've typed something
    if (input.value.length > 0 && !regex.test(input.value)) {
        errorDiv.style.display = 'block';
        input.classList.add('border-[#e41e3f]', 'focus:border-[#e41e3f]', 'focus:ring-[#e41e3f]');
        input.classList.remove('border-[#ced0d4]', 'focus:border-[#1a43a8]', 'focus:ring-[#1a43a8]');
        input.setCustomValidity('Format email tidak valid.');
    } else {
        clearLiveError(input, 'email_live_error');
    }
}

function validateLivePhone(input) {
    const errorDiv = document.getElementById('phone_live_error');
    if (input.value.length > 0 && input.value.length < 10) {
        errorDiv.style.display = 'block';
        input.classList.add('border-[#e41e3f]', 'focus:border-[#e41e3f]', 'focus:ring-[#e41e3f]');
        input.classList.remove('border-[#ced0d4]', 'focus:border-[#1a43a8]', 'focus:ring-[#1a43a8]');
        input.setCustomValidity('Nomor HP minimal 10 digit.');
    } else {
        clearLiveError(input, 'phone_live_error');
    }
}

function clearLiveError(input, errorDivId) {
    const errorDiv = document.getElementById(errorDivId);
    if (errorDiv) errorDiv.style.display = 'none';
    input.classList.remove('border-[#e41e3f]', 'focus:border-[#e41e3f]', 'focus:ring-[#e41e3f]');
    input.classList.add('border-[#ced0d4]', 'focus:border-[#1a43a8]', 'focus:ring-[#1a43a8]');
    input.setCustomValidity('');
}

const form = document.getElementById('referrer_form');
if (form) {
    form.addEventListener('submit', function(e) {
        const emailInput = document.getElementById('reg_email');
        const phoneInput = document.getElementById('reg_phone');
        
        if (emailInput) validateLiveEmail(emailInput);
        if (phoneInput) validateLivePhone(phoneInput);
    });
}
</script>
@endsection
