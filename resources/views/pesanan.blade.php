<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Saya - Hamzah Style Official</title>
    <meta name="description" content="Pantau status transaksi, resi pengiriman, dan riwayat pesanan busana batik autentik Hamzah Style Official.">

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
          searchQuery: '',
          activeTab: 'semua',
          toastMessage: '',
          trackingModalOpen: false,
          detailModalOpen: false,
          reviewModalOpen: false,
          selectedOrder: null,
          reviewOrder: null,
          reviewDrafts: {},
          cartCount: {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }},

          orders: [
              @if(isset($orders) && $orders->isNotEmpty())
                  @foreach($orders as $ord)
                  @php
                      $statusRaw = strtolower(trim($ord->status));
                      $statusKey = match($statusRaw) {
                          'belum dibayar', 'menunggu pembayaran' => 'menunggu_pembayaran',
                          'menunggu verifikasi' => 'diproses',
                          'diproses', 'sedang diproses' => 'diproses',
                          'dikirim', 'sedang dikirim' => 'dikirim',
                          'selesai' => 'selesai',
                          'dibatalkan' => 'dibatalkan',
                          default => 'menunggu_pembayaran'
                      };
                      $badgeClass = match($statusRaw) {
                          'menunggu verifikasi' => 'bg-amber-50 text-amber-800 border-amber-300',
                          default => match($statusKey) {
                              'menunggu_pembayaran' => 'bg-amber-100 text-amber-900 border-amber-300',
                              'diproses' => 'bg-blue-100 text-blue-900 border-blue-300',
                              'dikirim' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                              'selesai' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                              'dibatalkan' => 'bg-rose-100 text-rose-900 border-rose-300',
                              default => 'bg-stone-100 text-stone-900 border-stone-300'
                          }
                      };
                      $statusLabel = match($statusRaw) {
                          'menunggu verifikasi' => 'Menunggu Verifikasi',
                          default => match($statusKey) {
                              'menunggu_pembayaran' => 'Menunggu Pembayaran',
                              'diproses' => 'Sedang Diproses',
                              'dikirim' => 'Sedang Dikirim',
                              'selesai' => 'Selesai',
                              'dibatalkan' => 'Dibatalkan',
                              default => $ord->status
                          }
                      };
                      $trackingNo = $ord->tracking_number ?: 'HS-RESI-' . substr(md5($ord->id), 0, 8);
                  @endphp
                  {
                      id: '{{ $ord->code }}',
                      tanggal: '{{ $ord->created_at->format('d M Y, H:i') }} WIB',
                      status: '{{ $statusKey }}',
                      statusLabel: '{{ $statusLabel }}',
                      statusBadgeClass: '{{ $badgeClass }}',
                      metodePembayaran: '{{ addslashes($ord->payment_method ?? 'Transfer Bank BCA') }}',
                      batasBayar: '{{ $ord->created_at->addDay()->format('d M Y, H:i') }} WIB',
                      kurir: 'JNE Regular Express',
                      noResi: '{{ $trackingNo }}',
                      ongkir: 20000,
                      diskon: 0,
                      subtotal: {{ (float) $ord->total_price }},
                      total: {{ (float) $ord->total_price }},
                      alamat: '{{ addslashes($ord->customer_name) }} ({{ addslashes($ord->phone) }}) • {{ addslashes($ord->address) }}',
                      items: [
                          @foreach($ord->items as $it)
                          {
                              produk_id: {{ $it->produk_id }},
                              nama: '{{ addslashes($it->produk_name) }}',
                              kategori: 'BATIK AUTENTIK',
                              varian: 'Kuantitas: {{ $it->quantity }} pcs',
                              harga: {{ (float) $it->price }},
                              qty: {{ $it->quantity }},
                              gambar: '{{ $it->produk && $it->produk->gambar ? asset('storage/' . $it->produk->gambar) : asset('images/beranda/folded-shirts.jpg') }}'
                          }@if(!$loop->last),@endif
                          @endforeach
                      ]
                  }@if(!$loop->last),@endif
                  @endforeach
              @endif
          ],

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3000);
          },

          copyText(txt) {
              navigator.clipboard.writeText(txt);
              this.showToast('Berhasil disalin ke clipboard: ' + txt);
          },

          formatRupiah(num) {
              return 'Rp ' + Number(num).toLocaleString('id-ID');
          },

          openTracking(order) {
              this.selectedOrder = order;
              this.trackingModalOpen = true;
          },

          openDetail(order) {
              this.selectedOrder = order;
              this.detailModalOpen = true;
          },

          openReview(order) {
              this.reviewOrder = order;
              this.reviewDrafts = {};
              order.items.forEach((item, idx) => {
                  this.reviewDrafts[idx] = { rating: 5, comment: '', sending: false, done: false };
              });
              this.reviewModalOpen = true;
          },

          submitItemReview(order, idx) {
              const item = order.items[idx];
              const draft = this.reviewDrafts[idx];
              if (!draft || draft.sending || draft.done) return;
              if (!draft.comment || draft.comment.trim().length < 3) {
                  this.showToast('Tulis ulasan minimal 3 karakter.');
                  return;
              }
              draft.sending = true;
              fetch('{{ route('ulasan.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      produk_id: item.produk_id,
                      order_code: order.id,
                      rating: draft.rating,
                      comment: draft.comment.trim()
                  })
              })
              .then(async res => {
                  const data = await res.json().catch(() => ({}));
                  draft.sending = false;
                  if (res.ok && data.success) {
                      draft.done = true;
                      this.showToast('Terima kasih! Ulasan untuk ' + item.nama + ' tersimpan.');
                  } else {
                      this.showToast(data.message || 'Ulasan gagal dikirim.');
                  }
              })
              .catch(() => {
                  draft.sending = false;
                  this.showToast('Gagal menghubungi server.');
              });
          },

          confirmReceived(order) {
              order.status = 'selesai';
              order.statusLabel = 'Selesai';
              order.statusBadgeClass = 'bg-emerald-100 text-emerald-900 border-emerald-300';
              this.showToast('Terima kasih! Pesanan ' + order.id + ' telah ditandai Selesai.');
          },

          cancelOrder(order) {
              if (confirm('Apakah Anda yakin ingin membatalkan pesanan ' + order.id + '?')) {
                  order.status = 'dibatalkan';
                  order.statusLabel = 'Dibatalkan';
                  order.statusBadgeClass = 'bg-rose-100 text-rose-900 border-rose-300';
                  this.showToast('Pesanan ' + order.id + ' telah dibatalkan.');
              }
          },

          get filteredOrders() {
              return this.orders.filter(order => {
                  const matchTab = (this.activeTab === 'semua') || (order.status === this.activeTab);
                  const q = this.searchQuery.trim().toLowerCase();
                  if (!q) return matchTab;
                  
                  const matchId = order.id.toLowerCase().includes(q);
                  const matchItem = order.items.some(it => it.nama.toLowerCase().includes(q) || it.varian.toLowerCase().includes(q));
                  return matchTab && (matchId || matchItem);
              });
          },

          get counts() {
              return {
                  semua: this.orders.length,
                  menunggu_pembayaran: this.orders.filter(o => o.status === 'menunggu_pembayaran').length,
                  diproses: this.orders.filter(o => o.status === 'diproses').length,
                  dikirim: this.orders.filter(o => o.status === 'dikirim').length,
                  selesai: this.orders.filter(o => o.status === 'selesai').length
              };
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
                    <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Home
                    </a>
                    <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Produk
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="text-sm font-semibold text-stone-900 border-b-2 border-stone-900 pb-0.5 transition">
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
                       class="relative p-2 text-stone-700 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition"
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
                <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-200/30">Produk</a>
                <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-stone-900 bg-stone-200/50">Pesanan</a>
                @guest
                    <div class="pt-2 border-t border-[#ECE4D8] flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 text-center py-2 px-4 rounded-lg bg-[#201A17] text-white font-medium text-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="flex-1 text-center py-2 px-4 rounded-lg border border-stone-300 text-stone-800 font-medium text-sm">Daftar</a>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-stone-400 mb-6">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">BERANDA</a>
                <span>/</span>
                <span class="text-stone-900 border-b border-stone-900 pb-0.5">PESANAN SAYA</span>
            </nav>

            <!-- Page Title & Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-serif-title font-bold text-stone-900 tracking-tight">
                        Pesanan Saya
                    </h1>
                    <p class="text-sm text-stone-600 mt-1 max-w-xl">
                        Pantau status transaksi, no. resi pengiriman, serta riwayat pembelian karya batik autentik dan busana sirkular Anda.
                    </p>
                </div>
                <div>
                    <a href="{{ route('koleksi.index') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-stone-800 hover:text-amber-800 transition">
                        <span>Belanja Produk Lainnya</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Search & Filters Container -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-[#ECE4D8] mb-8 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Cari berdasarkan No. Pesanan atau Nama Produk..."
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] focus:border-transparent bg-[#FAF7F2]/40 transition">
                        <button x-show="searchQuery" 
                                @click="searchQuery = ''" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-stone-400 hover:text-stone-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Quick Refresh / Help Note -->
                    <div class="flex items-center gap-3 text-xs text-stone-500 shrink-0">
                        <span class="inline-flex items-center gap-1.5 bg-[#FAF7F2] px-3 py-1.5 rounded-lg border border-[#ECE4D8]">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Data pesanan tersinkronisasi
                        </span>
                    </div>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-sm border-t border-stone-100 pt-3 scrollbar-none">
                    <button @click="activeTab = 'semua'"
                            :class="activeTab === 'semua' ? 'bg-[#201A17] text-white font-semibold shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200/70 font-medium'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
                        <span>Semua Pesanan</span>
                        <span :class="activeTab === 'semua' ? 'bg-stone-700 text-white' : 'bg-stone-200 text-stone-700'" class="text-xs px-2 py-0.5 rounded-full" x-text="counts.semua"></span>
                    </button>

                    <button @click="activeTab = 'menunggu_pembayaran'"
                            :class="activeTab === 'menunggu_pembayaran' ? 'bg-amber-800 text-white font-semibold shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200/70 font-medium'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
                        <span>Menunggu Pembayaran</span>
                        <span :class="activeTab === 'menunggu_pembayaran' ? 'bg-amber-900 text-white' : 'bg-amber-100 text-amber-800'" class="text-xs px-2 py-0.5 rounded-full" x-text="counts.menunggu_pembayaran"></span>
                    </button>

                    <button @click="activeTab = 'dikirim'"
                            :class="activeTab === 'dikirim' ? 'bg-indigo-900 text-white font-semibold shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200/70 font-medium'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
                        <span>Sedang Dikirim</span>
                        <span :class="activeTab === 'dikirim' ? 'bg-indigo-950 text-white' : 'bg-indigo-100 text-indigo-800'" class="text-xs px-2 py-0.5 rounded-full" x-text="counts.dikirim"></span>
                    </button>

                    <button @click="activeTab = 'selesai'"
                            :class="activeTab === 'selesai' ? 'bg-emerald-800 text-white font-semibold shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200/70 font-medium'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap">
                        <span>Selesai</span>
                        <span :class="activeTab === 'selesai' ? 'bg-emerald-900 text-white' : 'bg-emerald-100 text-emerald-800'" class="text-xs px-2 py-0.5 rounded-full" x-text="counts.selesai"></span>
                    </button>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-6">
                <template x-for="order in filteredOrders" :key="order.id">
                    <div class="bg-white rounded-2xl border border-[#ECE4D8] overflow-hidden shadow-xs hover:shadow-md transition">
                        
                        <!-- Order Card Header -->
                        <div class="px-5 py-4 bg-[#FAF7F2]/60 border-b border-[#ECE4D8] flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-stone-500">No. Pesanan:</span>
                                    <button @click="copyText(order.id)" 
                                            class="font-mono text-xs sm:text-sm font-bold text-stone-900 hover:text-amber-800 flex items-center gap-1 transition"
                                            title="Salin No. Pesanan">
                                        <span x-text="order.id"></span>
                                        <svg class="w-3.5 h-3.5 text-stone-400 hover:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                                <span class="text-stone-300 hidden sm:inline">•</span>
                                <div class="text-xs text-stone-500">
                                    <span x-text="order.tanggal"></span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex items-center gap-2">
                                <span :class="order.statusBadgeClass" 
                                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="order.statusLabel"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Order Card Items List -->
                        <div class="p-5 divide-y divide-stone-100">
                            <template x-for="(item, idx) in order.items" :key="idx">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center gap-4">
                                        <img :src="item.gambar" 
                                             :alt="item.nama" 
                                             class="w-20 h-20 sm:w-22 sm:h-22 rounded-xl object-cover border border-[#ECE4D8] shrink-0 bg-stone-100">
                                        <div>
                                            <div class="text-[10px] font-bold tracking-wider uppercase text-amber-800 mb-0.5" x-text="item.kategori"></div>
                                            <h3 class="font-bold text-stone-900 text-sm sm:text-base leading-snug" x-text="item.nama"></h3>
                                            <p class="text-xs text-stone-500 mt-1" x-text="item.varian"></p>
                                            <p class="text-xs text-stone-600 mt-1 font-medium sm:hidden">
                                                <span x-text="item.qty"></span> x <span x-text="formatRupiah(item.harga)"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right hidden sm:block shrink-0">
                                        <p class="text-xs text-stone-500" x-text="item.qty + ' barang'"></p>
                                        <p class="font-bold text-stone-900 text-sm" x-text="formatRupiah(item.harga * item.qty)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Order Card Footer & Context Notice -->
                        <div class="px-5 py-4 bg-[#FAF7F2]/40 border-t border-[#ECE4D8] flex flex-col md:flex-row md:items-center justify-between gap-4">
                            
                            <!-- Context info / deadline / tracking number -->
                            <div class="text-xs text-stone-600 space-y-1">
                                <template x-if="order.status === 'menunggu_pembayaran'">
                                    <div class="flex items-center gap-2 text-amber-800 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200/80">
                                        <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Batas Pembayaran: <strong x-text="order.batasBayar"></strong> (<span x-text="order.metodePembayaran"></span>)</span>
                                    </div>
                                </template>

                                <template x-if="order.status === 'dikirim'">
                                    <div class="flex items-center gap-2 text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-200/80">
                                        <svg class="w-4 h-4 shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Kurir: <strong x-text="order.kurir"></strong> • Resi: <strong class="font-mono" x-text="order.noResi"></strong></span>
                                        <button @click="copyText(order.noResi)" class="underline hover:text-indigo-950 font-medium ml-1">Salin</button>
                                    </div>
                                </template>

                                <template x-if="order.status === 'selesai'">
                                    <div class="flex items-center gap-2 text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200/80">
                                        <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Pesanan diterima • Terima kasih telah mendukung pelestarian wastra batik Nusantara</span>
                                    </div>
                                </template>

                                <p class="text-stone-500">
                                    Metode Pembayaran: <span class="font-medium text-stone-700" x-text="order.metodePembayaran"></span>
                                </p>
                            </div>

                            <!-- Total & Action Buttons -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4 shrink-0 justify-end">
                                <div class="text-left sm:text-right">
                                    <span class="text-xs text-stone-500 block">Total Belanja:</span>
                                    <span class="text-lg font-bold text-stone-900" x-text="formatRupiah(order.total)"></span>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                    <!-- Detail Modal Button (always present) -->
                                    <button @click="openDetail(order)"
                                            class="px-3.5 py-2 text-xs font-semibold text-stone-700 hover:text-stone-900 border border-stone-300 hover:border-stone-500 rounded-xl bg-white transition">
                                        Detail Pesanan
                                    </button>

                                    <!-- Status-specific primary buttons -->
                                    <template x-if="order.status === 'menunggu_pembayaran'">
                                        <div class="flex items-center gap-2">
                                            <button @click="cancelOrder(order)" 
                                                    class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-xl transition">
                                                Batalkan
                                            </button>
                                            <a :href="'{{ url('/pembayaran') }}/' + order.id" 
                                               class="px-4 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl shadow-xs transition flex items-center gap-1.5">
                                                <span>Bayar Sekarang</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        </div>
                                    </template>

                                    <template x-if="order.status === 'dikirim'">
                                        <div class="flex items-center gap-2">
                                            <button @click="openTracking(order)"
                                                    class="px-4 py-2 text-xs font-semibold text-indigo-900 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-xl transition flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>Lacak Resi</span>
                                            </button>
                                            <button @click="confirmReceived(order)"
                                                    class="px-4 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl shadow-xs transition">
                                                Pesanan Diterima
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="order.status === 'selesai'">
                                        <div class="flex items-center gap-2">
                                            <button @click="openReview(order)"
                                                    class="px-3.5 py-2 text-xs font-semibold text-stone-700 hover:text-stone-900 border border-stone-300 hover:border-stone-500 rounded-xl bg-white transition">
                                                Beri Ulasan
                                            </button>
                                            <a href="{{ route('koleksi.index') }}" 
                                               class="px-4 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl shadow-xs transition">
                                                Beli Lagi
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

                <!-- Empty State -->
                <div x-cloak x-show="filteredOrders.length === 0" 
                     class="text-center py-16 px-4 bg-white rounded-2xl border border-[#ECE4D8] shadow-xs">
                    <div class="w-16 h-16 rounded-full bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center mx-auto mb-4 text-stone-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900 mb-1">Belum Ada Pesanan yang Sesuai</h3>
                    <p class="text-sm text-stone-500 max-w-md mx-auto mb-6">
                        Tidak ada riwayat transaksi yang cocok dengan tab status atau kata kunci yang Anda masukkan.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <button @click="searchQuery = ''; activeTab = 'semua'" 
                                class="px-4 py-2 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-xl transition">
                            Reset Filter
                        </button>
                        <a href="{{ route('koleksi.index') }}" 
                           class="px-5 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl transition shadow-xs">
                            Mulai Belanja Batik
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Service & Assurance Banner -->
            <div class="mt-12 bg-gradient-to-br from-[#201A17] to-stone-900 text-white rounded-3xl p-8 sm:p-10 relative overflow-hidden shadow-lg">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#B58742]/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#E5C38E] bg-[#E5C38E]/10 px-3 py-1 rounded-full mb-3 border border-[#E5C38E]/20">
                            ✦ BANTUAN & GARANSI PESANAN
                        </span>
                        <h3 class="text-xl sm:text-2xl font-serif-title font-bold text-white mb-2">
                            Butuh Bantuan dengan Pesanan Anda?
                        </h3>
                        <p class="text-stone-300 text-xs sm:text-sm leading-relaxed max-w-xl">
                            Setiap helai batik dikemas rapi dengan kotak ramah lingkungan. Jika ada kendala dengan nomor resi, pengiriman terlambat, atau permintaan penyesuaian ukuran, tim layanan pelanggan kami siap membantu Anda.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row md:flex-col gap-3 justify-center md:items-end">
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Hamzah%20Style,%20saya%20ingin%20menanyakan%20status%20pesanan%20saya" 
                           target="_blank"
                           class="inline-flex items-center justify-center gap-2.5 px-6 py-3 rounded-full bg-[#B58742] text-white hover:bg-[#9d7335] text-xs sm:text-sm font-semibold transition shadow-md">
                            <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                            <span>Hubungi Customer Care</span>
                        </a>
                        <p class="text-[11px] text-stone-400 text-center md:text-right">
                            Senin - Minggu • 08.00 - 21.00 WIB
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ================= MODAL: TRACKING / LACAK PENGIRIMAN ================= -->
    <div x-cloak x-show="trackingModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="trackingModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="trackingModalOpen = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="trackingModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-stone-200">
                
                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-stone-900">
                            Lacak Pengiriman Pesanan
                        </h3>
                        <p class="text-xs text-stone-500 font-mono mt-0.5" x-text="selectedOrder ? selectedOrder.id : ''"></p>
                    </div>
                    <button @click="trackingModalOpen = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <template x-if="selectedOrder">
                        <div>
                            <!-- Courier & Waybill Info Card -->
                            <div class="p-4 rounded-xl bg-stone-50 border border-stone-200 mb-6 flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-stone-500 block">Ekspedisi / Kurir:</span>
                                    <span class="font-bold text-stone-900 text-sm" x-text="selectedOrder.kurir"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-stone-500 block">Nomor Resi:</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-stone-900 text-sm" x-text="selectedOrder.noResi"></span>
                                        <button @click="copyText(selectedOrder.noResi)" class="text-stone-400 hover:text-stone-700" title="Salin Resi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking Timeline -->
                            <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-stone-200">
                                <template x-for="(step, sIdx) in (selectedOrder.trackingSteps || [
                                    { title: 'Pesanan Dibuat', time: selectedOrder.tanggal, done: true },
                                    { title: 'Pembayaran Diterima', time: selectedOrder.tanggal, done: true },
                                    { title: 'Paket Sedang Dikemas', time: '-', done: selectedOrder.status !== 'menunggu_pembayaran' }
                                ])" :key="sIdx">
                                    <div class="relative flex items-start gap-4">
                                        <div :class="step.current ? 'bg-amber-600 ring-4 ring-amber-100' : (step.done ? 'bg-emerald-600' : 'bg-stone-300')"
                                             class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white"></div>
                                        <div class="flex-1">
                                            <p :class="step.current ? 'text-amber-900 font-bold' : (step.done ? 'text-stone-900 font-semibold' : 'text-stone-400 font-normal')"
                                               class="text-xs sm:text-sm" x-text="step.title"></p>
                                            <p class="text-[11px] text-stone-500 mt-0.5" x-text="step.time"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 text-right">
                    <button @click="trackingModalOpen = false" 
                            class="px-5 py-2 text-xs font-semibold text-stone-700 bg-white border border-stone-300 rounded-xl hover:bg-stone-100 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL: DETAIL PESANAN ================= -->
    <div x-cloak x-show="detailModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="detailModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="detailModalOpen = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="detailModalOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-stone-200">
                
                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-stone-900">
                            Rincian Faktur Pesanan
                        </h3>
                        <p class="text-xs text-stone-500 font-mono mt-0.5" x-text="selectedOrder ? selectedOrder.id : ''"></p>
                    </div>
                    <button @click="detailModalOpen = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6">
                    <template x-if="selectedOrder">
                        <div>
                            <!-- Status & Date -->
                            <div class="flex items-center justify-between p-4 rounded-xl bg-stone-50 border border-stone-200">
                                <div>
                                    <span class="text-xs text-stone-500 block">Status Pesanan:</span>
                                    <span :class="selectedOrder.statusBadgeClass" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span x-text="selectedOrder.statusLabel"></span>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-stone-500 block">Waktu Pemesanan:</span>
                                    <span class="text-xs font-semibold text-stone-800" x-text="selectedOrder.tanggal"></span>
                                </div>
                            </div>

                            <!-- Alamat Pengiriman -->
                            <div class="mt-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Alamat Pengiriman</h4>
                                <div class="p-3.5 rounded-xl border border-stone-200 bg-[#FAF7F2]/40 text-xs text-stone-700 leading-relaxed">
                                    <p x-text="selectedOrder.alamat"></p>
                                    <p class="mt-2 text-stone-500 font-medium">Kurir: <strong class="text-stone-800" x-text="selectedOrder.kurir"></strong></p>
                                </div>
                            </div>

                            <!-- Produk Dipesan -->
                            <div class="mt-6">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-3">Daftar Produk</h4>
                                <div class="space-y-3">
                                    <template x-for="(item, idx) in selectedOrder.items" :key="idx">
                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 bg-white">
                                            <img :src="item.gambar" class="w-14 h-14 rounded-lg object-cover border border-stone-200 shrink-0">
                                            <div class="flex-1 min-w-0">
                                                <h5 class="text-xs sm:text-sm font-bold text-stone-900 truncate" x-text="item.nama"></h5>
                                                <p class="text-[11px] text-stone-500" x-text="item.varian"></p>
                                                <p class="text-xs text-stone-700 mt-0.5"><span x-text="item.qty"></span> x <span x-text="formatRupiah(item.harga)"></span></p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="font-bold text-xs sm:text-sm text-stone-900" x-text="formatRupiah(item.harga * item.qty)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Rincian Biaya -->
                            <div class="mt-6 border-t border-stone-200 pt-4 space-y-2 text-xs">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Rincian Pembayaran</h4>
                                <div class="flex justify-between text-stone-600">
                                    <span>Subtotal Produk</span>
                                    <span class="font-medium text-stone-900" x-text="formatRupiah(selectedOrder.subtotal)"></span>
                                </div>
                                <div class="flex justify-between text-stone-600">
                                    <span>Ongkos Kirim</span>
                                    <span class="font-medium text-stone-900" x-text="formatRupiah(selectedOrder.ongkir)"></span>
                                </div>
                                <template x-if="selectedOrder.diskon > 0">
                                    <div class="flex justify-between text-emerald-700">
                                        <span>Diskon Voucher</span>
                                        <span class="font-medium" x-text="'- ' + formatRupiah(selectedOrder.diskon)"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between text-sm font-bold text-stone-900 pt-2 border-t border-stone-200">
                                    <span>Total Pembayaran</span>
                                    <span class="text-base text-stone-900" x-text="formatRupiah(selectedOrder.total)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between">
                    <button @click="showToast('Mengunduh faktur invoice PDF...')"
                            class="text-xs font-semibold text-stone-700 hover:text-stone-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Cetak / Unduh Invoice</span>
                    </button>
                    <button @click="detailModalOpen = false" 
                            class="px-5 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl transition shadow-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= REVIEW MODAL (hanya pesanan Selesai) ================= -->
    <div x-cloak x-show="reviewModalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            <div x-show="reviewModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="reviewModalOpen = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="reviewModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-stone-200">

                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-stone-900">
                            Beri Ulasan Produk
                        </h3>
                        <p class="text-xs text-stone-500 font-mono mt-0.5" x-text="reviewOrder ? reviewOrder.id : ''"></p>
                    </div>
                    <button @click="reviewModalOpen = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                    <p class="text-xs text-stone-500 leading-relaxed">
                        Ulasan hanya dapat diberikan untuk produk pada pesanan yang telah selesai (diterima).
                    </p>
                    <template x-if="reviewOrder">
                        <div class="space-y-4">
                            <template x-for="(item, idx) in reviewOrder.items" :key="idx">
                                <div class="p-4 rounded-2xl border border-stone-200 bg-[#FAF7F2]/40 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <img :src="item.gambar" class="w-12 h-12 rounded-xl object-cover border border-stone-200 shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-xs sm:text-sm font-bold text-stone-900 truncate" x-text="item.nama"></h5>
                                            <p class="text-[11px] text-stone-500" x-text="item.varian"></p>
                                        </div>
                                        <span x-show="reviewDrafts[idx] && reviewDrafts[idx].done" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full shrink-0">
                                            Terkirim
                                        </span>
                                    </div>
                                    <template x-if="reviewDrafts[idx] && !reviewDrafts[idx].done">
                                        <div class="space-y-2.5">
                                            <div class="flex items-center gap-1.5 text-xl cursor-pointer">
                                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                                    <span @click="reviewDrafts[idx].rating = star" :class="star <= reviewDrafts[idx].rating ? 'text-amber-500' : 'text-stone-300'">★</span>
                                                </template>
                                                <span class="text-[11px] text-stone-500 ml-1" x-text="reviewDrafts[idx].rating + ' / 5'"></span>
                                            </div>
                                            <textarea x-model="reviewDrafts[idx].comment" rows="2" placeholder="Ceritakan kualitas bahan, jahitan, atau kesesuaian ukuran..."
                                                      class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-white"></textarea>
                                            <button @click="submitItemReview(reviewOrder, idx)" :disabled="reviewDrafts[idx].sending"
                                                    class="px-5 py-2 text-xs font-bold text-white bg-[#201A17] hover:bg-stone-800 disabled:opacity-50 rounded-xl transition shadow-xs">
                                                <span x-text="reviewDrafts[idx].sending ? 'Mengirim...' : 'Kirim Ulasan'"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 flex items-center justify-end">
                    <button @click="reviewModalOpen = false"
                            class="px-5 py-2 text-xs font-semibold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl transition shadow-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed mb-6 max-w-sm">
                        Batik Modern & Olahan Kain Sisa Berkelanjutan. Menjaga warisan tekstil Nusantara melalui inovasi kriya beretika dan potongan adibusana ramah lingkungan.
                    </p>
                    <div class="inline-flex items-center gap-2 text-xs font-semibold text-stone-800 bg-[#EFE8DD] px-3 py-1.5 rounded-full border border-[#DFCDBB]">
                        <span>✦</span>
                        <span>KARYA BERKELANJUTAN</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi -->
                <div class="lg:col-span-2">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Navigasi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><a href="{{ url('/') }}" class="text-stone-600 hover:text-stone-950 transition">Home</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="text-stone-600 hover:text-stone-950 transition">Produk</a></li>
                        <li><a href="{{ route('pesanan.index') }}" class="text-stone-900 font-semibold hover:text-stone-950 transition">Pesanan</a></li>
                        <li><a href="{{ route('keranjang.index') }}" class="text-stone-600 hover:text-stone-950 transition">Keranjang Belanja</a></li>
                        <li><a href="{{ url('/') }}#kontak" class="text-stone-600 hover:text-stone-950 transition">Kontak</a></li>
                    </ul>
                </div>

                <!-- Col 3: Saluran Penjualan Resmi -->
                <div class="lg:col-span-3">
                    <h5 class="font-bold text-stone-900 text-sm mb-4">
                        Saluran Penjualan Resmi
                    </h5>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li>
                            <a href="https://wa.me/6281234567890" target="_blank" class="text-stone-600 hover:text-stone-950 transition flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                WhatsApp Business
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-stone-600 hover:text-stone-950 transition flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                                Shopee Official
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-stone-600 hover:text-stone-950 transition flex items-center gap-2">
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
                        Dapatkan diskon eksklusif untuk rilisan koleksi sirkular dan seri terbatas:
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

</body>
</html>
