<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Konfirmasi Kata Sandi | Hamzah Store</title>

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
    <header class="w-full border-b border-[#ECE4D8] bg-[#FAF7F2]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm">
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

    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <div class="w-full max-w-md bg-white border border-[#ECE4D8] rounded-3xl p-6 sm:p-10 shadow-[0_20px_60px_-15px_rgba(32,26,23,0.06)] relative z-10">
            <h1 class="font-serif-luxury text-2xl font-semibold text-[#26211D]">Area Aman</h1>
            <p class="text-xs text-stone-600 mt-2 leading-relaxed">
                Ini adalah area aman aplikasi. Harap konfirmasikan kata sandi Anda sebelum melanjutkan.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="password" class="block text-xs font-semibold tracking-wider text-stone-800 uppercase mb-1.5">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••••••"
                        class="w-full px-4 py-2.5 bg-[#FAF7F2] border @error('password') border-red-400 @else border-[#ECE4D8] @enderror rounded-xl text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent transition" />
                    @error('password')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-6 rounded-xl bg-[#201A17] text-[#FAF7F2] text-sm font-semibold tracking-wider hover:bg-stone-800 active:scale-[0.99] transition shadow-md">
                    Konfirmasi
                </button>
            </form>
        </div>
    </main>
</body>
</html>