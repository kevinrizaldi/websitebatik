<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Akun | Hamzah Store</title>

    <!-- Google Fonts: Playfair Display & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF7F2;
            color: #26211D;
        }
        .font-serif-luxury {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-[#B58742] selection:text-white antialiased bg-[#FAF7F2] text-[#26211D]">

    <!-- Top Minimal Header -->
    <header class="w-full border-b border-[#ECE4D8] bg-[#FAF7F2]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center group">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="HAMZAH STYLE OFFICIAL" 
                     class="h-8 sm:h-9 w-auto object-contain">
            </a>

            <!-- Nav Links (Desktop) -->
            <div class="hidden md:flex items-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#201A17]/20 text-xs tracking-wider uppercase font-medium text-[#201A17] hover:bg-[#201A17] hover:text-white hover:border-[#201A17] transition duration-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            <!-- Mobile Back Button -->
            <div class="md:hidden">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-stone-600 hover:text-stone-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Beranda</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Registration Section -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 lg:p-12 relative overflow-hidden">
        <!-- Ambient Background Glows -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#EEDBC5]/50 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#F5EDE2]/70 blur-3xl pointer-events-none"></div>

        <!-- Registration Card Form -->
        <div class="w-full max-w-xl bg-white border border-[#ECE4D8] rounded-3xl shadow-[0_20px_60px_-15px_rgba(32,26,23,0.06)] p-6 sm:p-10 relative z-10">

            <!-- Header Branding & Emblem -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div class="h-4 w-px bg-[#ECE4D8]"></div>
                    <span class="text-xs uppercase tracking-widest text-stone-700 font-semibold">
                        Hamzah Store
                    </span>
                </div>

                <h1 class="font-serif-luxury text-2xl sm:text-3xl font-semibold text-[#26211D] tracking-tight">
                    Buat Akun Baru
                </h1>
                <p class="text-sm text-stone-600 mt-1.5 leading-relaxed font-light">
                    Daftar untuk mulai berbelanja busana batik Nusantara di Hamzah Style Official.
                </p>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- 1. Full Name -->
                <div>
                    <label for="name" class="block text-[11px] font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Nama Lengkap <span class="text-[#B58742]">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap"
                            class="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border @error('name') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                        />
                    </div>
                    @error('name')
                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 2. Email Address -->
                <div>
                    <label for="email" class="block text-[11px] font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Email <span class="text-[#B58742]">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            placeholder="Masukkan email aktif"
                            class="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border @error('email') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                        />
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 3. Phone Number -->
                <div>
                    <label for="phone" class="block text-[11px] font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        No. Telepon / WhatsApp <span class="text-stone-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full pl-10 pr-4 py-2.5 bg-[#FAF7F2] border @error('phone') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                        />
                    </div>
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 4. Password with Strength Indicator -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-[11px] font-semibold tracking-wider text-stone-800 uppercase">
                            Password <span class="text-[#B58742]">*</span>
                        </label>
                        <span id="strengthText" class="text-[10px] tracking-wider text-stone-400 font-medium">
                            Kekuatan: Belum terisi
                        </span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Buat password minimal 8 karakter"
                            class="w-full pl-10 pr-11 py-2.5 bg-[#FAF7F2] border @error('password') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                        />
                        <!-- Toggle Password -->
                        <button
                            type="button"
                            id="togglePasswordBtn"
                            aria-label="Tampilkan kata sandi"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-stone-700 transition focus:outline-none"
                        >
                            <svg id="eyeIcon1" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashIcon1" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>

                    <!-- Password Strength 4-Bar Meter -->
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        <div id="bar1" class="h-1 rounded-full bg-[#ECE4D8] transition-colors duration-300"></div>
                        <div id="bar2" class="h-1 rounded-full bg-[#ECE4D8] transition-colors duration-300"></div>
                        <div id="bar3" class="h-1 rounded-full bg-[#ECE4D8] transition-colors duration-300"></div>
                        <div id="bar4" class="h-1 rounded-full bg-[#ECE4D8] transition-colors duration-300"></div>
                    </div>

                    @error('password')
                        <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 5. Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Konfirmasi Password <span class="text-[#B58742]">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Masukkan kembali password"
                            class="w-full pl-10 pr-11 py-2.5 bg-[#FAF7F2] border border-[#ECE4D8] rounded-xl text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                        />
                        <!-- Toggle Confirm Password -->
                        <button
                            type="button"
                            id="toggleConfirmBtn"
                            aria-label="Tampilkan konfirmasi kata sandi"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-stone-700 transition focus:outline-none"
                        >
                            <svg id="eyeIcon2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashIcon2" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 6. Agreement Checkbox -->
                <div class="pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer text-xs text-stone-600">
                        <input
                            type="checkbox"
                            name="terms"
                            required
                            class="mt-0.5 w-4 h-4 rounded border-[#ECE4D8] text-[#B58742] focus:ring-[#B58742] transition"
                        />
                        <span class="leading-relaxed">
                            Saya menyetujui <span class="font-semibold text-stone-900">Syarat & Ketentuan</span> dan <span class="font-semibold text-stone-900">Kebijakan Privasi</span> Hamzah Style Official.
                        </span>
                    </label>
                </div>

                <!-- 7. Submit Button -->
                <button
                    type="submit"
                    class="w-full mt-2 py-3.5 px-6 rounded-xl bg-[#201A17] text-[#FAF7F2] text-sm font-semibold tracking-wider hover:bg-stone-800 active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group"
                >
                    <span>Daftar</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <!-- Visual Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-[#ECE4D8]"></div>
                </div>
                <div class="relative flex justify-center text-[11px] uppercase tracking-widest font-semibold text-stone-400">
                    <span class="bg-white px-3">Atau</span>
                </div>
            </div>

            <!-- Google Registration Button -->
            <a
                href="{{ route('auth.google') }}"
                class="w-full py-3 px-4 rounded-xl bg-[#F5EDE2] hover:bg-[#ECE1D2] border border-[#ECE4D8] text-xs font-semibold text-stone-900 tracking-wide flex items-center justify-center gap-3 transition duration-200"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                </svg>
                <span>Daftar dengan Google</span>
            </a>

            <!-- Footer Redirection -->
            <div class="text-center text-sm text-stone-600 mt-6">
                <span>Sudah punya akun?</span>
                <a href="{{ route('login') }}" class="font-semibold text-[#B58742] hover:text-[#8C6428] underline underline-offset-4 ml-1 transition">
                    Masuk sekarang
                </a>
            </div>
        </div>
    </main>

    <!-- Interactive JS: Password Visibility & Strength Meter -->
    <script>
        // Password Visibility Toggle for Password
        const passwordInput = document.getElementById('password');
        const toggleBtn1 = document.getElementById('togglePasswordBtn');
        const eyeIcon1 = document.getElementById('eyeIcon1');
        const eyeSlashIcon1 = document.getElementById('eyeSlashIcon1');

        if (toggleBtn1 && passwordInput) {
            toggleBtn1.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon1.classList.toggle('hidden', isPassword);
                eyeSlashIcon1.classList.toggle('hidden', !isPassword);
            });
        }

        // Password Visibility Toggle for Confirm Password
        const confirmInput = document.getElementById('password_confirmation');
        const toggleBtn2 = document.getElementById('toggleConfirmBtn');
        const eyeIcon2 = document.getElementById('eyeIcon2');
        const eyeSlashIcon2 = document.getElementById('eyeSlashIcon2');

        if (toggleBtn2 && confirmInput) {
            toggleBtn2.addEventListener('click', function () {
                const isPassword = confirmInput.getAttribute('type') === 'password';
                confirmInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon2.classList.toggle('hidden', isPassword);
                eyeSlashIcon2.classList.toggle('hidden', !isPassword);
            });
        }

        // Password Strength Calculation & Meter
        const strengthText = document.getElementById('strengthText');
        const bar1 = document.getElementById('bar1');
        const bar2 = document.getElementById('bar2');
        const bar3 = document.getElementById('bar3');
        const bar4 = document.getElementById('bar4');
        const bars = [bar1, bar2, bar3, bar4];

        if (passwordInput && strengthText) {
            passwordInput.addEventListener('input', function () {
                const val = passwordInput.value;
                let score = 0;

                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                bars.forEach(b => {
                    b.className = 'h-1 rounded-full bg-[#ECE4D8] transition-colors duration-300';
                });

                if (val.length === 0) {
                    strengthText.textContent = 'Kekuatan: Belum terisi';
                    strengthText.className = 'text-[10px] tracking-wider text-stone-400 font-medium';
                } else if (score <= 1) {
                    strengthText.textContent = 'Kekuatan: Lemah';
                    strengthText.className = 'text-[10px] tracking-wider text-rose-600 font-semibold';
                    bar1.className = 'h-1 rounded-full bg-rose-500 transition-colors duration-300';
                } else if (score === 2) {
                    strengthText.textContent = 'Kekuatan: Cukup';
                    strengthText.className = 'text-[10px] tracking-wider text-amber-600 font-semibold';
                    bar1.className = bar2.className = 'h-1 rounded-full bg-amber-500 transition-colors duration-300';
                } else if (score === 3) {
                    strengthText.textContent = 'Kekuatan: Baik';
                    strengthText.className = 'text-[10px] tracking-wider text-blue-600 font-semibold';
                    bar1.className = bar2.className = bar3.className = 'h-1 rounded-full bg-blue-500 transition-colors duration-300';
                } else {
                    strengthText.textContent = 'Kekuatan: Sangat Kuat';
                    strengthText.className = 'text-[10px] tracking-wider text-emerald-600 font-semibold';
                    bars.forEach(b => b.className = 'h-1 rounded-full bg-emerald-600 transition-colors duration-300');
                }
            });
        }
    </script>
</body>
</html>