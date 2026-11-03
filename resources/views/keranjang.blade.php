<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang Belanja — HAMZAH STYLE OFFICIAL</title>
    <meta name="description" content="Keranjang belanja pesanan busana batik autentik dan aksesoris sirkular Hamzah Style Official.">

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
            color: #241E1A;
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#241E1A] min-h-screen selection:bg-[#B58742] selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          toastMessage: '',
          
          items: [
              @if(isset($cartItems) && $cartItems->isNotEmpty())
                  @foreach($cartItems as $ci)
                  {
                      id: {{ $ci->id }},
                      produkId: {{ $ci->produk_id }},
                      selected: {{ $ci->selected ? 'true' : 'false' }},
                      nama: {{ json_encode($ci->produk->nama ?? 'Produk Batik') }},
                      kategoriBadge: {{ json_encode(strtoupper($ci->produk->kategoriRelasi?->nama_kategori ?? $ci->produk->kategori ?? 'BATIK')) }},
                      kategoriSub: {{ json_encode(strtoupper($ci->produk->kategoriRelasi?->nama_kategori ?? $ci->produk->kategori ?? 'BATIK')) }},
                      varian: {{ json_encode($ci->varian ?: ($ci->ukuran ? 'Ukuran: ' . $ci->ukuran : 'Standar')) }},
                      harga: {{ (float) ($ci->produk->harga ?? 0) }},
                      qty: {{ (int) $ci->qty }},
                      gambar: {{ json_encode($ci->produk ? $ci->produk->gambar_url : asset('images/beranda/folded-shirts.jpg')) }}
                  }@if(!$loop->last),@endif
                  @endforeach
              @endif
          ],

          recommendations: [
              @if(isset($produksRekomendasi) && $produksRekomendasi->isNotEmpty())
                  @foreach($produksRekomendasi as $pr)
                  {
                      id: {{ $pr->id }},
                      nama: {{ json_encode($pr->nama) }},
                      badge: {{ json_encode(strtoupper($pr->kategoriRelasi?->nama_kategori ?? $pr->kategori ?? 'BATIK')) }},
                      deskripsi: {{ json_encode(mb_strimwidth($pr->deskripsi ?: 'Koleksi Batik Berkualitas', 0, 40, '...')) }},
                      harga: {{ (float) $pr->harga }},
                      detailUrl: '{{ route('produk.detail', $pr->id) }}',
                      gambar: {{ json_encode($pr->gambar_url) }}
                  }@if(!$loop->last),@endif
                  @endforeach
              @endif
          ],

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },

          formatRupiah(num) {
              return 'Rp ' . Number(num).toLocaleString('id-ID');
          },

          increaseQty(item) {
              const newQty = item.qty + 1;
              item.qty = newQty;
              fetch('/keranjang/' + item.id, {
                  method: 'PATCH',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  credentials: 'same-origin',
                  body: JSON.stringify({ qty: newQty })
              });
          },

          decreaseQty(item) {
              if (item.qty > 1) {
                  const newQty = item.qty - 1;
                  item.qty = newQty;
                  fetch('/keranjang/' + item.id, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      credentials: 'same-origin',
                      body: JSON.stringify({ qty: newQty })
                  });
              } else {
                  this.removeItem(item.id);
              }
          },

          removeItem(id) {
              const item = this.items.find(i => i.id === id);
              this.items = this.items.filter(i => i.id !== id);
              fetch('/keranjang/' + id, {
                  method: 'DELETE',
                  headers: {
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  credentials: 'same-origin'
              });
              this.showToast((item ? item.nama : 'Produk') + ' dihapus dari keranjang');
          },

          removeSelected() {
              const toRemove = this.items.filter(i => i.selected);
              if (toRemove.length === 0) {
                  this.showToast('Pilih produk yang ingin dihapus terlebih dahulu');
                  return;
              }
              toRemove.forEach(i => {
                  fetch('/keranjang/' + i.id, {
                      method: 'DELETE',
                      headers: {
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      credentials: 'same-origin'
                  });
              });
              this.items = this.items.filter(i => !i.selected);
              this.showToast(toRemove.length + ' produk terpilih berhasil dihapus');
          },

          toggleItem(item) {
              fetch('/keranjang/' + item.id, {
                  method: 'PATCH',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  credentials: 'same-origin',
                  body: JSON.stringify({ selected: item.selected })
              });
          },

          get allSelected() {
              return this.items.length > 0 && this.items.every(i => i.selected);
          },

          toggleSelectAll() {
              const newVal = !this.allSelected;
              this.items.forEach(i => {
                  i.selected = newVal;
                  fetch('/keranjang/' + i.id, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ selected: newVal })
                  });
              });
          },

          get selectedItems() {
              return this.items.filter(i => i.selected);
          },

          get totalSelectedQty() {
              return this.selectedItems.reduce((acc, curr) => acc + curr.qty, 0);
          },

          get totalCartQty() {
              return this.items.reduce((acc, curr) => acc + curr.qty, 0);
          },

          get rawSubtotal() {
              return this.selectedItems.reduce((acc, curr) => acc + (curr.harga * curr.qty), 0);
          },

          get shippingCost() {
              return this.selectedItems.length > 0 ? 20000 : 0;
          },

          get grandTotal() {
              if (this.selectedItems.length === 0) return 0;
              const calculated = this.rawSubtotal + this.shippingCost - (this.rawSubtotal >= 900000 ? 50000 : 0);
              return Math.max(0, calculated);
          },

          addRecommendation(prod) {
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
                      window.location.reload();
                  } else {
                      this.showToast(data.message || 'Gagal menambahkan produk');
                  }
              })
              .catch(() => {
                  window.location.reload();
              });
          },

          proceedCheckout() {
              if (this.totalSelectedQty === 0) {
                  this.showToast('Pilih minimal satu produk untuk melanjutkan checkout');
                  return;
              }
              window.location.href = '{{ route('checkout.index') }}';
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
                    <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
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
                       class="relative p-2 text-stone-900 rounded-full hover:bg-stone-200/50 transition"
                       title="Keranjang Belanja">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-[#B58742] text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                              x-text="items.length">
                            {{ count($cartItems ?? [1,2,3]) }}
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
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-stone-700 hover:text-stone-900 rounded-lg hover:bg-stone-200/50">
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
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Produk</a>
                <a href="{{ url('/#koleksi') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kategori</a>
                <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Pesanan</a>
                <a href="{{ url('/#tentang-kami') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Tentang Kami</a>
                <a href="#kontak" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900">Kontak</a>
            </div>
        </div>
    </header>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs / Checkout Steps (Exact Screenshot Style) -->
            <nav class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-6">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">BERANDA</a>
                <span>&gt;</span>
                <span class="text-stone-900 font-extrabold">KERANJANG BELANJA</span>
                <span>&gt;</span>
                <span class="text-stone-400 font-medium">PENGIRIMAN</span>
                <span>&gt;</span>
                <span class="text-stone-400 font-medium">PEMBAYARAN</span>
            </nav>

            <!-- Page Title Row -->
            <div class="mb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EFE8DD] border border-[#DFCDBB] text-stone-800 text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-700"></span>
                    <span>KOLEKSI PILIHAN ANDA</span>
                </div>
                <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-stone-900">
                    Keranjang Belanja <span class="text-stone-500 font-normal text-2xl sm:text-3xl" x-text="'(' + items.length + ' Item)'"></span>
                </h1>
            </div>

            <!-- Main Layout: 2 Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Items List & Actions (8 Cols) -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- Empty Cart State -->
                    <div x-cloak x-show="items.length === 0" class="bg-white rounded-2xl p-12 border border-[#EDE6DB] text-center shadow-2xs">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center text-stone-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="font-serif-title text-xl font-bold text-stone-900">Keranjang Belanja Anda Kosong</h3>
                        <p class="text-xs sm:text-sm text-stone-500 mt-2 max-w-md mx-auto">Anda belum menambahkan produk ke keranjang belanja. Jelajahi busana batik autentik dan kriya olahan kain sisa kami.</p>
                        <a href="{{ route('koleksi.index') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#201A17] text-white text-xs font-bold hover:bg-stone-800 transition shadow-sm">
                            <span>Mulai Belanja</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div x-show="items.length > 0" class="space-y-4">
                    <!-- Table Header Row: Select All & Delete Selected -->
                    <div class="flex items-center justify-between pb-3 text-xs font-semibold text-stone-700">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" 
                                   :checked="allSelected" 
                                   @change="toggleSelectAll()"
                                   class="w-4 h-4 rounded text-stone-900 focus:ring-stone-900 border-stone-300">
                            <span class="text-xs font-bold text-stone-800">
                                Pilih Semua (<span x-text="items.length"></span> Item)
                            </span>
                        </label>

                        <button @click="removeSelected()" 
                                class="inline-flex items-center gap-1.5 text-stone-500 hover:text-rose-600 transition text-xs font-medium">
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            <span>Hapus Produk Terpilih</span>
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-4">
                        <template x-for="item in items" :key="item.id">
                            <div class="bg-white rounded-2xl p-5 border border-[#EDE6DB] shadow-2xs hover:border-stone-400 transition-all flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                
                                <!-- Checkbox -->
                                <div class="shrink-0 pt-1 sm:pt-0">
                                    <input type="checkbox" 
                                           x-model="item.selected" @change="toggleItem(item)"
                                           class="w-4 h-4 rounded text-stone-900 focus:ring-stone-900 border-stone-300">
                                </div>

                                <!-- Product Image with badge in top-left -->
                                <div class="relative w-24 h-24 rounded-xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                    <span class="absolute top-1.5 left-1.5 text-[8px] font-extrabold px-1.5 py-0.5 rounded bg-[#FAF7F2]/90 backdrop-blur-sm text-stone-800 uppercase tracking-wider"
                                          x-text="item.kategoriBadge">
                                    </span>
                                    <img :src="item.gambar" :alt="item.nama" class="w-full h-full object-cover">
                                </div>

                                <!-- Info & Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-stone-400 block" 
                                                  x-text="item.kategoriSub"></span>
                                            <h3 class="font-bold text-stone-900 text-sm sm:text-base leading-snug mt-0.5" 
                                                x-text="item.nama"></h3>
                                            <div class="inline-flex items-center gap-1.5 mt-1 text-xs text-stone-500 font-medium">
                                                <span x-text="item.varian"></span>
                                            </div>
                                        </div>

                                        <!-- Trash Button -->
                                        <button @click="removeItem(item.id)" 
                                                class="text-stone-400 hover:text-rose-600 p-1 rounded-lg hover:bg-stone-50 transition" 
                                                title="Hapus Barang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Bottom Row: Harga Satuan, Stepper, Subtotal -->
                                    <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t border-stone-100">
                                        <div>
                                            <span class="text-[10px] text-stone-400 block font-medium">Harga Satuan</span>
                                            <span class="text-xs sm:text-sm font-bold text-stone-800" x-text="formatRupiah(item.harga)"></span>
                                        </div>

                                        <!-- Stepper -->
                                        <div class="inline-flex items-center rounded-xl bg-stone-50 border border-stone-200 p-0.5">
                                            <button @click="decreaseQty(item)" 
                                                    class="w-7 h-7 rounded-lg hover:bg-white flex items-center justify-center text-stone-600 font-bold transition text-xs">
                                                -
                                            </button>
                                            <span class="w-8 text-center text-xs font-bold text-stone-900" x-text="item.qty"></span>
                                            <button @click="increaseQty(item)" 
                                                    class="w-7 h-7 rounded-lg hover:bg-white flex items-center justify-center text-stone-600 font-bold transition text-xs">
                                                +
                                            </button>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[10px] text-stone-400 block font-medium">Subtotal</span>
                                            <span class="text-sm sm:text-base font-extrabold text-stone-900" x-text="formatRupiah(item.harga * item.qty)"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>

                <!-- Right Column: Order Summary (4 Cols) -->
                <div class="lg:col-span-4 sticky top-28 space-y-5">
                    
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-xs space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <h2 class="font-bold text-stone-900 text-base">
                                Ringkasan Belanja
                            </h2>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-stone-100 text-stone-600" 
                                  x-text="totalSelectedQty + ' Barang'">
                            </span>
                        </div>

                        <!-- Rows Breakdown -->
                        <div class="space-y-3 text-xs sm:text-sm text-stone-600">
                            <div class="flex items-center justify-between">
                                <span x-text="'Total Harga (' + totalSelectedQty + ' barang)'"></span>
                                <span class="font-semibold text-stone-900" x-text="formatRupiah(rawSubtotal)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <span>Estimasi Ongkos Kirim</span>
                                    <span class="text-stone-400 text-xs cursor-pointer" title="Ongkir flat promo nasional">ⓘ</span>
                                </span>
                                <span class="font-semibold text-stone-900" x-text="formatRupiah(shippingCost)"></span>
                            </div>
                        </div>

                        <!-- Divider & Total Tagihan -->
                        <div class="pt-4 border-t border-stone-100">
                            <div class="flex items-baseline justify-between">
                                <span class="text-sm font-bold text-stone-900">Total Tagihan</span>
                                <span class="font-serif-title text-2xl sm:text-3xl font-extrabold text-stone-900" 
                                      x-text="formatRupiah(grandTotal)"></span>
                            </div>
                            <p class="text-[10px] text-stone-500 mt-1">
                                Termasuk PPN & Kemasan Ramah Lingkungan
                            </p>
                        </div>

                        <!-- Main Checkout CTA -->
                        <button @click="proceedCheckout()" 
                                class="w-full py-3.5 px-6 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white font-bold text-sm transition duration-200 shadow-sm flex items-center justify-center gap-2 group">
                            <span x-text="'Checkout (' + totalSelectedQty + ' Item)'"></span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </button>

                        <!-- Trust Badges List -->
                        <div class="pt-4 border-t border-stone-100 space-y-3.5 text-xs text-stone-600">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-stone-100 text-stone-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-[11px]">Jaminan Transaksi Aman</strong>
                                    <span class="text-[10px] text-stone-500">Enkripsi 256-bit dan escrow terlindungi</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-stone-100 text-stone-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-[11px]">100% Produk Autentik</strong>
                                    <span class="text-[10px] text-stone-500">Batik cap, tulis, & olahan kriya bergaransi</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-stone-100 text-stone-700 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-[11px]">Dukungan WhatsApp 24 Jam</strong>
                                    <span class="text-[10px] text-stone-500">Bantuan personal dan konsultasi ukuran busana</span>
                                </div>
                            </div>
                        </div>

                        <!-- Official Payment Logos -->
                        <div class="pt-4 border-t border-stone-100 text-center">
                            <span class="text-[10px] text-stone-400 uppercase tracking-widest block mb-2 font-semibold">
                                Metode Pembayaran Resmi
                            </span>
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded bg-stone-100 text-[10px] font-bold text-stone-700 border border-stone-200">BCA VA</span>
                                <span class="px-2 py-0.5 rounded bg-stone-100 text-[10px] font-bold text-stone-700 border border-stone-200">Mandiri</span>
                                <span class="px-2 py-0.5 rounded bg-stone-100 text-[10px] font-bold text-stone-700 border border-stone-200">QRIS</span>
                                <span class="px-2 py-0.5 rounded bg-stone-100 text-[10px] font-bold text-stone-700 border border-stone-200">ShopeePay</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ================= CROSS-SELL SECTION: LENGKAPI PENAMPILAN ANDA ================= -->
            <section class="mt-20 pt-12 border-t border-[#EDE6DB]">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-stone-400 block mb-1">
                            HARMONI BUSANA
                        </span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-stone-900">
                            Lengkapi Penampilan Anda
                        </h2>
                    </div>

                    <!-- Navigation arrows -->
                    <div class="flex items-center gap-2">
                        <button @click="showToast('Rekomendasi busana sebelumnya')" 
                                class="w-8 h-8 rounded-lg border border-stone-300 hover:border-stone-500 bg-white flex items-center justify-center text-stone-700 transition">
                            &lt;
                        </button>
                        <button @click="showToast('Rekomendasi busana berikutnya')" 
                                class="w-8 h-8 rounded-lg border border-stone-300 hover:border-stone-500 bg-white flex items-center justify-center text-stone-700 transition">
                            &gt;
                        </button>
                    </div>
                </div>

                <!-- 4 Product Cards Grid matching the mockup -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <template x-for="(prod, idx) in recommendations" :key="idx">
                        <div class="bg-white rounded-2xl overflow-hidden border border-[#EDE6DB] shadow-2xs hover:shadow-md transition flex flex-col group">
                            
                            <!-- Image with badge -->
                            <div class="relative aspect-square overflow-hidden bg-stone-100">
                                <span class="absolute top-3 left-3 z-10 text-[9px] font-bold px-2 py-0.5 rounded bg-white/90 backdrop-blur-sm text-stone-800 uppercase tracking-wider"
                                      x-text="prod.badge"></span>
                                <img :src="prod.gambar" :alt="prod.nama" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>

                            <!-- Content -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-stone-900 text-sm leading-snug line-clamp-1 mb-1" x-text="prod.nama"></h3>
                                    <p class="text-[11px] text-stone-500 mb-3" x-text="prod.deskripsi"></p>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                                    <span class="text-sm font-extrabold text-stone-900" x-text="formatRupiah(prod.harga)"></span>
                                    
                                    <button @click="addRecommendation(prod)" 
                                            class="w-7 h-7 rounded-lg bg-stone-100 hover:bg-[#201A17] hover:text-white text-stone-800 flex items-center justify-center font-bold text-sm transition shadow-2xs"
                                            title="Tambahkan ke Keranjang">
                                        +
                                    </button>
                                </div>
                            </div>

                        </div>
                    </template>
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
                            <svg class="w-4 h-4 text-[#E5C38E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="3" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-stone-900 uppercase text-sm">
                            HAMZAH STYLE
                        </span>
                    </div>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-6 max-w-sm">
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
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 transition">Produk</a></li>
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
                            <a href="https://wa.me/6281234567890" target="_blank" class="hover:text-stone-950 transition flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                WhatsApp Business
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-stone-950 transition flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                                Shopee Official
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-stone-950 transition flex items-center gap-2">
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
