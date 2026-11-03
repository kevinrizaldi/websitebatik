<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — HAMZAH STYLE OFFICIAL' : 'HAMZAH STYLE OFFICIAL — Warisan Batik Nusantara Modern' }}</title>
    <meta name="description" content="{{ $description ?? 'Koleksi busana batik modern dan autentik Indonesia karya pengrajin lokal pilihan. Elegan dalam setiap motif untuk momen berharga Anda.' }}">

    <!-- Google Fonts: Playfair Display (Serif) & Plus Jakarta Sans (Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF7F2;
            color: #26211D;
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{ $extraHead ?? '' }}
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen flex flex-col selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          userMenu: false,
          cartCount: {{ $cartCount ?? 0 }},
          toastMessage: '{{ addslashes(session('success') ?? session('status') ?? session('info') ?? session('error') ?? '') }}',
          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3500);
          }
      }"
      x-init="if (toastMessage) { setTimeout(() => { toastMessage = ''; }, 3500); };
              window.addEventListener('show-toast', e => showToast(e.detail));
              window.addEventListener('cart-updated', e => { if (e.detail?.count !== undefined) cartCount = e.detail.count; });">

    <!-- Toast Notification -->
    <div x-cloak x-show="toastMessage"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-stone-700 max-w-md">
        <svg class="w-5 h-5 text-[#E5C38E] shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
        <button @click="toastMessage = ''" class="ml-auto text-stone-400 hover:text-white">&times;</button>
    </div>

    <!-- ================= NAVBAR ================= -->
    <header class="sticky top-0 z-40 bg-[#FAF7F2]/90 backdrop-blur-md border-b border-[#ECE4D8] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm group-hover:bg-stone-800 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold tracking-wider text-sm sm:text-base text-stone-900 uppercase font-sans">
                                HAMZAH STYLE
                            </span>
                            <span class="text-[10px] text-stone-500 uppercase tracking-widest font-medium -mt-1">
                                OFFICIAL
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-7">
                    <a href="{{ url('/') }}"
                       @class([
                           'text-sm font-semibold transition',
                           'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->is('/'),
                           'text-stone-600 hover:text-stone-900' => ! request()->is('/'),
                       ])>
                        Home
                    </a>
                    <a href="{{ route('koleksi.index') }}"
                       @class([
                           'text-sm font-semibold transition',
                           'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->routeIs('koleksi.*') || request()->routeIs('produk.detail'),
                           'text-stone-600 hover:text-stone-900' => ! (request()->routeIs('koleksi.*') || request()->routeIs('produk.detail')),
                       ])>
                        Produk
                    </a>
                    <a href="{{ route('pesanan.index') }}"
                       @class([
                           'text-sm font-semibold transition',
                           'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->routeIs('pesanan.*'),
                           'text-stone-600 hover:text-stone-900' => ! request()->routeIs('pesanan.*'),
                       ])>
                        Pesanan
                    </a>
                    <a href="{{ url('/#tentang-kami') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Tentang Kami
                    </a>
                    <a href="{{ url('/#kontak') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Kontak
                    </a>
                </nav>

                <!-- Action Buttons: Search, Cart, Akun -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Search Icon Button -->
                    <a href="{{ route('koleksi.index') }}"
                       class="p-2 text-stone-700 hover:text-stone-950 rounded-full hover:bg-stone-200/50 transition"
                       title="Cari Produk">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </a>

                    <!-- Cart Button with badge -->
                    <a href="{{ route('keranjang.index') }}"
                       class="relative p-2 text-stone-700 hover:text-stone-950 rounded-full hover:bg-stone-200/50 transition"
                       title="Keranjang Belanja">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-[#B58742] text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                              x-text="cartCount">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>

                    <!-- Akun Pill Button -->
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu"
                                @click.away="userMenu = false"
                                class="flex items-center gap-2 pl-1.5 pr-3.5 py-1.5 rounded-full border border-stone-300 hover:border-stone-400 bg-white/80 hover:bg-white transition shadow-2xs">
                            <div class="w-7 h-7 rounded-full bg-[#1F1916] text-[#E5C38E] flex items-center justify-center font-bold text-xs uppercase">
                                @auth
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                @else
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                @endauth
                            </div>
                            <span class="text-xs font-semibold text-stone-800 max-w-[100px] truncate">
                                @auth {{ Auth::user()->name }} @else Akun @endauth
                            </span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-cloak x-show="userMenu"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-stone-200 py-2 z-50 text-sm">
                            @auth
                                <div class="px-4 py-2 border-b border-stone-100">
                                    <p class="text-xs text-stone-500">Masuk sebagai</p>
                                    <p class="font-semibold text-stone-800 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-[11px] text-stone-400 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                        <span>Dashboard Admin</span>
                                    </a>
                                @endif

                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Profil Saya</span>
                                </a>

                                <a href="{{ route('alamat.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Daftar Alamat</span>
                                </a>

                                <a href="{{ route('pesanan.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Pesanan Saya</span>
                                </a>

                                <a href="{{ route('ulasan.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    <span>Ulasan Saya</span>
                                </a>

                                <div class="border-t border-stone-100 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium text-left">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-semibold">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    <span>Masuk ke Akun</span>
                                </a>
                                <a href="{{ route('register') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    <span>Daftar Akun Baru</span>
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Mobile Menu Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 text-stone-700 hover:text-stone-950 rounded-lg hover:bg-stone-200/50 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-cloak x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-[#ECE4D8] bg-[#FAF7F2] px-4 py-4 space-y-2">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Home</a>
            <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Produk</a>
            <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Pesanan Saya</a>
            <a href="{{ route('keranjang.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Keranjang Belanja</a>
            @auth
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Profil & Alamat</a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#B58742] hover:bg-stone-200/40">Masuk ke Akun</a>
            @endauth
        </div>
    </header>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- ================= FOOTER ================= -->
    <footer id="kontak" class="bg-[#FAF7F2] border-t border-[#ECE4D8] pt-16 pb-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 lg:gap-12 mb-12">
                <!-- Col 1: Brand Info -->
                <div class="md:col-span-1 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-sm text-stone-900 uppercase font-sans">
                            HAMZAH STYLE
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-stone-600 leading-relaxed">
                        Warisan batik nusantara autentik bergaya kontemporer. Memadukan tradisi canting dan cap dengan keanggunan busana modern.
                    </p>
                    <div class="pt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-200/60 text-stone-800 text-[11px] font-semibold">
                            <span>🇮🇩</span> 100% Karya Pengrajin Nusantara
                        </span>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div>
                    <h4 class="font-serif-title font-bold text-stone-900 text-sm mb-4">Navigasi</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-900 transition">Beranda</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-900 transition">Semua Koleksi</a></li>
                        <li><a href="{{ route('pesanan.index') }}" class="hover:text-stone-900 transition">Lacak Pesanan</a></li>
                        <li><a href="{{ route('keranjang.index') }}" class="hover:text-stone-900 transition">Keranjang Belanja</a></li>
                    </ul>
                </div>

                <!-- Col 3: Layanan & Informasi -->
                <div>
                    <h4 class="font-serif-title font-bold text-stone-900 text-sm mb-4">Layanan Pelanggan</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ route('alamat.index') }}" class="hover:text-stone-900 transition">Buku Alamat</a></li>
                        <li><a href="{{ route('ulasan.index') }}" class="hover:text-stone-900 transition">Ulasan Saya</a></li>
                        <li><span class="text-stone-500">Pengiriman: JNE, J&T, SiCepat, POS</span></li>
                        <li><span class="text-stone-500">Pembayaran: Midtrans (QRIS, VA, E-Wallet)</span></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Workshop -->
                <div class="space-y-3 text-xs sm:text-sm text-stone-600">
                    <h4 class="font-serif-title font-bold text-stone-900 text-sm mb-4">Workshop & Kontak</h4>
                    <p class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-stone-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Surakarta (Solo), Jawa Tengah, Indonesia</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>+62 812-3456-7890</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>halo@hamzahstyle.com</span>
                    </p>
                </div>
            </div>

            <!-- Bottom Copyright Line -->
            <div class="pt-8 border-t border-[#ECE4D8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>&copy; {{ date('Y') }} Hamzah Style Official. All rights reserved.</p>
                <p>Warisan Budaya Indonesia</p>
            </div>
        </div>
    </footer>

    {{ $extraBody ?? '' }}

</body>
</html>

