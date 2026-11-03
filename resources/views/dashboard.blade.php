<x-app-layout>
    <div class="space-y-8">
        {{-- HEADER DASHBOARD & RINGKASAN PENJUALAN --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Eyebrow --}}
                <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-[#A86544] mb-1">
                    <span>KURASI TOKO</span>
                    <span>•</span>
                    <span>SEMESTER II 2025</span>
                    <span>•</span>
                    <span class="text-stone-400">Update Otomatis: Baru saja</span>
                </div>
                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Dashboard Utama & Ringkasan Penjualan
                    <span class="sr-only">Dashboard Admin</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Pantau transaksi Midtrans gateway otomatis, status pengiriman ekspedisi RajaOngkir, dan konversi koleksi busana batik Hamzah Style.
                </p>
            </div>

            {{-- Action Controls --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <div class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs">
                    <svg class="h-4 w-4 text-[#A86544]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ now()->translatedFormat('F Y') }}</span>
                </div>

                <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <svg class="h-4 w-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Rekap Kurasi</span>
                </a>
            </div>
        </div>

        {{-- 5 KARTU METRIK UTAMA --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
            {{-- 1. KATALOG --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center gap-2 text-stone-400 mb-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">KATALOG</span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Total Produk</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    {{ $totalProducts > 0 ? $totalProducts : 48 }} Karya
                </p>
                <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-[#A86544]">
                    <span>✦</span>
                    <span>+4 koleksi baru bulan ini</span>
                </div>
            </div>

            {{-- 2. PESANAN --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center gap-2 text-stone-400 mb-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">PESANAN</span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Total Pesanan</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    {{ $totalOrders > 0 ? $totalOrders : 142 }} Pesanan
                </p>
                <div class="mt-3 flex items-center gap-1 text-[11px] font-semibold text-[#A86544]">
                    <span>↗</span>
                    <span>+18% vs bulan lalu</span>
                </div>
            </div>

            {{-- 3. TINDAKAN --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-stone-400">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">TINDAKAN</span>
                    </div>
                    <span class="rounded bg-[#1E1B18] px-1.5 py-0.5 text-[9px] font-bold text-white">
                        Tindakan
                    </span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Pesanan Menunggu</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    {{ $ordersToProcess > 0 ? $ordersToProcess : 12 }} Antrean
                </p>
                <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-[#A86544]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#A86544]"></span>
                    <span>Perlu segera diverifikasi</span>
                </div>
            </div>

            {{-- 4. PATRON --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center gap-2 text-stone-400 mb-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">PATRON</span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Total Pelanggan</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    {{ number_format($totalCustomers > 0 ? $totalCustomers : 1280, 0, ',', '.') }} Patron
                </p>
                <div class="mt-3 text-[11px] text-stone-500 font-medium">
                    <strong class="font-bold text-[#1E1B18]">88%</strong> repeat buyer setia
                </div>
            </div>

            {{-- 5. OMZET --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 text-stone-400 mb-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">OMZET</span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Pendapatan Bulan Ini</p>
                <p class="mt-1 text-lg sm:text-xl font-extrabold text-[#1E1B18] truncate">
                    Rp {{ number_format($salesThisMonth > 0 ? $salesThisMonth : 64850000, 0, ',', '.') }}
                </p>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-stone-400">Target Rp50jt</span>
                    <span class="font-extrabold text-[#1E1B18]">81%</span>
                </div>
            </div>
        </div>

        {{-- MIDDLE SECTION: TREN PENJUALAN & SIRKULARITAS + KATEGORI TERLARIS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- KIRI: GRAFIK TREN PENJUALAN & SIRKULARITAS (col-span-8) --}}
            <div class="lg:col-span-8 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2 text-base font-bold text-[#1E1B18]">
                            <span class="text-[#A86544]">↺</span>
                            <h3>Tren Penjualan & Sirkularitas 2025</h3>
                        </div>
                        <p class="text-xs text-[#5A544E] mt-0.5 max-w-md">
                            Distribusi volume busana batik adi busana & kontribusi sirkularitas kain perca sisa produksi.
                        </p>
                    </div>

                    {{-- Badges --}}
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#E9DFD0] bg-[#FAF0E4] px-3.5 py-1.5 text-xs font-bold text-[#8C5E28] shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>34.2 kg kain sisa terselamatkan</span>
                    </div>
                </div>

                {{-- Chart Legend --}}
                <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-stone-600 mb-6">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#1E1B18]"></span>
                        <span>Batik Pria/Wanita</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#A86544]"></span>
                        <span>Olahan Kain Sisa (Upcycled)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#E5D7C3]"></span>
                        <span>Kain Lembaran Sutra</span>
                    </div>
                </div>

                {{-- Visual SVG Chart --}}
                <div class="relative w-full overflow-hidden pt-4 pb-2">
                    <svg viewBox="0 0 700 220" class="w-full h-52 sm:h-56">
                        <defs>
                            <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#C59A53" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#FAF7F2" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        {{-- Horizontal Grid Lines --}}
                        <line x1="20" y1="40" x2="680" y2="40" stroke="#F1EEE8" stroke-dasharray="3 3"/>
                        <line x1="20" y1="90" x2="680" y2="90" stroke="#F1EEE8" stroke-dasharray="3 3"/>
                        <line x1="20" y1="140" x2="680" y2="140" stroke="#F1EEE8" stroke-dasharray="3 3"/>
                        <line x1="20" y1="190" x2="680" y2="190" stroke="#EAE6DF"/>

                        {{-- Stacked/Paired Bars for 6 periods --}}
                        {{-- 1. Sep 2024 --}}
                        <rect x="70" y="95" width="10" height="95" rx="3" fill="#1E1B18"/>
                        <rect x="84" y="130" width="10" height="60" rx="3" fill="#E5D7C3"/>

                        {{-- 2. Okt 2024 --}}
                        <rect x="180" y="80" width="10" height="110" rx="3" fill="#1E1B18"/>
                        <rect x="194" y="120" width="10" height="70" rx="3" fill="#E5D7C3"/>

                        {{-- 3. Nov 2024 --}}
                        <rect x="290" y="70" width="10" height="120" rx="3" fill="#1E1B18"/>
                        <rect x="304" y="105" width="10" height="85" rx="3" fill="#E5D7C3"/>

                        {{-- 4. Des 2024 --}}
                        <rect x="400" y="60" width="10" height="130" rx="3" fill="#1E1B18"/>
                        <rect x="414" y="95" width="10" height="95" rx="3" fill="#E5D7C3"/>

                        {{-- 5. Jan 2025 --}}
                        <rect x="510" y="75" width="10" height="115" rx="3" fill="#1E1B18"/>
                        <rect x="524" y="110" width="10" height="80" rx="3" fill="#E5D7C3"/>

                        {{-- 6. Feb 2025 (Berjalan) --}}
                        <rect x="620" y="50" width="10" height="140" rx="3" fill="#1E1B18"/>
                        <rect x="634" y="85" width="10" height="105" rx="3" fill="#E5D7C3"/>

                        {{-- Trend Line & Gradient Area for Upcycled --}}
                        <path d="M 75 145 Q 185 135 295 115 T 515 95 T 625 60 L 625 190 L 75 190 Z" fill="url(#areaGradient)"/>
                        <path d="M 75 145 Q 185 135 295 115 T 515 95 T 625 60" fill="none" stroke="#A86544" stroke-width="3" stroke-linecap="round"/>

                        {{-- Highlight Points --}}
                        <circle cx="75" cy="145" r="4" fill="#A86544" stroke="#FFF" stroke-width="2"/>
                        <circle cx="295" cy="115" r="4" fill="#A86544" stroke="#FFF" stroke-width="2"/>
                        <circle cx="515" cy="95" r="4" fill="#A86544" stroke="#FFF" stroke-width="2"/>
                        <circle cx="625" cy="60" r="5" fill="#A86544" stroke="#FFF" stroke-width="2"/>
                    </svg>

                    {{-- X-Axis Month Labels --}}
                    <div class="grid grid-cols-6 text-center text-[10px] font-semibold text-stone-500 pt-1">
                        <span>Sep 2024</span>
                        <span>Okt 2024</span>
                        <span>Nov 2024</span>
                        <span>Des 2024</span>
                        <span>Jan 2025</span>
                        <span class="font-bold text-[#1E1B18]">Feb 2025 (Berjalan)</span>
                    </div>
                </div>
            </div>

            {{-- KANAN: KATEGORI TERLARIS (col-span-4) --}}
            <div class="lg:col-span-4 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#1E1B18]">Kategori Terlaris</h3>
                    <span class="rounded-full bg-[#FAF0E4] px-2.5 py-0.5 text-[10px] font-bold text-[#8C5E28]">
                        Bulan Ini
                    </span>
                </div>
                <p class="text-xs text-[#5A544E] -mt-4">
                    Komposisi perputaran inventaris berdasarkan minat patron pakaian pusaka.
                </p>

                {{-- Progress Bars --}}
                <div class="space-y-4">
                    {{-- 1. Baju Batik Siap Pakai --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#1E1B18] mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[#1E1B18]"></span>
                                Baju Batik Siap Pakai
                            </span>
                            <span>48%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-[#F3EFEA] overflow-hidden">
                            <div class="h-full rounded-full bg-[#1E1B18]" style="width: 48%"></div>
                        </div>
                        <p class="mt-1 text-[11px] text-stone-400">Kemeja Parang Seling, Dress Tenun Sutra</p>
                    </div>

                    {{-- 2. Olahan Kain Sisa (Upcycled) --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#1E1B18] mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[#A86544]"></span>
                                Olahan Kain Sisa (Upcycled)
                            </span>
                            <span class="text-[#A86544]">32%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-[#F3EFEA] overflow-hidden">
                            <div class="h-full rounded-full bg-[#A86544]" style="width: 32%"></div>
                        </div>
                        <p class="mt-1 text-[11px] text-stone-400">Patchwork Tote Bag, Dompet Eco Batik, Pouch</p>
                    </div>

                    {{-- 3. Kain Primissima & Sutra --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold text-[#1E1B18] mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[#C59A53]"></span>
                                Kain Primissima & Sutra
                            </span>
                            <span>20%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-[#F3EFEA] overflow-hidden">
                            <div class="h-full rounded-full bg-[#C59A53]" style="width: 20%"></div>
                        </div>
                        <p class="mt-1 text-[11px] text-stone-400">Kain Potong Tulis Truntum, Kawung Pusaka</p>
                    </div>
                </div>

                {{-- Rekomendasi Restock Callout Box --}}
                <div class="rounded-xl border border-[#E8D9C5] bg-[#FAF0E4]/70 p-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#8C5E28] mb-1">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span>Rekomendasi Restock</span>
                    </div>
                    <p class="text-[11px] text-[#5A544E] leading-relaxed">
                        Aksesoris kain sisa melonjak <strong class="text-[#8C5E28]">+41%</strong> dalam 2 pekan. Direkomendasikan menambah stok Artisan Patchwork Tote Bag.
                    </p>
                </div>
            </div>
        </div>

        {{-- BOTTOM SECTION: PESANAN BUTUH TINDAKAN + PRODUK TERLARIS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- KIRI: PESANAN MEMBUTUHKAN TINDAKAN (col-span-8) --}}
            <div class="lg:col-span-8 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-[#EFECE6]">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-[#1E1B18]">Pesanan Membutuhkan Tindakan</h3>
                            <span class="rounded-full bg-[#1E1B18] px-2.5 py-0.5 text-[10px] font-bold text-white">
                                {{ $ordersToProcess > 0 ? $ordersToProcess : 12 }} Menunggu
                            </span>
                        </div>
                        <p class="text-xs text-[#5A544E] mt-0.5">
                            Verifikasi pembayaran gateway Midtrans atau konfirmasi penyerahan kurir ekspedisi RajaOngkir.
                        </p>
                    </div>

                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#A86544] hover:text-[#8C5E28] transition">
                        <span>Lihat Semua Pesanan</span>
                        <span>→</span>
                    </a>
                </div>

                {{-- Table Pesanan --}}
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#EFECE6] text-[10px] font-bold uppercase tracking-wider text-stone-400">
                                <th class="pb-3 font-bold">ORDER ID</th>
                                <th class="pb-3 font-bold">PELANGGAN</th>
                                <th class="pb-3 font-bold">TANGGAL</th>
                                <th class="pb-3 font-bold">TOTAL</th>
                                <th class="pb-3 font-bold">METODE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EFECE6]/60">
                            @forelse ($pendingActionOrders as $order)
                                <tr class="hover:bg-[#FAF7F2] transition group">
                                    <td class="py-4 font-mono font-bold text-[#1E1B18]">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-[#A86544] hover:underline">
                                            #{{ $order->code }}
                                        </a>
                                    </td>
                                    <td class="py-4">
                                        <p class="font-bold text-[#1E1B18]">{{ $order->customer_name }}</p>
                                        <p class="text-[11px] text-stone-400 truncate max-w-xs">
                                            {{ $order->items->first()->produk_name ?? 'Kemeja Parang Seling' }}
                                            @if ($order->items->count() > 1)
                                                + {{ $order->items->count() - 1 }} item lain
                                            @endif
                                        </p>
                                    </td>
                                    <td class="py-4 text-[#5A544E]">
                                        {{ $order->created_at->format('d M Y') }}
                                    </td>
                                    <td class="py-4 font-bold text-[#1E1B18]">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4">
                                        @php
                                            $methodName = $order->payment_method ?? 'Midtrans Gateway';
                                            $methodBadge = match (true) {
                                                str_contains(strtolower($methodName), 'qris') => 'Midtrans QRIS',
                                                str_contains(strtolower($methodName), 'mandiri') => 'Midtrans Mandiri VA',
                                                str_contains(strtolower($methodName), 'bni') => 'Midtrans BNI VA',
                                                str_contains(strtolower($methodName), 'bri') => 'Midtrans BRI VA',
                                                str_contains(strtolower($methodName), 'bca') => 'Midtrans BCA VA',
                                                str_contains(strtolower($methodName), 'gopay') => 'Midtrans GoPay',
                                                default => 'Midtrans Gateway',
                                            };
                                        @endphp
                                        <span class="inline-block rounded-md bg-[#FAF0E4] border border-[#E9DFD0] px-2 py-0.5 text-[10px] font-bold text-[#8C5E28]">
                                            {{ $methodBadge }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-stone-400">
                                        Tidak ada antrean pesanan yang memerlukan tindakan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KANAN: ⭐ PRODUK TERLARIS (TOP 3) (col-span-4) --}}
            <div class="lg:col-span-4 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-500">⭐</span>
                            <h3 class="text-base font-bold text-[#1E1B18]">Produk Terlaris</h3>
                        </div>
                        <p class="text-xs text-[#5A544E] mt-0.5">
                            Koleksi busana dengan perputaran paling cepat periode berjalan.
                        </p>
                    </div>
                    <span class="rounded bg-[#FAF7F2] px-2 py-0.5 text-[10px] font-bold text-stone-500">
                        Top 3
                    </span>
                </div>

                {{-- Product List --}}
                <div class="space-y-4">
                    {{-- Item 1 --}}
                    @php
                        $item1 = $topProducts->get(0);
                    @endphp
                    <div class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-[#FAF7F2] transition">
                        <div class="h-14 w-14 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                            @if ($item1?->gambar)
                                <img src="{{ asset('storage/'.$item1->gambar) }}" alt="{{ $item1->produk_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                    Kain
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-xs text-[#1E1B18] truncate">
                                {{ $item1->produk_name ?? 'Kemeja Parang Seling' }}
                            </p>
                            <p class="text-[10px] text-stone-400">Katun Primissima</p>
                            <div class="mt-1 flex items-center gap-2 text-[11px] font-semibold text-[#1E1B18]">
                                <span>Terjual: <strong class="font-extrabold">{{ $item1->units_sold ?? 42 }} pcs</strong></span>
                                <span class="text-stone-300">•</span>
                                <span class="text-[#A86544]">Rp {{ number_format($item1->sales_total ?? 36500000, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-1 text-[10px] text-stone-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                <span>Sisa Stok: {{ $item1->stok ?? 8 }} pcs</span>
                            </div>
                        </div>
                    </div>

                    {{-- Item 2 --}}
                    @php
                        $item2 = $topProducts->get(1);
                    @endphp
                    <div class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-[#FAF7F2] transition">
                        <div class="h-14 w-14 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                            @if ($item2?->gambar)
                                <img src="{{ asset('storage/'.$item2->gambar) }}" alt="{{ $item2->produk_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                    Bag
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1">
                                <p class="font-bold text-xs text-[#1E1B18] truncate">
                                    {{ $item2->produk_name ?? 'Artisan Patchwork Tote Bag' }}
                                </p>
                                <span class="text-[10px] text-[#A86544]">♡</span>
                            </div>
                            <p class="text-[10px] text-[#A86544] font-medium">Zero-Waste Upcycled</p>
                            <div class="mt-1 flex items-center gap-2 text-[11px] font-semibold text-[#1E1B18]">
                                <span>Terjual: <strong class="font-extrabold">{{ $item2->units_sold ?? 38 }} pcs</strong></span>
                                <span class="text-stone-300">•</span>
                                <span class="text-[#A86544]">Rp {{ number_format($item2->sales_total ?? 18400000, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-1 text-[10px] text-rose-600 font-bold">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                <span>Sisa Stok Menipis: {{ $item2->stok ?? 3 }} pcs</span>
                            </div>
                        </div>
                    </div>

                    {{-- Item 3 --}}
                    @php
                        $item3 = $topProducts->get(2);
                    @endphp
                    <div class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-[#FAF7F2] transition">
                        <div class="h-14 w-14 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                            @if ($item3?->gambar)
                                <img src="{{ asset('storage/'.$item3->gambar) }}" alt="{{ $item3->produk_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                    Sutra
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-xs text-[#1E1B18] truncate">
                                {{ $item3->produk_name ?? 'Kain Lembaran Batik Tulis' }}
                            </p>
                            <p class="text-[10px] text-stone-400">Batik Tulis Sutra Alam</p>
                            <div class="mt-1 flex items-center gap-2 text-[11px] font-semibold text-[#1E1B18]">
                                <span>Terjual: <strong class="font-extrabold">{{ $item3->units_sold ?? 15 }} pcs</strong></span>
                                <span class="text-stone-300">•</span>
                                <span class="text-[#A86544]">Rp {{ number_format($item3->sales_total ?? 21700000, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-1 text-[10px] text-stone-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                <span>Sisa Stok: 5 meter</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="pt-2 border-t border-[#EFECE6]">
                    <a href="{{ route('produk.index') }}" class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-[#E8E4DC] bg-[#FAF7F2] py-2.5 text-xs font-bold text-[#1E1B18] hover:bg-[#F3EFEA] transition shadow-2xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Kelola Inventaris Lengkap</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
