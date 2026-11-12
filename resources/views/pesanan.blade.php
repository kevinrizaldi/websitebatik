<x-storefront-layout title="Pesanan Saya — Hamzah Style Official">
    <div class="min-h-screen bg-[#FAF7F2] py-8 sm:py-10"
         x-data="{
             activeTab: 'semua',
             searchQuery: '',
             trackingModalOpen: false,
             detailModalOpen: false,
             reviewModalOpen: false,
             selectedOrder: null,
             reviewOrder: null,
             reviewDrafts: {},

             orders: [
                 @if(isset($orders) && $orders->isNotEmpty())
                     @foreach($orders as $ord)
                     @php
                         $deadline = $ord->getEffectivePaymentDeadline();
                         $isExpired = $ord->isPaymentExpired();
                         $statusRaw = $isExpired ? 'dibatalkan' : strtolower(trim($ord->status));
                         $statusKey = match($statusRaw) {
                             'belum dibayar', 'menunggu pembayaran' => 'menunggu_pembayaran',
                             'menunggu verifikasi', 'sudah dibayar' => 'diproses',
                             'diproses', 'sedang diproses' => 'diproses',
                             'dikirim', 'sedang dikirim' => 'dikirim',
                             'selesai' => 'selesai',
                             'batal', 'dibatalkan' => 'dibatalkan',
                             default => 'menunggu_pembayaran'
                         };
                         $statusLabel = match($statusRaw) {
                             'menunggu verifikasi' => 'Menunggu Verifikasi',
                             default => match($statusKey) {
                                 'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                 'diproses' => 'Diproses',
                                 'dikirim' => 'Dikirim',
                                 'selesai' => 'Selesai',
                                 'dibatalkan' => 'Dibatalkan',
                                 default => $ord->status
                             }
                         };
                         $trackingNo = $ord->tracking_number ?: ($ord->shipment?->tracking_number ?: ($ord->shipment?->no_resi ?: 'JNE-88291039912'));
                         $ordCourier = $ord->shipment?->ekspedisi ?: ($ord->shipment?->courier ?: 'JNE Reguler');
                         $firstItem = $ord->items->first();
                         $otherItemsCount = max(0, $ord->items->count() - 1);
                         $categoryTag = $firstItem?->produk?->kategori?->nama_kategori ?? 'BATIK KATUN PRIMISSIMA';
                         $categoryTag = strtoupper($categoryTag);
                     @endphp
                     {
                         id: '{{ $ord->code }}',
                         dbId: {{ $ord->id }},
                         tanggal: '{{ $ord->created_at->format('d M Y, H:i') }} WIB',
                         status: '{{ $statusKey }}',
                         statusLabel: '{{ $statusLabel }}',
                         categoryTag: '{{ addslashes($categoryTag) }}',
                         nama: '{{ addslashes($firstItem?->produk_name ?? 'Kemeja Batik Premium') }}',
                         varian: 'Ukuran: L • Lengan Panjang',
                         extraItemsCount: {{ $otherItemsCount }},
                         extraItemsText: '{{ $otherItemsCount > 0 ? "+ {$otherItemsCount} produk lainnya" : "" }}',
                         total: {{ (float) $ord->total_price }},
                         batasBayarText: '05 jam 22 menit',
                         kurir: '{{ addslashes($ordCourier) }}',
                         noResi: '{{ addslashes($trackingNo) }}',
                         estimasi: 'Estimasi tiba besok di Jakarta Selatan',
                         penerima: '{{ addslashes($ord->customer_name) }}',
                         metodePembayaran: '{{ addslashes($ord->payment_method ?? 'Transfer Bank') }}',
                         alamat: '{{ addslashes($ord->address) }}',
                         gambar: '{{ $firstItem?->produk ? $firstItem->produk->gambar_url : asset('images/beranda/folded-shirts.jpg') }}',
                         items: [
                             @foreach($ord->items as $it)
                             {
                                 produk_id: {{ $it->produk_id }},
                                 nama: '{{ addslashes($it->produk_name) }}',
                                 harga: {{ (float) $it->price }},
                                 qty: {{ $it->quantity }},
                                 gambar: '{{ $it->produk ? $it->produk->gambar_url : asset('images/beranda/folded-shirts.jpg') }}'
                             }@if(!$loop->last),@endif
                             @endforeach
                         ]
                     }@if(!$loop->last),@endif
                     @endforeach
                 @else
                     {{-- Mockup Default Data Preserving Exact Visuals from Design Image 2 --}}
                     {
                         id: 'HSO-20250218-0849',
                         dbId: 101,
                         tanggal: '18 Feb 2025, 14:30 WIB',
                         status: 'menunggu_pembayaran',
                         statusLabel: 'Menunggu Pembayaran',
                         categoryTag: 'BATIK KATUN PRIMISSIMA',
                         categoryIcon: 'tag',
                         nama: 'Kemeja Batik Parang Seling Premium',
                         varian: 'Ukuran: L • Lengan Panjang',
                         extraItemsCount: 2,
                         extraItemsText: '+ 2 produk lainnya',
                         total: 871000,
                         batasBayarText: '05 jam 22 menit',
                         kurir: 'JNE Reguler',
                         noResi: null,
                         estimasi: 'Estimasi tiba 20 Feb 2025',
                         penerima: 'Budi Santoso',
                         metodePembayaran: 'Transfer Bank BCA',
                         alamat: 'Jl. Senopati No. 42, RT 02 / RW 03, Kel. Senayan, Kec. Kebayoran Baru, Jakarta Selatan',
                         gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}',
                         items: [
                             { produk_id: 1, nama: 'Kemeja Batik Parang Seling Premium', harga: 385000, qty: 1, gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}' },
                             { produk_id: 2, nama: 'Rompi Kawung Sisa Tenun', harga: 330000, qty: 1, gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}' }
                         ]
                     },
                     {
                         id: 'HSO-20250218-0842',
                         dbId: 102,
                         tanggal: '18 Feb 2025, 09:30 WIB',
                         status: 'diproses',
                         statusLabel: 'Diproses',
                         categoryTag: 'UPCYCLED KRIYA NUSANTARA',
                         categoryIcon: 'refresh',
                         nama: 'Rompi Kawung Sisa Tenun Etnik',
                         varian: 'Ukuran: All Size • Edisi Terbatas',
                         extraItemsCount: 0,
                         extraItemsText: '',
                         total: 485000,
                         batasBayarText: null,
                         kurir: 'SiCepat Reguler',
                         noResi: '002938192831',
                         estimasi: 'Estimasi tiba 21 Feb 2025',
                         penerima: 'Budi Santoso',
                         metodePembayaran: 'Mandiri VA',
                         alamat: 'Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan',
                         gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}',
                         items: [
                             { produk_id: 3, nama: 'Rompi Kawung Sisa Tenun Etnik', harga: 485000, qty: 1, gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}' }
                         ]
                     },
                     {
                         id: 'HSO-20250214-0412',
                         dbId: 103,
                         tanggal: '14 Feb 2025, 10:15 WIB',
                         status: 'dikirim',
                         statusLabel: 'Dikirim',
                         categoryTag: 'ZERO-WASTE UPCYCLED CRAFT',
                         categoryIcon: 'refresh',
                         nama: 'Artisan Patchwork Batik Tote Bag',
                         varian: 'Varian: Soga Indigo Tan Leather • Edisi Terbatas',
                         extraItemsCount: 0,
                         extraItemsText: '',
                         total: 265000,
                         batasBayarText: null,
                         kurir: 'JNE Reguler',
                         noResi: 'JNE-88291039912',
                         estimasi: 'Estimasi tiba besok, 20 Feb 2025 di Jakarta Selatan',
                         penerima: 'Budi Santoso',
                         metodePembayaran: 'QRIS Gopay',
                         alamat: 'Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan',
                         gambar: '{{ asset('images/beranda/bags-accessories.jpg') }}',
                         items: [
                             { produk_id: 4, nama: 'Artisan Patchwork Batik Tote Bag', harga: 265000, qty: 1, gambar: '{{ asset('images/beranda/bags-accessories.jpg') }}' }
                         ]
                     },
                     {
                         id: 'HSO-20250129-0190',
                         dbId: 104,
                         tanggal: '29 Jan 2025, 16:40 WIB',
                         status: 'selesai',
                         statusLabel: 'Selesai',
                         categoryTag: 'BATIK TULIS HALUS CANTING',
                         categoryIcon: 'pen',
                         nama: 'Kain Batik Tulis Motif Truntum Sutra',
                         varian: 'Dimensi: 240 x 115 cm • Bahan Sutra ATBM',
                         extraItemsCount: 0,
                         extraItemsText: '',
                         total: 650000,
                         batasBayarText: null,
                         kurir: 'JNE YES',
                         noResi: 'JNE-77182901239',
                         estimasi: 'Telah Diterima',
                         penerima: 'Diterima oleh Rian A.',
                         metodePembayaran: 'Transfer Bank BCA',
                         alamat: 'Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan',
                         gambar: '{{ asset('images/beranda/cloth-fabric.jpg') }}',
                         items: [
                             { produk_id: 5, nama: 'Kain Batik Tulis Motif Truntum Sutra', harga: 650000, qty: 1, gambar: '{{ asset('images/beranda/cloth-fabric.jpg') }}' }
                         ]
                     },
                     {
                         id: 'HSO-20250115-0102',
                         dbId: 105,
                         tanggal: '15 Jan 2025, 11:20 WIB',
                         status: 'selesai',
                         statusLabel: 'Selesai',
                         categoryTag: 'BATIK KATUN PRIMISSIMA',
                         categoryIcon: 'tag',
                         nama: 'Kemeja Batik Katun Parang Seling Kencana',
                         varian: 'Ukuran: L • Lengan Panjang',
                         extraItemsCount: 0,
                         extraItemsText: '',
                         total: 385000,
                         batasBayarText: null,
                         kurir: 'JNE Reguler',
                         noResi: 'JNE-66152431092',
                         estimasi: 'Telah Diterima',
                         penerima: 'Diterima oleh Budi Santoso',
                         metodePembayaran: 'BCA VA',
                         alamat: 'Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan',
                         gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}',
                         items: [
                             { produk_id: 6, nama: 'Kemeja Batik Katun Parang Seling Kencana', harga: 385000, qty: 1, gambar: '{{ asset('images/beranda/folded-shirts.jpg') }}' }
                         ]
                     }
                 @endif
             ],

             get filteredOrders() {
                 return this.orders.filter(order => {
                     const matchTab = (this.activeTab === 'semua') || (order.status === this.activeTab);
                     const q = this.searchQuery.trim().toLowerCase();
                     if (!q) return matchTab;
                     const matchId = order.id.toLowerCase().includes(q);
                     const matchName = order.nama.toLowerCase().includes(q);
                     return matchTab && (matchId || matchName);
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
             },

             formatRupiah(num) {
                 return 'Rp ' + Number(num).toLocaleString('id-ID');
             },

             copyText(txt) {
                 if (!txt) return;
                 navigator.clipboard.writeText(txt);
                 window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Berhasil disalin: ' + txt }));
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
                 this.reviewDrafts = { rating: 5, comment: '' };
                 this.reviewModalOpen = true;
             },

             submitReview() {
                 window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Terima kasih atas ulasan Anda!' }));
                 this.reviewModalOpen = false;
             }
         }">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
                {{-- Kolom Kiri: Sidebar Profil & Navigasi --}}
                <div class="lg:col-span-4">
                    <x-customer-sidebar active="pesanan" :user="Auth::user()" :alamatCount="Auth::check() ? Auth::user()->alamats()->count() : 2" :pesananAktif="4" />
                </div>

                {{-- Kolom Kanan: Pesanan Saya --}}
                <div class="space-y-6 lg:col-span-8">
                    {{-- Header Section --}}
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">Pesanan Saya</h1>
                        <p class="mt-1 text-xs text-stone-500 sm:text-sm">
                            Pantau proses kurasi, penjahitan berkelanjutan, serta status pengiriman paket karya batik dan kriya upcycled Anda.
                        </p>
                    </div>

                    {{-- Filter Status Tabs (Semua 5, Menunggu Pembayaran 1, Diproses 1, Dikirim 1, Selesai 2) --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs scrollbar-none sm:text-sm">
                        <button @click="activeTab = 'semua'"
                                :class="activeTab === 'semua' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-4 py-1.5 transition whitespace-nowrap">
                            <span>Semua</span>
                            <span class="text-xs" x-text="counts.semua">5</span>
                        </button>
                        <button @click="activeTab = 'menunggu_pembayaran'"
                                :class="activeTab === 'menunggu_pembayaran' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Menunggu Pembayaran</span>
                            <span class="text-xs" x-text="counts.menunggu_pembayaran">1</span>
                        </button>
                        <button @click="activeTab = 'diproses'"
                                :class="activeTab === 'diproses' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Diproses</span>
                            <span class="text-xs" x-text="counts.diproses">1</span>
                        </button>
                        <button @click="activeTab = 'dikirim'"
                                :class="activeTab === 'dikirim' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Dikirim</span>
                            <span class="text-xs" x-text="counts.dikirim">1</span>
                        </button>
                        <button @click="activeTab = 'selesai'"
                                :class="activeTab === 'selesai' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Selesai</span>
                            <span class="text-xs" x-text="counts.selesai">2</span>
                        </button>
                    </div>

                    {{-- Daftar Kartu Pesanan --}}
                    <div class="space-y-4">
                        <template x-for="order in filteredOrders" :key="order.id">
                            <div class="space-y-4 rounded-3xl border border-[#ECE4D8] bg-white p-5 shadow-xs sm:p-6 transition hover:border-[#DECDB8]">
                                {{-- Header Kartu: Kode Pesanan, Tanggal, Resi / Penerima, Status Badge --}}
                                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="flex flex-wrap items-center gap-2 text-stone-500">
                                        <button @click="copyText(order.id)" class="font-bold text-stone-900 hover:underline" title="Salin nomor pesanan">
                                            #<span x-text="order.id"></span>
                                        </button>
                                        <span>•</span>
                                        <span x-text="order.tanggal"></span>

                                        {{-- Resi Info (if shipped) --}}
                                        <template x-if="order.status === 'dikirim' && order.noResi">
                                            <div class="flex items-center gap-1.5">
                                                <span>•</span>
                                                <span>Resi: <span class="font-semibold text-stone-800" x-text="order.noResi"></span></span>
                                                <button @click="copyText(order.noResi)" class="text-stone-400 hover:text-stone-700" title="Salin nomor resi">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                </button>
                                            </div>
                                        </template>

                                        {{-- Diterima Oleh (if finished) --}}
                                        <template x-if="order.status === 'selesai' && order.penerima">
                                            <div class="flex items-center gap-1.5">
                                                <span>•</span>
                                                <svg class="h-3.5 w-3.5 text-stone-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                <span class="text-stone-700 font-medium" x-text="order.penerima.includes('Diterima oleh') ? order.penerima : 'Diterima oleh ' + order.penerima"></span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Status Pill --}}
                                    <div>
                                        <template x-if="order.status === 'menunggu_pembayaran'">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FDEEE8] px-3 py-1 text-xs font-semibold text-[#B84A39]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#B84A39]"></span>
                                                <span>Menunggu Pembayaran</span>
                                            </span>
                                        </template>
                                        <template x-if="order.status === 'diproses'">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200/80 px-3 py-1 text-xs font-semibold text-amber-900">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-700"></span>
                                                <span>Diproses</span>
                                            </span>
                                        </template>
                                        <template x-if="order.status === 'dikirim'">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-stone-600"></span>
                                                <span>Dikirim</span>
                                            </span>
                                        </template>
                                        <template x-if="order.status === 'selesai'">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#201A17] px-3 py-1 text-xs font-semibold text-white">
                                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                                <span>Selesai</span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Isi Kartu: Thumbnail, Tag Kategori, Judul, Varian, Total Belanja --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="order.gambar"
                                             :alt="order.nama"
                                             class="h-20 w-20 rounded-2xl object-cover border border-[#ECE4D8] shrink-0 bg-stone-100 shadow-2xs">
                                        <div>
                                            {{-- Tag Kategori dengan Ikon --}}
                                            <div class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-amber-800">
                                                <template x-if="order.categoryTag.includes('CRAFT') || order.categoryTag.includes('UPCYCLE')">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                                </template>
                                                <template x-if="order.categoryTag.includes('TULIS') || order.categoryTag.includes('CANTING')">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                                                </template>
                                                <template x-if="!order.categoryTag.includes('CRAFT') && !order.categoryTag.includes('UPCYCLE') && !order.categoryTag.includes('TULIS')">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                                                </template>
                                                <span x-text="order.categoryTag"></span>
                                            </div>

                                            {{-- Judul Produk --}}
                                            <h3 class="mt-0.5 text-sm font-bold text-stone-900 sm:text-base leading-snug" x-text="order.nama"></h3>

                                            {{-- Varian & Extra items --}}
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-stone-500">
                                                <span x-text="order.varian"></span>
                                                <template x-if="order.extraItemsText">
                                                    <span class="rounded bg-stone-100 px-1.5 py-0.5 text-[11px] font-semibold text-stone-600" x-text="order.extraItemsText"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kolom Total Belanja --}}
                                    <div class="text-left sm:text-right shrink-0">
                                        <span class="text-[11px] font-medium text-stone-400 block">Total Belanja</span>
                                        <span class="text-base font-bold text-stone-900 sm:text-lg block mt-0.5" x-text="formatRupiah(order.total)"></span>
                                    </div>
                                </div>

                                {{-- In-Card Tracking Banner (Khusus Dikirim) --}}
                                <template x-if="order.status === 'dikirim'">
                                    <div class="flex items-center justify-between rounded-2xl bg-[#FAF7F2] p-3.5 border border-[#ECE4D8]/80">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white border border-stone-200 text-stone-700 shadow-2xs shrink-0">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-stone-800" x-text="'Paket dalam perjalanan via ' + (order.kurir || 'JNE Reguler')"></p>
                                                <p class="text-[11px] text-stone-500" x-text="order.estimasi || 'Estimasi tiba besok di Jakarta Selatan'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 pr-2">
                                            <span class="h-2 w-2 rounded-full bg-stone-800"></span>
                                            <span class="h-2 w-2 rounded-full bg-stone-800"></span>
                                            <span class="h-2 w-2 rounded-full bg-stone-300"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Footer Kartu: Pesan Konfirmasi & Tombol Aksi --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-stone-100">
                                    {{-- Sisi Kiri: Pesan Info/Batas/Garansi --}}
                                    <div class="text-xs">
                                        <template x-if="order.status === 'menunggu_pembayaran'">
                                            <div class="flex items-center gap-2 text-stone-600">
                                                <svg class="h-4 w-4 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                                                <span>Batas pembayaran tersisa <strong class="text-stone-800" x-text="order.batasBayarText || '05 jam 22 menit'"></strong></span>
                                            </div>
                                        </template>

                                        <template x-if="order.status === 'dikirim'">
                                            <p class="text-stone-500">Konfirmasi penerimaan barang setelah pesanan Anda tiba dengan selamat.</p>
                                        </template>

                                        <template x-if="order.status === 'selesai'">
                                            <div class="flex items-center gap-2 text-stone-600">
                                                <svg class="h-4 w-4 text-stone-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                                <span>Garansi keaslian batik tulis & jahitan bersertifikat</span>
                                            </div>
                                        </template>

                                        <template x-if="order.status === 'diproses'">
                                            <p class="text-stone-500">Pesanan sedang dalam proses kurasi dan penjahitan oleh pengrajin.</p>
                                        </template>
                                    </div>

                                    {{-- Sisi Kanan: Tombol Aksi --}}
                                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                        {{-- Tombol Lihat Detail (Selalu Ada) --}}
                                        <button @click="openDetail(order)"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE4D5]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Lihat Detail</span>
                                        </button>

                                        {{-- Tombol Status Menunggu Pembayaran -> Bayar Sekarang --}}
                                        <template x-if="order.status === 'menunggu_pembayaran'">
                                            <a :href="'{{ url('/pembayaran') }}/' + order.id"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                                </svg>
                                                <span>Bayar Sekarang</span>
                                            </a>
                                        </template>

                                        {{-- Tombol Status Dikirim -> Lacak Pengiriman --}}
                                        <template x-if="order.status === 'dikirim'">
                                            <button @click="openTracking(order)"
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                                </svg>
                                                <span>Lacak Pengiriman</span>
                                            </button>
                                        </template>

                                        {{-- Tombol Status Selesai -> Beli Lagi & Berikan Ulasan --}}
                                        <template x-if="order.status === 'selesai'">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('koleksi.index') }}"
                                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE4D5]">
                                                    <span>Beli Lagi</span>
                                                </a>
                                                <button @click="openReview(order)"
                                                        type="button"
                                                        class="inline-flex items-center gap-1.5 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                                    </svg>
                                                    <span>Berikan Ulasan</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Empty State jika filter tidak menemukan pesanan --}}
                        <div x-cloak x-show="filteredOrders.length === 0"
                             class="rounded-3xl border border-[#ECE4D8] bg-white p-12 text-center shadow-xs">
                            <p class="text-sm font-semibold text-stone-700">Tidak ada pesanan pada status ini</p>
                            <p class="mt-1 text-xs text-stone-500">Silakan pilih tab filter status lainnya.</p>
                            <button @click="activeTab = 'semua'" class="mt-4 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white">Lihat Semua Pesanan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MODAL: DETAIL PESANAN ================= --}}
        <div x-cloak x-show="detailModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="detailModalOpen = false"></div>

            <div class="relative w-full max-w-xl rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8 max-h-[90vh] overflow-y-auto"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Detail Pesanan</h3>
                        <p class="text-xs text-stone-500 font-mono mt-0.5" x-text="selectedOrder ? '#' + selectedOrder.id : ''"></p>
                    </div>
                    <button @click="detailModalOpen = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-if="selectedOrder">
                    <div class="mt-5 space-y-5 text-sm">
                        {{-- Ringkasan Status & Waktu --}}
                        <div class="rounded-2xl bg-[#FAF7F2] p-4 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-stone-500 block">Status Transaksi</span>
                                <span class="font-bold text-stone-900 text-sm" x-text="selectedOrder.statusLabel"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-stone-500 block">Tanggal Pemesanan</span>
                                <span class="text-xs font-semibold text-stone-800" x-text="selectedOrder.tanggal"></span>
                            </div>
                        </div>

                        {{-- Daftar Produk --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-3">Item Pesanan</h4>
                            <div class="space-y-3">
                                <template x-for="(item, idx) in selectedOrder.items" :key="idx">
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-stone-100 p-3">
                                        <div class="flex items-center gap-3">
                                            <img :src="item.gambar" :alt="item.nama" class="h-12 w-12 rounded-lg object-cover bg-stone-100">
                                            <div>
                                                <p class="font-bold text-stone-900 text-xs sm:text-sm" x-text="item.nama"></p>
                                                <p class="text-xs text-stone-500" x-text="item.qty + ' barang'"></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-stone-900 text-xs sm:text-sm" x-text="formatRupiah(item.harga * item.qty)"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Info Pengiriman & Pembayaran --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-stone-100 pt-4 text-xs">
                            <div>
                                <span class="font-bold text-stone-400 uppercase tracking-wider block mb-1">Alamat Penerima</span>
                                <p class="font-semibold text-stone-900" x-text="selectedOrder.penerima"></p>
                                <p class="text-stone-600 mt-0.5 leading-relaxed" x-text="selectedOrder.alamat"></p>
                            </div>
                            <div>
                                <span class="font-bold text-stone-400 uppercase tracking-wider block mb-1">Pengiriman & Pembayaran</span>
                                <p class="text-stone-700">Kurir: <strong x-text="selectedOrder.kurir"></strong></p>
                                <template x-if="selectedOrder.noResi">
                                    <p class="text-stone-700">No. Resi: <strong class="font-mono" x-text="selectedOrder.noResi"></strong></p>
                                </template>
                                <p class="text-stone-700 mt-1">Metode: <strong x-text="selectedOrder.metodePembayaran"></strong></p>
                            </div>
                        </div>

                        {{-- Total Pembayaran --}}
                        <div class="border-t border-stone-100 pt-4 flex items-center justify-between">
                            <span class="font-bold text-stone-900 text-sm">Total Pembayaran</span>
                            <span class="text-lg font-extrabold text-stone-900" x-text="formatRupiah(selectedOrder.total)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ================= MODAL: TRACKING PENGIRIMAN ================= --}}
        <div x-cloak x-show="trackingModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="trackingModalOpen = false"></div>

            <div class="relative w-full max-w-md rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Lacak Pengiriman</h3>
                        <p class="text-xs text-stone-500 font-mono mt-0.5" x-text="selectedOrder ? '#' + selectedOrder.id : ''"></p>
                    </div>
                    <button @click="trackingModalOpen = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-if="selectedOrder">
                    <div class="mt-5 space-y-5">
                        <div class="rounded-2xl bg-[#FAF7F2] p-4 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-stone-500 block">Ekspedisi / Kurir:</span>
                                <span class="font-bold text-stone-900 text-sm" x-text="selectedOrder.kurir"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-stone-500 block">Nomor Resi:</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-stone-900 text-sm" x-text="selectedOrder.noResi"></span>
                                    <button @click="copyText(selectedOrder.noResi)" class="text-stone-400 hover:text-stone-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Timeline Pelacakan --}}
                        <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-stone-200 text-xs">
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-emerald-600 border-2 border-white"></div>
                                <div>
                                    <p class="font-bold text-stone-900">Paket Diterima Kurir JNE</p>
                                    <p class="text-stone-500 mt-0.5" x-text="selectedOrder.tanggal"></p>
                                </div>
                            </div>
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-emerald-600 border-2 border-white"></div>
                                <div>
                                    <p class="font-bold text-stone-900">Dalam Perjalanan ke Kota Tujuan</p>
                                    <p class="text-stone-500 mt-0.5">Manifest transit gateway Solo - Jakarta</p>
                                </div>
                            </div>
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-amber-600 ring-4 ring-amber-100 border-2 border-white"></div>
                                <div>
                                    <p class="font-bold text-amber-900">Sedang Diantar ke Alamat Tujuan</p>
                                    <p class="text-stone-500 mt-0.5" x-text="selectedOrder.estimasi"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ================= MODAL: BERIKAN ULASAN ================= --}}
        <div x-cloak x-show="reviewModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="reviewModalOpen = false"></div>

            <div class="relative w-full max-w-md rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Berikan Ulasan Produk</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Bagikan pengalaman Anda menggunakan karya batik ini.</p>
                    </div>
                    <button @click="reviewModalOpen = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Penilaian Bintang</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" @click="reviewDrafts.rating = star" class="p-1 focus:outline-none">
                                    <svg class="h-7 w-7 transition" :class="star <= reviewDrafts.rating ? 'text-amber-400 fill-amber-400' : 'text-stone-300 fill-none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label for="review-comment" class="block text-xs font-bold uppercase tracking-wider text-stone-400 mb-1">Komentar & Kesan Anda</label>
                        <textarea id="review-comment" x-model="reviewDrafts.comment" rows="4" placeholder="Kualitas kain sangat lembut dan corak parang sangat rapi..."
                                  class="w-full rounded-2xl border border-stone-200 bg-white p-3 text-sm focus:border-stone-400 focus:outline-none"></textarea>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-2">
                        <button @click="reviewModalOpen = false" type="button" class="rounded-xl px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Batal</button>
                        <button @click="submitReview()" type="button" class="rounded-xl bg-[#201A17] px-6 py-2.5 text-xs font-semibold text-white hover:bg-stone-800">Kirim Ulasan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-storefront-layout>
