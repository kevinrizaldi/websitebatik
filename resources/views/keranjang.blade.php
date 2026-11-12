<x-storefront-layout title="Keranjang Belanja">
    <div class="bg-[#FAF7F2] min-h-screen py-8 sm:py-10"
         x-data="{
             toastMessage: '',
             showToast(msg) {
                 this.toastMessage = msg;
                 if (typeof window.dispatchEvent === 'function') {
                     window.dispatchEvent(new CustomEvent('show-toast', { detail: msg }));
                 }
                 setTimeout(() => { this.toastMessage = ''; }, 3000);
             },

             items: [
                 @if(isset($cartItems) && $cartItems->isNotEmpty())
                     @foreach($cartItems as $ci)
                     {
                         id: {{ $ci->id }},
                         produkId: {{ $ci->produk_id }},
                         selected: {{ $ci->selected ? 'true' : 'false' }},
                         nama: {{ json_encode($ci->produk->nama ?? 'Produk Batik') }},
                         kategoriBadge: {{ json_encode(strtoupper($ci->produk->kategoriRelasi?->nama_kategori ?? $ci->produk->kategori ?? 'TULIS')) }},
                         kategoriSub: {{ json_encode(strtoupper($ci->produk->kategoriRelasi?->nama_kategori ?? $ci->produk->kategori ?? 'ATASAN PRIA')) }},
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
                         deskripsi: {{ json_encode(mb_strimwidth($pr->deskripsi ?: 'Bahan Tradisional Halus', 0, 35, '...')) }},
                         harga: {{ (float) $pr->harga }},
                         detailUrl: '{{ route('produk.detail', $pr->id) }}',
                         gambar: {{ json_encode($pr->gambar_url) }}
                     }@if(!$loop->last),@endif
                     @endforeach
                 @endif
             ],

             formatRupiah(num) {
                 return 'Rp ' + Number(num).toLocaleString('id-ID');
             },

             increaseQty(item) {
                 item.qty++;
                 this.syncQtyToServer(item);
             },

             decreaseQty(item) {
                 if (item.qty > 1) {
                     item.qty--;
                     this.syncQtyToServer(item);
                 } else {
                     this.removeItem(item.id);
                 }
             },

             syncQtyToServer(item) {
                 fetch('/keranjang/' + item.id, {
                     method: 'PATCH',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     credentials: 'same-origin',
                     body: JSON.stringify({ qty: item.qty })
                 });
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
                         credentials: 'same-origin',
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

             get rawSubtotal() {
                 return this.selectedItems.reduce((acc, curr) => acc + (curr.harga * curr.qty), 0);
             },

             get shippingCost() {
                 return this.selectedItems.length > 0 ? 20000 : 0;
             },

             get discountPromo() {
                 return (this.rawSubtotal >= 900000 && this.shippingCost > 0) ? 50000 : 0;
             },

             get grandTotal() {
                 if (this.selectedItems.length === 0) return 0;
                 return Math.max(0, this.rawSubtotal + this.shippingCost - this.discountPromo);
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

        <!-- Local Toast Notification -->
        <div x-cloak x-show="toastMessage" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-stone-700 max-w-md">
            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs / Steps Navigation -->
            <nav class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-6">
                <a href="{{ url('/') }}" class="hover:text-stone-700 transition">BERANDA</a>
                <span class="text-stone-300 font-normal">&gt;</span>
                <span class="text-stone-900 font-extrabold">KERANJANG BELANJA</span>
                <span class="text-stone-300 font-normal">&gt;</span>
                <span class="text-stone-400 font-medium">PENGIRIMAN</span>
                <span class="text-stone-300 font-normal">&gt;</span>
                <span class="text-stone-400 font-medium">PEMBAYARAN</span>
            </nav>

            <!-- Page Title Row -->
            <div class="mb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#F5ECE5] text-[#8D5B4C] text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#8D5B4C]"></span>
                    <span>KOLEKSI PILIHAN ANDA</span>
                </div>
                <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-stone-900">
                    Keranjang Belanja <span class="font-sans font-normal text-stone-500 text-2xl sm:text-3xl" x-text="'(' + items.length + ' Item)'"></span>
                </h1>
            </div>

            <!-- Empty Cart State (when no products are added) -->
            <div x-cloak x-show="items.length === 0" class="bg-white rounded-3xl p-12 sm:p-16 border border-[#ECE4D8] text-center shadow-xs max-w-2xl mx-auto my-6">
                <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-[#FAF7F2] border border-[#ECE4D8] flex items-center justify-center text-stone-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 class="font-serif-title text-2xl font-bold text-stone-900">Keranjang Belanja Anda Kosong</h3>
                <p class="text-sm text-stone-500 mt-2 max-w-md mx-auto leading-relaxed">
                    Anda belum menambahkan produk ke keranjang belanja. Jelajahi busana batik autentik dan kriya olahan kain sisa kami.
                </p>
                <div class="mt-8">
                    <a href="{{ route('koleksi.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#201A17] text-white text-sm font-semibold hover:bg-stone-800 transition shadow-sm">
                        <span>Mulai Belanja</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Active Cart: 2 Columns Layout (when items exist) -->
            <div x-cloak x-show="items.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Items List & Actions (8 Cols) -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- Table Header Row: Select All & Delete Selected -->
                    <div class="flex items-center justify-between pb-1 text-xs font-semibold text-stone-700">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" 
                                   :checked="allSelected" 
                                   @change="toggleSelectAll()"
                                   class="w-4 h-4 rounded text-stone-900 focus:ring-stone-900 border-stone-300 accent-[#201A17] cursor-pointer">
                            <span class="text-xs font-bold text-stone-800">
                                Pilih Semua (<span x-text="items.length"></span> Item)
                            </span>
                        </label>

                        <button @click="removeSelected()" 
                                class="inline-flex items-center gap-1.5 text-stone-400 hover:text-rose-600 transition text-xs font-medium cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            <span>Hapus Produk Terpilih</span>
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-4">
                        <template x-for="item in items" :key="item.id">
                            <div class="bg-white rounded-3xl p-5 border border-[#ECE4D8] shadow-xs hover:border-stone-300 transition-all flex items-center gap-4">
                                
                                <!-- Checkbox (Centered vertically beside image) -->
                                <div class="shrink-0">
                                    <input type="checkbox" 
                                           x-model="item.selected" 
                                           @change="toggleItem(item)"
                                           class="w-4 h-4 rounded text-stone-900 focus:ring-stone-900 border-stone-300 accent-[#201A17] cursor-pointer">
                                </div>

                                <!-- Product Image container with badge in top-left -->
                                <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-[#FAF2EA] border border-[#ECE4D8] shrink-0 flex items-center justify-center">
                                    <span class="absolute top-2 left-2 z-10 text-[9px] font-bold px-2 py-0.5 rounded bg-white/95 text-stone-700 uppercase tracking-wider shadow-2xs"
                                          x-text="item.kategoriBadge">
                                    </span>
                                    <template x-if="item.gambar">
                                        <img :src="item.gambar" :alt="item.nama" class="w-full h-full object-cover">
                                    </template>
                                </div>

                                <!-- Info & Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-stone-400 block" 
                                                  x-text="item.kategoriSub"></span>
                                            <h3 class="font-bold text-stone-900 text-sm sm:text-base leading-snug mt-0.5" 
                                                x-text="item.nama"></h3>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#FAF4ED] border border-[#ECE4D8] text-stone-600 text-[11px] font-medium"
                                                      x-text="item.varian"></span>
                                            </div>
                                        </div>

                                        <!-- Trash Button in Top-Right -->
                                        <button @click="removeItem(item.id)" 
                                                class="text-stone-300 hover:text-rose-600 p-1 rounded-lg transition cursor-pointer" 
                                                title="Hapus Barang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Bottom Row: Harga Satuan, Stepper, Subtotal -->
                                    <div class="flex flex-wrap items-end justify-between gap-3 mt-3 pt-2">
                                        <div>
                                            <span class="text-[10px] text-stone-400 block font-medium">Harga Satuan</span>
                                            <span class="text-xs sm:text-sm font-bold text-stone-900" x-text="formatRupiah(item.harga)"></span>
                                        </div>

                                        <!-- Stepper -->
                                        <div class="inline-flex items-center rounded-xl bg-[#FAF7F2] border border-stone-200 p-0.5">
                                            <button @click="decreaseQty(item)" 
                                                    class="w-7 h-7 rounded-lg hover:bg-white flex items-center justify-center text-stone-600 font-bold transition text-xs cursor-pointer">
                                                -
                                            </button>
                                            <span class="w-8 text-center text-xs font-bold text-stone-900" x-text="item.qty"></span>
                                            <button @click="increaseQty(item)" 
                                                    class="w-7 h-7 rounded-lg hover:bg-white flex items-center justify-center text-stone-600 font-bold transition text-xs cursor-pointer">
                                                +
                                            </button>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[10px] text-stone-400 block font-medium text-right">Subtotal</span>
                                            <span class="text-sm sm:text-base font-extrabold text-stone-900 text-right" x-text="formatRupiah(item.harga * item.qty)"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                </div>

                <!-- Right Column: Order Summary (4 Cols) -->
                <div class="lg:col-span-4 sticky top-28 space-y-5">
                    
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#ECE4D8] shadow-xs space-y-5">
                        <div class="flex items-center justify-between pb-1">
                            <h2 class="font-bold text-stone-900 text-base">
                                Ringkasan Belanja
                            </h2>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#FAF3EA] text-stone-600" 
                                  x-text="totalSelectedQty + ' Barang'">
                            </span>
                        </div>

                        <!-- Rows Breakdown -->
                        <div class="space-y-3 text-xs sm:text-sm text-stone-600">
                            <div class="flex items-center justify-between">
                                <span x-text="'Total Harga (' + totalSelectedQty + ' barang)'"></span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(rawSubtotal)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <span>Estimasi Ongkos Kirim</span>
                                    <span class="text-stone-400 text-xs cursor-pointer" title="Ongkos kirim flat kurir reguler">ⓘ</span>
                                </span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(shippingCost)"></span>
                            </div>
                        </div>

                        <!-- Divider & Total Tagihan -->
                        <div class="pt-4 border-t border-[#ECE4D8]">
                            <div class="flex items-baseline justify-between">
                                <div>
                                    <span class="text-sm font-bold text-stone-900 block">Total Tagihan</span>
                                    <p class="text-[10px] text-stone-400 mt-0.5">
                                        Termasuk PPN & Kemasan Ramah Lingkungan
                                    </p>
                                </div>
                                <span class="font-serif-title text-2xl sm:text-3xl font-extrabold text-stone-900" 
                                      x-text="formatRupiah(grandTotal)"></span>
                            </div>
                        </div>

                        <!-- Main Checkout CTA -->
                        <button @click="proceedCheckout()" 
                                :disabled="totalSelectedQty === 0"
                                :class="totalSelectedQty === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-stone-800'"
                                class="w-full py-3.5 px-6 rounded-2xl bg-[#201A17] text-white font-semibold text-sm transition duration-200 shadow-sm flex items-center justify-center gap-2 group">
                            <span x-text="'Checkout (' + totalSelectedQty + ' Item)'"></span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </button>

                        <!-- Trust Badges List -->
                        <div class="pt-4 border-t border-[#ECE4D8] space-y-3.5 text-xs text-stone-600">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-[#FAF3EA] text-[#8D5B4C] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-xs">Jaminan Transaksi Aman</strong>
                                    <span class="text-[11px] text-stone-400">Enkripsi 256-bit dan escrow terlindungi</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-[#FAF3EA] text-[#8D5B4C] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-xs">100% Produk Autentik</strong>
                                    <span class="text-[11px] text-stone-400">Batik cap, tulis, & olahan kriya bergaransi</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full bg-[#FAF3EA] text-[#8D5B4C] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                                </div>
                                <div>
                                    <strong class="text-stone-900 block font-semibold text-xs">Dukungan WhatsApp 24 Jam</strong>
                                    <span class="text-[11px] text-stone-400">Bantuan personal dan konsultasi ukuran busana</span>
                                </div>
                            </div>
                        </div>

                        <!-- Official Payment Logos -->
                        <div class="pt-4 border-t border-[#ECE4D8] text-center">
                            <span class="text-[10px] text-stone-400 uppercase tracking-widest block mb-2 font-semibold">
                                Metode Pembayaran Resmi
                            </span>
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="px-2.5 py-1 rounded-md bg-[#FAF3EA] text-[11px] font-bold text-stone-700 border border-[#ECE4D8]">BCA VA</span>
                                <span class="px-2.5 py-1 rounded-md bg-[#FAF3EA] text-[11px] font-bold text-stone-700 border border-[#ECE4D8]">Mandiri</span>
                                <span class="px-2.5 py-1 rounded-md bg-[#FAF3EA] text-[11px] font-bold text-stone-700 border border-[#ECE4D8]">QRIS</span>
                                <span class="px-2.5 py-1 rounded-md bg-[#FAF3EA] text-[11px] font-bold text-stone-700 border border-[#ECE4D8]">ShopeePay</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ================= CROSS-SELL SECTION: LENGKAPI PENAMPILAN ANDA ================= -->
            <section x-cloak x-show="recommendations.length > 0" class="mt-20 pt-10 border-t border-[#ECE4D8]">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-stone-400 block mb-1">
                            HARMONI BUSANA
                        </span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-stone-900">
                            Lengkapi Penampilan Anda
                        </h2>
                    </div>

                    <!-- Carousel Navigation Arrows -->
                    <div class="flex items-center gap-2">
                        <button @click="showToast('Rekomendasi sebelumnya')" 
                                class="w-9 h-9 rounded-xl border border-[#ECE4D8] hover:border-stone-400 bg-white flex items-center justify-center text-stone-700 transition shadow-2xs cursor-pointer">
                            &lt;
                        </button>
                        <button @click="showToast('Rekomendasi berikutnya')" 
                                class="w-9 h-9 rounded-xl border border-[#ECE4D8] hover:border-stone-400 bg-white flex items-center justify-center text-stone-700 transition shadow-2xs cursor-pointer">
                            &gt;
                        </button>
                    </div>
                </div>

                <!-- Product Cards Grid from Database -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <template x-for="(prod, idx) in recommendations" :key="idx">
                        <div class="bg-white rounded-3xl p-3 border border-[#ECE4D8] shadow-xs hover:shadow-md transition flex flex-col group">
                            
                            <!-- Image area: tall aspect ratio with rounded-2xl -->
                            <div class="relative aspect-[4/5] rounded-2xl overflow-hidden bg-[#FAF2EA] border border-[#ECE4D8]/60 flex items-center justify-center">
                                <span class="absolute top-2.5 left-2.5 z-10 text-[9px] font-bold px-2 py-0.5 rounded bg-white/95 text-stone-700 uppercase tracking-wider shadow-2xs"
                                      x-text="prod.badge"></span>
                                <template x-if="prod.gambar">
                                    <img :src="prod.gambar" :alt="prod.nama" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </template>
                            </div>

                            <!-- Content -->
                            <div class="pt-3 pb-1 px-1 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-stone-900 text-sm leading-snug line-clamp-1" x-text="prod.nama"></h3>
                                    <p class="text-xs text-stone-400 mt-0.5 line-clamp-1" x-text="prod.deskripsi"></p>
                                </div>

                                <div class="flex items-center justify-between pt-3 mt-1">
                                    <span class="text-sm sm:text-base font-bold text-stone-900" x-text="formatRupiah(prod.harga)"></span>
                                    
                                    <button @click="addRecommendation(prod)" 
                                            class="w-8 h-8 rounded-full bg-[#FAF3EA] hover:bg-[#201A17] hover:text-white text-stone-800 flex items-center justify-center font-bold text-sm transition shadow-2xs cursor-pointer"
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
    </div>
</x-storefront-layout>
