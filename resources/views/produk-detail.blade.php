@php
    $namaProduk = $produk->nama ?? 'Kemeja Batik Parang Seling Premium';
    $kategoriProduk = $produk->kategori ?? 'Baju Batik';
    $hargaProduk = $produk->harga ?? 385000;
    $hargaFormatted = 'Rp ' . number_format($hargaProduk, 0, ',', '.');
    $materialProduk = $produk->material ?? 'Katun Primissima Halus (Furing Katun Nyaman)';
    $deskripsiProduk = $produk->deskripsi ?? 'Kemeja batik formal pria berdesain eksklusif dengan paduan motif nusantara yang berwibawa dan bernilai seni tinggi. Dikerjakan dengan ketelitian tinggi oleh pengrajin lokal berpengalaman.';
    $gambarProduk = $produk ? $produk->gambar_url : asset('images/beranda/folded-shirts.jpg');
    $gambarLainnya = ($produk && $produk->gambar_lainnya) ? $produk->gambar_lainnya : [];
    $stokProduk = $produk->stok ?? 14;
    $stokUkuranArray = $produk ? $produk->getStokUkuranArray() : ['S' => 2, 'M' => 4, 'L' => 4, 'XL' => 3, 'XXL' => 1];
    $firstAvailableSize = collect($stokUkuranArray)->filter(fn($qty) => $qty > 0)->keys()->first() ?? (array_key_first($stokUkuranArray) ?? 'M');
    $produkId = $produk->id ?? 1;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $namaProduk }} — Hamzah Style Official</title>
    <meta name="description" content="{{ $namaProduk }} berbahan {{ $materialProduk }} dari Hamzah Style Official. Warisan batik nusantara modern berkualitas premium.">

    <!-- Google Fonts: Playfair Display & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF7F2;
            color: #241E1A;
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
        /* Custom delicate scrollbar for thumbnails */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#241E1A] min-h-screen selection:bg-[#B58742] selection:text-white relative pb-20 sm:pb-0"
      x-data="{
          mobileMenuOpen: false,
          activeImage: '{{ $gambarProduk }}',
          stokUkuran: {{ json_encode($stokUkuranArray) }},
          selectedSize: '{{ $firstAvailableSize }}',
          quantity: 1,
          activeTab: 'deskripsi',
          get currentStock() {
              if (!this.selectedSize || this.stokUkuran[this.selectedSize] === undefined) {
                  return {{ max(0, (int) $stokProduk) }};
              }
              return Math.max(0, parseInt(this.stokUkuran[this.selectedSize]) || 0);
          },
          get isOutOfStock() {
              return this.currentStock <= 0;
          },
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          sizeGuideModal: false,
          imageZoomModal: false,
          toastMessage: '',
          isAdding: false,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3200);
          },
          selectSize(sz) {
              const stock = parseInt(this.stokUkuran[sz] || 0);
              if (stock <= 0) return;
              this.selectedSize = sz;
              if (this.quantity > stock) {
                  this.quantity = Math.max(1, stock);
              }
          },
          increaseQty() {
              if (this.isOutOfStock) return;
              if (this.quantity < this.currentStock) this.quantity++;
          },
          decreaseQty() {
              if (this.quantity > 1) this.quantity--;
          },
          addToCart(redirect = false) {
              if (this.isOutOfStock) {
                  this.showToast('Mohon maaf, ukuran ini sedang habis.');
                  return;
              }
              this.isAdding = true;
              fetch('{{ route('keranjang.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      produk_id: {{ $produkId }},
                      qty: this.quantity,
                      ukuran: this.selectedSize,
                      varian: 'Ukuran: ' + this.selectedSize
                  })
              })
              .then(res => res.json())
              .then(data => {
                  this.isAdding = false;
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
                  this.isAdding = false;
                  this.showToast('Gagal menghubungi server');
              });
          }
      }">

    <!-- Floating Toast Notification -->
    <div x-cloak x-show="toastMessage"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-6 right-6 z-50 bg-[#1D1714] text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-[#3A3029]/80 backdrop-blur-md">
        <div class="w-6 h-6 rounded-full bg-[#B58742]/20 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-[#D8AB64]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </div>
        <span x-text="toastMessage" class="text-xs sm:text-sm font-medium"></span>
        <a href="{{ route('keranjang.index') }}" class="ml-2 text-xs font-bold text-[#E5C38E] hover:text-white transition underline underline-offset-2 shrink-0">
            Buka Keranjang →
        </a>
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

                <!-- Action Buttons: Cart, User -->
                <div class="flex items-center gap-4">
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

            <!-- Refined Breadcrumb -->
            <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-8" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">Beranda</a>
                <span class="text-stone-300">/</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-700 transition">Koleksi</a>
                <span class="text-stone-300">/</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-700 transition">{{ $kategoriProduk }}</a>
                <span class="text-stone-300">/</span>
                <span class="text-stone-900 font-bold truncate max-w-xs sm:max-w-md">{{ $namaProduk }}</span>
            </nav>

            <!-- Product Showcase Grid (2 Columns: Editorial Luxury) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                <!-- Left Column: Gallery (Main Visual + Tactile Thumbnails) -->
                <div class="lg:col-span-6 space-y-4 lg:sticky lg:top-28">
                    <!-- Main Image Frame with Ambient Shadow -->
                    <div class="relative aspect-[4/5] rounded-3xl overflow-hidden bg-stone-100 border border-[#EAE1D3] shadow-[0_12px_40px_-15px_rgba(32,26,23,0.08)] group">
                        <img :src="activeImage"
                             alt="{{ $namaProduk }}"
                             class="w-full h-full object-cover transition-all duration-700 ease-out group-hover:scale-105 cursor-pointer"
                             @click="imageZoomModal = true">

                        <!-- Badge Category in Corner -->
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1F1916]/80 backdrop-blur-md text-white text-[10px] font-semibold tracking-wider uppercase shadow-sm border border-white/10">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E5C38E]"></span>
                                {{ $kategoriProduk }}
                            </span>
                        </div>

                        <!-- Zoom Indicator Pill (Bottom Right) -->
                        <button @click="imageZoomModal = true"
                                class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-full bg-white/85 backdrop-blur-md text-stone-800 hover:text-stone-950 flex items-center gap-1.5 text-xs font-semibold shadow-md hover:bg-white transition-all transform hover:scale-102 border border-white/60"
                                title="Klik untuk memperbesar gambar">
                            <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
                            </svg>
                            <span>Perbesar</span>
                        </button>
                    </div>

                    <!-- Thumbnails Gallery (Haptic Selector) -->
                    @php $extraGambars = array_slice($gambarLainnya, 0, 9); @endphp
                    @if(count($extraGambars) > 0)
                        <div class="grid grid-cols-5 gap-2.5 sm:gap-3 pt-1">
                            {{-- Thumbnail Gambar Utama --}}
                            <button @click="activeImage = '{{ $gambarProduk }}'"
                                    :class="activeImage === '{{ $gambarProduk }}' ? 'ring-2 ring-[#B58742] ring-offset-2 ring-offset-[#FAF7F2] opacity-100' : 'opacity-65 hover:opacity-100 border-[#EAE1D3]'"
                                    class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition-all duration-200 shadow-2xs">
                                <img src="{{ $gambarProduk }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover">
                            </button>

                            {{-- Thumbnail Gambar Lainnya --}}
                            @foreach($extraGambars as $extraImg)
                                <button @click="activeImage = '{{ asset('storage/' . $extraImg) }}'"
                                        :class="activeImage === '{{ asset('storage/' . $extraImg) }}' ? 'ring-2 ring-[#B58742] ring-offset-2 ring-offset-[#FAF7F2] opacity-100' : 'opacity-65 hover:opacity-100 border-[#EAE1D3]'"
                                        class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border transition-all duration-200 shadow-2xs">
                                    <img src="{{ asset('storage/' . $extraImg) }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right Column: Product Detail & Commercial Controls -->
                <div class="lg:col-span-6 space-y-6">

                    <!-- Title & Header Eyebrow -->
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-[#B58742] mb-1.5">
                            <span>✦</span>
                            <span>WARISAN BATIK NUSANTARA</span>
                        </div>
                        <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-[40px] font-bold text-[#1F1916] leading-[1.2] tracking-tight">
                            {{ $namaProduk }}
                        </h1>
                    </div>

                    <!-- Rating & Reviews Bar -->
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-stone-600 pb-3 border-b border-[#EAE1D3]">
                        <div class="flex items-center gap-1.5 font-bold text-stone-900">
                            <div class="flex text-amber-500 text-sm">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <span class="ml-0.5 text-sm font-extrabold">{{ $ratingCount > 0 ? number_format($ratingAvg, 1) : '5.0' }}</span>
                        </div>
                        <span class="text-stone-300">|</span>
                        <a href="#ulasan-section" class="hover:text-stone-950 font-medium text-stone-700 underline underline-offset-4 decoration-stone-300 transition">
                            {{ $ratingCount }} Ulasan Pembeli
                        </a>
                    </div>

                    <!-- Editorial Price Box -->
                    <div class="bg-[#F6EFE5] rounded-3xl p-5 sm:p-6 border border-[#E9DECf] flex items-center justify-between shadow-2xs">
                        <div>
                            <span class="text-[11px] text-[#8C7662] uppercase tracking-wider font-bold block">Harga Resmi</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-3xl sm:text-4xl font-extrabold text-[#1F1916] tracking-tight">
                                    {{ $hargaFormatted }}
                                </span>
                            </div>
                        </div>

                        <!-- Dynamic Stock Badge -->
                        <div class="text-right">
                            <span :class="currentStock > 0 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200'"
                                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition">
                                <span :class="currentStock > 0 ? 'bg-emerald-500' : 'bg-rose-500'" class="w-1.5 h-1.5 rounded-full"></span>
                                <span x-text="currentStock > 0 ? 'Stok Tersedia' : 'Habis'"></span>
                            </span>
                            <span class="block text-[11px] text-stone-500 mt-1 font-medium">
                                Sisa <strong class="text-stone-900" x-text="currentStock"></strong> pcs (Size <span class="font-bold text-stone-900" x-text="selectedSize"></span>)
                            </span>
                        </div>
                    </div>

                    <!-- Size Selector Module ("Pilih Ukuran") -->
                    <div class="space-y-3 pt-1">
                        <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                            <div class="flex items-center gap-2">
                                <span class="text-stone-900 font-bold">Pilih Ukuran</span>
                                <span class="text-xs font-normal text-stone-400">(Ukuran siap kirim)</span>
                            </div>
                            <button @click="sizeGuideModal = true" class="text-[#8C642A] hover:text-[#5E4219] flex items-center gap-1.5 transition text-xs font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                <span>Panduan Ukuran</span>
                            </button>
                        </div>

                        <!-- Tactile Size Pill Buttons -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <template x-for="(stock, sz) in stokUkuran" :key="sz">
                                <button type="button"
                                        @click="selectSize(sz)"
                                        :disabled="stock <= 0"
                                        :title="stock <= 0 ? 'Ukuran ' + sz + ' Stok Habis' : 'Stok Ukuran ' + sz + ': ' + stock + ' pcs'"
                                        :class="{
                                            'bg-[#1F1916] text-[#FAF7F2] shadow-md ring-2 ring-[#1F1916] border-[#1F1916] scale-102': selectedSize === sz && stock > 0,
                                            'bg-white text-stone-900 border border-[#DCD2C3] hover:border-stone-800 hover:bg-[#FAF7F2]': selectedSize !== sz && stock > 0,
                                            'bg-stone-100 text-stone-400 border border-stone-200 cursor-not-allowed opacity-50 line-through select-none': stock <= 0
                                        }"
                                        class="min-w-12 h-11 px-4 rounded-xl text-xs sm:text-sm font-bold flex flex-col items-center justify-center transition-all duration-150 relative">
                                    <span x-text="sz"></span>
                                    <span x-show="stock <= 0" class="text-[8px] uppercase tracking-tighter -mt-0.5 no-underline font-bold text-rose-500">Habis</span>
                                </button>
                            </template>
                        </div>

                        <!-- Out of Stock Notice -->
                        <div x-cloak x-show="currentStock <= 0" class="text-xs pt-0.5">
                            <p class="text-rose-600 font-semibold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Ukuran <span x-text="selectedSize"></span> saat ini habis. Silakan pilih varian ukuran lain.
                            </p>
                        </div>
                    </div>

                    <!-- Quantity Stepper & Primary Purchase CTAs -->
                    <div class="space-y-3.5 pt-2">
                        <div class="flex items-center gap-3">
                            <!-- Stepper with 44px Touch Target -->
                            <div class="inline-flex items-center rounded-2xl bg-white border border-[#D5CABB] p-1 shadow-2xs">
                                <button @click="decreaseQty()" :disabled="isOutOfStock || quantity <= 1"
                                        class="w-10 h-10 rounded-xl hover:bg-[#F6EFE5] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center text-stone-700 font-bold text-base transition">
                                    −
                                </button>
                                <span class="w-10 text-center text-sm font-bold text-stone-900" x-text="quantity"></span>
                                <button @click="increaseQty()" :disabled="isOutOfStock || quantity >= currentStock"
                                        class="w-10 h-10 rounded-xl hover:bg-[#F6EFE5] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center text-stone-700 font-bold text-base transition">
                                    +
                                </button>
                            </div>

                            <!-- Primary Button: Tambah ke Keranjang -->
                            <button @click="addToCart(false)" :disabled="isOutOfStock || isAdding"
                                    class="flex-1 py-3.5 px-6 rounded-2xl bg-[#1F1916] hover:bg-[#332A24] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs sm:text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2 group">
                                <svg class="w-4 h-4 text-[#E5C38E] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span x-text="isAdding ? 'Menyimpan...' : '+ Tambah ke Keranjang'"></span>
                            </button>
                        </div>

                        <!-- Secondary Row Buttons: Beli Sekarang & WhatsApp -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button @click.prevent="addToCart(true)" :disabled="isOutOfStock || isAdding"
                                    class="py-3 px-4 rounded-2xl bg-white border-2 border-[#1F1916] hover:bg-[#1F1916] hover:text-white active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-[#1F1916] font-bold text-xs sm:text-sm transition-all duration-200 shadow-2xs text-center flex items-center justify-center">
                                Beli Sekarang
                            </button>

                            <a :href="'https://wa.me/6281234567890?text=Halo%20Hamzah%20Style,%20saya%20tertarik%20dengan%20' + encodeURIComponent('{{ $namaProduk }}') + '%20Ukuran%20' + selectedSize + '%20Jumlah%20' + quantity + '%20pcs'"
                               target="_blank"
                               class="py-3 px-4 rounded-2xl bg-[#523A2A] hover:bg-[#3F2B1E] active:scale-[0.98] text-white font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 shadow-2xs">
                                <svg class="w-4 h-4 fill-current text-emerald-400" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Konsultasi Ukuran</span>
                            </a>
                        </div>
                    </div>

                    <!-- Accordion: Deskripsi & Detail Produk -->
                    <div class="border border-[#EAE1D3] rounded-2xl bg-white overflow-hidden shadow-2xs mt-6"
                         x-data="{ descOpen: true }">
                        <button @click="descOpen = !descOpen"
                                class="w-full p-4 sm:p-5 flex items-center justify-between font-bold text-stone-900 text-sm sm:text-base hover:bg-[#FAF7F2] transition">
                            <span class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#B58742]"></span>
                                <span>Deskripsi Produk</span>
                            </span>
                            <svg class="w-4 h-4 text-stone-500 transition-transform duration-200"
                                 :class="descOpen ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="descOpen" class="px-5 pb-5 text-xs sm:text-sm text-stone-600 leading-relaxed space-y-4 border-t border-[#EAE1D3]/70 pt-4">
                            <p class="text-stone-800 leading-relaxed">
                                {{ $deskripsiProduk }}
                            </p>
                            <div class="pt-3 border-t border-stone-100">
                                <ul class="space-y-2 text-stone-700 text-xs sm:text-sm">
                                    <li class="flex items-start gap-2">
                                        <span class="text-stone-400 w-28 shrink-0 font-medium">Bahan / Material:</span>
                                        <span class="font-bold text-stone-900">{{ $materialProduk }}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-stone-400 w-28 shrink-0 font-medium">Kategori:</span>
                                        <span class="font-bold text-stone-900">{{ $kategoriProduk }}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-stone-400 w-28 shrink-0 font-medium">Ketersediaan:</span>
                                        <span class="font-semibold text-emerald-700">{{ $stokProduk > 0 ? $stokProduk . ' unit siap kirim' : 'Stok Habis' }}</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-stone-400 w-28 shrink-0 font-medium">Jaminan:</span>
                                        <span class="font-semibold text-stone-900">100% Batik Nusantara Autentik</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ================= REVIEWS SECTION (ULASAN PELANGGAN) ================= -->
            <section id="ulasan-section" class="mt-20 pt-12 border-t border-[#EAE1D3]">
                <!-- Header Title -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-[#B58742] mb-1">
                            <span>✦</span>
                            <span>TESTIMONI & PENGALAMAN</span>
                        </div>
                        <h2 class="font-serif-title text-2xl sm:text-3xl md:text-4xl font-bold text-[#1F1916]">
                            Ulasan Pelanggan Terverifikasi
                            @if($ratingCount > 0)
                                <span class="text-base sm:text-lg font-sans font-medium text-stone-500 block sm:inline sm:ml-2">({{ $ratingCount }} Ulasan Pelanggan)</span>
                            @endif
                        </h2>
                    </div>
                </div>

                <!-- Rating Scorecard Banner -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EAE1D3] shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">

                    <!-- Left Large Score -->
                    <div class="md:col-span-4 text-center md:border-r border-stone-200 md:pr-8">
                        <span class="text-5xl sm:text-6xl font-extrabold text-[#1F1916] block font-serif-title tracking-tight">
                            {{ $ratingCount > 0 ? number_format($ratingAvg, 1) : '5.0' }}
                        </span>
                        <div class="flex items-center justify-center gap-1 text-amber-500 text-lg my-2">
                            @for ($s = 1; $s <= 5; $s++)
                                <span class="{{ $ratingCount > 0 ? ($s <= round($ratingAvg) ? 'text-amber-500' : 'text-stone-300') : 'text-amber-500' }}">★</span>
                            @endfor
                        </div>
                        <span class="font-bold text-stone-900 text-sm block">
                            {{ $ratingCount > 0 ? ($ratingAvg >= 4.5 ? 'Kualitas Sangat Memuaskan' : ($ratingAvg >= 3.5 ? 'Puas' : 'Cukup')) : 'Kualitas Teruji' }}
                        </span>
                        <span class="text-xs text-stone-400 mt-0.5 block">Berdasarkan {{ $ratingCount }} penilaian terverifikasi</span>
                    </div>

                    <!-- Right Distribution Bars -->
                    <div class="md:col-span-8 space-y-2 text-xs font-semibold text-stone-600">
                        @foreach ([5, 4, 3, 2, 1] as $star)
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-stone-700">{{ $star }} Bintang</span>
                                <div class="flex-1 h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full {{ $star >= 4 ? 'bg-[#1F1916]' : ($star === 3 ? 'bg-[#8C7662]' : 'bg-stone-300') }} rounded-full transition-all duration-500"
                                         style="width: {{ $ratingCount > 0 ? round(($ratingBars[$star] ?? 0) / $ratingCount * 100) : ($star === 5 ? 100 : 0) }}%;"></div>
                                </div>
                                <span class="w-8 text-right text-stone-400">{{ $ratingBars[$star] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Review Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($ulasans as $u)
                        @php
                            $words = preg_split('/\s+/', trim($u->customer_name));
                            $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
                        @endphp
                        <div class="bg-white rounded-3xl p-6 border border-[#EAE1D3] shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="flex items-center justify-between mb-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#1F1916] text-[#E5C38E] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-stone-900">{{ $u->customer_name }}</h4>
                                            <span class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5">
                                                <span>✓</span> Pembeli Terverifikasi
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-stone-400">{{ $u->created_at->format('d M Y') }}</span>
                                </div>

                                <div class="flex text-amber-500 text-xs mb-3">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <span class="{{ $s <= $u->rating ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                                    @endfor
                                </div>

                                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-4 italic">
                                    "{{ $u->comment }}"
                                </p>
                                @if ($u->image_path)
                                    <img src="{{ asset('storage/'.$u->image_path) }}" alt="Foto ulasan {{ $u->customer_name }}" class="mb-4 max-h-52 w-full rounded-2xl border border-stone-200 object-cover object-center">
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-3 bg-white rounded-3xl p-8 border border-dashed border-[#DCD2C3] text-center">
                            <p class="font-bold text-stone-900 text-sm">Belum ada ulasan untuk produk ini</p>
                            <p class="text-xs text-stone-500 mt-1">Ulasan dapat diberikan setelah pesanan Anda berstatus Selesai melalui menu Pesanan Saya.</p>
                        </div>
                    @endforelse
                </div>

                @if($ratingCount > $ulasans->count())
                    <div class="text-center mt-8">
                        <span class="inline-block px-6 py-2.5 rounded-full bg-white border border-[#EAE1D3] text-stone-800 text-xs sm:text-sm font-semibold shadow-2xs">
                            + {{ $ratingCount - $ulasans->count() }} Ulasan Lainnya
                        </span>
                    </div>
                @endif
            </section>

            <!-- ================= SECTION: PRODUK TERKAIT ================= -->
            <section class="mt-20 pt-12 border-t border-[#EAE1D3]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#B58742] block mb-1">
                            ✦ REKOMENDASI PILIHAN
                        </span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-[#1F1916]">
                            Koleksi Batik Serupa
                        </h2>
                    </div>
                    <a href="{{ route('koleksi.index') }}" class="text-xs sm:text-sm font-semibold text-stone-800 hover:text-[#B58742] transition flex items-center gap-1.5 group">
                        <span>Lihat Semua</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                <!-- Related Product Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($produksTerkait as $rel)
                        <div class="bg-white rounded-3xl overflow-hidden border border-[#EAE1D3] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group">
                            <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                                <span class="absolute top-3 left-3 z-10 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs bg-[#1F1916]/80 backdrop-blur-md text-white">
                                    {{ $rel->kategori }}
                                </span>
                                <img src="{{ $rel->gambar_url }}"
                                     alt="{{ $rel->nama }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-stone-400 block mb-1">{{ strtoupper($rel->kategori) }}</span>
                                    <h3 class="font-bold text-stone-900 text-sm leading-snug line-clamp-1 mb-2 group-hover:text-[#B58742] transition-colors">
                                        {{ $rel->nama }}
                                    </h3>
                                    <p class="text-base font-extrabold text-[#1F1916] mb-4">Rp {{ number_format($rel->harga, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <a href="{{ route('produk.detail', ['id' => $rel->id]) }}" class="w-full block py-2.5 rounded-xl bg-[#1F1916] hover:bg-[#332A24] text-white text-xs font-semibold transition text-center shadow-xs">
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

    <!-- ================= STICKY MOBILE BOTTOM BAR ================= -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#ECE3D5] p-3 px-4 shadow-[0_-8px_20px_rgba(0,0,0,0.08)] flex items-center justify-between gap-3">
        <div>
            <span class="text-[10px] text-stone-400 uppercase font-bold block">Harga</span>
            <span class="text-base font-extrabold text-[#1F1916]">{{ $hargaFormatted }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="addToCart(false)" :disabled="isOutOfStock || isAdding"
                    class="py-2.5 px-4 rounded-xl bg-[#1F1916] active:scale-95 disabled:opacity-50 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5 text-[#E5C38E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                <span>Keranjang</span>
            </button>
            <button @click.prevent="addToCart(true)" :disabled="isOutOfStock || isAdding"
                    class="py-2.5 px-4 rounded-xl bg-[#B58742] active:scale-95 disabled:opacity-50 text-white font-bold text-xs shadow-sm">
                Beli Sekarang
            </button>
        </div>
    </div>

    <!-- ================= FOOTER ================= -->
    <footer id="kontak" class="bg-[#FAF7F2] border-t border-[#ECE3D5] pt-16 pb-12 text-stone-700 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-12">

                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-[#1F1916] flex items-center justify-center text-[#E5C38E]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-stone-900 uppercase text-sm">
                            HAMZAH STYLE
                        </span>
                    </div>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4 max-w-sm">
                        Batik Modern & Warisan Tekstil Berkualitas Tinggi. Menjaga kelestarian kain tradisional Indonesia dengan potongan presisi dan siluet kontemporer.
                    </p>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-amber-900 text-[11px] font-bold">
                        <span>✦</span>
                        <span>WARISAN BERKELANJUTAN NUSANTARA</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi -->
                <div class="lg:col-span-2">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Navigasi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-950 transition">Beranda</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 font-semibold text-stone-900 transition">Koleksi Produk</a></li>
                        <li><a href="{{ route('pesanan.index') }}" class="hover:text-stone-950 transition">Pesanan Saya</a></li>
                        <li><a href="{{ route('keranjang.index') }}" class="hover:text-stone-950 transition">Keranjang Belanja</a></li>
                    </ul>
                </div>

                <!-- Col 3: Saluran Penjualan Resmi -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Layanan Resmi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li>
                            <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                WhatsApp Business CS
                            </a>
                        </li>
                        <li>
                            <a href="#" class="inline-flex items-center gap-2 hover:text-stone-950 transition">
                                <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                Shopee Official Store
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

                <!-- Col 4: Keamanan & Pengiriman -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Jaminan Belanja
                    </h5>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4">
                        Pengiriman cepat ke seluruh Indonesia dengan kurir terpercaya dan garansi produk autentik 100%.
                    </p>
                    <div class="p-3.5 rounded-2xl bg-white border border-[#EAE1D3] text-xs text-stone-700">
                        <span class="font-bold text-stone-900 block">Butuh Bantuan Memilih Motif?</span>
                        <span class="text-stone-500 text-[11px] block mt-0.5">Tim kurator kami siap melayani konsultasi via chat setiap hari pukul 08:00 - 21:00 WIB.</span>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright Line -->
            <div class="pt-8 border-t border-[#ECE3D5] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>© {{ date('Y') }} Hamzah Style Official. Hak cipta dilindungi.</p>
                <p class="text-stone-400">Warisan Budaya Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- ================= SIZE GUIDE MODAL ================= -->
    <div x-cloak x-show="sizeGuideModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div x-show="sizeGuideModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-[#1F1916]/70 backdrop-blur-sm" @click="sizeGuideModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="sizeGuideModal" x-transition class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-200">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#B58742]">PANDUAN STANDAR</span>
                        <h3 class="font-serif-title font-bold text-xl text-stone-900">Ukuran Kemeja & Busana Batik</h3>
                    </div>
                    <button @click="sizeGuideModal = false" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:text-stone-900 hover:bg-stone-200 flex items-center justify-center transition">✕</button>
                </div>
                <div class="overflow-x-auto text-xs sm:text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#FAF7F2] border-b border-stone-200 text-stone-800 font-bold">
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
                    *Toleransi jahitan ± 1–2 cm. Ukuran diukur dalam keadaan pakaian dibentangkan mendatar. Jika ragu memilih ukuran, hubungi customer service kami.
                </p>
            </div>
        </div>
    </div>

    <!-- ================= IMAGE ZOOM MODAL ================= -->
    <div x-cloak x-show="imageZoomModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-[#120E0C]/90 backdrop-blur-md" @click="imageZoomModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative max-w-3xl w-full">
                <button @click="imageZoomModal = false" class="absolute -top-12 right-0 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-md transition">
                    ✕ Tutup Pratinjau
                </button>
                <img :src="activeImage" alt="Preview Gambar" class="w-full h-auto rounded-3xl shadow-2xl border border-stone-800">
            </div>
        </div>
    </div>

</body>
</html>
