<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Lupa Password | Hamzah Style Official — Boutique Atelier</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

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

    <!-- Top Minimal Header -->
    <header class="w-full border-b border-[#EFE0D6]/60 bg-[#FFF8F5]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3.5 group">
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

            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#110703] hover:text-[#C9704C] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali Masuk</span>
            </a>
        </div>
    </header>

    <!-- Main Section -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#F6DED1]/60 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#F4E5DC]/70 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-white border border-[#EFE0D6] rounded-3xl p-6 sm:p-10 shadow-[0_20px_60px_-15px_rgba(43,30,22,0.07)] relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF8F5] border border-[#EFE0D6] flex items-center justify-center text-[#2B1E16] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>

            <h1 class="font-serif-luxury text-2xl font-semibold text-[#110703] tracking-tight">
                Lupa Kata Sandi?
            </h1>
            <p class="text-xs text-[#4E4540] mt-2 leading-relaxed font-light">
                Masukkan alamat email akun Anda. Kami akan mengirimkan tautan verifikasi untuk mengatur ulang kata sandi Anda.
            </p>

            <!-- Status Alert -->
            @if (session('status'))
                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold tracking-wider text-[#211A15] uppercase mb-1.5">
                        Email Terdaftar
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
                            placeholder="pelanggan@hamzahstyle.id"
                            class="w-full pl-10 pr-4 py-3 bg-[#FFF8F5] border @error('email') border-red-400 @else border-[#EFE0D6] @enderror rounded-xl text-sm text-[#211A15] placeholder-[#80756F]/60 focus:outline-none focus:ring-2 focus:ring-[#2B1E16] focus:border-transparent transition duration-200"
                        />
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-6 rounded-xl bg-[#2B1E16] text-[#FFF8F5] text-sm font-semibold tracking-wider hover:bg-[#110703] active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group"
                >
                    <span>Kirim Tautan Reset</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-[#EFE0D6] text-center">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-[#80756F] hover:text-[#110703] transition">
                    ← Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-[#EFE0D6]/60 bg-[#FFF1E9]/60 px-6 sm:px-12 py-5 text-xs text-[#4E4540] text-center">
        <span>© {{ date('Y') }} HAMZAH STYLE OFFICIAL. CONSCIOUS CRAFTSMANSHIP.</span>
    </footer>
</body>
</html>
