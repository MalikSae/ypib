<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-card">
            <a href="/" class="auth-logo">
                <div class="auth-logo-box">
                    <img src="{{ asset('images/logo-ypib.png') }}" alt="Logo" style="height:24px; filter:brightness(0) invert(1);">
                </div>
                <div>
                    <h2 style="font-size:16px; font-weight:700; color:#1C1E21; margin:0; line-height:1.2;">PMB YPIB</h2>
                    <p style="font-size:12px; color:#5D6C7B; margin:0;">Reset Password</p>
                </div>
            </a>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="auth-group">
                    <label for="email" class="auth-label">{{ __('Email') }}</label>
                    <input id="email" class="auth-input @error('email') auth-input-error @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                    @error('email')
                        <div class="auth-error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="auth-group">
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <input id="password" class="auth-input @error('password') auth-input-error @enderror" type="password" name="password" required autocomplete="new-password" />
                    @error('password')
                        <div class="auth-error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="auth-group">
                    <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" class="auth-input @error('password_confirmation') auth-input-error @enderror" type="password" name="password_confirmation" required autocomplete="new-password" />
                    @error('password_confirmation')
                        <div class="auth-error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="auth-btn-primary">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
