<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('auth-title', 'PMB YPIB Majalengka')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; padding: 0; background: #F1F4F7; min-height: 100vh; display: flex; align-items: center; justify-content: center; }

        .auth-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border: 1px solid #DEE3E9;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(10,19,23,0.08);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
            text-decoration: none;
        }
        .auth-logo-box {
            width: 40px; height: 40px;
            background: #082e8f; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        /* Input styling */
        .auth-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444950;
            margin-bottom: 6px;
        }
        .auth-input {
            width: 100%;
            height: 44px;
            border: 1px solid #CED0D4;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 14px;
            color: #1C1E21;
            background: #FFFFFF;
            outline: none;
            transition: border 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .auth-input:focus { border: 2px solid #082e8f; }
        .auth-input-error { border-color: #E41E3F !important; }

        .auth-error-msg {
            font-size: 12px;
            color: #E41E3F;
            margin-top: 4px;
        }
        .auth-group { margin-bottom: 20px; }

        /* Auth buttons */
        .auth-btn-primary {
            width: 100%;
            height: 44px;
            background: #082e8f;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 9999px;
            cursor: pointer;
            transition: background 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .auth-btn-primary:hover { background: #052066; }

        .auth-divider {
            height: 1px;
            background: #DEE3E9;
            margin: 24px 0;
        }
        .auth-link {
            color: #082e8f;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }
        .auth-link:hover { text-decoration: underline; }

        .auth-session-status {
            background: #E8F5E9;
            border: 1px solid #A5D6A7;
            color: #2E7D32;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 20px; border-radius: 16px; }
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    {{ $slot }}
</div>
</body>
</html>
