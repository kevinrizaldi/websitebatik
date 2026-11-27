<x-storefront-layout title="Pesanan Saya — Hamzah Style">
@php
    $ordersData = [];
    if (isset($orders) && $orders->isNotEmpty()) {
        foreach ($orders as $ord) {
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
                default => 'menunggu_pembayaran',
            };
            $statusLabel = match($statusRaw) {
                'menunggu verifikasi' => 'Menunggu Verifikasi',
                default => match($statusKey) {
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'diproses' => 'Diproses',
                    'dikirim' => 'Dikirim',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                    default => $ord->status,
                },
            };
            $trackingNo = $ord->tracking_number ?: ($ord->shipment?->tracking_number ?: ($ord->shipment?->no_resi ?: 'JNE-88291039912'));
            $ordCourier = $ord->shipment?->ekspedisi ?: ($ord->shipment?->courier ?: 'JNE Reguler');
            $firstItem = $ord->items->first();
            $otherItemsCount = max(0, $ord->items->count() - 1);
            $categoryTag = $firstItem?->produk?->kategori?->nama_kategori ?? 'BATIK KATUN PRIMISSIMA';
            $categoryTag = strtoupper($categoryTag);
            $diff = now()->diff($deadline);
            $isPast = $deadline->isPast() || $isExpired;
            if ($isPast) {
                $initialBatasBayar = 'Waktu pembayaran telah habis';
            } else {
                $totalHours = ($diff->d * 24) + $diff->h;
                $initialBatasBayar = sprintf('%02d jam %02d menit', $totalHours, $diff->i);
            }

            $orderItems = [];
            foreach ($ord->items as $it) {
                $itReviewed = isset($reviewMap[$ord->id][$it->produk_id]);
                $itReview = $reviewMap[$ord->id][$it->produk_id] ?? null;
                $orderItems[] = [
                    'produk_id' => (int) $it->produk_id,
                    'nama' => (string) ($it->produk_name ?? ''),
                    'harga' => (float) $it->price,
                    'qty' => (int) $it->quantity,
                    'gambar' => (string) ($it->produk ? $it->produk->gambar_url : asset('images/beranda/folded-shirts.jpg')),
                    'reviewed' => (bool) $itReviewed,
                    'review' => $itReview,
                ];
            }

            $ordersData[] = [
                'id' => (string) $ord->code,
                'dbId' => (int) $ord->id,
                'tanggal' => $ord->created_at->format('d M Y, H:i') . ' WIB',
                'status' => $statusKey,
                'statusLabel' => $statusLabel,
                'categoryTag' => $categoryTag,
                'nama' => (string) ($firstItem?->produk_name ?? 'Kemeja Batik Premium'),
                'varian' => 'Ukuran: ' . ($firstItem?->ukuran ?? 'L'),
                'extraItemsCount' => $otherItemsCount,
                'extraItemsText' => $otherItemsCount > 0 ? "+ {$otherItemsCount} produk lainnya" : '',
                'total' => (float) $ord->total_price,
                'batasBayarText' => $initialBatasBayar,
                'deadlineTimestamp' => $deadline->getTimestamp() * 1000,
                'kurir' => (string) $ordCourier,
                'noResi' => (string) $trackingNo,
                'estimasi' => 'Estimasi tiba besok di Jakarta Selatan',
                'penerima' => (string) ($ord->customer_name ?? ''),
                'metodePembayaran' => (string) ($ord->payment_method ?? 'Transfer Bank'),
                'alamat' => (string) ($ord->address ?? ''),
                'gambar' => (string) ($firstItem?->produk ? $firstItem->produk->gambar_url : asset('images/beranda/folded-shirts.jpg')),
                'items' => $orderItems,
            ];
        }
    }
