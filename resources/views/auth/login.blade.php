<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk / Daftar - {{ config('app.name', 'Website Batik') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ffffff;
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 380px;
        }

        .auth-header {
            margin-bottom: 26px;
        }

        .auth-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.02em;
        }

        .auth-header p {
            margin-top: 6px;
            font-size: 14px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            color: #111827;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-input:focus {
            border-color: #111827;
            box-shadow: 0 0 0 1px #111827;
        }

        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 1px #ef4444;
        }

        .error-message {
            margin-top: 5px;
            font-size: 12px;
            color: #dc2626;
        }

        .status-alert {
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #065f46;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #4b5563;
            user-select: none;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            accent-color: #111827;
            cursor: pointer;
        }

        .forgot-link {
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: #111827;
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            background-color: #111827;
            border: 1px solid #111827;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-submit:hover {
            background-color: #1f2937;
        }

        .btn-submit:active {
            background-color: #030712;
        }

        .switch-box {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }

        .switch-link {
            background: none;
            border: none;
            color: #111827;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            font-family: inherit;
        }

        .switch-link:hover {
            color: #374151;
        }

        .hidden-form {
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">

        {{-- FORM LOGIN --}}
        <div id="login-section">
            <div class="auth-header">
                <h1>Masuk</h1>
                <p>Masukkan email dan kata sandi Anda untuk melanjutkan</p>
            </div>

            @if (session('status'))
                <div class="status-alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="login_email" class="form-label">Email</label>
                    <input
                        id="login_email"
                        type="email"
                        name="email"
                        class="form-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@email.com"
                    >
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="login_password" class="form-label">Kata Sandi</label>
                    <input
                        id="login_password"
                        type="password"
                        name="password"
                        class="form-input @error('password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <label for="remember_me" class="remember-me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    Masuk
                </button>
            </form>

            <div class="switch-box">
                Belum punya akun?
                <button type="button" class="switch-link" onclick="switchTo('register')">
                    Register di sini
                </button>
            </div>
        </div>

        {{-- FORM REGISTER --}}
        <div id="register-section" class="hidden-form">
            <div class="auth-header">
                <h1>Daftar Akun</h1>
                <p>Lengkapi formulir di bawah ini untuk membuat akun baru</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="form-group">
                    <label for="reg_name" class="form-label">Nama Lengkap</label>
                    <input
                        id="reg_name"
                        type="text"
                        name="name"
                        class="form-input @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        placeholder="Nama lengkap Anda"
                    >
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="form-group">
                    <label for="reg_email" class="form-label">Email</label>
                    <input
                        id="reg_email"
                        type="email"
                        name="email"
                        class="form-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        placeholder="nama@email.com"
                    >
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="reg_password" class="form-label">Kata Sandi</label>
                    <input
                        id="reg_password"
                        type="password"
                        name="password"
                        class="form-input @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    >
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="reg_password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                    <input
                        id="reg_password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-input @error('password_confirmation') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi"
                    >
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" style="margin-top: 8px;">
                    Daftar Sekarang
                </button>
            </form>

            <div class="switch-box">
                Sudah punya akun?
                <button type="button" class="switch-link" onclick="switchTo('login')">
                    Login di sini
                </button>
            </div>
        </div>

    </div>

    <script>
        function switchTo(form) {
            const loginSection = document.getElementById('login-section');
            const registerSection = document.getElementById('register-section');

            if (form === 'register') {
                loginSection.style.display = 'none';
                registerSection.style.display = 'block';
                window.location.hash = 'register';
                const nameInput = document.getElementById('reg_name');
                if (nameInput) nameInput.focus();
            } else {
                registerSection.style.display = 'none';
                loginSection.style.display = 'block';
                window.location.hash = '';
                const emailInput = document.getElementById('login_email');
                if (emailInput) emailInput.focus();
            }
        }

        // Deteksi apakah saat load perlu membuka form register (misal ada error register atau hash #register)
        window.addEventListener('DOMContentLoaded', () => {
            const isRegisterError = @json($errors->has('name') || $errors->has('password_confirmation') || (bool) old('name'));
            if (isRegisterError || window.location.hash === '#register') {
                switchTo('register');
            } else {
                switchTo('login');
            }
        });
    </script>
</body>
</html>
