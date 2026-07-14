<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-card">
            <a href="/" class="auth-logo">
                <div class="auth-logo-box">
                    <img src="{{ asset('images/logo-ypib.png') }}" alt="Logo" style="height:24px; filter:brightness(0) invert(1);">
                </div>
                <div>
                    <h2 style="font-size:16px; font-weight:700; color:#1C1E21; margin:0; line-height:1.2;">PMB YPIB</h2>
                    <p style="font-size:12px; color:#5D6C7B; margin:0;">Verify Email</p>
                </div>
            </a>

            <div class="mb-4 text-sm" style="color: #444950; line-height: 1.5;">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="auth-session-status">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="mt-4 flex flex-col gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="auth-btn-primary">
                        {{ __('Resend Verification Email') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="text-align: center;">
                    @csrf
                    <button type="submit" class="auth-link" style="background:none; border:none; cursor:pointer; padding:0;">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
