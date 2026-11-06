<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Email | Hamzah Style Official</title>

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
    <header class="w-full border-b border-[#EFE0D6]/60 bg-[#FFF8F5]/90 backdrop-blur-md sticky top-0 z-30 px-6 sm:px-12 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3.5 group">
                <div class="w-10 h-10 rounded-xl bg-[#2B1E16] flex items-center justify-center text-[#FFDBCE] shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="font-bold tracking-widest text-sm text-[#110703] uppercase">Hamzah Style Official</span>
                    <span class="text-[10px] text-[#4E4540] uppercase tracking-[0.2em] font-medium -mt-0.5">Boutique Atelier</span>
                </div>
            </a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 sm:p-8 relative overflow-hidden">
        <div class="w-full max-w-md bg-white border border-[#EFE0D6] rounded-3xl p-6 sm:p-10 shadow-[0_20px_60px_-15px_rgba(43,30,22,0.07)] relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF8F5] border border-[#EFE0D6] flex items-center justify-center text-[#2B1E16] mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h1 class="font-serif-luxury text-2xl font-semibold text-[#110703]">Verifikasi Email Anda</h1>
            <p class="text-xs text-[#4E4540] mt-2 leading-relaxed">
                Terima kasih telah mendaftar di Hamzah Style Official! Silakan periksa kotak masuk email Anda dan klik tautan verifikasi yang baru saja kami kirimkan.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium">
                    Tautan verifikasi baru telah berhasil dikirim ke alamat email yang Anda berikan saat pendaftaran.
                </div>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-[#EFE0D6]">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto py-2.5 px-5 rounded-xl bg-[#2B1E16] text-[#FFF8F5] text-xs font-semibold tracking-wider hover:bg-[#110703] transition">
                        Kirim Ulang Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-[#80756F] hover:text-[#110703] underline">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer class="w-full border-t border-[#EFE0D6]/60 bg-[#FFF1E9]/60 px-6 sm:px-12 py-5 text-xs text-[#4E4540] text-center">
        <span>© {{ date('Y') }} HAMZAH STYLE OFFICIAL. CONSCIOUS CRAFTSMANSHIP.</span>
    </footer>
</body>
</html>