@endphp

    <div class="min-h-screen bg-[#FAF7F2] py-8 sm:py-10" x-data="pesananPage()">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
                {{-- Kolom Kiri: Sidebar Profil & Navigasi --}}
                <div class="lg:col-span-4">
                    <x-customer-sidebar active="pesanan" :user="Auth::user()" :alamatCount="Auth::check() ? Auth::user()->alamats()->count() : 0" :pesananAktif="$pesananAktif ?? (Auth::check() ? Auth::user()->orders()->whereIn('status', ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi', 'Diproses', 'Sudah Dibayar', 'Dikirim', 'sedang dikirim', 'sedang diproses'])->count() : 0)" />
                </div>

                {{-- Kolom Kanan: Pesanan Saya --}}
                <div class="space-y-6 lg:col-span-8">
                    {{-- Header Section --}}
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">Pesanan Saya</h1>
                        <p class="mt-1 text-xs text-stone-500 sm:text-sm">
                            Pantau status pesanan dan pengiriman paket batik Anda di sini.
                        </p>
                    </div>

                    {{-- Filter Status Tabs --}}
                    @php
                        $ordersCol = collect($ordersData);
                    @endphp
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs scrollbar-none sm:text-sm">
                        <button @click="activeTab = 'semua'"
                                :class="activeTab === 'semua' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-4 py-1.5 transition whitespace-nowrap">
                            <span>Semua</span>
                            <span class="text-xs" x-text="counts.semua">{{ $ordersCol->count() }}</span>
                        </button>
                        <button @click="activeTab = 'menunggu_pembayaran'"
                                :class="activeTab === 'menunggu_pembayaran' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Menunggu Pembayaran</span>
                            <span class="text-xs" x-text="counts.menunggu_pembayaran">{{ $ordersCol->where('status', 'menunggu_pembayaran')->count() }}</span>
                        </button>
                        <button @click="activeTab = 'diproses'"
                                :class="activeTab === 'diproses' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Diproses</span>
                            <span class="text-xs" x-text="counts.diproses">{{ $ordersCol->where('status', 'diproses')->count() }}</span>
                        </button>
                        <button @click="activeTab = 'dikirim'"
                                :class="activeTab === 'dikirim' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Dikirim</span>
                            <span class="text-xs" x-text="counts.dikirim">{{ $ordersCol->where('status', 'dikirim')->count() }}</span>
                        </button>
                        <button @click="activeTab = 'selesai'"
                                :class="activeTab === 'selesai' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Selesai</span>
                            <span class="text-xs" x-text="counts.selesai">{{ $ordersCol->where('status', 'selesai')->count() }}</span>
                        </button>
                        <button @click="activeTab = 'dibatalkan'"
                                :class="activeTab === 'dibatalkan' ? 'bg-[#201A17] text-white font-semibold' : 'bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium'"
                                class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition whitespace-nowrap">
                            <span>Dibatalkan</span>
                            <span class="text-xs" x-text="counts.dibatalkan">{{ $ordersCol->where('status', 'dibatalkan')->count() }}</span>
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
                                        <template x-if="order.status === 'dibatalkan'">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-200/80 px-3 py-1 text-xs font-semibold text-rose-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                <span>Dibatalkan</span>
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
                                                <span>Batas pembayaran tersisa <strong class="text-stone-800" x-text="getBatasBayar(order)"></strong></span>
                                            </div>
                                        </template>

                                        <template x-if="order.status === 'dikirim'">
                                            <p class="text-stone-500">Konfirmasi penerimaan barang setelah pesanan Anda tiba dengan selamat.</p>
                                        </template>

                                        <template x-if="order.status === 'selesai'">
                                            <div class="flex items-center gap-2 text-stone-600">
                                                <svg class="h-4 w-4 text-stone-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                                <span>Garansi 100% batik asli dan jahitan rapi</span>
                                            </div>
                                        </template>

                                        <template x-if="order.status === 'diproses'">
                                            <p class="text-stone-500">Pesanan sedang dipersiapkan dan dikemas.</p>
                                        </template>

                                        <template x-if="order.status === 'dibatalkan'">
                                            <div class="flex items-center gap-2 text-stone-500">
                                                <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6"/></svg>
                                                <span>Pesanan telah dibatalkan</span>
                                            </div>
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

                                        {{-- Tombol Status Menunggu Pembayaran -> Batalkan & Bayar Sekarang --}}
                                        <template x-if="order.status === 'menunggu_pembayaran'">
                                            <div class="flex items-center gap-2">
                                                <form :action="'{{ url('/pesanan') }}/' + (order.dbId || order.id) + '/batal'" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-rose-600">
                                                        <span>Batalkan</span>
                                                    </button>
                                                </form>
                                                <a :href="'{{ url('/pembayaran') }}/' + order.id"
                                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                                    </svg>
                                                    <span>Bayar Sekarang</span>
                                                </a>
                                            </div>
                                        </template>

                                        {{-- Tombol Status Dibatalkan -> Beli Lagi --}}
                                        <template x-if="order.status === 'dibatalkan'">
                                            <a href="{{ route('koleksi.index') }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE4D5]">
                                                <span>Beli Lagi</span>
                                            </a>
                                        </template>

                                        {{-- Tombol Status Dikirim -> Lacak Pengiriman & Pesanan Diterima --}}
                                        <template x-if="order.status === 'dikirim'">
                                            <div class="flex items-center gap-2">
                                                <button @click="openTracking(order)"
                                                        type="button"
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-50">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                                    </svg>
                                                    <span>Lacak Pengiriman</span>
                                                </button>
                                                <form :action="'{{ url('/pesanan') }}/' + (order.dbId || order.id) + '/terima'"
                                                      method="POST"
                                                      onsubmit="return confirm('Konfirmasi bahwa Anda telah menerima paket pesanan ini dengan baik?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                                        <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                        </svg>
                                                        <span>Pesanan Diterima</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </template>

                                        {{-- Tombol Status Selesai -> Beli Lagi & Berikan Ulasan --}}
                                        <template x-if="order.status === 'selesai'">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('koleksi.index') }}"
                                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F5EDE1] px-4 py-2 text-xs font-semibold text-stone-800 transition hover:bg-[#EFE4D5]">
                                                    <span>Beli Lagi</span>
                                                </a>
                                                <button @click="openReview(order, order.items[0])"
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

                        {{-- Empty State jika belum ada pesanan atau filter tidak menemukan pesanan --}}
                        <div x-cloak x-show="filteredOrders.length === 0"
                             class="rounded-3xl border border-[#ECE4D8] bg-white p-10 text-center shadow-xs sm:p-12">
                            <template x-if="orders.length === 0">
                                <div class="mx-auto max-w-md py-4">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F5EDE1] text-stone-700">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-stone-900 sm:text-lg">Belum Ada Riwayat Pesanan</h3>
                                    <p class="mt-1.5 text-xs text-stone-500 sm:text-sm">Anda belum pernah melakukan pemesanan batik. Jelajahi katalog koleksi kami dan temukan busana favorit Anda.</p>
                                    <a href="{{ route('koleksi.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#201A17] px-5 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-stone-800 transition">
                                        <span>Mulai Belanja</span>
                                    </a>
                                </div>
                            </template>
                            <template x-if="orders.length > 0">
                                <div>
                                    <p class="text-sm font-semibold text-stone-700">Tidak ada pesanan pada status ini</p>
                                    <p class="mt-1 text-xs text-stone-500">Silakan pilih tab filter status lainnya.</p>
                                    <button @click="activeTab = 'semua'" class="mt-4 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white">Lihat Semua Pesanan</button>
                                </div>
                            </template>
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

                        {{-- Tombol Konfirmasi Pesanan Diterima di Modal Tracking --}}
                        <template x-if="selectedOrder.status === 'dikirim'">
                            <div class="pt-3 border-t border-stone-100">
                                <form :action="'{{ url('/pesanan') }}/' + (selectedOrder.dbId || selectedOrder.id) + '/terima'"
                                      method="POST"
                                      onsubmit="return confirm('Konfirmasi bahwa Anda telah menerima paket pesanan ini dengan baik?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#201A17] px-4 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                        <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                        <span>Konfirmasi Pesanan Diterima</span>
                                    </button>
                                </form>
                            </div>
                        </template>
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
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="closeReviewModal()"></div>

            <div class="relative w-full max-w-2xl rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8 max-h-[90vh] overflow-y-auto"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Berikan Ulasan Produk</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Ulasan hanya bisa dikirim untuk pesanan <span class="font-semibold">Selesai</span>. Satu produk = satu ulasan per pesanan.</p>
                        <p class="text-[11px] text-stone-400 font-mono mt-1" x-text="reviewOrder ? '#' + reviewOrder.id : ''"></p>
                    </div>
                    <button @click="closeReviewModal()" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-if="reviewOrder">
                    <div class="mt-5 space-y-4">
                        <template x-for="item in reviewOrder.items" :key="item.produk_id">
                            <div class="rounded-2xl border border-stone-100 p-4">
                                <div class="flex items-start gap-3">
                                    <img :src="item.gambar" :alt="item.nama" class="h-14 w-14 rounded-xl object-cover bg-stone-100 border border-stone-100 shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-bold text-stone-900 text-sm" x-text="item.nama"></p>
                                            <template x-if="item.reviewed">
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">Sudah diulas</span>
                                            </template>
                                        </div>
                                        <p class="text-[11px] text-stone-500" x-text="item.qty + ' barang'"></p>
                                    </div>
                                </div>

                                <template x-if="item.reviewed">
                                    <div class="mt-3 rounded-xl bg-stone-50 p-3 text-xs text-stone-600">
                                        <div class="text-amber-500 text-sm mb-1">
                                            <template x-for="s in [1,2,3,4,5]" :key="s"><span x-text="s <= (item.review?.rating || 0) ? '★' : '☆'"></span></template>
                                        </div>
                                        <p x-text="item.review?.comment || ''"></p>
                                    </div>
                                </template>

                                <template x-if="!item.reviewed && !reviewDrafts[item.produk_id]">
                                    <button @click="openReview(reviewOrder, item)"
                                            type="button"
                                            class="mt-3 w-full rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white hover:bg-stone-800">
                                        <span>Beri Ulasan</span>
                                    </button>
                                </template>

                                <template x-if="!item.reviewed && reviewDrafts[item.produk_id]">
                                    <div class="mt-3 space-y-3">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-1.5">Penilaian</label>
                                            <div class="flex items-center gap-1">
                                                <template x-for="star in [1,2,3,4,5]" :key="star">
                                                    <button type="button" @click="reviewDrafts[item.produk_id].rating = star" class="p-0.5 focus:outline-none">
                                                        <svg class="h-6 w-6 transition" :class="star <= reviewDrafts[item.produk_id].rating ? 'text-amber-400 fill-amber-400' : 'text-stone-300 fill-none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                                        </svg>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-1">Komentar</label>
                                            <textarea x-model="reviewDrafts[item.produk_id].comment" rows="3" placeholder="Bagikan pengalaman Anda tentang produk ini..."
                                                      class="w-full rounded-xl border border-stone-200 bg-white p-3 text-sm focus:border-stone-400 focus:outline-none"></textarea>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Media Produk (opsional)</label>
                                                <span class="text-[11px] text-stone-400 font-medium" x-text="(reviewDrafts[item.produk_id].photos ? reviewDrafts[item.produk_id].photos.length : 0) + '/3 Terunggah'"></span>
                                            </div>
                                            
                                            <!-- Preview & Tombol Unggah Foto -->
                                            <div class="flex flex-wrap items-center gap-2.5 mt-1">
                                                <!-- Preview Thumbnails -->
                                                <template x-for="(previewUrl, photoIdx) in (reviewDrafts[item.produk_id].photoPreviews || [])" :key="photoIdx">
                                                    <div class="relative h-16 w-16 rounded-xl border border-stone-200 overflow-hidden group bg-stone-50 shrink-0">
                                                        <img :src="previewUrl" alt="Preview foto ulasan" class="h-full w-full object-cover">
                                                        <button type="button"
                                                                @click.stop="removeReviewPhoto(item.produk_id, photoIdx)"
                                                                class="absolute top-1 right-1 h-5 w-5 rounded-full bg-stone-900/80 hover:bg-stone-900 text-white flex items-center justify-center transition shadow-xs"
                                                                title="Hapus foto">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                </template>

                                                <!-- Tombol Tambah Foto (maks 3) -->
                                                <template x-if="!reviewDrafts[item.produk_id].photos || reviewDrafts[item.produk_id].photos.length < 3">
                                                    <label class="h-16 w-16 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-stone-200 hover:border-stone-400 hover:bg-stone-50 cursor-pointer transition text-stone-400 hover:text-stone-600 shrink-0">
                                                        <svg class="h-5 w-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        <span class="text-[9px] font-semibold leading-none">Foto</span>
                                                        <input type="file"
                                                               accept="image/jpeg,image/png,image/webp"
                                                               multiple
                                                               @change="addReviewPhotos(item.produk_id, $event)"
                                                               class="sr-only">
                                                    </label>
                                                </template>
                                            </div>
                                            <p class="text-[10px] text-stone-400 mt-1.5">
                                                Maksimal 3 foto kain & produk riil (JPG, PNG, atau WebP, maks. 5 MB/foto).
                                            </p>
                                        </div>
                                        <p x-show="reviewDrafts[item.produk_id].error" x-text="reviewDrafts[item.produk_id].error" class="text-xs text-rose-600"></p>
                                        <div class="flex justify-end">
                                            <button @click="submitItemReview(item)" :disabled="reviewDrafts[item.produk_id].submitting" type="button" class="rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white hover:bg-stone-800 disabled:opacity-60">
                                                <span x-show="!reviewDrafts[item.produk_id].submitting">Kirim Ulasan Produk Ini</span>
                                                <span x-show="reviewDrafts[item.produk_id].submitting">Mengirim...</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-stone-100">
                            <button @click="closeReviewModal()" type="button" class="rounded-xl px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-100">Tutup</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function pesananPage() {
            return {
                activeTab: 'semua',
                searchQuery: '',
                trackingModalOpen: false,
                detailModalOpen: false,
                reviewModalOpen: false,
                selectedOrder: null,
                reviewOrder: null,
                reviewItem: null,
                reviewDrafts: {},
                orders: @json($ordersData),
                nowTime: Date.now(),

                init() {
                    setInterval(() => {
                        this.nowTime = Date.now();
                    }, 1000);
                },

                getBatasBayar(order) {
                    if (!order.deadlineTimestamp) {
                        return order.batasBayarText || 'Waktu pembayaran telah habis';
                    }
                    const diff = Math.floor((order.deadlineTimestamp - this.nowTime) / 1000);
                    if (diff <= 0) {
                        return 'Waktu pembayaran telah habis';
                    }
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;
                    if (hours > 0) {
                        return `${String(hours).padStart(2, '0')} jam ${String(minutes).padStart(2, '0')} menit`;
                    }
                    return `${String(minutes).padStart(2, '0')} menit ${String(seconds).padStart(2, '0')} detik`;
                },

                get filteredOrders() {
                    return this.orders.filter(order => {
                        const matchTab = (this.activeTab === 'semua') || (order.status === this.activeTab);
                        const q = this.searchQuery.trim().toLowerCase();
                        if (!q) return matchTab;
                        const matchId = (order.id || '').toLowerCase().includes(q);
                        const matchName = (order.nama || '').toLowerCase().includes(q);
                        return matchTab && (matchId || matchName);
                    });
                },

                get counts() {
                    return {
                        semua: this.orders.length,
                        menunggu_pembayaran: this.orders.filter(o => o.status === 'menunggu_pembayaran').length,
                        diproses: this.orders.filter(o => o.status === 'diproses').length,
                        dikirim: this.orders.filter(o => o.status === 'dikirim').length,
                        selesai: this.orders.filter(o => o.status === 'selesai').length,
                        dibatalkan: this.orders.filter(o => o.status === 'dibatalkan').length
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

                openReview(order, item) {
                    this.reviewOrder = order;
                    this.reviewItem = item;
                    this.reviewDrafts = {
                        [item.produk_id]: {
                            rating: 5,
                            comment: '',
                            photos: [],
                            photoPreviews: [],
                            submitting: false,
                            error: ''
                        }
                    };
                    this.reviewModalOpen = true;
                },

                closeReviewModal() {
                    this.reviewModalOpen = false;
                    Object.values(this.reviewDrafts || {}).forEach(draft => {
                        if (draft && Array.isArray(draft.photoPreviews)) {
                            draft.photoPreviews.forEach(url => URL.revokeObjectURL(url));
                        }
                    });
                },

                addReviewPhotos(produkId, event) {
                    const draft = this.reviewDrafts[produkId];
                    if (!draft) return;
                    if (!Array.isArray(draft.photos)) draft.photos = [];
                    if (!Array.isArray(draft.photoPreviews)) draft.photoPreviews = [];

                    const inputFiles = Array.from(event?.target?.files || []);
                    if (!inputFiles.length) return;

                    const remainingSlots = 3 - draft.photos.length;
                    if (remainingSlots <= 0) {
                        event.target.value = '';
                        return;
                    }

                    draft.error = '';
                    const toAdd = inputFiles.slice(0, remainingSlots);
                    for (const file of toAdd) {
                        if (!file.type.startsWith('image/')) {
                            draft.error = 'Hanya file gambar (JPG, PNG, WebP) yang diizinkan.';
                            continue;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            draft.error = `Ukuran foto ${file.name} melebihi 5 MB.`;
                            continue;
                        }
                        draft.photos.push(file);
                        draft.photoPreviews.push(URL.createObjectURL(file));
                    }
                    event.target.value = '';
                },

                removeReviewPhoto(produkId, photoIndex) {
                    const draft = this.reviewDrafts[produkId];
                    if (!draft || !Array.isArray(draft.photos)) return;

                    if (draft.photoPreviews && draft.photoPreviews[photoIndex]) {
                        URL.revokeObjectURL(draft.photoPreviews[photoIndex]);
                    }
                    draft.photos.splice(photoIndex, 1);
                    draft.photoPreviews.splice(photoIndex, 1);
                },

                async submitItemReview(item) {
                    const draft = this.reviewDrafts[item.produk_id];
                    if (!draft || draft.submitting) return;
                    draft.error = '';
                    if (!draft.comment || draft.comment.trim().length < 3) {
                        draft.error = 'Komentar minimal 3 karakter.';
                        return;
                    }
                    draft.submitting = true;
                    const form = new FormData();
                    form.append('produk_id', item.produk_id);
                    form.append('order_code', this.reviewOrder.id);
                    form.append('rating', draft.rating);
                    form.append('comment', draft.comment.trim());

                    if (Array.isArray(draft.photos) && draft.photos.length > 0) {
                        draft.photos.forEach(file => {
                            form.append('photos[]', file);
                        });
                        // Kompatibilitas field single 'image'
                        form.append('image', draft.photos[0]);
                    }

                    try {
                        const res = await fetch(@json(route('ulasan.store')), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': @json(csrf_token()),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: form,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || data.success === false) {
                            draft.error = data.message || 'Gagal mengirim ulasan. Coba lagi.';
                            return;
                        }

                        // Bebaskan memory ObjectURL
                        if (Array.isArray(draft.photoPreviews)) {
                            draft.photoPreviews.forEach(url => URL.revokeObjectURL(url));
                        }

                        item.reviewed = true;
                        item.review = { rating: draft.rating, comment: draft.comment.trim() };
                        delete this.reviewDrafts[item.produk_id];
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: data.message || 'Ulasan berhasil dikirim.' }));
                        this.reviewModalOpen = false;
                    } catch (e) {
                        draft.error = 'Tidak bisa terhubung. Periksa koneksi lalu coba lagi.';
                    } finally {
                        draft.submitting = false;
                    }
                },

                submitReview() {
                    const firstPending = (this.reviewOrder?.items || []).find(i => !i.reviewed && this.reviewDrafts[i.produk_id]);
                    if (firstPending) this.submitItemReview(firstPending);
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('pesananPage', pesananPage);
        });
    </script>
</x-storefront-layout>
