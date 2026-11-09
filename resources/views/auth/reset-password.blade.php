<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Atur Ulang Kata Sandi | Hamzah Store</title>

    <!-- Google Fonts: Playfair Display & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm group-hover:bg-stone-800 transition-all duration-300">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                </div>
                <span class="font-bold tracking-wider text-sm sm:text-base text-[#26211D] uppercase font-sans">
                    Hamzah Style Official
                </span>
            </a>
        </div>
    </header>

    <!-- Main Section -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-[#EEDBC5]/50 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#F5EDE2]/70 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-white border border-[#ECE4D8] rounded-3xl p-6 sm:p-10 shadow-[0_20px_60px_-15px_rgba(32,26,23,0.06)] relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center text-[#201A17] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <h1 class="font-serif-luxury text-2xl font-semibold text-[#26211D] tracking-tight">
                Buat Sandi Baru
            </h1>
            <p class="text-xs text-stone-600 mt-2 leading-relaxed font-light">
                Silakan buat kata sandi baru yang kuat untuk mengamankan akun belanja Anda.
            </p>

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full px-4 py-2.5 bg-[#FAF7F2] border @error('email') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                    />
                    @error('email')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Password Baru
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 bg-[#FAF7F2] border @error('password') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                    />
                    @error('password')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold tracking-wider text-stone-800 uppercase mb-1.5">
                        Konfirmasi Password
                    </label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 bg-[#FAF7F2] border border-[#ECE4D8] rounded-xl text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition duration-200"
                    />
                    @error('password_confirmation')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 py-3.5 px-6 rounded-xl bg-[#201A17] text-[#FAF7F2] text-sm font-semibold tracking-wider hover:bg-stone-800 active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group"
                >
                    <span>Simpan Kata Sandi Baru</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-[#ECE4D8] text-center">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-stone-600 hover:text-[#B58742] transition">
                    ← Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </main>
</body>
</html>