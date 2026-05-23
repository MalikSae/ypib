<x-guest-layout>
@section('auth-title', 'Buat Akun — PMB YPIB Majalengka')
<div class="auth-card">
    <a href="{{ route('landing') }}" class="auth-logo" style="margin-bottom: 32px; display: inline-block;">
        <img src="{{ asset('images/logo-ypib.png') }}" alt="PMB YPIB" style="height:44px;width:auto;object-fit:contain;">
    </a>
    <h1 style="font-size:22px;font-weight:700;margin:0 0 4px 0;" class="text-neutral-900">Buat Akun Baru</h1>
    <p style="font-size:14px;margin:0 0 28px 0;" class="text-neutral-500">Isi data di bawah untuk mendaftar ke PMB YPIB</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="auth-group">
            <label for="name" class="auth-label">Nama Lengkap</label>
            <input id="name" type="text" name="name" class="auth-input {{ $errors->has('name') ? 'auth-input-error' : '' }}" value="{{ old('name') }}" required autofocus placeholder="Nama sesuai KTP">
            @error('name')<p class="auth-error-msg">{{ $message }}</p>@enderror
        </div>
        <div class="auth-group">
            <label for="email" class="auth-label">Alamat Email</label>
            <input id="email" type="email" name="email" class="auth-input {{ $errors->has('email') ? 'auth-input-error' : '' }}" value="{{ old('email') }}" required placeholder="nama@email.com">
            @error('email')<p class="auth-error-msg">{{ $message }}</p>@enderror
        </div>
        <div class="auth-group">
            <label for="phone" class="auth-label">Nomor HP (WhatsApp)</label>
            <input id="phone" type="number" name="phone" class="auth-input {{ $errors->has('phone') ? 'auth-input-error' : '' }}" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
            @error('phone')<p class="auth-error-msg">{{ $message }}</p>@enderror
        </div>
        <div class="auth-group">
            <label for="password" class="auth-label">Kata Sandi</label>
            <div style="position:relative;">
                <input id="password" type="password" name="password" class="auth-input {{ $errors->has('password') ? 'auth-input-error' : '' }}" required placeholder="Minimal 8 karakter" style="padding-right:40px;">
                <button type="button" onclick="let input = this.previousElementSibling; let iconOpen = this.querySelector('.icon-open'); let iconClose = this.querySelector('.icon-close'); if(input.type==='password'){input.type='text';iconOpen.style.display='none';iconClose.style.display='block';}else{input.type='password';iconOpen.style.display='block';iconClose.style.display='none';}" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer;  padding:0;" class="text-neutral-400">
                    <svg class="icon-open" style="width:20px;height:20px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg class="icon-close" style="width:20px;height:20px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            @error('password')<p class="auth-error-msg">{{ $message }}</p>@enderror
        </div>
        <div class="auth-group" style="margin-bottom:24px;">
            <label for="password_confirmation" class="auth-label">Konfirmasi Kata Sandi</label>
            <div style="position:relative;">
                <input id="password_confirmation" type="password" name="password_confirmation" class="auth-input" required placeholder="Ulangi kata sandi" style="padding-right:40px;">
                <button type="button" onclick="let input = this.previousElementSibling; let iconOpen = this.querySelector('.icon-open'); let iconClose = this.querySelector('.icon-close'); if(input.type==='password'){input.type='text';iconOpen.style.display='none';iconClose.style.display='block';}else{input.type='password';iconOpen.style.display='block';iconClose.style.display='none';}" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer;  padding:0;" class="text-neutral-400">
                    <svg class="icon-open" style="width:20px;height:20px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg class="icon-close" style="width:20px;height:20px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="auth-btn-primary">Buat Akun</button>
    </form>
    <div class="auth-divider"></div>
    <p style="text-align:center;font-size:14px;margin:0;" class="text-neutral-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="auth-link" style="margin-left:4px;">Masuk sekarang</a>
    </p>
</div>
</x-guest-layout>
