<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-card">
            <a href="/" class="auth-logo">
                <div class="auth-logo-box">
                    <img src="{{ asset('images/logo-ypib.png') }}" alt="Logo" style="height:24px; filter:brightness(0) invert(1);">
                </div>
                <div>
                    <h2 style="font-size:16px; font-weight:700; color:#1C1E21; margin:0; line-height:1.2;">PMB YPIB</h2>
                    <p style="font-size:12px; color:#5D6C7B; margin:0;">Confirm Password</p>
                </div>
            </a>

            <div class="mb-4 text-sm" style="color: #444950; line-height: 1.5;">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="auth-group">
                    <label for="password" class="auth-label">{{ __('Password') }}</label>
                    <input id="password" class="auth-input @error('password') auth-input-error @enderror" type="password" name="password" required autocomplete="current-password" />
                    @error('password')
                        <div class="auth-error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="auth-btn-primary">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
