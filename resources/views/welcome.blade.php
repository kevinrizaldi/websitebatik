<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hamzah Style Official - Warisan Batik Nusantara Modern</title>
    <meta name="description" content="Koleksi busana batik modern dan autentik Indonesia karya pengrajin lokal pilihan. Elegan dalam setiap motif untuk momen berharga Anda.">

    <!-- Google Fonts -->
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
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          activeCategory: 'Semua',
          searchOpen: false,
          searchQuery: '',
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          quickViewModal: false,
          selectedProduct: null,
          toastMessage: '',
          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },
          openQuickView(prod) {
              this.selectedProduct = prod;
              this.quickViewModal = true;
          }
      }">

    <!-- Toast Notification -->
    <div x-cloak x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-stone-700">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
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
                            <span class="text-[10px] text-stone-500 tracking-widest uppercase -mt-1 font-medium">
                                OFFICIAL
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-stone-900 border-b-2 border-stone-900 pb-0.5 hover:text-stone-950 transition">
                        Home
                    </a>
                    <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Produk
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Pesanan
                    </a>
                </nav>

                <!-- Action Buttons: Search, Cart, User -->
                <div class="flex items-center gap-4">
                    <!-- Search Button -->
                    <button @click="searchOpen = !searchOpen" 
                            class="p-2 text-stone-600 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition" 
                            title="Cari Produk">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </button>

                    <!-- Cart Button with badge -->
                    <a href="{{ route('keranjang.index') }}" 
                       class="relative p-2 text-stone-600 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition"
                       title="Keranjang Belanja">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-[#B58742] text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                              x-text="cartCount">
                            {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}
                        </span>
                    </a>

                    <!-- User Account / Auth Dropdown -->
                    @if (Route::has('login'))
                        <div class="relative" x-data="{ userMenu: false }">
                            @auth
                                <button @click="userMenu = !userMenu" 
                                        @click.away="userMenu = false"
                                        class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-stone-300 hover:border-stone-400 bg-white/70 transition">
                                    <div class="w-7 h-7 rounded-full bg-[#201A17] text-[#E5C38E] flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-semibold text-stone-800 max-w-[90px] truncate hidden sm:inline">
                                        {{ Auth::user()->name }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-cloak x-show="userMenu" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     class="absolute right-0 mt-2 w-52 rounded-xl bg-white shadow-xl border border-stone-200 py-1.5 z-50 text-sm">
                                    <div class="px-4 py-2 border-b border-stone-100">
                                        <p class="text-xs text-stone-500">Masuk sebagai</p>
                                        <p class="font-semibold text-stone-800 truncate">{{ Auth::user()->name }}</p>
                                    </div>

                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('produk.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            Kelola Produk (Admin)
                                        </a>
                                        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            Kelola Pesanan
                                        </a>
                                    @endif

                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900">
                                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Profile
                                    </a>

                                    <div class="border-t border-stone-100 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('login') }}" 
                                       class="flex items-center gap-1.5 px-4 py-2 rounded-full border border-stone-300 hover:border-stone-500 text-xs font-semibold text-stone-800 bg-white/60 hover:bg-white transition">
                                        <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        User
                                    </a>
                                </div>
                            @endauth
                        </div>
                    @endif

                    <!-- Mobile Menu Hamburger Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden p-2 text-stone-700 hover:text-stone-900 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expandable Search Bar -->
            <div x-cloak x-show="searchOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pb-4 pt-1">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari motif batik, kemeja pria, gamis, aksesoris..." 
                           class="w-full bg-white border border-stone-300 rounded-full pl-11 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-[#B58742]">
                    <div class="absolute left-4 top-3 text-stone-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <button @click="searchOpen = false" class="absolute right-4 top-3 text-stone-400 hover:text-stone-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div x-cloak x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="md:hidden py-4 border-t border-[#ECE4D8] space-y-2">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-900 bg-stone-200/50">Home</a>
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-200/30">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-200/30">Pesanan</a>
                @guest
                    <div class="pt-2 border-t border-[#ECE4D8] flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 text-center py-2 px-4 rounded-lg bg-[#201A17] text-white font-medium text-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="flex-1 text-center py-2 px-4 rounded-lg border border-stone-300 text-stone-800 font-medium text-sm">Daftar</a>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="relative overflow-hidden pt-8 pb-16 md:pt-14 md:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Headline & Subtitle -->
                <div class="lg:col-span-6 lg:pr-6">
                    <!-- Pill Tag -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-[#785933] text-xs font-semibold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-[#B58742]"></span>
                        <span>KOLEKSI TERBARU • BATIK MODERN</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="font-serif-title text-4xl sm:text-5xl lg:text-[62px] font-bold text-[#1F1916] leading-[1.12] tracking-tight mb-6">
                        Elegan dalam<br>
                        Setiap Motif
                    </h1>

                    <!-- Paragraph -->
                    <p class="text-stone-600 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
                        Sentuhan estetika tradisional yang berpadu dengan kenyamanan modern. Dibuat dengan dedikasi tinggi oleh pengrajin berpengalaman untuk menemani setiap langkah percaya diri Anda.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#produk-unggulan" 
                           class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-[#201A17] text-white font-medium hover:bg-stone-800 transition duration-200 shadow-md hover:shadow-lg text-sm sm:text-base">
                            Belanja Sekarang
                        </a>
                        <a href="#koleksi" 
                           class="inline-flex items-center justify-center px-8 py-3.5 rounded-full bg-white text-stone-800 border border-stone-300 font-medium hover:bg-stone-50 hover:border-stone-400 transition duration-200 text-sm sm:text-base shadow-xs">
                            Lihat Koleksi
                        </a>
                    </div>
                </div>

                <!-- Right Column: Featured Image with Overlay Card -->
                <div class="lg:col-span-6">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Main Couple Image -->
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-stone-200 aspect-[3/4] max-h-[580px] w-full">
                            <img src="{{ asset('images/beranda/hero-couple.jpg') }}" 
                                 alt="Koleksi Batik Pasangan Hamzah Style" 
                                 class="w-full h-full object-cover object-top hover:scale-102 transition duration-700">

                            <!-- Floating Card at Bottom of Image -->
                            <div class="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl shadow-xl border border-stone-100 flex items-center justify-between">
                                <div class="pr-2">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-amber-800">
                                        KOLEKSI COUPLE HARMONI
                                    </span>
                                    <h4 class="font-bold text-stone-900 text-sm sm:text-base mt-0.5 leading-snug">
                                        Perpaduan Keanggunan & Wibawa
                                    </h4>
                                </div>
                                <a href="#produk-unggulan" 
                                   class="w-10 h-10 rounded-full bg-[#201A17] text-white flex items-center justify-center shrink-0 hover:bg-[#B58742] transition shadow-md"
                                   title="Lihat Koleksi Ini">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= SECTION: JELAJAHI KOLEKSI ================= -->
    <section id="koleksi" class="py-16 md:py-24 bg-white/60 border-t border-b border-[#EFE8DE]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                <div>
                    <h2 class="font-serif-title text-3xl sm:text-4xl font-bold text-[#1F1916]">
                        Jelajahi Koleksi
                    </h2>
                </div>
                <div class="md:max-w-md">
                    <p class="text-stone-600 text-sm sm:text-base leading-relaxed">
                        Pilihan busana batik terbaik dengan perpaduan filosofi tradisional dan siluet modern kontemporer untuk segala suasana.
                    </p>
                </div>
            </div>

            <!-- 3 Collection Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Card 1: Baju Modern -->
                <div class="group bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                        <span class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-sm text-stone-800 text-xs font-semibold px-3 py-1 rounded-full shadow-xs">
                            Pria & Wanita
                        </span>
                        <img src="{{ asset('images/beranda/hero-couple.jpg') }}" 
                             alt="Baju Batik Modern" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-xl text-stone-900 mb-2 group-hover:text-amber-800 transition">
                                Baju Modern
                            </h3>
                            <p class="text-stone-600 text-sm leading-relaxed mb-4">
                                Koleksi kemeja pria dan dress wanita berdesain kontemporer untuk gaya profesional, formal, maupun santai.
                            </p>
                        </div>
                        <a href="#produk-unggulan" 
                           @click="activeCategory = 'Baju Pria'"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-900 group-hover:text-amber-800 group-hover:translate-x-1 transition-all">
                            Lihat Koleksi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Batik Tulis -->
                <div class="group bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                        <span class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-sm text-stone-800 text-xs font-semibold px-3 py-1 rounded-full shadow-xs">
                            Batik Tradisional
                        </span>
                        <img src="{{ asset('images/beranda/folded-shirts.jpg') }}" 
                             alt="Batik Tulis Tradisional" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-xl text-stone-900 mb-2 group-hover:text-amber-800 transition">
                                Batik Tulis
                            </h3>
                            <p class="text-stone-600 text-sm leading-relaxed mb-4">
                                Mahakarya seni canting asli pengrajin lokal dengan motif parang, kawung, dan filosofi luhur nusantara.
                            </p>
                        </div>
                        <a href="#produk-unggulan" 
                           @click="activeCategory = 'Semua'"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-900 group-hover:text-amber-800 group-hover:translate-x-1 transition-all">
                            Lihat Koleksi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Tas & Pouch Batik -->
                <div class="group bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                        <span class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-sm text-stone-800 text-xs font-semibold px-3 py-1 rounded-full shadow-xs">
                            Aksesoris & Tas
                        </span>
                        <img src="{{ asset('images/beranda/bags-accessories.jpg') }}" 
                             alt="Tas & Pouch Batik" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-xl text-stone-900 mb-2 group-hover:text-amber-800 transition">
                                Tas & Pouch Batik
                            </h3>
                            <p class="text-stone-600 text-sm leading-relaxed mb-4">
                                Sentuhan estetika etnik dalam tas jinjing dan clutch modern untuk melengkapi gaya fashion harian Anda.
                            </p>
                        </div>
                        <a href="#produk-unggulan" 
                           @click="activeCategory = 'Aksesoris'"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-900 group-hover:text-amber-800 group-hover:translate-x-1 transition-all">
                            Lihat Koleksi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ================= SECTION: PRODUK UNGGULAN ================= -->
    <section id="produk-unggulan" class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header and Filter Tabs -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-[#B58742] block mb-1">
                        PRODUK PILIHAN
                    </span>
                    <h2 class="font-serif-title text-3xl sm:text-4xl font-bold text-[#1F1916]">
                        Produk Unggulan
                    </h2>
                    <p class="text-stone-600 text-sm sm:text-base mt-2 max-w-xl">
                        Pilihan favorit pelanggan dengan jaminan keaslian bahan, jahitan rapi, dan sentuhan motif berkarakter.
                    </p>
                </div>

                <!-- Category Filter Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="activeCategory = 'Semua'" 
                            :class="activeCategory === 'Semua' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                        Semua
                    </button>
                    <button @click="activeCategory = 'Baju Batik'" 
                            :class="activeCategory === 'Baju Batik' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                        Baju Batik
                    </button>
                    <button @click="activeCategory = 'Kain Batik'" 
                            :class="activeCategory === 'Kain Batik' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                        Kain Batik
                    </button>
                    <button @click="activeCategory = 'Olahan Kain Sisa'" 
                            :class="activeCategory === 'Olahan Kain Sisa' ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition">
                        Olahan Kain Sisa
                    </button>
                </div>
            </div>

            <!-- Products Grid (Dynamic from Database) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($produks->take(8) as $p)
                    <div x-show="activeCategory === 'Semua' || activeCategory === '{{ $p->kategori }}'"
                         x-transition
                         class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
                        <a href="{{ route('produk.detail', ['id' => $p->id]) }}" class="relative aspect-square overflow-hidden bg-stone-100 block">
                            <span class="absolute top-3 left-3 z-10 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs {{ $p->status === 'Stok Menipis' ? 'bg-[#5C3D28] text-white' : 'bg-white/95 text-stone-800 border-stone-200' }}">
                                {{ $p->status === 'Stok Menipis' ? 'Stok Terbatas' : ($p->status === 'Habis' ? 'Habis' : 'Ready Stock') }}
                            </span>
                            <span class="absolute bottom-3 left-3 z-10 bg-[#201A17]/80 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ $p->kategori }}
                            </span>
                            <img src="{{ $p->gambar_url }}" 
                                 alt="{{ $p->nama }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Star Rating -->
                                <div class="flex items-center gap-1 mb-2">
                                    <span class="text-amber-500 text-xs">★</span>
                                    <span class="text-xs font-bold text-stone-800">5.0</span>
                                    <span class="text-[11px] text-stone-400">({{ 15 + (($p->id * 4) % 25) }})</span>
                                </div>
                                <!-- Title -->
                                <h4 class="font-bold text-stone-900 text-sm leading-snug line-clamp-2 mb-2 group-hover:text-amber-800 transition">
                                    <a href="{{ route('produk.detail', ['id' => $p->id]) }}">
                                        {{ $p->nama }}
                                    </a>
                                </h4>
                                <!-- Price -->
                                <p class="text-base font-extrabold text-stone-900 mb-4">
                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                </p>
                            </div>
                            <a href="{{ route('produk.detail', ['id' => $p->id]) }}" 
                               class="w-full py-2.5 px-4 rounded-full bg-[#201A17] hover:bg-stone-800 text-white text-xs font-semibold transition duration-200 shadow-xs flex items-center justify-center gap-1.5 text-center">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- View All Link -->
            <div class="text-center mt-12">
                <a href="{{ route('koleksi.index') }}" 
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#201A17] hover:bg-stone-800 text-[#E5C38E] text-sm font-semibold transition shadow-md group">
                    <span>Lihat Semua Produk ({{ $produks->count() }})</span>
                    <svg class="w-4 h-4 text-[#E5C38E] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

        </div>
    </section>



    <!-- ================= FOOTER ================= -->
    <footer id="kontak" class="bg-[#FAF7F2] border-t border-[#ECE4D8] pt-16 pb-12 text-stone-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-12">
                
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-stone-900 uppercase text-sm">
                            HAMZAH STYLE
                        </span>
                    </div>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4 max-w-sm">
                        Pusat busana batik berkualitas dengan ragam motif nusantara terlengkap. Menghadirkan karya seni batik elegan untuk gaya hidup masa kini.
                    </p>
                    <p class="text-xs text-stone-500">
                        📍 Yogyakarta & Solo, Indonesia
                    </p>
                </div>

                <!-- Col 2: Navigasi -->
                <div class="lg:col-span-2">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Navigasi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-950 transition">Beranda</a></li>
                        <li><a href="#koleksi" class="hover:text-stone-950 transition">Koleksi Pilihan</a></li>
                        <li><a href="#produk-unggulan" class="hover:text-stone-950 transition">Produk Unggulan</a></li>
                        <li><a href="#kontak" class="hover:text-stone-950 transition">Kontak Resmi</a></li>
                    </ul>
                </div>

                <!-- Col 3: Saluran Penjualan Resmi -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Saluran Penjualan Resmi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                Shopee Official Store
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Tokopedia Official
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-stone-900"></span>
                                TikTok Shop
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                WhatsApp Order Direct
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Media Sosial & Desain -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Media Sosial & Desain
                    </h5>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4">
                        Ikuti update koleksi terbaru, tutorial berpakaian batik, dan inspirasi motif nusantara di media sosial kami:
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 00-1-.08A6.34 6.34 0 003 15.66a6.34 6.34 0 0010.86 4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-.04-4.52z"/></svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright Line -->
            <div class="pt-8 border-t border-[#ECE4D8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>© 2026 Hamzah Style Official. Seluruh Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-stone-800 transition">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-stone-800 transition">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ================= QUICK VIEW MODAL ================= -->
    <div x-cloak x-show="quickViewModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="quickViewModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="quickViewModal = false"
             class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="quickViewModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-stone-200">
                
                <!-- Close Button -->
                <button @click="quickViewModal = false" 
                        class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/80 hover:bg-stone-100 text-stone-600 flex items-center justify-center transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <template x-if="selectedProduct">
                    <div class="grid grid-cols-1 sm:grid-cols-2">
                        <!-- Product Image -->
                        <div class="relative aspect-square sm:aspect-auto bg-stone-100 overflow-hidden">
                            <img :src="selectedProduct.gambar" :alt="selectedProduct.nama" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 bg-[#201A17] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full" x-text="selectedProduct.kategori"></span>
                        </div>

                        <!-- Product Info -->
                        <div class="p-6 sm:p-8 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 text-xs text-amber-600 mb-2">
                                    <span>★</span>
                                    <span class="font-semibold text-stone-800" x-text="selectedProduct.rating"></span>
                                </div>
                                <h3 class="font-serif-title font-bold text-xl sm:text-2xl text-stone-900 mb-2" x-text="selectedProduct.nama"></h3>
                                <p class="text-2xl font-extrabold text-[#B58742] mb-4" x-text="selectedProduct.harga"></p>
                                
                                <div class="border-t border-b border-stone-100 py-3 mb-4 space-y-1.5 text-xs text-stone-600">
                                    <p><strong class="text-stone-900">Material:</strong> <span x-text="selectedProduct.material"></span></p>
                                    <p><strong class="text-stone-900">Garansi:</strong> 100% Batik Asli Berkualitas</p>
                                </div>

                                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-6" x-text="selectedProduct.deskripsi"></p>
                            </div>

                            <div class="space-y-2">
                                <a :href="'https://wa.me/6281234567890?text=Halo%20Hamzah%20Style,%20saya%20tertarik%20dengan%20produk%20' + encodeURIComponent(selectedProduct.nama) + '%20(' + encodeURIComponent(selectedProduct.harga) + ')'"
                                   target="_blank"
                                   class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    Pesan via WhatsApp
                                </a>
                                <button @click="quickViewModal = false; showToast('Produk ditambahkan ke keranjang belanja'); cartCount++"
                                        class="w-full py-2.5 px-4 rounded-xl border border-stone-300 hover:bg-stone-50 text-stone-800 font-semibold text-sm transition">
                                    + Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

</body>
</html>
