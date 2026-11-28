<x-app-layout>
    <div class="space-y-8">
        {{-- HEADER DASHBOARD & RINGKASAN PENJUALAN --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Dashboard Utama & Ringkasan Penjualan
                    <span class="sr-only">Dashboard Admin</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Pantau data transaksi pembayaran, status pengiriman pesanan, dan ringkasan penjualan batik Hamzah Style.
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
                    <span>Unduh Laporan Penjualan</span>
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
                    {{ $totalProducts }} Produk
                </p>
                <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-[#A86544]">
                    <span>✦</span>
                    @if(($newProductsThisMonth ?? 0) > 0)
                        <span>+{{ $newProductsThisMonth }} produk baru bulan ini</span>
                    @else
                        <span>Katalog aktif terdata</span>
                    @endif
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
                    {{ $totalOrders }} Pesanan
                </p>
                <div class="mt-3 flex items-center gap-1 text-[11px] font-semibold text-[#A86544]">
                    @if(isset($orderGrowthPercent) && ($ordersLastMonth ?? 0) > 0)
                        <span>{{ $orderGrowthPercent >= 0 ? '↗ +' : '↘ ' }}{{ $orderGrowthPercent }}% vs bulan lalu</span>
                    @else
                        <span>{{ $ordersThisMonth ?? $totalOrders }} pesanan bulan ini</span>
                    @endif
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
                    {{ $ordersToProcess }} Antrean
                </p>
                <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-[#A86544]">
                    @if($ordersToProcess > 0)
                        <span class="h-1.5 w-1.5 rounded-full bg-[#A86544]"></span>
                        <span>Perlu segera diverifikasi</span>
                    @else
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-700">Semua pesanan terproses</span>
                    @endif
                </div>
            </div>

            {{-- 4. PELANGGAN --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center gap-2 text-stone-400 mb-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">PELANGGAN</span>
                </div>
                <p class="text-[11px] text-[#5A544E]">Total Pelanggan</p>
                <p class="mt-1 text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    {{ number_format($totalCustomers, 0, ',', '.') }} Pelanggan
                </p>
                <div class="mt-3 text-[11px] text-stone-500 font-medium">
                    <strong class="font-bold text-[#1E1B18]">{{ $repeatBuyerPercent ?? 0 }}%</strong> pelanggan beli ulang
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
                    Rp {{ number_format($salesThisMonth, 0, ',', '.') }}
                </p>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-stone-400">Target Rp50jt</span>
                    <span class="font-extrabold text-[#1E1B18]">{{ $targetPercentage ?? 0 }}%</span>
                </div>
            </div>
        </div>

        {{-- MIDDLE SECTION: TREN PENJUALAN + KATEGORI TERLARIS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- KIRI: GRAFIK TREN PENJUALAN (col-span-8) --}}
            <div class="lg:col-span-8 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs">
                <div class="mb-6">
                    <div class="flex items-center gap-2 text-base font-bold text-[#1E1B18]">
                        <span class="text-[#A86544]">↺</span>
                        <h3>Tren Penjualan Koleksi {{ now()->year }}</h3>
                    </div>
                    <p class="text-xs text-[#5A544E] mt-0.5 max-w-md">
                        Distribusi volume penjualan busana batik dan produk koleksi toko Hamzah Style.
                    </p>
                </div>

                {{-- Chart Legend --}}
                <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-stone-600 mb-6">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#1E1B18]"></span>
                        <span>Batik Pria/Wanita</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#E5D7C3]"></span>
                        <span>Kain Lembaran</span>
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
                        @if(!empty($trendMonths))
                            @foreach($trendMonths as $m)
                                @if(($m['h_bar1'] ?? 0) > 0)
                                    <rect x="{{ $m['x_bar1'] }}" y="{{ $m['y_bar1'] }}" width="10" height="{{ $m['h_bar1'] }}" rx="3" fill="#1E1B18"/>
                                @endif
                                @if(($m['h_bar2'] ?? 0) > 0)
                                    <rect x="{{ $m['x_bar2'] }}" y="{{ $m['y_bar2'] }}" width="10" height="{{ $m['h_bar2'] }}" rx="3" fill="#E5D7C3"/>
                                @endif
                            @endforeach
                        @endif

                        {{-- Trend Line & Gradient Area --}}
                        @if(!empty($trendAreaPath))
                            <path d="{{ $trendAreaPath }}" fill="url(#areaGradient)"/>
                        @endif
                        @if(!empty($trendPath))
                            <path d="{{ $trendPath }}" fill="none" stroke="#A86544" stroke-width="3" stroke-linecap="round"/>
                        @endif

                        {{-- Highlight Points --}}
                        @if(!empty($trendMonths))
                            @foreach($trendMonths as $m)
                                @if(($m['total_sales'] ?? 0) > 0)
                                    <circle cx="{{ $m['x_point'] }}" cy="{{ $m['y_point'] }}" r="4" fill="#A86544" stroke="#FFF" stroke-width="2"/>
                                @endif
                            @endforeach
                        @endif
                    </svg>

                    {{-- X-Axis Month Labels --}}
                    <div class="grid grid-cols-6 text-center text-[10px] font-semibold text-stone-500 pt-1">
                        @if(!empty($trendMonths))
                            @foreach($trendMonths as $m)
                                <span @class(['font-bold text-[#1E1B18]' => str_contains($m['label'], 'Berjalan')])>{{ $m['label'] }}</span>
                            @endforeach
                        @else
                            <span>-</span><span>-</span><span>-</span><span>-</span><span>-</span><span class="font-bold text-[#1E1B18]">{{ now()->translatedFormat('M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KANAN: KATEGORI TERLARIS (col-span-4) --}}
            <div class="lg:col-span-4 rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#1E1B18]">Kategori Terlaris</h3>
                    <span class="rounded-full bg-[#FAF0E4] px-2.5 py-0.5 text-[10px] font-bold text-[#8C5E28]">
                        Periode Aktif
                    </span>
                </div>
                <p class="text-xs text-[#5A544E] -mt-4">
                    Komposisi perputaran inventaris berdasarkan minat pelanggan busana batik.
                </p>

                {{-- Progress Bars --}}
                <div class="space-y-4">
                    @forelse($categoriesData as $cat)
                        <div>
                            <div class="flex justify-between text-xs font-bold text-[#1E1B18] mb-1.5">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                    {{ $cat['nama'] }}
                                </span>
                                <span style="color: {{ $cat['color'] }}">{{ $cat['percentage'] }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-[#F3EFEA] overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-stone-400 truncate">{{ $cat['samples'] }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 py-2">Belum ada kategori terdata.</p>
                    @endforelse
                </div>

                {{-- Rekomendasi Restock Callout Box --}}
                <div class="rounded-xl border border-[#E8D9C5] bg-[#FAF0E4]/70 p-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#8C5E28] mb-1">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span>Rekomendasi Restock</span>
                    </div>
                    @if($lowStockProduct)
                        <p class="text-[11px] text-[#5A544E] leading-relaxed">
                            Stok <strong class="text-[#8C5E28]">{{ $lowStockProduct->nama }}</strong> tersisa <strong class="text-rose-600 font-bold">{{ $lowStockProduct->stok }} unit</strong> (menipis). Direkomendasikan segera menambah stok untuk memenuhi permintaan.
                        </p>
                    @else
                        <p class="text-[11px] text-[#5A544E] leading-relaxed">
                            Semua stok produk saat ini berada dalam kondisi aman (di atas batas minimum stok).
                        </p>
                    @endif
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
                                {{ $ordersToProcess }} Menunggu
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
                                            {{ $order->items->first()->produk_name ?? 'Produk Batik' }}
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
                    @forelse($topProducts->take(3) as $item)
                        <div class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-[#FAF7F2] transition">
                            <div class="h-14 w-14 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                @if (!empty($item->gambar_url))
                                    <img src="{{ $item->gambar_url }}" alt="{{ $item->produk_name }}" class="h-full w-full object-cover">
                                @elseif(!empty($item->gambar))
                                    <img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->produk_name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                        Batik
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-[#1E1B18] truncate">
                                    {{ $item->produk_name }}
                                </p>
                                <p class="text-[10px] text-stone-400">{{ $item->kategori_nama ?? 'Koleksi Batik' }}</p>
                                <div class="mt-1 flex items-center gap-2 text-[11px] font-semibold text-[#1E1B18]">
                                    <span>Terjual: <strong class="font-extrabold">{{ $item->units_sold ?? 0 }} pcs</strong></span>
                                    <span class="text-stone-300">•</span>
                                    <span class="text-[#A86544]">Rp {{ number_format($item->sales_total ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1 text-[10px] {{ ($item->stok ?? 0) <= \App\Models\Product::LOW_STOCK_THRESHOLD ? 'text-rose-600 font-bold' : 'text-stone-500' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ ($item->stok ?? 0) <= \App\Models\Product::LOW_STOCK_THRESHOLD ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                    <span>Sisa Stok: {{ $item->stok ?? 0 }} pcs</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-stone-400">
                            Belum ada produk terjual pada periode ini.
                        </div>
                    @endforelse
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
