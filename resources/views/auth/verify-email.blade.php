<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Email | Hamzah Store</title>

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
            <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center text-[#201A17] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h1 class="font-serif-luxury text-2xl font-semibold text-[#26211D]">Verifikasi Email Anda</h1>
            <p class="text-xs text-stone-600 mt-2 leading-relaxed">
                Terima kasih telah mendaftar di Hamzah Style Official! Silakan periksa kotak masuk email Anda dan klik tautan verifikasi yang baru saja kami kirimkan.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                    Tautan verifikasi baru telah berhasil dikirim ke alamat email yang Anda berikan saat pendaftaran.
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-[#201A17] text-[#FAF7F2] text-xs font-semibold tracking-wider hover:bg-stone-800 transition">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-center text-xs text-stone-500 hover:text-stone-900 transition py-1">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>