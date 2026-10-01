@php
    $namaProduk = $produk->nama ?? 'Kemeja Batik Parang Seling Premium';
    $kategoriProduk = $produk->kategori ?? 'Baju Batik';
    $hargaProduk = $produk->harga ?? 385000;
    $hargaFormatted = 'Rp ' . number_format($hargaProduk, 0, ',', '.');
    $materialProduk = $produk->material ?? 'Katun Primissima Halus (Furing Katun Nyaman)';
    $deskripsiProduk = $produk->deskripsi ?? 'Kemeja batik formal pria berdesain eksklusif dengan paduan motif nusantara yang berwibawa dan bernilai seni tinggi.';
    $gambarProduk = $produk ? $produk->gambar_url : asset('images/beranda/folded-shirts.jpg');
    $stokProduk = $produk->stok ?? 14;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $namaProduk }} - Hamzah Style Official</title>
    <meta name="description" content="{{ $namaProduk }} berbahan {{ $materialProduk }} dari Hamzah Style Official.">

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
          activeImage: '{{ $gambarProduk }}',
          selectedSize: 'M',
          quantity: 1,
          maxStock: {{ max(0, (int) $stokProduk) }},
          isOutOfStock: {{ $stokProduk > 0 ? 'false' : 'true' }},
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          descOpen: true,
          sizeGuideModal: false,
          imageZoomModal: false,
          toastMessage: '',

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },
          increaseQty() {
              if (this.isOutOfStock) return;
              if (this.quantity < this.maxStock) this.quantity++;
          },
          decreaseQty() {
              if (this.quantity > 1) this.quantity--;
          },
          addToCart(redirect = false) {
              if (this.isOutOfStock) {
                  this.showToast('Stok produk ini habis.');
                  return;
              }
              fetch('{{ route('keranjang.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      produk_id: {{ $produk->id }},
                      qty: this.quantity,
                      ukuran: this.selectedSize,
                      varian: 'Ukuran: ' + this.selectedSize
                  })
              })
              .then(res => res.json())
              .then(data => {
                  if (data.success) {
                      this.cartCount = data.cartCount;
                      if (redirect) {
                          window.location.href = '{{ route('checkout.index') }}';
                      } else {
                          this.showToast(data.message);
                      }
                  } else {
                      this.showToast(data.message || 'Gagal menambahkan ke keranjang');
                  }
              })
              .catch(err => {
                  this.showToast('Gagal menghubungi server');
              });
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
        <a href="{{ route('keranjang.index') }}" class="ml-2 text-xs font-bold text-[#E5C38E] hover:underline shrink-0">Lihat Keranjang →</a>
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
                    <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Home
                    </a>
                    <a href="{{ route('koleksi.index') }}" class="text-sm font-semibold text-stone-900 border-b-2 border-stone-900 pb-0.5 transition">
                        Produk
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Pesanan
                    </a>
                </nav>

                <!-- Action Buttons: Search, Cart, User -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('koleksi.index') }}" class="p-2 text-stone-600 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition" title="Cari Produk">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </a>

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
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-stone-700 hover:text-stone-900 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div x-cloak x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="md:hidden py-4 border-t border-[#ECE4D8] space-y-2">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-200/30">Home</a>
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-900 bg-stone-200/50">Produk</a>
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

    <!-- ================= MAIN PRODUCT DETAIL SECTION ================= -->
    <main class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb matching screenshot -->
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-stone-400 mb-8">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">BERANDA</a>
                <span>/</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-700 transition">PRODUK</a>
                <span>/</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-700 transition">{{ strtoupper($kategoriProduk) }}</a>
                <span>/</span>
                <span class="text-stone-800">{{ strtoupper($namaProduk) }}</span>
            </nav>

            <!-- Product Showcase Grid (2 Columns) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                <!-- Left Column: Gallery (Main Image + Thumbnails) -->
                <div class="lg:col-span-6 space-y-4">
                    <!-- Main Image with Zoom Button -->
                    <div class="relative aspect-[4/5] rounded-3xl overflow-hidden bg-stone-100 border border-[#EDE6DB] shadow-md group">
                        <img :src="activeImage"
                             alt="{{ $namaProduk }}"
                             class="w-full h-full object-cover transition-all duration-500 group-hover:scale-103 cursor-pointer"
                             @click="imageZoomModal = true">

                        <!-- Zoom Button at Bottom Right -->
                        <button @click="imageZoomModal = true"
                                class="absolute bottom-4 right-4 w-10 h-10 rounded-full bg-white/90 backdrop-blur-md text-stone-700 hover:text-stone-950 flex items-center justify-center shadow-md hover:bg-white transition"
                                title="Perbesar Gambar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- 4 Thumbnails Gallery -->
                    <div class="grid grid-cols-4 gap-3 sm:gap-4">
                        <!-- Thumb 1 -->
                        <button @click="activeImage = '{{ $gambarProduk }}'"
                                :class="activeImage === '{{ $gambarProduk }}' ? 'ring-2 ring-stone-900 border-transparent' : 'border-[#EDE6DB] opacity-70 hover:opacity-100'"
                                class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition shadow-2xs">
                            <img src="{{ $gambarProduk }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover">
                        </button>

                        <!-- Thumb 2 -->
                        <button @click="activeImage = '{{ asset('images/beranda/hero-couple.jpg') }}'"
                                :class="activeImage === '{{ asset('images/beranda/hero-couple.jpg') }}' ? 'ring-2 ring-stone-900 border-transparent' : 'border-[#EDE6DB] opacity-70 hover:opacity-100'"
                                class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition shadow-2xs">
                            <img src="{{ asset('images/beranda/hero-couple.jpg') }}" alt="Model Pakai" class="w-full h-full object-cover object-top">
                        </button>

                        <!-- Thumb 3 -->
                        <button @click="activeImage = '{{ asset('images/beranda/cloth-fabric.jpg') }}'"
                                :class="activeImage === '{{ asset('images/beranda/cloth-fabric.jpg') }}' ? 'ring-2 ring-stone-900 border-transparent' : 'border-[#EDE6DB] opacity-70 hover:opacity-100'"
                                class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition shadow-2xs">
                            <img src="{{ asset('images/beranda/cloth-fabric.jpg') }}" alt="Detail Motif" class="w-full h-full object-cover">
                        </button>

                        <!-- Thumb 4 -->
                        <button @click="activeImage = '{{ asset('images/beranda/gift-box.jpg') }}'"
                                :class="activeImage === '{{ asset('images/beranda/gift-box.jpg') }}' ? 'ring-2 ring-stone-900 border-transparent' : 'border-[#EDE6DB] opacity-70 hover:opacity-100'"
                                class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition shadow-2xs">
                            <img src="{{ asset('images/beranda/gift-box.jpg') }}" alt="Packaging Box" class="w-full h-full object-cover">
                        </button>
                    </div>
                </div>

                <!-- Right Column: Product Information & Purchase Controls -->
                <div class="lg:col-span-6 space-y-6">

                    <!-- Sub-category tag -->
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-[#B58742] block mb-1">
                            {{ strtoupper($kategoriProduk) }}
                        </span>
                        <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-stone-900 leading-tight">
                            {{ $namaProduk }}
                        </h1>
                    </div>

                    <!-- Rating & Sold Summary -->
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-stone-600 pb-2 border-b border-[#EDE6DB]">
                        <div class="flex items-center gap-1 font-bold text-stone-900">
                            <div class="flex text-amber-500 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <span class="ml-1 text-sm font-extrabold">5.0</span>
                        </div>
                        <span class="text-stone-300">|</span>
                        <a href="#ulasan-section" class="hover:underline text-stone-700 font-medium">{{ $ratingCount }} Ulasan Pelanggan</a>
                        <span class="text-stone-300">|</span>
                        <span class="text-emerald-700 font-semibold">{{ $stokProduk > 0 ? 'Tersedia ' . $stokProduk . ' pcs' : 'Stok Habis' }}</span>
                    </div>

                    <!-- Price Card Box -->
                    <div class="bg-[#FAF4EC] rounded-2xl p-5 border border-[#EFE8DE] flex items-center justify-between">
                        <div>
                            <span class="text-xs text-stone-500 font-medium block">Harga Produk</span>
                            <div class="flex items-baseline gap-3 mt-0.5">
                                <span class="text-3xl sm:text-4xl font-extrabold text-stone-900 tracking-tight">
                                    {{ $hargaFormatted }}
                                </span>
                            </div>
                        </div>

                        <!-- Stock Badge -->
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                {{ $stokProduk > 0 ? 'Stok Tersedia' : 'Habis' }}
                            </span>
                            <span class="block text-[11px] text-stone-500 mt-1 font-medium">Sisa {{ $stokProduk }} pcs</span>
                        </div>
                    </div>

                    <!-- Ethical / Artisan Quality Notice -->
                    <div class="bg-[#F5EDE1]/60 rounded-2xl p-4 border border-[#EADBCC] flex items-start gap-3.5 text-xs text-stone-700 leading-relaxed">
                        <div class="w-7 h-7 rounded-lg bg-[#EADCC8] text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <p>
                            <strong>100% Katun Primissima Halus</strong> ditenun dengan teknik canting tradisional modern. Dikerjakan dengan jahitan tailor presisi berlapisan furing katun adem, diproduksi bertanggung jawab bersama sentra batik di Pekalongan.
                        </p>
                    </div>

                    <!-- Size Selector ("Pilih Ukuran") -->
                    <div>
                        <div class="flex items-center justify-between text-xs sm:text-sm font-semibold mb-3">
                            <span class="text-stone-900">Pilih Ukuran</span>
                            <button @click="sizeGuideModal = true" class="text-amber-800 hover:text-amber-950 flex items-center gap-1 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Panduan Ukuran
                            </button>
                        </div>

                        <!-- Size Pills -->
                        <div class="flex items-center gap-2.5">
                            <template x-for="sz in ['S', 'M', 'L', 'XL', 'XXL']" :key="sz">
                                <button @click="selectedSize = sz"
                                        :class="selectedSize === sz ? 'bg-[#201A17] text-white shadow-sm' : 'bg-white text-stone-800 border border-stone-300 hover:border-stone-500'"
                                        class="w-12 h-11 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center transition">
                                    <span x-text="sz"></span>
                                </button>
                            </template>
                        </div>

                        <p class="text-[11px] text-stone-500 mt-2 font-medium">
                            Ukuran model: TB 180 cm, BB 75 kg (Menggunakan Size L)
                        </p>
                    </div>

                    <!-- Quantity Stepper & Add to Cart -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-3">
                            <!-- Stepper -->
                            <div class="inline-flex items-center rounded-xl bg-white border border-stone-300 p-1 shadow-2xs">
                                <button @click="decreaseQty()" :disabled="isOutOfStock"
                                        class="w-9 h-9 rounded-lg hover:bg-stone-100 disabled:opacity-40 flex items-center justify-center text-stone-600 font-bold transition">
                                    -
                                </button>
                                <span class="w-10 text-center text-sm font-bold text-stone-900" x-text="quantity"></span>
                                <button @click="increaseQty()" :disabled="isOutOfStock"
                                        class="w-9 h-9 rounded-lg hover:bg-stone-100 disabled:opacity-40 flex items-center justify-center text-stone-600 font-bold transition">
                                    +
                                </button>
                            </div>

                            <!-- Button Tambah ke Keranjang -->
                            <button @click="addToCart(false)" :disabled="isOutOfStock"
                                    class="flex-1 py-3 px-6 rounded-xl bg-[#201A17] hover:bg-stone-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs sm:text-sm transition duration-200 shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span>+ Tambahkan ke Keranjang</span>
                            </button>
                        </div>

                        <!-- Row 2 Buttons: Beli Sekarang & WhatsApp -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button @click.prevent="addToCart(true)" :disabled="isOutOfStock"
                               class="py-3 px-4 rounded-xl bg-white border border-stone-300 hover:border-stone-500 disabled:opacity-50 disabled:cursor-not-allowed text-stone-900 font-bold text-xs sm:text-sm transition shadow-2xs text-center flex items-center justify-center">
                                Beli Sekarang
                            </button>

                            <a :href="'https://wa.me/6281234567890?text=Halo%20Hamzah%20Style,%20saya%20tertarik%20dengan%20' + encodeURIComponent('{{ $namaProduk }}') + '%20Ukuran%20' + selectedSize + '%20Jumlah%20' + quantity + '%20pcs'"
                               target="_blank"
                               class="py-3 px-4 rounded-xl bg-[#5C3D28] hover:bg-[#482F1E] text-white font-semibold text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-sm">
                                <svg class="w-4 h-4 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Konsultasi via WhatsApp</span>
                            </a>
                        </div>
                    </div>

                    <!-- Accordion: Deskripsi Produk -->
                    <div class="border border-[#EDE6DB] rounded-2xl bg-white overflow-hidden shadow-2xs mt-6">
                        <button @click="descOpen = !descOpen"
                                class="w-full p-4 sm:p-5 flex items-center justify-between font-bold text-stone-900 text-sm sm:text-base hover:bg-stone-50 transition">
                            <span>Deskripsi Produk</span>
                            <svg class="w-4 h-4 text-stone-500 transition-transform duration-200"
                                 :class="descOpen ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="descOpen" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-stone-600 leading-relaxed space-y-3 border-t border-stone-100 pt-4">
                            <p>
                                {{ $deskripsiProduk }}
                            </p>
                            <ul class="space-y-2 list-disc list-inside text-stone-700">
                                <li><strong>Bahan / Material:</strong> {{ $materialProduk }}</li>
                                <li><strong>Stok:</strong> {{ $stokProduk }} unit siap kirim</li>
                                <li><strong>Kategori:</strong> {{ $kategoriProduk }}</li>
                                <li><strong>Jaminan:</strong> 100% Batik Nusantara Autentik</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ================= REVIEWS SECTION (ULASAN PELANGGAN) ================= -->
            <section id="ulasan-section" class="mt-20 pt-12 border-t border-[#EDE6DB]">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-[#B58742] block mb-1">
                            TESTIMONI NYATA
                        </span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl md:text-4xl font-bold text-stone-900">
                            Ulasan Pelanggan
                        </h2>
                    </div>
                </div>

                <!-- Rating Breakdown Card (data ulasan asli) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EDE6DB] shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">

                    <!-- Left Score -->
                    <div class="md:col-span-4 text-center md:border-r border-stone-200 md:pr-8">
                        <span class="text-5xl sm:text-6xl font-extrabold text-stone-900 block font-serif-title">
                            {{ $ratingCount > 0 ? number_format($ratingAvg, 1) : '–' }}
                        </span>
                        <div class="flex items-center justify-center gap-1 text-amber-500 text-lg my-2">
                            @for ($s = 1; $s <= 5; $s++)
                                <span class="{{ $ratingCount > 0 && $s <= round($ratingAvg) ? '' : 'text-stone-300' }}">★</span>
                            @endfor
                        </div>
                        <span class="font-bold text-stone-800 text-sm block">{{ $ratingCount > 0 ? ($ratingAvg >= 4.5 ? 'Puas Sempurna' : ($ratingAvg >= 3.5 ? 'Puas' : 'Cukup')) : 'Belum Ada Penilaian' }}</span>
                        <span class="text-xs text-stone-400 mt-0.5 block">Berdasarkan {{ $ratingCount }} penilaian terverifikasi</span>
                    </div>

                    <!-- Right Rating Bars -->
                    <div class="md:col-span-8 space-y-2 text-xs font-semibold text-stone-600">
                        @foreach ([5, 4, 3, 2, 1] as $star)
                            <div class="flex items-center gap-3">
                                <span class="w-16">{{ $star }} Bintang</span>
                                <div class="flex-1 h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full {{ $star >= 4 ? 'bg-stone-900' : ($star === 3 ? 'bg-stone-500' : 'bg-stone-300') }} rounded-full" style="width: {{ $ratingCount > 0 ? round($ratingBars[$star] / $ratingCount * 100) : 0 }}%;"></div>
                                </div>
                                <span class="w-8 text-right text-stone-400">{{ $ratingBars[$star] }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Review Cards Grid (data ulasan asli) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($ulasans as $u)
                        @php
                            $words = preg_split('/\s+/', trim($u->customer_name));
                            $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
                        @endphp
                        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EDE6DB] shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-stone-900 text-[#E5C38E] font-bold text-xs flex items-center justify-center">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-stone-900">{{ $u->customer_name }}</h4>
                                            <span class="text-[10px] text-emerald-700 font-semibold block flex items-center gap-1">
                                                <span>✓</span> Pembeli Terverifikasi
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-stone-400">{{ $u->created_at->format('d M Y') }}</span>
                                </div>

                                <div class="flex text-xs mb-2.5">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="{{ $s <= $u->rating ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                                    @endfor
                                </div>

                                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-4">
                                    "{{ $u->comment }}"
                                </p>
                                @if ($u->image_path)
                                    <img src="{{ asset('storage/'.$u->image_path) }}" alt="Foto dari ulasan {{ $u->customer_name }}" class="mb-4 max-h-56 w-full rounded-xl border border-stone-200 object-cover object-center">
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-3 bg-white rounded-2xl p-8 border border-dashed border-stone-300 text-center">
                            <p class="font-bold text-stone-900 text-sm">Belum ada ulasan untuk produk ini</p>
                            <p class="text-xs text-stone-500 mt-1">Ulasan hanya dapat diberikan setelah pesanan berstatus Selesai, melalui halaman Pesanan Saya.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Load More Reviews Button -->
                @if($ratingCount > $ulasans->count())
                    <div class="text-center mt-8">
                        <span class="inline-block px-6 py-2.5 rounded-full bg-white border border-[#EDE6DB] text-stone-800 text-xs sm:text-sm font-semibold shadow-2xs">
                            + {{ $ratingCount - $ulasans->count() }} Ulasan Lainnya
                        </span>
                    </div>
                @endif
            </section>

            <!-- ================= SECTION: PRODUK TERKAIT ================= -->
            <section class="mt-20 pt-12 border-t border-[#EDE6DB]">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-stone-900">
                        Produk Terkait
                    </h2>
                    <a href="{{ route('koleksi.index') }}" class="text-xs sm:text-sm font-semibold text-stone-800 hover:text-amber-800 transition flex items-center gap-1">
                        Lihat Semua Produk
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                <!-- Related Product Cards from Database -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($produksTerkait as $rel)
                        <div class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
                            <div class="relative aspect-square overflow-hidden bg-stone-100">
                                <span class="absolute top-3 left-3 z-10 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs bg-[#201A17] text-white">
                                    {{ $rel->kategori }}
                                </span>
                                <img src="{{ $rel->gambar_url }}"
                                     alt="{{ $rel->nama }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-stone-400 block mb-1">{{ strtoupper($rel->kategori) }}</span>
                                    <h3 class="font-bold text-stone-900 text-sm leading-snug line-clamp-1 mb-2 group-hover:text-amber-800 transition">
                                        {{ $rel->nama }}
                                    </h3>
                                    <p class="text-base font-extrabold text-stone-900 mb-4">Rp {{ number_format($rel->harga, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('produk.detail', ['id' => $rel->id]) }}" class="flex-1 py-2 rounded-full bg-[#201A17] hover:bg-stone-800 text-white text-xs font-semibold transition text-center shadow-xs">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-4 text-center py-8 text-stone-500 text-sm">
                            Belum ada produk terkait.
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer id="kontak" class="bg-[#FAF7F2] border-t border-[#ECE4D8] pt-16 pb-12 text-stone-700 mt-20">
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
                        Batik Modern & Olahan Kain Sisa Berkelanjutan. Menjaga warisan tekstil Nusantara melalui inovasi kriya estetika dan potongan siluet ramah lingkungan.
                    </p>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-amber-900 text-[11px] font-bold">
                        <span>🌱</span>
                        <span>WARISAN BERKELANJUTAN</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi -->
                <div class="lg:col-span-2">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Navigasi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-950 transition">Home</a></li>
                        <li><a href="{{ url('/') }}#koleksi" class="hover:text-stone-950 transition">Tentang Kami</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 font-semibold text-stone-900 transition">Produk</a></li>
                        <li><a href="{{ route('pesanan.index') }}" class="hover:text-stone-950 transition">Pesanan</a></li>
                        <li><a href="{{ route('keranjang.index') }}" class="hover:text-stone-950 transition">Keranjang Belanja</a></li>
                        <li><a href="{{ url('/') }}#kontak" class="hover:text-stone-950 transition">Kontak</a></li>
                    </ul>
                </div>

                <!-- Col 3: Saluran Penjualan Resmi -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Saluran Penjualan Resmi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li>
                            <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                WhatsApp Business
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                Shopee Official
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-stone-900"></span>
                                TikTok Shop
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Media Sosial & Buletin -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Media Sosial & Buletin
                    </h5>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4">
                        Dapatkan kabar terbaru untuk rilis produk baru dan penawaran terbatas:
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
                <p>© 2026 Hamzah Style Official. All rights reserved.</p>
                <p class="text-stone-400">Made with pride in Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- ================= SIZE GUIDE MODAL ================= -->
    <div x-cloak x-show="sizeGuideModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div x-show="sizeGuideModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" @click="sizeGuideModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="sizeGuideModal" x-transition class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-200">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                    <h3 class="font-serif-title font-bold text-xl text-stone-900">Panduan Ukuran Kemeja Pria</h3>
                    <button @click="sizeGuideModal = false" class="text-stone-400 hover:text-stone-700">✕</button>
                </div>
                <div class="overflow-x-auto text-xs sm:text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-700 font-bold">
                                <th class="p-3">Size</th>
                                <th class="p-3">Lingkar Dada</th>
                                <th class="p-3">Panjang Baju</th>
                                <th class="p-3">Panjang Lengan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-600">
                            <tr><td class="p-3 font-bold text-stone-900">S</td><td class="p-3">100 cm</td><td class="p-3">71 cm</td><td class="p-3">60 cm</td></tr>
                            <tr class="bg-stone-50/50"><td class="p-3 font-bold text-stone-900">M</td><td class="p-3">104 cm</td><td class="p-3">73 cm</td><td class="p-3">61 cm</td></tr>
                            <tr><td class="p-3 font-bold text-stone-900">L</td><td class="p-3">108 cm</td><td class="p-3">75 cm</td><td class="p-3">62 cm</td></tr>
                            <tr class="bg-stone-50/50"><td class="p-3 font-bold text-stone-900">XL</td><td class="p-3">114 cm</td><td class="p-3">77 cm</td><td class="p-3">63 cm</td></tr>
                            <tr><td class="p-3 font-bold text-stone-900">XXL</td><td class="p-3">120 cm</td><td class="p-3">79 cm</td><td class="p-3">64 cm</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-stone-500 mt-4 leading-relaxed">
                    *Toleransi jahitan ± 1–2 cm. Ukuran di atas diukur dalam keadaan baju dibentangkan mendatar.
                </p>
            </div>
        </div>
    </div>

    <!-- ================= IMAGE ZOOM MODAL ================= -->
    <div x-cloak x-show="imageZoomModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-stone-950/85 backdrop-blur-md" @click="imageZoomModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative max-w-3xl w-full">
                <button @click="imageZoomModal = false" class="absolute -top-12 right-0 text-white hover:text-stone-300 text-2xl font-bold">✕ Tutup</button>
                <img :src="activeImage" alt="Preview Gambar" class="w-full h-auto rounded-3xl shadow-2xl border border-stone-800">
            </div>
        </div>
    </div>

</body>
</html>
