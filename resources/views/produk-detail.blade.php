@php
    $namaProduk = $produk->nama ?? 'Produk Batik';
    $kategoriProduk = $produk->kategoriRelasi?->nama_kategori ?? $produk->kategori ?? 'Koleksi Batik';
    $hargaProduk = (float) ($produk->harga ?? 0);
    $hargaFormatted = 'Rp ' . number_format($hargaProduk, 0, ',', '.');
    $hargaCoret = $hargaProduk > 0 ? 'Rp ' . number_format($hargaProduk * 1.15, 0, ',', '.') : '';
    $materialProduk = $produk->material ?: 'Katun Primissima Halus Benang 60s';
    $deskripsiProduk = $produk->deskripsi ?: 'Koleksi busana batik eksklusif bermutu tinggi berdaya pakai harian dan formal.';
    $gambarProduk = $produk ? $produk->gambar_url : asset('images/beranda/folded-shirts.jpg');
    $stokProduk = (int) ($produk->stok ?? 0);
    $stokUkuranArray = $produk ? $produk->getStokUkuranArray() : ['All Size' => $stokProduk];

    $firstAvailableSize = 'All Size';
    foreach ($stokUkuranArray as $sz => $stk) {
        if ($stk > 0) {
            $firstAvailableSize = (string) $sz;
            break;
        }
    }
    $produkId = $produk->id ?? 1;

    // Foto asli produk dari database
    $galleryThumbnails = [$gambarProduk];
    if ($produk && !empty($produk->gambar_lainnya) && is_array($produk->gambar_lainnya)) {
        foreach ($produk->gambar_lainnya as $gl) {
            if ($gl) {
                $galleryThumbnails[] = str_starts_with($gl, 'http') ? $gl : (file_exists(public_path($gl)) ? asset($gl) : asset('storage/' . $gl));
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $namaProduk }} — HAMZAH STYLE OFFICIAL</title>
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
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#241E1A] min-h-screen selection:bg-[#B58742] selection:text-white relative"
      x-data="{
          mobileMenuOpen: false,
          activeImage: '{{ $galleryThumbnails[0] }}',
          selectedSize: 'M',
          sizeDescriptions: {
              'S': '104 cm',
              'M': '108 cm',
              'L': '112 cm',
              'XL': '116 cm',
              'XXL': '120 cm'
          },
          quantity: 1,
          descOpen: true,
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          sizeGuideModal: false,
          imageZoomModal: false,
          writeReviewModal: false,
          toastMessage: '',
          isAdding: false,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3200);
          },
          selectSize(sz) {
              this.selectedSize = sz;
          },
          increaseQty() {
              this.quantity++;
          },
          decreaseQty() {
              if (this.quantity > 1) this.quantity--;
          },
          addToCart(redirect = false) {
              this.isAdding = true;
              fetch('{{ route('keranjang.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  credentials: 'same-origin',
                  body: JSON.stringify({
                      produk_id: {{ $produkId }},
                      qty: this.quantity,
                      ukuran: this.selectedSize,
                      varian: 'Ukuran: ' + this.selectedSize + ' • Lengan Panjang'
                  })
              })
              .then(res => res.json())
              .then(data => {
                  this.isAdding = false;
                  if (data.success) {
                      this.cartCount = data.cartCount;
                      if (redirect) {
                          window.location.href = '{{ route('keranjang.index') }}';
                      } else {
                          this.showToast(data.message || 'Berhasil ditambahkan ke keranjang');
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
    </div>

    <!-- ================= NAVBAR ================= -->
    <header class="sticky top-0 z-40 bg-[#FAF7F2] border-b border-[#ECE4D8] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-xs group-hover:bg-stone-800 transition">
                            <!-- Delicate batik icon box -->
                            <svg class="w-5 h-5 text-[#E5C38E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="3" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-sm sm:text-base text-stone-900 uppercase font-sans">
                            HAMZAH STYLE OFFICIAL
                        </span>
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
                    <a href="{{ url('/#koleksi') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Kategori
                    </a>
                    <a href="{{ url('/#tentang-kami') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Tentang Kami
                    </a>
                    <a href="#kontak" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
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
                            {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}
                        </span>
                    </a>

                    <!-- Akun Pill Button -->
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" 
                                @click.away="userMenu = false"
                                class="flex items-center gap-2 pl-1.5 pr-3.5 py-1.5 rounded-full border border-stone-300 hover:border-stone-400 bg-white/80 hover:bg-white transition shadow-2xs">
                            <div class="w-7 h-7 rounded-full bg-[#1F1916] text-[#E5C38E] flex items-center justify-center font-bold text-xs">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-stone-800">
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

                                <a href="{{ route('pesanan.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900">
                                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    Pesanan Saya
                                </a>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900">
                                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profil Akun
                                </a>

                                <div class="border-t border-stone-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Keluar
                                    </button>
                                </form>
                            @else
                                <div class="p-2 space-y-1">
                                    <a href="{{ route('login') }}" class="flex items-center justify-center w-full py-2 px-4 rounded-xl bg-[#201A17] text-white text-xs font-semibold hover:bg-stone-800 transition">
                                        Masuk
                                    </a>
                                    <a href="{{ route('register') }}" class="flex items-center justify-center w-full py-2 px-4 rounded-xl border border-stone-300 text-stone-800 text-xs font-semibold hover:bg-stone-50 transition">
                                        Daftar Akun
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>

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
                 class="md:hidden py-4 border-t border-[#ECE4D8] space-y-2">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Home</a>
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-900 bg-stone-200/50">Produk</a>
                <a href="{{ url('/#koleksi') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kategori</a>
                <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Pesanan</a>
                <a href="{{ url('/#tentang-kami') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Tentang Kami</a>
                <a href="#kontak" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kontak</a>
            </div>
        </div>
    </header>

    <!-- ================= MAIN PRODUCT DETAIL SECTION ================= -->
    <main class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumbs -->
            <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-stone-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-stone-900 transition">Beranda</a>
                <span class="text-stone-400">&gt;</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-900 transition">Produk</a>
                <span class="text-stone-400">&gt;</span>
                <a href="{{ route('koleksi.index') }}" class="hover:text-stone-900 transition">{{ $kategoriProduk }}</a>
                <span class="text-stone-400">&gt;</span>
                <span class="text-stone-900 font-semibold truncate">{{ $namaProduk }}</span>
            </nav>

            <!-- Product Showcase (2 Columns) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                <!-- Left Column: Gallery -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- Main Image Frame -->
                    <div class="relative aspect-[4/5] rounded-2xl overflow-hidden bg-stone-100 border border-[#EAE1D3] shadow-sm group">
                        <img :src="activeImage"
                             alt="{{ $namaProduk }}"
                             class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105 cursor-pointer"
                             @click="imageZoomModal = true">

                        <!-- Zoom Indicator Pill (Bottom Right) -->
                        <button @click="imageZoomModal = true"
                                class="absolute bottom-4 right-4 w-9 h-9 rounded-full bg-white/90 backdrop-blur-md text-stone-800 hover:text-stone-950 flex items-center justify-center shadow-md hover:bg-white transition-all border border-stone-200"
                                title="Perbesar gambar">
                            <svg class="w-4 h-4 text-stone-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Thumbnails Gallery -->
                    @if(count($galleryThumbnails) > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($galleryThumbnails as $idx => $thumb)
                            <button @click="activeImage = '{{ $thumb }}'"
                                    :class="activeImage === '{{ $thumb }}' ? 'ring-2 ring-[#201A17] border-transparent opacity-100' : 'opacity-70 hover:opacity-100 border-[#EAE1D3]'"
                                    class="aspect-square rounded-xl overflow-hidden bg-stone-100 border transition-all duration-200 shadow-2xs">
                                <img src="{{ $thumb }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Right Column: Product Detail & Controls -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Category Badge -->
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-stone-500 block mb-1">
                            {{ strtoupper($kategoriProduk) }}
                        </span>
                        <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-[#1F1916] leading-tight">
                            {{ $namaProduk }}
                        </h1>
                    </div>

                    <!-- Rating & Reviews Bar -->
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-stone-600">
                        @if($ratingCount > 0)
                            <div class="flex items-center gap-1.5 font-bold text-stone-900">
                                <div class="flex text-amber-500 text-sm">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span class="{{ $s <= round($ratingAvg) ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="ml-1 text-sm font-bold">{{ number_format($ratingAvg, 1) }}</span>
                            </div>
                            <span class="text-stone-300">|</span>
                            <a href="#ulasan-section" class="hover:text-stone-950 font-medium text-stone-600">
                                {{ $ratingCount }} Ulasan Pelanggan
                            </a>
                        @else
                            <div class="flex items-center gap-1 text-stone-400 text-xs">
                                <span class="text-amber-500">★</span>
                                <span>Belum ada ulasan</span>
                            </div>
                        @endif
                    </div>

                    <!-- Price Card -->
                    <div class="bg-[#FAF4EC] rounded-2xl p-5 border border-[#EFE5D6] flex items-center justify-between shadow-2xs">
                        <div>
                            <span class="text-[10px] text-stone-500 uppercase tracking-widest font-bold block">HARGA TERBAIK</span>
                            <div class="flex items-baseline gap-3 mt-1">
                                <span class="text-3xl sm:text-4xl font-extrabold text-[#1F1916] tracking-tight">
                                    {{ $hargaFormatted }}
                                </span>
                                <span class="text-sm sm:text-base text-stone-400 line-through">
                                    {{ $hargaCoret }}
                                </span>
                            </div>
                        </div>

                        <!-- Stock Badge -->
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white border border-stone-200 text-stone-800 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Stok Tersedia</span>
                            </span>
                            <span class="block text-xs text-stone-500 mt-1 font-medium">
                                Sisa <strong class="text-stone-800">{{ $stokProduk }}</strong> pcs
                            </span>
                        </div>
                    </div>

                    <!-- Value Proposition Card -->
                    <div class="bg-[#FAF5EF]/80 border border-[#ECE2D2] rounded-2xl p-4 flex items-start gap-3.5 text-xs text-stone-700 leading-relaxed">
                        <div class="w-8 h-8 rounded-lg bg-[#EFE8DD] text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-stone-800" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                            </svg>
                        </div>
                        <p>
                            <strong>{{ $materialProduk }}.</strong> {{ $deskripsiProduk }}
                        </p>
                    </div>

                    <!-- Size Selector -->
                    <div class="space-y-3 pt-1">
                        <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                            <span class="text-stone-900 font-bold">Pilih Ukuran</span>
                            <button @click="sizeGuideModal = true" class="text-stone-700 hover:text-stone-950 flex items-center gap-1.5 transition text-xs font-medium">
                                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                <span>Panduan Ukuran</span>
                            </button>
                        </div>

                        <!-- Size Buttons from Database -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            @foreach($stokUkuranArray as $sz => $stk)
                                <button type="button"
                                        @click="selectSize('{{ $sz }}')"
                                        :class="selectedSize === '{{ $sz }}'
                                            ? 'bg-[#201A17] text-white shadow-xs'
                                            : 'bg-[#FAF7F2] text-stone-800 border border-[#D5CABB] hover:border-stone-800'"
                                        class="min-w-12 h-11 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center transition-all duration-150">
                                    {{ $sz }}
                                    @if($stk <= 5 && $stk > 0)
                                        <span class="ml-1 text-[9px] text-amber-500">({{ $stk }})</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <!-- Dynamic Size Subtext -->
                        <div class="text-xs text-stone-500 pt-0.5">
                            Ukuran terpilih: <strong class="text-stone-900" x-text="selectedSize"></strong> | Lingkar Dada: <span class="font-semibold text-stone-800" x-text="sizeDescriptions[selectedSize] || '108 cm'"></span>
                        </div>
                    </div>

                    <!-- Stepper & Add to Cart Row -->
                    <div class="flex items-center gap-3 pt-2">
                        <!-- Stepper -->
                        <div class="inline-flex items-center rounded-xl bg-white border border-[#D5CABB] p-1 shadow-2xs">
                            <button @click="decreaseQty()"
                                    class="w-10 h-10 rounded-lg hover:bg-[#FAF4EC] flex items-center justify-center text-stone-700 font-bold text-base transition">
                                −
                            </button>
                            <span class="w-10 text-center text-sm font-bold text-stone-900" x-text="quantity"></span>
                            <button @click="increaseQty()"
                                    class="w-10 h-10 rounded-lg hover:bg-[#FAF4EC] flex items-center justify-center text-stone-700 font-bold text-base transition">
                                +
                            </button>
                        </div>

                        <!-- Primary CTA Button: Tambah ke Keranjang -->
                        <button @click="addToCart(false)" :disabled="isAdding"
                                class="flex-1 py-3.5 px-6 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white font-semibold text-xs sm:text-sm transition duration-200 shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-stone-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span x-text="isAdding ? 'Menyimpan...' : 'Tambah ke Keranjang'"></span>
                        </button>
                    </div>

                    <!-- Secondary Row: Beli Sekarang & WhatsApp -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <button @click="addToCart(true)"
                                class="py-3 px-4 rounded-xl bg-[#EFE8DD] hover:bg-[#E5DBCB] text-stone-900 font-bold text-xs sm:text-sm transition text-center shadow-2xs">
                            Beli Sekarang
                        </button>

                        <a href="https://wa.me/6281234567890?text=Halo%20Hamzah%20Style,%20saya%20ingin%20konsultasi%20Kemeja%20Batik%20Parang%20Seling%20Premium"
                           target="_blank"
                           class="py-3 px-4 rounded-xl bg-[#4A2D1B] hover:bg-[#3D2415] text-white font-semibold text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-2xs">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Konsultasi Via WhatsApp</span>
                        </a>
                    </div>

                    <!-- Accordion: Deskripsi Produk -->
                    <div class="border border-[#EAE1D3] rounded-2xl bg-white overflow-hidden shadow-2xs mt-4">
                        <button @click="descOpen = !descOpen"
                                class="w-full p-4 sm:p-5 flex items-center justify-between font-bold text-stone-900 text-sm sm:text-base hover:bg-[#FAF7F2] transition">
                            <span>Deskripsi Produk</span>
                            <svg class="w-4 h-4 text-stone-500 transition-transform duration-200"
                                 :class="descOpen ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="descOpen" class="px-5 pb-5 text-xs sm:text-sm text-stone-600 leading-relaxed space-y-3 border-t border-[#EAE1D3]/70 pt-4">
                            <p class="text-stone-800 leading-relaxed">
                                {{ $deskripsiProduk }}
                            </p>
                            <ul class="space-y-1.5 text-stone-700 text-xs sm:text-sm pt-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-stone-400">•</span>
                                    <span><strong>Potongan:</strong> Regular Fit modern kerah kancing tersembunyi</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-stone-400">•</span>
                                    <span><strong>Bahan:</strong> Katun Prima Halus kombinasi soga alam non-alergi</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-stone-400">•</span>
                                    <span><strong>Kancing:</strong> Tempurung kelapa eksklusif dengan jahitan silang kuat</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-stone-400">•</span>
                                    <span><strong>Saku:</strong> Dada kiri satu pasang dengan motif menyambung sempurna (matching pattern)</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ================= REVIEWS SECTION (ULASAN PELANGGAN) ================= -->
            <section id="ulasan-section" class="mt-20 pt-12 border-t border-[#EAE1D3]">
                <!-- Header Title -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-stone-500 block mb-1">
                            TESTIMONI NYATA
                        </span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-[#1F1916]">
                            Ulasan Pelanggan
                        </h2>
                    </div>

                    <button @click="writeReviewModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white text-xs sm:text-sm font-semibold transition shadow-xs">
                        <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                        </svg>
                        <span>Tulis Ulasan Anda</span>
                    </button>
                </div>

                <!-- Rating Scorecard Banner -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-[#EAE1D3] shadow-xs grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">

                    <!-- Left Large Score -->
                    <div class="md:col-span-4 text-center md:border-r border-stone-200 md:pr-8">
                        <span class="text-5xl font-extrabold text-[#1F1916] block font-serif-title tracking-tight">
                            {{ $ratingCount > 0 ? number_format($ratingAvg, 1) : '5.0' }}
                        </span>
                        <div class="flex items-center justify-center gap-1 text-amber-500 text-lg my-2">
                            @for($s = 1; $s <= 5; $s++)
                                <span class="{{ $s <= round($ratingAvg ?: 5) ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                            @endfor
                        </div>
                        <span class="font-bold text-stone-900 text-sm block">
                            {{ $ratingAvg >= 4.5 ? 'Puas Sempurna' : ($ratingAvg >= 4.0 ? 'Sangat Baik' : ($ratingCount > 0 ? 'Cukup Puas' : 'Produk Autentik')) }}
                        </span>
                        <span class="text-xs text-stone-400 mt-0.5 block">
                            @if($ratingCount > 0)
                                Berdasarkan {{ $ratingCount }} ulasan pembeli
                            @else
                                Belum ada ulasan untuk produk ini
                            @endif
                        </span>
                    </div>

                    <!-- Right Distribution Bars -->
                    <div class="md:col-span-8 space-y-2 text-xs font-medium text-stone-600">
                        @foreach([5, 4, 3, 2, 1] as $star)
                            @php
                                $bCount = $ratingBars[$star] ?? 0;
                                $bPct = $ratingCount > 0 ? round(($bCount / $ratingCount) * 100) : ($star === 5 ? 100 : 0);
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-stone-700">{{ $star }} Bintang</span>
                                <div class="flex-1 h-2 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full bg-[#1F1916] rounded-full transition-all" style="width: {{ $bPct }}%;"></div>
                                </div>
                                <span class="w-8 text-right text-stone-400 font-semibold">{{ $bCount }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Customer Review Cards -->
                @if($ulasans && $ulasans->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($ulasans as $u)
                            @php
                                $words = preg_split('/\s+/', trim($u->customer_name));
                                $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
                            @endphp
                            <div class="bg-white rounded-2xl p-6 border border-[#EAE1D3] shadow-xs flex flex-col justify-between hover:shadow-md transition">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#EFE8DD] text-[#785933] font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $initials ?: 'PL' }}
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-sm text-stone-900">{{ $u->customer_name }}</h4>
                                                <span class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
                                                    <span>✓</span> Pembeli Terverifikasi
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[11px] text-stone-400">{{ $u->created_at ? $u->created_at->diffForHumans() : 'Baru saja' }}</span>
                                    </div>

                                    <div class="flex text-amber-500 text-xs mb-3">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="{{ $s <= $u->rating ? 'text-amber-500' : 'text-stone-300' }}">★</span>
                                        @endfor
                                    </div>

                                    <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-4">
                                        "{{ $u->comment }}"
                                    </p>

                                    @if($u->image_path)
                                        <div class="flex items-center gap-2 pt-1">
                                            <img src="{{ asset('storage/' . $u->image_path) }}" alt="Foto ulasan" class="w-12 h-12 rounded-lg object-cover border border-stone-200">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-8 border border-dashed border-[#DCD2C3] text-center">
                        <p class="font-bold text-stone-900 text-sm">Belum ada ulasan untuk produk ini</p>
                        <p class="text-xs text-stone-500 mt-1">Ulasan dapat diberikan setelah pesanan Anda berstatus Selesai melalui menu Pesanan Saya.</p>
                    </div>
                @endif

                @if($ratingCount > 6)
                    <div class="text-center mt-8">
                        <button @click="showToast('Menampilkan semua ulasan')" 
                                class="inline-block px-7 py-3 rounded-full bg-white border border-[#EAE1D3] text-stone-800 text-xs sm:text-sm font-semibold hover:border-stone-400 hover:bg-stone-50 transition shadow-2xs">
                            Lihat Semua {{ $ratingCount }} Ulasan
                        </button>
                    </div>
                @endif
            </section>

            <!-- ================= SECTION: PRODUK TERKAIT ================= -->
            <section class="mt-20 pt-12 border-t border-[#EAE1D3]">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-[#1F1916]">
                        Produk Terkait
                    </h2>
                    <a href="{{ route('koleksi.index') }}" class="text-xs sm:text-sm font-semibold text-stone-800 hover:text-stone-950 transition flex items-center gap-1.5 group">
                        <span>Jelajahi Semua Produk</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <!-- Real Related Products from Database -->
                @if(isset($produksTerkait) && $produksTerkait->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($produksTerkait as $rel)
                            <div class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-2xs hover:shadow-md transition flex flex-col group">
                                <a href="{{ route('produk.detail', $rel->id) }}" class="relative aspect-[4/5] overflow-hidden bg-stone-100 block">
                                    <span class="absolute top-3 left-3 z-10 text-[9px] font-bold px-2 py-0.5 rounded bg-white/90 backdrop-blur-sm text-stone-800 uppercase tracking-wider">
                                        {{ $rel->stok > 0 ? ($rel->stok <= 10 ? 'Stok Terbatas' : 'Ready Stock') : 'Stok Habis' }}
                                    </span>
                                    <img src="{{ $rel->gambar_url }}" alt="{{ $rel->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </a>
                                <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-stone-400 block mb-1">
                                            {{ strtoupper($rel->kategoriRelasi?->nama_kategori ?? $rel->kategori ?? 'BATIK') }}
                                        </span>
                                        <a href="{{ route('produk.detail', $rel->id) }}">
                                            <h3 class="font-bold text-stone-900 text-sm leading-snug mb-1 hover:text-amber-800 transition line-clamp-1">
                                                {{ $rel->nama }}
                                            </h3>
                                        </a>
                                        <p class="text-[11px] text-stone-500 line-clamp-1 mb-3">
                                            {{ $rel->deskripsi ?: 'Koleksi busana batik autentik premium.' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                                        <span class="text-sm font-extrabold text-stone-900">
                                            Rp {{ number_format($rel->harga, 0, ',', '.') }}
                                        </span>
                                        <a href="{{ route('produk.detail', $rel->id) }}" 
                                           class="w-8 h-8 rounded-full bg-stone-100 hover:bg-[#201A17] hover:text-white text-stone-800 flex items-center justify-center transition shadow-2xs"
                                           title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
                            <svg class="w-4 h-4 text-[#E5C38E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="3" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-stone-900 uppercase text-sm">
                            HAMZAH STYLE
                        </span>
                    </div>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-4 max-w-sm">
                        Batik Modern & Olahan Kain Sisa Berkelanjutan. Menjaga warisan tekstil Nusantara melalui inovasi kriya beretika dan potongan adibusana ramah lingkungan.
                    </p>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-amber-900 text-[11px] font-bold">
                        <span>🌱</span>
                        <span>KARYA BERKELANJUTAN</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi -->
                <div class="lg:col-span-2">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Navigasi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-950 transition">Home</a></li>
                        <li><a href="{{ url('/') }}#tentang-kami" class="hover:text-stone-950 transition">Tentang Kami</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 font-semibold text-stone-900 transition">Produk</a></li>
                        <li><a href="{{ url('/') }}#koleksi" class="hover:text-stone-950 transition">Kategori</a></li>
                        <li><a href="#kontak" class="hover:text-stone-950 transition">Kontak</a></li>
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
                        Dapatkan akses awal untuk rilisan eksklusif dan seri upcycle terbatas.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-white border border-stone-300 hover:border-stone-500 flex items-center justify-center text-stone-700 hover:text-stone-950 transition shadow-xs">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 00-1-.08A6.34 6.34 0 003 15.66a6.34 6.34 0 0010.86 4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-.04-4.52z"/></svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright Line -->
            <div class="pt-8 border-t border-[#ECE4D8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>&copy; 2025 Hamzah Style Official. All rights reserved.</p>
                <p class="text-stone-400">Made with pride in Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- Zoom Modal -->
    <div x-cloak x-show="imageZoomModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 backdrop-blur-sm p-4" @click="imageZoomModal = false">
        <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-3xl overflow-hidden p-2" @click.stop>
            <button @click="imageZoomModal = false" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-black/60 text-white flex items-center justify-center">
                &times;
            </button>
            <img :src="activeImage" alt="Preview zoom" class="max-h-[85vh] w-auto mx-auto rounded-2xl object-contain">
        </div>
    </div>

    <!-- Size Guide Modal -->
    <div x-cloak x-show="sizeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/70 backdrop-blur-sm p-4" @click="sizeGuideModal = false">
        <div class="relative max-w-lg w-full bg-white rounded-3xl p-6 sm:p-8" @click.stop>
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-100">
                <h3 class="font-serif-title text-xl font-bold text-stone-900">Panduan Ukuran Kemeja Pria</h3>
                <button @click="sizeGuideModal = false" class="text-stone-400 hover:text-stone-800 text-xl font-bold">&times;</button>
            </div>
            <div class="overflow-x-auto text-xs sm:text-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-stone-200 text-stone-500 font-semibold">
                            <th class="py-2.5">Size</th>
                            <th class="py-2.5">Lingkar Dada</th>
                            <th class="py-2.5">Panjang Baju</th>
                            <th class="py-2.5">Panjang Lengan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <tr><td class="py-2.5 font-bold">S</td><td>104 cm</td><td>72 cm</td><td>60 cm</td></tr>
                        <tr><td class="py-2.5 font-bold">M</td><td>108 cm</td><td>74 cm</td><td>61 cm</td></tr>
                        <tr><td class="py-2.5 font-bold">L</td><td>112 cm</td><td>76 cm</td><td>62 cm</td></tr>
                        <tr><td class="py-2.5 font-bold">XL</td><td>116 cm</td><td>78 cm</td><td>63 cm</td></tr>
                        <tr><td class="py-2.5 font-bold">XXL</td><td>120 cm</td><td>80 cm</td><td>64 cm</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
