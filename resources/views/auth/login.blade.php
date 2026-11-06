<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk | Hamzah Style Official — Boutique Atelier</title>

    <!-- Google Fonts: Playfair Display, Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
            background-color: #FFF8F5;
            color: #110703;
        }
        .font-serif-luxury {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-[#2B1E16] selection:text-white antialiased">

    <!-- Top Atelier Minimal Header -->
    <header class="w-full border-b border-[#EFE0D6]/60 bg-[#FFF8F5]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3.5 group">
                <!-- Luxury Geometric Motif Emblem -->
                <div class="w-10 h-10 rounded-xl bg-[#2B1E16] flex items-center justify-center text-[#FFDBCE] shadow-sm group-hover:bg-[#110703] transition-all duration-300">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="font-bold tracking-widest text-sm text-[#110703] uppercase">
                        Hamzah Style Official
                    </span>
                    <span class="text-[10px] text-[#4E4540] uppercase tracking-[0.2em] font-medium -mt-0.5">
                        Boutique Atelier
                    </span>
                </div>
            </a>

            <!-- Nav Links (Desktop) -->
            <div class="hidden md:flex items-center gap-8 text-xs tracking-wider uppercase font-medium text-[#4E4540]">
                <a href="{{ url('/') }}#koleksi" class="hover:text-[#110703] transition">Atelier</a>
                <span class="text-[#EFE0D6]">|</span>
                <a href="{{ url('/') }}#tentang" class="hover:text-[#110703] transition">Filosofi</a>
                <span class="text-[#EFE0D6]">|</span>
                <span class="text-[#80756F]">ID / EN</span>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#2B1E16]/20 text-[#110703] hover:bg-[#2B1E16] hover:text-[#FFF8F5] transition duration-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            <!-- Mobile Back Button -->
            <div class="md:hidden">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#4E4540] hover:text-[#110703]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>Beranda</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Authentication Section -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 lg:p-12 relative overflow-hidden">
        <!-- Ambient Background Glows -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#F6DED1]/60 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#F4E5DC]/70 blur-3xl pointer-events-none"></div>

        <!-- Balanced Two-Column Editorial Card -->
        <div class="w-full max-w-5xl bg-white border border-[#EFE0D6] rounded-3xl shadow-[0_20px_60px_-15px_rgba(43,30,22,0.07)] overflow-hidden grid grid-cols-1 lg:grid-cols-12 relative z-10">

            <!-- LEFT COLUMN: Editorial & Heritage Visual Panel (Desktop) -->
            <div class="hidden lg:flex lg:col-span-5 bg-[#2B1E16] text-[#FFF8F5] relative p-10 flex-col justify-between overflow-hidden">
                <!-- Background Image & Scrim -->
                <div class="absolute inset-0 z-0">
                    <img src="{{ asset('images/beranda/cloth-fabric.jpg') }}" alt="Wastra Nusantara" class="w-full h-full object-cover opacity-35 scale-105 transform hover:scale-110 transition duration-1000 ease-out">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#110703] via-[#2B1E16]/85 to-[#2B1E16]/60"></div>
                </div>

                <!-- Top Header & Material Seal -->
                <div class="relative z-10 flex items-center justify-between">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#FFF8F5]/10 backdrop-blur-md border border-[#FFDBCE]/20 text-[11px] font-medium tracking-widest text-[#FFDBCE] uppercase">
                        <svg class="w-3.5 h-3.5 text-[#FFDBCE]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Kurasi Sirkular</span>
                    </div>
                    <span class="text-[11px] tracking-widest uppercase font-semibold text-[#FFDBCE]/80">Edisi 2025</span>
                </div>

                <!-- Inspirational Quote & Brand Narrative -->
                <div class="relative z-10 my-12 space-y-4">
                    <p class="text-[11px] uppercase tracking-[0.25em] text-[#FFDBCE] font-semibold">
                        Filosofi & Adibusana
                    </p>
                    <blockquote class="font-serif-luxury text-2xl xl:text-3xl font-medium leading-snug text-white">
                        “Wastra Nusantara Berkelanjutan & Busana Adibusana.”
                    </blockquote>
                    <p class="text-xs text-[#FFDBCE]/80 leading-relaxed font-light">
                        Menyatukan keanggunan batik tulis tradisional dengan material kain terselamatkan dalam wujud siluet modern yang abadi.
                    </p>
                </div>

                <!-- Sustainable Material Lineage Badge -->
                <div class="relative z-10 p-4 rounded-2xl bg-[#FFF8F5]/10 backdrop-blur-md border border-[#FFDBCE]/20 flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#401100] flex items-center justify-center text-[#FFDBCE] flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-semibold text-white tracking-wide">Batik Tulis & Upcycled Primissima</p>
                        <p class="text-[11px] text-[#FFDBCE]/75">100% Terlacak & Terdaftar Hak Cipta</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Authentication Card Form -->
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">

                <!-- Header: Emblem & Welcome Heading -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-[#2B1E16] flex items-center justify-center text-[#FFDBCE]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <div class="h-4 w-px bg-[#EFE0D6]"></div>
                        <span class="text-[11px] uppercase tracking-widest text-[#4E4540] font-semibold">
                            Boutique Atelier
                        </span>
                    </div>

                    <h1 class="font-serif-luxury text-2xl sm:text-3xl font-semibold text-[#110703] tracking-tight">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-sm text-[#4E4540] mt-1.5 leading-relaxed font-light">
                        Masuk ke akun Anda untuk melanjutkan belanja koleksi batik dan kriya sirkular.
                    </p>
                </div>

                <!-- Session Status / Alert -->
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-[#F6DED1]/60 border border-[#2B1E16]/15 text-xs text-[#2B1E16] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Form Area -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold tracking-wider text-[#211A15] uppercase mb-1.5">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#4E4540]">
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
                                autofocus
                                autocomplete="username"
                                placeholder="pelanggan@hamzahstyle.id"
                                class="w-full pl-10 pr-4 py-3 bg-[#FFF8F5] border @error('email') border-red-400 @else border-[#EFE0D6] @enderror rounded-xl text-sm text-[#211A15] placeholder-[#80756F]/60 focus:outline-none focus:ring-2 focus:ring-[#2B1E16] focus:border-transparent transition duration-200"
                            />
                        </div>
                        @error('email')
                            <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold tracking-wider text-[#211A15] uppercase">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#4E4540]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••••"
                                class="w-full pl-10 pr-11 py-3 bg-[#FFF8F5] border @error('password') border-red-400 @else border-[#EFE0D6] @enderror rounded-xl text-sm text-[#211A15] placeholder-[#80756F]/60 focus:outline-none focus:ring-2 focus:ring-[#2B1E16] focus:border-transparent transition duration-200"
                            />
                            <!-- Toggle Password Visibility -->
                            <button
                                type="button"
                                id="togglePasswordBtn"
                                aria-label="Tampilkan atau sembunyikan password"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#4E4540] hover:text-[#110703] transition focus:outline-none"
                            >
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeSlashIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Controls Row: Remember me & Forgot Password -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none text-[#332621]">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded border-[#EFE0D6] text-[#2B1E16] focus:ring-[#2B1E16] transition"
                            />
                            <span>Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="font-medium text-[#C9704C] hover:text-[#110703] transition">
                                Lupa Password?
                            </a>
                        @endif
                    </div>

                    <!-- Primary Action CTA Button -->
                    <button
                        type="submit"
                        class="w-full py-3.5 px-6 rounded-xl bg-[#2B1E16] text-[#FFF8F5] text-sm font-semibold tracking-wider hover:bg-[#110703] active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group"
                    >
                        <span>Masuk</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>

                <!-- Visual Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-[#EFE0D6]"></div>
                    </div>
                    <div class="relative flex justify-center text-[11px] uppercase tracking-widest font-semibold text-[#80756F]">
                        <span class="bg-white px-3">Atau</span>
                    </div>
                </div>

                <!-- Google Login Button -->
                <a
                    href="{{ route('auth.google') }}"
                    class="w-full py-3 px-4 rounded-xl bg-[#FFF1E9] hover:bg-[#FFE8DC] border border-[#EFE0D6] text-xs font-semibold text-[#211A15] tracking-wide flex items-center justify-center gap-3 transition duration-200"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                    </svg>
                    <span>Lanjutkan dengan Google</span>
                </a>

                <!-- Alternative Action: Register Prompt -->
                <div class="text-center text-sm text-[#4E4540] mt-6">
                    <span>Belum memiliki akun?</span>
                    <a href="{{ route('register') }}" class="font-semibold text-[#C9704C] hover:text-[#110703] underline underline-offset-4 ml-1 transition">
                        Daftar sekarang
                    </a>
                </div>


            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-[#EFE0D6]/60 bg-[#FFF1E9]/60 px-6 sm:px-12 py-5 text-xs text-[#4E4540]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div>
                <span class="font-medium">© {{ date('Y') }} HAMZAH STYLE OFFICIAL. CONSCIOUS CRAFTSMANSHIP.</span>
                <span class="hidden md:inline mx-1.5 text-[#D2C4BD]">•</span>
                <span class="text-[#80756F]">Handcrafted Upcycled & Batik Tulis</span>
            </div>
            <div class="flex items-center gap-5 text-[11px] font-medium tracking-wide uppercase">
                <a href="{{ url('/') }}" class="hover:text-[#110703] transition">Privacy Policy</a>
                <a href="{{ url('/') }}" class="hover:text-[#110703] transition">Terms of Service</a>
                <a href="{{ url('/') }}" class="hover:text-[#110703] transition">Client Care</a>
            </div>
        </div>
    </footer>

    <!-- Password Visibility Toggle Script -->
    <script>
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.classList.toggle('hidden', isPassword);
                eyeSlashIcon.classList.toggle('hidden', !isPassword);
            });
        }
    </script>
</body>
</html>
