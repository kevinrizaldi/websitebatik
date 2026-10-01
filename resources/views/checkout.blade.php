<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selesaikan Pembayaran - Hamzah Style Official</title>
    <meta name="description" content="Selesaikan pembayaran pesanan busana batik autentik ramah lingkungan di Hamzah Style Official dengan metode pembayaran aman dan terenkripsi.">

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
          userMenu: false,
          searchModal: false,
          editAddressModal: false,
          toastMessage: '',
          isSubmitting: false,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3500);
          },

@php
    $defaultAlamat = (isset($alamats) && $alamats->isNotEmpty()) 
        ? ($alamats->where('is_utama', true)->first() ?: $alamats->first())
        : null;
@endphp
          // Customer & Shipping Address State
          // Hanya memakai alamat yang diinput customer (tanpa data dummy).
          addressData: {
              recipient: '{{ $defaultAlamat ? addslashes($defaultAlamat->penerima) : (Auth::check() ? addslashes(Auth::user()->name) : '') }}',
              label: '{{ $defaultAlamat ? addslashes($defaultAlamat->label_alamat) : '' }}',
              phone: '{{ $defaultAlamat ? addslashes($defaultAlamat->no_telepon) : (Auth::check() && Auth::user()->phone ? addslashes(Auth::user()->phone) : '') }}',
              address: '{{ $defaultAlamat ? addslashes($defaultAlamat->alamat_lengkap) : '' }}',
              city: '{{ $defaultAlamat ? addslashes($defaultAlamat->kota ?? '') : '' }}',
              hasSavedAddress: {{ $defaultAlamat ? 'true' : 'false' }}
          },

          get fullAddress() {
              if (!this.addressData.address) return '';
              const city = (this.addressData.city || '').trim();
              if (city && !this.addressData.address.includes(city)) {
                  return this.addressData.address + ', ' + city;
              }
              return this.addressData.address;
          },

          // Form fields inside address modal
          tempAddress: {
              recipient: '',
              label: '',
              phone: '',
              address: '',
              city: ''
          },

          openEditAddress() {
              this.tempAddress.recipient = this.addressData.recipient;
              this.tempAddress.label = this.addressData.label;
              this.tempAddress.phone = this.addressData.phone;
              this.tempAddress.address = this.addressData.address;
              this.tempAddress.city = this.addressData.city;
              this.editAddressModal = true;
          },

          saveAddress() {
              if (!this.tempAddress.recipient.trim() || !this.tempAddress.phone.trim() || !this.tempAddress.address.trim() || !this.tempAddress.city.trim()) {
                  this.showToast('Mohon lengkapi nama, nomor telepon, alamat, dan kota');
                  return;
              }
              this.addressData.recipient = this.tempAddress.recipient.trim();
              this.addressData.label = this.tempAddress.label.trim();
              this.addressData.phone = this.tempAddress.phone.trim();
              this.addressData.address = this.tempAddress.address.trim();
              this.addressData.city = this.tempAddress.city.trim();
              this.addressData.hasSavedAddress = true;
              this.editAddressModal = false;

              // Persist to database if authenticated
              fetch('{{ route('alamat.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      penerima: this.addressData.recipient,
                      label_alamat: this.addressData.label || 'Alamat Utama',
                      no_telepon: this.addressData.phone,
                      alamat_lengkap: this.addressData.address,
                      kota: this.addressData.city,
                      is_utama: true
                  })
              }).catch(() => {});

              this.showToast('Alamat pengiriman berhasil diperbarui!');
          },

          // Selected Items in Checkout
          items: [
              @if(isset($cartItems) && $cartItems->isNotEmpty())
                  @foreach($cartItems as $item)
                  @php
                      $prod = $item->produk;
                      $imgUrl = $prod && $prod->gambar 
                          ? (str_starts_with($prod->gambar, 'http') ? $prod->gambar : asset('storage/'.$prod->gambar))
                          : asset('images/batik-placeholder.jpg');
                      $kategori = $prod ? $prod->kategori : 'Koleksi Batik';
                      $nama = $prod ? $prod->nama : 'Busana Batik Nusantara';
                      $harga = $prod ? (float)$prod->harga : 0;
                  @endphp
                  {
                      id: {{ $item->id }},
                      produk_id: {{ $prod ? $prod->id : 'null' }},
                      nama: '{{ addslashes($nama) }}',
                      kategori: '{{ addslashes($kategori) }}',
                      ukuran: '{{ $item->ukuran ?? 'All Size' }}',
                      varian: '{{ addslashes($item->varian ?? ('Ukuran: ' . ($item->ukuran ?? 'L'))) }}',
                      qty: {{ $item->qty }},
                      harga: {{ $harga }},
                      gambar: '{{ $imgUrl }}'
                  },
                  @endforeach
              @endif
          ],

          // Shipping Selection
          selectedShipping: 'jne',
          shippingOptions: {
              jne: {
                  id: 'jne',
                  name: 'JNE Reguler',
                  desc: 'Estimasi 1 hingga 2-3 hari kerja',
                  cost: 20000
              },
              sicepat: {
                  id: 'sicepat',
                  name: 'SiCepat BEST',
                  desc: 'Next Day (1 hari kerja)',
                  cost: 35000
              },
              gosend: {
                  id: 'gosend',
                  name: 'Gosend / Grab',
                  desc: 'Instant (Khusus Jabodetabek)',
                  cost: 50000
              }
          },

          // Pembayaran: seluruh transaksi via Midtrans (metode dipilih di popup Snap).
          paymentMethodName: 'Midtrans Gateway',

          // Notes for seller
          sellerNotes: '',

          // Calculations
          get totalItemQty() {
              return this.items.reduce((sum, item) => sum + item.qty, 0);
          },

          get rawSubtotal() {
              return this.items.reduce((sum, item) => sum + (item.harga * item.qty), 0);
          },

          get shippingCost() {
              return this.shippingOptions[this.selectedShipping]?.cost || 20000;
          },


          serviceFee: 1000,

          get grandTotal() {
              return Math.max(0, this.rawSubtotal + this.shippingCost + this.serviceFee);
          },

          formatRupiah(amount) {
              return 'Rp ' + Number(amount).toLocaleString('id-ID');
          },

          // Submit Order to Database
          submitOrder() {
              if (!this.addressData.recipient || !this.addressData.phone || !this.addressData.address) {
                  this.showToast('Mohon periksa data alamat pengiriman Anda');
                  return;
              }

              this.isSubmitting = true;
              const shippingObj = this.shippingOptions[this.selectedShipping];

              fetch('{{ route('checkout.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      customer_name: this.addressData.recipient + (this.addressData.label ? ' (' + this.addressData.label + ')' : ''),
                      phone: this.addressData.phone,
                      address: this.fullAddress,
                      shipping_option: shippingObj.name,
                      shipping_cost: shippingObj.cost,
                      payment_method: this.paymentMethodName,
                      notes: this.sellerNotes
                  })
              })
              .then(res => res.json())
              .then(data => {
                  this.isSubmitting = false;
                  if (data.success) {
                      window.location.href = data.redirect_url || '{{ route('pesanan.index') }}';
                  } else {
                      this.showToast(data.message || 'Gagal memproses transaksi.');
                  }
              })
              .catch(err => {
                  this.isSubmitting = false;
                  // If direct redirect happened or JSON failed, fallback to pesanan page
                  window.location.href = '{{ route('pesanan.index') }}';
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
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-stone-700">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

    <!-- ================= MODAL UBAH / TAMBAH ALAMAT ================= -->
    <div x-cloak x-show="editAddressModal" 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editAddressModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="editAddressModal = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editAddressModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-stone-200">
                
                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-stone-900">
                            Kelola Alamat Pengiriman
                        </h3>
                        <p class="text-xs text-stone-500 mt-0.5">
                            Pastikan rincian alamat akurat untuk kemudahan kurir ekspres
                        </p>
                    </div>
                    <button @click="editAddressModal = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-1.5">
                                Nama Penerima <span class="text-rose-500">*</span>
                            </label>
                             <input type="text" 
                                    x-model="tempAddress.recipient"
                                    placeholder="Nama lengkap penerima"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-1.5">
                                Label Alamat
                            </label>
                            <input type="text" 
                                   x-model="tempAddress.label"
                                   placeholder="Rumah Utama / Kantor"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-1.5">
                            Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                        </label>
                             <input type="text" 
                                    x-model="tempAddress.phone"
                                    placeholder="Nomor HP aktif penerima"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                     </div>

                     <div>
                         <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-1.5">
                             Kabupaten / Kota <span class="text-rose-500">*</span>
                         </label>
                         <input type="text"
                                x-model="tempAddress.city"
                                placeholder="Kota tujuan pengiriman"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                     </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider mb-1.5">
                            Alamat Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="tempAddress.address"
                                  rows="3"
                                  placeholder="Nama Jalan, Nomor Gedung/Rumah, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between">
                    <button @click="editAddressModal = false" 
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
                        Batal
                    </button>
                    <button @click="saveAddress()" 
                            type="button"
                            class="px-6 py-2.5 text-xs font-bold text-white bg-[#201A17] hover:bg-stone-800 rounded-xl transition shadow-md">
                        Simpan Alamat
                    </button>
                </div>
            </div>
        </div>
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

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Home
                    </a>
                    <a href="{{ route('koleksi.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Produk
                    </a>
                    <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Pesanan
                    </a>
                </nav>

                <!-- Action Utilities: Search, Cart Icon, User Profile -->
                <div class="flex items-center gap-4">
                    <!-- Search Button -->
                    <a href="{{ route('koleksi.index') }}" 
                       class="p-2 text-stone-600 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition"
                       title="Cari Koleksi Batik">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </a>

                    <!-- Cart Icon with Badge -->
                    <a href="{{ route('keranjang.index') }}" 
                       class="relative p-2 text-stone-600 hover:text-stone-900 rounded-full hover:bg-stone-200/50 transition"
                       title="Lihat Keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="absolute top-1 right-1 w-4 h-4 bg-[#B58742] text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ \App\Models\CartItem::forCurrentVisitor()->sum('qty') }}
                        </span>
                    </a>

                    <!-- Auth Dropdown -->
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
                                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-cloak x-show="userMenu" 
                                     @click.outside="userMenu = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     class="absolute right-0 mt-2 w-52 rounded-2xl bg-white shadow-xl border border-stone-200 py-1.5 z-50 text-sm">
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
                                        Akun
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
                <a href="{{ route('keranjang.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-200/30">Keranjang</a>
            </div>
        </div>
    </header>

    <!-- ================= MAIN CHECKOUT CONTENT ================= -->
    <main class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header & Stepper matching Screenshot -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-[#ECE4D8]/80 mb-8 sm:mb-10">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-[#B58742] block mb-1.5">
                        PESANAN TERKURASI
                    </span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-extrabold text-stone-900">
                        Selesaikan Pembayaran
                    </h1>
                </div>

                <!-- Stepper Progress Bar -->
                <div class="flex items-center gap-2 sm:gap-3 text-xs font-semibold">
                    <!-- Step 1: Keranjang (Completed) -->
                    <a href="{{ route('keranjang.index') }}" class="flex items-center gap-1.5 text-stone-500 hover:text-stone-800 transition">
                        <span class="w-5 h-5 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Keranjang</span>
                    </a>

                    <span class="w-6 h-px bg-stone-300"></span>

                    <!-- Step 2: Pengiriman & Pembayaran (Active) -->
                    <div class="flex items-center gap-1.5 text-stone-900 font-bold">
                        <span class="w-5 h-5 rounded-full bg-[#201A17] text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Pengiriman & Pembayaran</span>
                    </div>

                    <span class="w-6 h-px bg-stone-300"></span>

                    <!-- Step 3: Selesai (Next) -->
                    <div class="flex items-center gap-1.5 text-stone-400">
                        <span class="w-5 h-5 rounded-full bg-stone-200 text-stone-400 flex items-center justify-center text-[10px]">3</span>
                        <span>Selesai</span>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid: Left (8 Cols) & Right (4 Cols) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                
                <!-- Left Column: Address, Order Items, Shipping, Payment (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- ================= CARD 1: ALAMAT PENGIRIMAN ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Alamat Pengiriman
                                </h2>
                            </div>

                            <div class="flex items-center gap-3">
                                <button @click="openEditAddress()" 
                                        type="button"
                                        class="text-xs font-semibold text-stone-600 hover:text-stone-900 underline transition">
                                    Ubah Alamat
                                </button>
                                <button @click="openEditAddress()" 
                                        type="button"
                                        class="px-3 py-1 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold transition flex items-center gap-1">
                                    <span>+</span> Tambah Baru
                                </button>
                            </div>
                        </div>

                        <!-- Address Info Box: data asli milik customer -->
                        <div x-show="addressData.address" class="p-4 sm:p-5 rounded-2xl bg-[#FAF7F2] border border-[#ECE4D8] space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2 font-bold text-stone-900 text-sm">
                                    <span x-text="addressData.recipient + (addressData.label ? ' (' + addressData.label + ')' : '')"></span>
                                    <span class="text-stone-400 font-normal">|</span>
                                    <span x-text="addressData.phone" class="font-semibold text-stone-700"></span>
                                </div>

                                <span x-show="addressData.hasSavedAddress" class="px-2.5 py-0.5 rounded-full bg-[#201A17] text-white text-[10px] font-bold tracking-wide uppercase">
                                    Alamat Utama
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-stone-600 leading-relaxed" x-text="fullAddress"></p>

                            <div class="pt-1">
                                <a href="{{ route('alamat.index') }}" class="text-[11px] font-bold text-stone-700 hover:text-stone-950 underline transition">
                                    Kelola alamat tersimpan →
                                </a>
                            </div>
                        </div>

                        <!-- Empty state: customer belum punya alamat -->
                        <div x-show="!addressData.address" class="p-5 rounded-2xl bg-amber-50/60 border border-dashed border-amber-300 text-center space-y-2">
                            <p class="font-bold text-sm text-stone-900">Belum ada alamat pengiriman</p>
                            <p class="text-xs text-stone-500">Isi alamat Anda atau pilih dari daftar alamat tersimpan.</p>
                            <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                                <button @click="openEditAddress()" type="button" class="px-4 py-2 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white text-xs font-bold transition">
                                    Isi Alamat
                                </button>
                                <a href="{{ route('alamat.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-stone-50 border border-stone-200 text-stone-800 text-xs font-bold transition">
                                    Daftar Alamat Saya
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ================= CARD 2: PRODUK YANG DIBELI (RINGKAS) ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Produk yang Dibeli (Ringkas)
                                </h2>
                            </div>

                            <span class="text-xs font-semibold text-stone-500" x-text="items.length + ' Macam Koleksi'">
                                3 Macam Koleksi
                            </span>
                        </div>

                        <!-- Product Items List -->
                        <div class="divide-y divide-stone-100">
                            <template x-for="item in items" :key="item.id">
                                <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <img :src="item.gambar" 
                                             :alt="item.nama"
                                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-stone-100 border border-stone-200 shrink-0">
                                        <div class="min-w-0 space-y-1">
                                            <span class="text-[11px] font-semibold text-stone-500 block truncate" x-text="item.varian"></span>
                                            <h3 class="font-bold text-sm sm:text-base text-stone-900 truncate" x-text="item.nama"></h3>
                                            <span class="text-xs text-stone-400 block" x-text="item.qty + 'x ' + (item.qty > 1 ? 'unit (' + formatRupiah(item.harga) + '/unit)' : 'potong')"></span>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="font-serif-title font-bold text-base sm:text-lg text-stone-900" x-text="formatRupiah(item.harga * item.qty)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Catatan untuk Penjual -->
                        <div class="mt-6 pt-5 border-t border-stone-100">
                            <label class="block text-[11px] font-bold text-stone-800 uppercase tracking-wider mb-2">
                                Catatan untuk Penjual
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                                    </svg>
                                </span>
                                <input type="text" 
                                       x-model="sellerNotes"
                                       placeholder="Contoh: Mohon sertakan kartu ucapan kado & kemasan ramah lingkungan ekstra"
                                       class="w-full pl-10 pr-4 py-3 rounded-2xl border border-stone-200 text-xs sm:text-sm text-stone-800 bg-[#FAF7F2]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#B58742]/40 focus:border-[#B58742] transition placeholder-stone-400">
                            </div>
                        </div>
                    </div>

                    <!-- ================= CARD 3: OPSI PENGIRIMAN ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875A1.125 1.125 0 0013.125 3.75h-7.5A1.125 1.125 0 004.5 4.875v9.375m9 0V9.75" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Opsi Pengiriman
                                </h2>
                            </div>
                            <p class="text-xs text-stone-500 mt-1 ml-10">
                                Dikemas menggunakan besek anyaman bambu & serat daur ulang
                            </p>
                        </div>

                        <!-- 3 Courier Options Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            
                            <!-- Courier 1: JNE Reguler -->
                            <div @click="selectedShipping = 'jne'"
                                 :class="selectedShipping === 'jne' 
                                     ? 'bg-[#201A17] text-white border-[#201A17] shadow-md ring-2 ring-[#B58742]/50' 
                                     : 'bg-[#FAF7F2] text-stone-800 border-stone-200 hover:border-stone-400'"
                                 class="rounded-2xl p-4 sm:p-5 border cursor-pointer transition-all duration-200 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm"
                                             :class="selectedShipping === 'jne' ? 'bg-stone-800 text-[#E5C38E]' : 'bg-white text-stone-700 shadow-2xs'">
                                            🧭
                                        </div>
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                             :class="selectedShipping === 'jne' ? 'border-amber-400 bg-amber-400' : 'border-stone-300'">
                                            <div x-show="selectedShipping === 'jne'" class="w-1.5 h-1.5 rounded-full bg-[#201A17]"></div>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-sm" :class="selectedShipping === 'jne' ? 'text-white' : 'text-stone-900'">
                                        JNE Reguler
                                    </h3>
                                    <p class="text-[11px] mt-0.5 leading-snug" :class="selectedShipping === 'jne' ? 'text-stone-300' : 'text-stone-500'">
                                        Estimasi 1 hingga 2-3 hari kerja
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 flex items-center justify-between border-t"
                                     :class="selectedShipping === 'jne' ? 'border-stone-700' : 'border-stone-200/60'">
                                    <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                          :class="selectedShipping === 'jne' ? 'bg-stone-800 text-stone-300' : 'bg-stone-200/60 text-stone-600'">
                                        TARIF
                                    </span>
                                    <span class="font-bold text-xs sm:text-sm" :class="selectedShipping === 'jne' ? 'text-white' : 'text-stone-900'">
                                        Rp 20.000
                                    </span>
                                </div>
                            </div>

                            <!-- Courier 2: SiCepat BEST -->
                            <div @click="selectedShipping = 'sicepat'"
                                 :class="selectedShipping === 'sicepat' 
                                     ? 'bg-[#201A17] text-white border-[#201A17] shadow-md ring-2 ring-[#B58742]/50' 
                                     : 'bg-[#FAF7F2] text-stone-800 border-stone-200 hover:border-stone-400'"
                                 class="rounded-2xl p-4 sm:p-5 border cursor-pointer transition-all duration-200 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm"
                                             :class="selectedShipping === 'sicepat' ? 'bg-stone-800 text-amber-400' : 'bg-white text-stone-700 shadow-2xs'">
                                            ⚡
                                        </div>
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                             :class="selectedShipping === 'sicepat' ? 'border-amber-400 bg-amber-400' : 'border-stone-300'">
                                            <div x-show="selectedShipping === 'sicepat'" class="w-1.5 h-1.5 rounded-full bg-[#201A17]"></div>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-sm" :class="selectedShipping === 'sicepat' ? 'text-white' : 'text-stone-900'">
                                        SiCepat BEST
                                    </h3>
                                    <p class="text-[11px] mt-0.5 leading-snug" :class="selectedShipping === 'sicepat' ? 'text-stone-300' : 'text-stone-500'">
                                        Next Day (1 hari kerja)
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 flex items-center justify-between border-t"
                                     :class="selectedShipping === 'sicepat' ? 'border-stone-700' : 'border-stone-200/60'">
                                    <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                          :class="selectedShipping === 'sicepat' ? 'bg-stone-800 text-stone-300' : 'bg-stone-200/60 text-stone-600'">
                                        TARIF
                                    </span>
                                    <span class="font-bold text-xs sm:text-sm" :class="selectedShipping === 'sicepat' ? 'text-white' : 'text-stone-900'">
                                        Rp 35.000
                                    </span>
                                </div>
                            </div>

                            <!-- Courier 3: Gosend / Grab -->
                            <div @click="selectedShipping = 'gosend'"
                                 :class="selectedShipping === 'gosend' 
                                     ? 'bg-[#201A17] text-white border-[#201A17] shadow-md ring-2 ring-[#B58742]/50' 
                                     : 'bg-[#FAF7F2] text-stone-800 border-stone-200 hover:border-stone-400'"
                                 class="rounded-2xl p-4 sm:p-5 border cursor-pointer transition-all duration-200 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm"
                                             :class="selectedShipping === 'gosend' ? 'bg-stone-800 text-emerald-400' : 'bg-white text-stone-700 shadow-2xs'">
                                            🛵
                                        </div>
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                             :class="selectedShipping === 'gosend' ? 'border-amber-400 bg-amber-400' : 'border-stone-300'">
                                            <div x-show="selectedShipping === 'gosend'" class="w-1.5 h-1.5 rounded-full bg-[#201A17]"></div>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-sm" :class="selectedShipping === 'gosend' ? 'text-white' : 'text-stone-900'">
                                        Gosend / Grab
                                    </h3>
                                    <p class="text-[11px] mt-0.5 leading-snug" :class="selectedShipping === 'gosend' ? 'text-stone-300' : 'text-stone-500'">
                                        Instant (Khusus Jabodetabek)
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 flex items-center justify-between border-t"
                                     :class="selectedShipping === 'gosend' ? 'border-stone-700' : 'border-stone-200/60'">
                                    <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                          :class="selectedShipping === 'gosend' ? 'bg-stone-800 text-stone-300' : 'bg-stone-200/60 text-stone-600'">
                                        TARIF
                                    </span>
                                    <span class="font-bold text-xs sm:text-sm" :class="selectedShipping === 'gosend' ? 'text-white' : 'text-stone-900'">
                                        Rp 50.000
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ================= CARD 4: METODE PEMBAYARAN ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Metode Pembayaran
                                </h2>
                            </div>

                            <div class="flex items-center gap-1.5 text-xs text-stone-500 font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <span>Enkripsi 256-bit</span>
                            </div>
                        </div>

                        <!-- Pembayaran tunggal via Midtrans (metode dipilih di popup Snap) -->
                        <div class="p-4 sm:p-5 rounded-2xl border border-[#B58742] bg-[#FAF7F2] ring-2 ring-[#B58742]/30 flex items-start gap-3">
                            <div class="mt-0.5 w-9 h-9 rounded-xl bg-[#201A17] text-[#E5C38E] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-stone-900">Midtrans Payment Gateway</span>
                                    <span class="px-2 py-0.5 rounded-full bg-[#201A17] text-white text-[10px] font-bold">Otomatis</span>
                                </div>
                                <p class="text-xs text-stone-500 mt-1">
                                    Virtual Account, QRIS, E-Wallet, dan Kartu Kredit/Debit. Metode pembayaran dipilih pada jendela Midtrans setelah checkout — terverifikasi instan tanpa unggah bukti transfer.
                                </p>
                                <div class="hidden sm:flex items-center gap-1.5 mt-2.5">
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-blue-900 border border-stone-200">BCA</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-amber-800 border border-stone-200">MANDIRI</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-orange-700 border border-stone-200">BNI</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-stone-700 border border-stone-200">QRIS</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-stone-700 border border-stone-200">VISA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Sticky Ringkasan Pesanan (4 Cols) -->
                <div class="lg:col-span-4 sticky top-28 space-y-5">
                    
                    <!-- Ringkasan Belanja Card matching Mockup -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-md space-y-6">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-0.5">
                                    RINGKASAN BELANJA
                                </span>
                                <h2 class="font-bold text-stone-900 text-lg">
                                    Ringkasan Pesanan
                                </h2>
                            </div>
                            <a href="{{ route('keranjang.index') }}" 
                               class="p-2 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100 transition"
                               title="Edit Isi Keranjang">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Rows Breakdown matching Mockup exactly -->
                        <div class="space-y-3.5 text-xs sm:text-sm text-stone-600">
                            
                            <div class="flex items-center justify-between">
                                <span x-text="'Subtotal Produk (' + totalItemQty + ' Item)'">Subtotal Produk (4 Item)</span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(rawSubtotal)">Rp 900.000</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <span>Total Ongkos Kirim</span>
                                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(shippingCost)">Rp 20.000</span>
                            </div>


                            <div class="flex items-center justify-between">
                                <span>Biaya Layanan & Asuransi Kurir</span>
                                <span class="font-bold text-stone-900">Rp 1.000</span>
                            </div>

                        </div>

                        <!-- Divider with center dot -->
                        <div class="relative flex items-center justify-center my-2">
                            <div class="w-full border-t border-stone-200"></div>
                            <div class="absolute w-2 h-2 rounded-full bg-stone-300"></div>
                        </div>

                        <!-- Total Tagihan Section -->
                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-stone-800 block">
                                    TOTAL TAGIHAN
                                </span>
                                <span class="text-[11px] text-stone-400">
                                    Sudah termasuk PPN
                                </span>
                            </div>
                            <span class="font-serif-title text-2xl sm:text-3xl font-extrabold text-stone-900" 
                                  x-text="formatRupiah(grandTotal)">
                                Rp 871.000
                            </span>
                        </div>

                        <!-- Main Checkout CTA Button matching Mockup -->
                        <button @click="submitOrder()" 
                                :disabled="isSubmitting || items.length === 0"
                                class="w-full py-4 px-6 rounded-2xl bg-[#201A17] hover:bg-stone-800 disabled:opacity-60 text-white font-bold text-sm transition duration-200 shadow-md flex items-center justify-center gap-2 group cursor-pointer">
                            <span x-show="!isSubmitting" class="flex items-center gap-2">
                                <span>🔒</span>
                                <span>Bayar Sekarang</span>
                            </span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses Transaksi...</span>
                            </span>
                        </button>

                        <!-- Legal & Sustainability Disclaimer -->
                        <p class="text-[11px] text-stone-500 text-center leading-relaxed px-2">
                            Dengan menekan Bayar Sekarang, Anda menyetujui Syarat & Ketentuan serta komitmen pelestarian warisan daya Official.
                        </p>

                    </div>

                    <!-- Butuh Bantuan Checkout? WhatsApp Card matching Mockup -->
                    <div class="bg-[#FAF7F2] rounded-3xl p-5 border border-[#EDE6DB] flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-white border border-stone-200 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-stone-900">
                                    Butuh Bantuan Checkout?
                                </h4>
                                <p class="text-[11px] text-stone-500 leading-tight">
                                    Layanan pendampingan pesanan anda via WhatsApp
                                </p>
                            </div>
                        </div>

                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Hamzah%20Style,%20saya%20butuh%20bantuan%20untuk%20checkout%20pesanan"
                           target="_blank"
                           class="px-4 py-2 rounded-xl bg-white hover:bg-stone-50 border border-stone-200 text-stone-800 font-bold text-xs transition shadow-2xs shrink-0">
                            Chat
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="bg-[#FAF7F2] border-t border-[#ECE4D8] pt-16 pb-12 mt-16 text-stone-700 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-[#ECE4D8]">
                
                <!-- Col 1: Brand & Philosophy -->
                <div class="space-y-4 md:pr-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E]">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <span class="font-bold tracking-wider text-stone-900 uppercase">HAMZAH STYLE</span>
                    </div>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Batik Modern & Olahan Kain Sisa Berkelanjutan. Menjaga warisan tekstil Nusantara melalui inovasi kriya beretika dan potongan adibusana ramah lingkungan.
                    </p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-200/60 text-stone-800 text-[10px] font-bold uppercase tracking-wider">
                        🌱 KARYA BERKELANJUTAN
                    </span>
                </div>

                <!-- Col 2: Navigation -->
                <div class="space-y-3">
                    <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">Navigasi</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/') }}" class="hover:text-stone-950 transition">Home</a></li>
                        <li><a href="{{ url('/') }}#tentang-kami" class="hover:text-stone-950 transition">Tentang Kami</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 transition">Produk</a></li>
                        <li><a href="{{ route('koleksi.index') }}" class="hover:text-stone-950 transition">Kategori</a></li>
                        <li><a href="{{ url('/') }}#kontak" class="hover:text-stone-950 transition">Kontak</a></li>
                    </ul>
                </div>

                <!-- Col 3: Official Sales Channels -->
                <div class="space-y-3">
                    <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">Saluran Penjualan Resmi</h4>
                    <ul class="space-y-2 text-xs">
                        <li class="flex items-center gap-2">
                            <span>💬</span>
                            <a href="https://wa.me/6281234567890" target="_blank" class="hover:text-stone-950 transition">WhatsApp Business</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>🛍️</span>
                            <span class="text-stone-600">Shopee Official</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>🎵</span>
                            <span class="text-stone-600">TikTok Shop</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter & Socials -->
                <div class="space-y-3">
                    <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">Media Sosial & Buletin</h4>
                    <p class="text-xs text-stone-500">
                        Dapatkan info eksklusif untuk rilis kain sirkular dan seri upaya otoritas.
                    </p>
                    <div class="flex items-center gap-2 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-stone-200/70 hover:bg-stone-300 text-stone-700 flex items-center justify-center text-xs transition">📷</a>
                        <a href="#" class="w-8 h-8 rounded-full bg-stone-200/70 hover:bg-stone-300 text-stone-700 flex items-center justify-center text-xs transition">🌐</a>
                        <a href="#" class="w-8 h-8 rounded-full bg-stone-200/70 hover:bg-stone-300 text-stone-700 flex items-center justify-center text-xs transition">✉️</a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright & Pride -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-4">
                <p>&copy; {{ date('Y') }} Hamzah Style Official. All rights reserved.</p>
                <div class="flex items-center gap-2 text-stone-600 font-medium">
                    <span>Made with pride in Indonesia</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
