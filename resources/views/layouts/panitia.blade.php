<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    @include('partials.pwa-head', ['manifestPath' => 'manifest-panitia.json'])
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panitia PMB YPIB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="text-neutral-900 antialiased">
    
    <nav class="bg-primary-800 shadow-md border-b border-primary-900 sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="{{ route('panitia.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="h-8 w-auto">
                        <span class="font-bold text-white text-lg">Panitia PMB</span>
                    </a>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-primary-100 hover:text-white flex items-center justify-center p-2 rounded-full hover:bg-primary-700 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="bg-primary-800 text-primary-100 py-3 shadow-inner">
        <div class="max-w-md mx-auto px-4 sm:px-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-700 flex items-center justify-center font-bold text-white shadow-sm border border-primary-600">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <p class="text-xs text-primary-300 font-medium tracking-wide uppercase">Interviewer</p>
                <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
            </div>
        </div>
    </div>

    <main class="py-6 max-w-md mx-auto px-4 sm:px-6 pb-24">
        @if(session('success'))
            <div class="mb-4 bg-success-100 border border-success-200 text-success-800 rounded-xl p-4 text-sm flex items-start shadow-sm">
                <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-error-100 border border-error-200 text-error-800 rounded-xl p-4 text-sm flex items-start shadow-sm">
                <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
    @include('partials.pwa-register')
    @include('partials.pwa-install-banner', ['section' => 'panitia'])
</body>
</html>
