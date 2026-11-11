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
    <x-storefront-navbar />

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
    <x-storefront-footer />

</body>
</html>
