<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Semua Produk — HAMZAH STYLE OFFICIAL</title>
    <meta name="description" content="Jelajahi keanggunan busana batik autentik dan ragam kriya sirkular olahan kain sisa bernilai tinggi di Hamzah Style Official.">

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
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          searchQuery: '',
          activeCategory: 'Semua Produk',
          priceRange: 'Semua',
          sortBy: 'paling-sesuai',
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},
          toastMessage: '',
          currentPage: 1,
          perPage: 12,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },

          addToCart(prod) {
              fetch('{{ route('keranjang.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  credentials: 'same-origin',
                  body: JSON.stringify({
                      produk_id: prod.id,
                      qty: 1
                  })
              })
              .then(res => res.json())
              .then(data => {
                  if (data.success) {
                      this.cartCount = data.cartCount;
                      this.showToast((prod.nama || 'Produk') + ' ditambahkan ke keranjang');
                  } else {
                      this.showToast(data.message || 'Gagal menambahkan produk');
                  }
              })
              .catch(() => {
                  this.showToast('Gagal menghubungi server saat menambah ke keranjang');
              });
          },

          products: [
              @if(isset($produks) && $produks->isNotEmpty())
                  @foreach($produks as $p)
                  {
                      id: {{ $p->id }},
                      nama: {{ json_encode($p->nama) }},
                      fullName: {{ json_encode($p->nama) }},
                      kategori: {{ json_encode($p->kategoriRelasi?->nama_kategori ?? $p->kategori ?? 'Koleksi Batik') }},
                      subkategori: {{ json_encode(strtoupper($p->kategoriRelasi?->nama_kategori ?? $p->kategori ?? 'BATIK')) }},
                      badge: {{ json_encode($p->stok > 0 ? ($p->stok <= 10 ? 'Stok Terbatas' : 'Ready Stock') : 'Stok Habis') }},
                      badgeColor: {{ json_encode($p->stok <= 0 ? 'bg-stone-500 text-white' : ($p->stok <= 10 ? 'bg-[#4A2D1B] text-white' : 'bg-white/95 text-stone-800 border border-stone-200')) }},
                      harga: {{ (float) $p->harga }},
                      hargaFormatted: {{ json_encode('Rp ' . number_format($p->harga, 0, ',', '.')) }},
                      rating: '{{ $p->avg_rating > 0 ? number_format($p->avg_rating, 1) : '5.0' }}',
                      reviews: {{ $p->ulasans()->where('status', 'Disetujui')->count() }},
                      detailUrl: '{{ route('produk.detail', $p->id) }}',
                      gambar: '{{ $p->gambar_url }}'
                  }@if(!$loop->last),@endif
                  @endforeach
              @endif
          ],

          get filteredProducts() {
              return this.products.filter(item => {
                  const matchCategory = this.activeCategory === 'Semua Produk' || item.kategori === this.activeCategory;
                  const q = this.searchQuery.toLowerCase().trim();
                  const matchSearch = !q || item.nama.toLowerCase().includes(q) || item.kategori.toLowerCase().includes(q);
                  let matchPrice = true;
                  if (this.priceRange === 'Dibawah 200rb') {
                      matchPrice = item.harga < 200000;
                  } else if (this.priceRange === '200rb - 400rb') {
                      matchPrice = item.harga >= 200000 && item.harga <= 400000;
                  } else if (this.priceRange === 'Diatas 400rb') {
                      matchPrice = item.harga > 400000;
                  }
                  return matchCategory && matchSearch && matchPrice;
              }).sort((a, b) => {
                  if (this.sortBy === 'termurah') return a.harga - b.harga;
                  if (this.sortBy === 'termahal') return b.harga - a.harga;
                  if (this.sortBy === 'rating') return parseFloat(b.rating) - parseFloat(a.rating);
                  return a.id - b.id;
              });
          },

          get totalPages() {
              return Math.ceil(this.filteredProducts.length / this.perPage) || 1;
          },

          get pagedProducts() {
              const start = (this.currentPage - 1) * this.perPage;
              return this.filteredProducts.slice(start, start + this.perPage);
          },

          resetPage() { this.currentPage = 1; },
          goToPage(p) { if (p >= 1 && p <= this.totalPages) this.currentPage = p; }
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
    <header class="sticky top-0 z-40 bg-[#FAF7F2] border-b border-[#ECE4D8] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-xs group-hover:bg-stone-800 transition">
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
                    <button @click="$refs.searchInput ? $refs.searchInput.focus() : null" 
                            class="p-2 text-stone-700 hover:text-stone-950 rounded-full hover:bg-stone-200/50 transition"
                            title="Cari Produk">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </button>

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
                                    <div class="border-t border-stone-100 my-1"></div>
                                    <a href="{{ route('pesanan.index') }}" class="flex items-center gap-2 px-3 py-2 text-xs text-stone-600 hover:text-stone-900">
                                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Cek Status Pesanan
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
            <div x-cloak x-show="mobileMenuOpen" class="md:hidden py-4 border-t border-[#ECE4D8] space-y-2">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Home</a>
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-900 bg-stone-200/50">Produk</a>
                <a href="{{ url('/#koleksi') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kategori</a>
                <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Pesanan</a>
                <a href="{{ url('/#tentang-kami') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Tentang Kami</a>
                <a href="#kontak" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kontak</a>
            </div>
        </div>
    </header>

    <!-- ================= MAIN CATALOG CONTENT ================= -->
    <main class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-3">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">KOLEKSI</a>
                <span>/</span>
                <span class="text-stone-900 font-extrabold">SEMUA PRODUK</span>
            </nav>

            <!-- Page Title & Subtitle -->
            <div class="mb-6">
                <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-stone-900 leading-tight">
                    Semua Produk
                </h1>
                <p class="text-stone-600 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Jelajahi keanggunan busana batik autentik dan ragam kriya sirkular olahan kain sisa bernilai tinggi.
                </p>
            </div>

            <!-- Filter & Search Controls Bar -->
            <div class="space-y-3 mb-6">
                <!-- Top Row: Search input + Rentang Harga + Urutkan -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                    
                    <!-- Search Input Box -->
                    <div class="relative flex-1">
                        <div class="absolute left-4 top-3 text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               x-ref="searchInput"
                               x-model="searchQuery" 
                               placeholder="Cari busana, jenis motif, atau olahan kriya..."
                               class="w-full bg-[#FAF4EC] border border-[#EFE8DE] rounded-xl pl-11 pr-10 py-2.5 text-xs sm:text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:bg-white transition">
                        <button x-show="searchQuery" 
                                @click="searchQuery = ''; resetPage()" 
                                class="absolute right-3.5 top-3 text-stone-400 hover:text-stone-600">
                            &times;
                        </button>
                    </div>

                    <!-- Filter 1: Rentang Harga Dropdown -->
                    <div class="relative" x-data="{ priceOpen: false }">
                        <button @click="priceOpen = !priceOpen" 
                                @click.away="priceOpen = false"
                                class="w-full md:w-auto inline-flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EFE8DE] text-xs font-semibold text-stone-800 hover:border-stone-400 transition shadow-2xs">
                            <span class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M6 12h12m-9 6h6"/>
                                </svg>
                                <span x-text="priceRange === 'Semua' ? 'Rentang Harga' : priceRange"></span>
                            </span>
                            <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-cloak x-show="priceOpen" 
                             class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-xl border border-stone-200 py-1.5 z-30 text-xs">
                            <button @click="priceRange = 'Semua'; priceOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Semua Harga</button>
                            <button @click="priceRange = 'Dibawah 200rb'; priceOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">&lt; Rp 200.000</button>
                            <button @click="priceRange = '200rb - 400rb'; priceOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Rp 200rb - Rp 400rb</button>
                            <button @click="priceRange = 'Diatas 400rb'; priceOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">&gt; Rp 400.000</button>
                        </div>
                    </div>

                    <!-- Filter 2: Urutkan Dropdown -->
                    <div class="relative" x-data="{ sortOpen: false }">
                        <button @click="sortOpen = !sortOpen" 
                                @click.away="sortOpen = false"
                                class="w-full md:w-auto inline-flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EFE8DE] text-xs font-semibold text-stone-800 hover:border-stone-400 transition shadow-2xs">
                            <span class="flex items-center gap-1.5">
                                <span class="text-stone-400 font-normal">Urutkan:</span>
                                <span x-text="sortBy === 'paling-sesuai' ? 'Paling Sesuai' : (sortBy === 'termurah' ? 'Harga: Terendah' : (sortBy === 'termahal' ? 'Harga: Tertinggi' : 'Rating'))"></span>
                            </span>
                            <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-cloak x-show="sortOpen" 
                             class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-xl border border-stone-200 py-1.5 z-30 text-xs">
                            <button @click="sortBy = 'paling-sesuai'; sortOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Paling Sesuai</button>
                            <button @click="sortBy = 'termurah'; sortOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Harga: Terendah</button>
                            <button @click="sortBy = 'termahal'; sortOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Harga: Tertinggi</button>
                            <button @click="sortBy = 'rating'; sortOpen = false; resetPage()" class="w-full text-left px-3 py-1.5 hover:bg-stone-50 text-stone-700">Rating Tertinggi</button>
                        </div>
                    </div>

                </div>

                <!-- Bottom Row: Category Pills & Products Count -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    
                    <!-- Category Pills -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button @click="activeCategory = 'Semua Produk'; resetPage()" 
                                :class="activeCategory === 'Semua Produk' ? 'bg-[#201A17] text-white shadow-xs' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                            Semua Produk
                        </button>
                        @if(isset($kategoris) && $kategoris->isNotEmpty())
                            @foreach($kategoris as $kat)
                                <button @click="activeCategory = '{{ addslashes($kat->nama_kategori) }}'; resetPage()" 
                                        :class="activeCategory === '{{ addslashes($kat->nama_kategori) }}' ? 'bg-[#201A17] text-white shadow-xs' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                                    {{ $kat->nama_kategori }} ({{ $kat->produks_count }})
                                </button>
                            @endforeach
                        @else
                            <button @click="activeCategory = 'Baju Batik'; resetPage()" 
                                    :class="activeCategory === 'Baju Batik' ? 'bg-[#201A17] text-white shadow-xs' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                                Baju Batik
                            </button>
                            <button @click="activeCategory = 'Kain Batik'; resetPage()" 
                                    :class="activeCategory === 'Kain Batik' ? 'bg-[#201A17] text-white shadow-xs' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                                Kain Batik
                            </button>
                            <button @click="activeCategory = 'Olahan Kain Sisa'; resetPage()" 
                                    :class="activeCategory === 'Olahan Kain Sisa' ? 'bg-[#201A17] text-white shadow-xs' : 'bg-white text-stone-700 border border-stone-300 hover:border-stone-400'"
                                    class="px-4 py-1.5 rounded-full text-xs font-semibold transition">
                                Olahan Kain Sisa
                            </button>
                        @endif
                    </div>

                    <!-- Count Text -->
                    <div class="text-xs text-stone-500 font-medium">
                        Menampilkan <span class="font-bold text-stone-800" x-text="pagedProducts.length"></span> dari <span class="font-bold text-stone-800" x-text="filteredProducts.length"></span> produk
                    </div>

                </div>
            </div>

            <!-- ================= PRODUCT GRID (4 COLUMNS X 3 ROWS = 12 ITEMS) ================= -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <template x-for="item in pagedProducts" :key="item.id">
                    <div class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-2xs hover:shadow-md transition-all flex flex-col group">
                        
                        <!-- Image Container with badges -->
                        <a :href="item.detailUrl" class="relative aspect-square overflow-hidden bg-stone-100 block">
                            <!-- Top-left Stock/Edition Badge -->
                            <span class="absolute top-3 left-3 z-10 text-[9px] font-bold px-2 py-0.5 rounded shadow-xs"
                                  :class="item.badgeColor"
                                  x-text="item.badge">
                            </span>

                            <!-- Secondary bottom-left badge if exists (e.g. +4 Klasik) -->
                            <span x-show="item.extraBadge" 
                                  class="absolute bottom-3 left-3 z-10 text-[9px] font-bold px-2 py-0.5 rounded bg-white/90 text-stone-800 shadow-xs"
                                  x-text="item.extraBadge">
                            </span>

                            <img :src="item.gambar" 
                                 :alt="item.nama" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </a>

                        <!-- Card Body -->
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Category & Rating Row -->
                                <div class="flex items-center justify-between text-[10px] mb-1">
                                    <span class="font-bold tracking-wider uppercase text-stone-400" x-text="item.subkategori"></span>
                                    <div class="flex items-center gap-1 font-semibold text-stone-700">
                                        <span class="text-amber-500 text-xs">★</span>
                                        <span x-text="item.rating"></span>
                                        <span class="text-stone-400 font-normal" x-text="'(' + item.reviews + ')'"></span>
                                    </div>
                                </div>

                                <!-- Product Title -->
                                <h3 class="font-bold text-stone-900 text-sm leading-snug line-clamp-1 mb-1.5">
                                    <a :href="item.detailUrl" x-text="item.nama" :title="item.fullName"></a>
                                </h3>

                                <!-- Price -->
                                <p class="text-sm sm:text-base font-extrabold text-stone-900 mb-3" x-text="item.hargaFormatted"></p>
                            </div>

                            <!-- Action Buttons Row (Lihat Produk + Secondary button) -->
                            <div class="flex items-center gap-2 pt-1 border-t border-stone-100">
                                <a :href="item.detailUrl" 
                                   class="flex-1 py-2 px-3 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white text-xs font-semibold transition text-center shadow-2xs">
                                    Lihat Produk
                                </a>
                                <button @click="addToCart(item)" 
                                        class="w-8 h-8 rounded-xl border border-stone-200 hover:bg-stone-100 flex items-center justify-center text-stone-700 transition"
                                        title="Tambah ke Keranjang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </template>

            </div>

            <!-- Empty Search State -->
            <div x-cloak x-show="filteredProducts.length === 0" class="text-center py-16 bg-white rounded-2xl border border-stone-200 mt-6">
                <h3 class="font-bold text-lg text-stone-800">Tidak ada produk yang cocok</h3>
                <p class="text-stone-500 text-xs sm:text-sm mt-1">Coba kata kunci lain atau reset filter Anda.</p>
                <button @click="searchQuery = ''; activeCategory = 'Semua Produk'; priceRange = 'Semua'; resetPage()" 
                        class="mt-4 px-5 py-2 rounded-xl bg-[#201A17] text-white text-xs font-semibold">
                    Reset Filter
                </button>
            </div>

            <!-- ================= PAGINATION CONTROLS ================= -->
            <div class="mt-12 pt-6 border-t border-[#EDE6DB] flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Info count -->
                <div class="text-xs text-stone-500 font-medium">
                    Menampilkan halaman <span class="font-bold text-stone-800" x-text="currentPage"></span> dari <span class="font-bold text-stone-800" x-text="totalPages"></span> (Total <span class="font-bold text-stone-800" x-text="filteredProducts.length"></span> Produk)
                </div>

                <!-- Page navigation -->
                <div class="flex items-center gap-1.5 text-xs font-semibold text-stone-700">
                    <!-- Sebelumnya -->
                    <button @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'text-stone-300 border-[#EDE6DB] cursor-not-allowed' : 'hover:border-stone-400 text-stone-700'"
                            class="px-3.5 py-2 rounded-xl bg-white border border-[#EDE6DB] transition">
                        &lt; Sebelumnya
                    </button>

                    <template x-for="p in totalPages" :key="p">
                        <button @click="goToPage(p)"
                                :class="currentPage === p ? 'bg-[#201A17] text-white' : 'bg-white border border-[#EDE6DB] text-stone-700 hover:border-stone-400'"
                                class="w-9 h-9 rounded-xl flex items-center justify-center transition"
                                x-text="p">
                        </button>
                    </template>

                    <!-- Selanjutnya -->
                    <button @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'text-stone-300 border-[#EDE6DB] cursor-not-allowed' : 'hover:border-stone-400 text-stone-700'"
                            class="px-3.5 py-2 rounded-xl bg-white border border-[#EDE6DB] transition">
                        Selanjutnya &gt;
                    </button>
                </div>
            </div>

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
                        <li><a href="{{ url('/#koleksi') }}" class="hover:text-stone-950 transition">Kategori</a></li>
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

</body>
</html>
