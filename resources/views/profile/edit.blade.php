@extends('layouts.landing')
@section('title', 'Pengaturan Akun — PMB YPIB')

@section('content')
<div class="pub-container" style="padding-top: 40px; padding-bottom: 80px;">
    <div style="max-width: 600px; width: 100%; margin: 0 auto; background: #FFFFFF; border: 1px solid #DEE3E9; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(10,19,23,0.04);">
        <div style="padding: 24px 32px; border-bottom: 1px solid #DEE3E9; background: #F8FAFC;">
            <h1 style="font-size: 20px; font-weight: 700; color: #0A1317; margin: 0;">Pengaturan Akun</h1>
            <p style="font-size: 14px; color: #5D6C7B; margin: 4px 0 0 0;">Perbarui informasi profil dan kata sandi Anda di sini.</p>
        </div>

        <div style="padding: 32px;">
            {{-- Update Profile Info --}}
            <form method="post" action="{{ route('profile.update') }}" style="margin-bottom: 40px;">
                @csrf
                @method('patch')
                <h2 style="font-size: 16px; font-weight: 700; color: #0A1317; margin: 0 0 16px 0;">Informasi Profil</h2>
                
                @if (session('status') === 'profile-updated')
                    <div style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; border-radius: 8px; padding: 12px 16px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
                        Profil berhasil diperbarui.
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <label for="name" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                        @error('name')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                        @error('email')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Nomor HP (WhatsApp)</label>
                        <input id="phone" name="phone" type="number" value="{{ old('phone', $user->phone) }}" required style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                        @error('phone')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">Simpan Profil</button>
                </div>
            </form>

            <div style="height: 1px; background: #DEE3E9; margin: 0 -32px 32px -32px;"></div>

            {{-- Update Password --}}
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                <h2 style="font-size: 16px; font-weight: 700; color: #0A1317; margin: 0 0 16px 0;">Ubah Kata Sandi</h2>

                @if (session('status') === 'password-updated')
                    <div style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; border-radius: 8px; padding: 12px 16px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
                        Kata sandi berhasil diubah.
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <label for="update_password_current_password" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Kata Sandi Saat Ini</label>
                        <div style="position:relative;">
                            <input id="update_password_current_password" name="current_password" type="password" required autocomplete="current-password" style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 40px 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                            <button type="button" onclick="let input = this.previousElementSibling; let iconOpen = this.querySelector('.icon-open'); let iconClose = this.querySelector('.icon-close'); if(input.type==='password'){input.type='text';iconOpen.style.display='none';iconClose.style.display='block';}else{input.type='password';iconOpen.style.display='block';iconClose.style.display='none';}" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#8595A4; padding:0;">
                                <svg class="icon-open" style="width:20px;height:20px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg class="icon-close" style="width:20px;height:20px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="update_password_password" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Kata Sandi Baru</label>
                        <div style="position:relative;">
                            <input id="update_password_password" name="password" type="password" required autocomplete="new-password" style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 40px 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                            <button type="button" onclick="let input = this.previousElementSibling; let iconOpen = this.querySelector('.icon-open'); let iconClose = this.querySelector('.icon-close'); if(input.type==='password'){input.type='text';iconOpen.style.display='none';iconClose.style.display='block';}else{input.type='password';iconOpen.style.display='block';iconClose.style.display='none';}" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#8595A4; padding:0;">
                                <svg class="icon-open" style="width:20px;height:20px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg class="icon-close" style="width:20px;height:20px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('password', 'updatePassword')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="update_password_password_confirmation" style="display: block; font-size: 13px; font-weight: 600; color: #444950; margin-bottom: 6px;">Konfirmasi Kata Sandi Baru</label>
                        <div style="position:relative;">
                            <input id="update_password_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" style="width: 100%; height: 44px; border: 1px solid #CED0D4; border-radius: 8px; padding: 0 40px 0 14px; font-size: 14px; color: #1C1E21; outline: none; transition: border 0.15s;">
                            <button type="button" onclick="let input = this.previousElementSibling; let iconOpen = this.querySelector('.icon-open'); let iconClose = this.querySelector('.icon-close'); if(input.type==='password'){input.type='text';iconOpen.style.display='none';iconClose.style.display='block';}else{input.type='password';iconOpen.style.display='block';iconClose.style.display='none';}" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#8595A4; padding:0;">
                                <svg class="icon-open" style="width:20px;height:20px;display:block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg class="icon-close" style="width:20px;height:20px;display:none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('password_confirmation', 'updatePassword')<p style="font-size: 12px; color: #E41E3F; margin: 4px 0 0 0;">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">Ubah Sandi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .pub-container input:focus { border: 2px solid #0064E0 !important; }
</style>
@endsection
