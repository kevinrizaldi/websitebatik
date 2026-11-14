<x-app-layout>
    <div x-data="{
        shippingModalOpen: false,
        activeOrderId: null,
        activeOrderCode: '',
        activeCourier: 'JNE Reguler',
        activeResi: '',
        activeStatus: 'Dikirim',
        activeActionUrl: ''
    }" class="space-y-8">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 shadow-2xs">
                <p class="font-bold mb-1">⚠️ Perhatian Pengelolaan Pesanan & Logistik:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- HEADER BREADCRUMB & TITLE --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Breadcrumbs & Eyebrow --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544] mb-1">
                    <span>KONSOL TERPADU</span>
                    <span>•</span>
                    <span class="text-emerald-700">MIDTRANS GATEWAY REAL-TIME</span>
                    <span>•</span>
                    <span class="text-stone-500">LOGISTIK RAJAONGKIR (SOLO)</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Pesanan, Pembayaran &amp; Logistik
                    <span class="sr-only">Kelola Pesanan</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Kendali terpusat status kurasi pesanan, validasi transaksi otomatis gateway Midtrans (Snap, QRIS, VA), dan pengiriman ekspedisi RajaOngkir (JNE, POS, TIKI) ke seluruh Nusantara.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                {{-- 1. Sync All Payments to Midtrans Gateway --}}
                <form method="POST" action="{{ route('admin.orders.sync-all-payments') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                        <svg class="h-4 w-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Sinkronisasi Midtrans</span>
                    </button>
                </form>

                {{-- 2. Request Pick-up Kurir RajaOngkir --}}
                <button type="button" onclick="alert('Permintaan pick-up paket berhasil dikirim ke mitra armada kurir RajaOngkir (JNE, POS Indonesia & TIKI)!')" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-[#A86544]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM3 9h11v8H3V9zm11 3h4l3 3v2h-7v-5z" />
                    </svg>
                    <span>Request Pick-up</span>
                </button>

                {{-- 3. Cetak Label & Export CSV --}}
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition" title="Cetak label / invoice">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak</span>
                </button>

                <a href="{{ route('admin.laporan.export', 'csv') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs transition hover:bg-[#FAF7F2]" title="Unduh CSV">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Ekspor CSV</span>
                </a>
            </div>
        </div>

        {{-- 4 UNIFIED METRIC CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. TOTAL PESANAN & OMZET TERVERIFIKASI --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL PESANAN &amp; OMZET</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $counts['all'] }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Transaksi</span>
                </div>
                <div class="mt-2 text-xs font-bold text-emerald-800">
                    Rp {{ number_format($successAmount, 0, ',', '.') }}
                </div>
                <div class="mt-3 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>{{ $counts['selesai'] }} pesanan selesai sukses</span>
                    <span class="font-bold text-emerald-700">✓ Terverifikasi</span>
                </div>
            </div>

            {{-- 2. GATEWAY MIDTRANS (SETTLEMENT & PENDING) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">GATEWAY MIDTRANS</span>
                    <span class="rounded bg-[#FAF7F2] px-2 py-0.5 text-[9px] font-bold text-stone-600 border border-[#EFECE6]">
                        Snap &amp; Webhook
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        Rp {{ number_format($gatewayAmount, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-2 text-xs font-semibold text-[#A86544]">
                    {{ $pendingCount }} Pending (Rp {{ number_format($pendingAmount, 0, ',', '.') }})
                </div>
                <div class="mt-3 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>Rekonsiliasi Otomatis IPN</span>
                    <span class="text-[#8C5E28]">⚡ Realtime</span>
                </div>
            </div>

            {{-- 3. LOGISTIK & ANTREAN ATELIER (RAJAONGKIR) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">LOGISTIK &amp; EKSPEDISI</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM3 9h11v8H3V9zm11 3h4l3 3v2h-7v-5z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $counts['diproses'] + $counts['dikirim'] }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Paket Aktif</span>
                </div>
                <div class="mt-2 flex items-center gap-2 text-xs">
                    <span class="text-stone-700 font-bold">📦 {{ $counts['diproses'] }} Diproses</span>
                    <span class="text-stone-300">•</span>
                    <span class="text-emerald-700 font-bold">🚚 {{ $counts['dikirim'] }} Dikirim</span>
                </div>
                <div class="mt-3 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>{{ $dalamPerjalananCount }} Paket Dalam Perjalanan</span>
                    <span class="text-emerald-700 font-bold">Transit</span>
                </div>
            </div>

            {{-- 4. ORIGIN GUDANG & STATUS SIKLUS --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">ASAL PENGIRIMAN</span>
                    <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-bold text-[#8C5E28]">
                        RajaOngkir API
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18] truncate">
                        {{ $originInfo['city_name'] ?? 'Kota Surakarta' }}
                    </p>
                </div>
                <div class="mt-2 text-xs text-[#5A544E]">
                    Origin Solo (Kode 445) • Kurir JNE, POS &amp; TIKI
                </div>
                <div class="mt-3 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>Selesai Diterima: {{ $diterimaCount }} Paket</span>
                    <span class="font-bold text-[#A86544]">100% Zero-Waste</span>
                </div>
            </div>
        </div>

        {{-- FILTER TABS BAR (STATUS UTAMA) --}}
        @php
            $activeStatus = request('status', '');
            $activeEkspedisi = request('ekspedisi', '');
            $activePembayaran = request('pembayaran', '');

            $tabs = [
                '' => ['label' => 'Semua Pesanan', 'count' => $counts['all']],
                'Menunggu Pembayaran' => ['label' => 'Menunggu Pembayaran', 'count' => $counts['menunggu_pembayaran']],
                'Diproses' => ['label' => 'Diproses', 'count' => $counts['diproses']],
                'Dikirim' => ['label' => 'Dikirim', 'count' => $counts['dikirim']],
                'Selesai' => ['label' => 'Selesai', 'count' => $counts['selesai']],
                'Dibatalkan' => ['label' => 'Dibatalkan', 'count' => $counts['dibatalkan']],
            ];
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#EFECE6] pb-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @foreach ($tabs as $key => $tab)
                    @php $isActive = ($activeStatus === (string) $key); @endphp
                    <a href="{{ route('admin.orders.index', array_merge(request()->only(['search', 'ekspedisi', 'pembayaran']), $key !== '' ? ['status' => $key] : [])) }}"
                       @class([
                           'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                           'bg-[#1E1B18] text-white' => $isActive,
                           'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => ! $isActive,
                       ])>
                        <span>{{ $tab['label'] }}</span>
                        <span class="ml-1 opacity-80">({{ $tab['count'] }})</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- FILTER SEARCH TOOLBAR TERPADU --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            {{-- Search Box --}}
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex-1 max-w-lg relative">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('ekspedisi'))
                    <input type="hidden" name="ekspedisi" value="{{ request('ekspedisi') }}">
                @endif
                @if(request('pembayaran'))
                    <input type="hidden" name="pembayaran" value="{{ request('pembayaran') }}">
                @endif
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Cari Order ID, Nama Pembeli, Resi AWB, WhatsApp, Alamat..."
                       class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
            </form>

            {{-- Controls Dropdowns --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Dropdown Ekspedisi RajaOngkir --}}
                <form action="{{ route('admin.orders.index') }}" method="GET" class="inline">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('pembayaran'))
                        <input type="hidden" name="pembayaran" value="{{ request('pembayaran') }}">
                    @endif
                    <select name="ekspedisi" onchange="this.form.submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="all">Semua Ekspedisi (RajaOngkir)</option>
                        <option value="jne" {{ request('ekspedisi') === 'jne' ? 'selected' : '' }}>JNE Express</option>
                        <option value="pos" {{ request('ekspedisi') === 'pos' ? 'selected' : '' }}>POS Indonesia</option>
                        <option value="tiki" {{ request('ekspedisi') === 'tiki' ? 'selected' : '' }}>TIKI</option>
                    </select>
                </form>

                {{-- Dropdown Status Pembayaran Midtrans --}}
                <form action="{{ route('admin.orders.index') }}" method="GET" class="inline">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('ekspedisi'))
                        <input type="hidden" name="ekspedisi" value="{{ request('ekspedisi') }}">
                    @endif
                    <select name="pembayaran" onchange="this.form.submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="all">Semua Status Bayar</option>
                        <option value="pending" {{ request('pembayaran') === 'pending' ? 'selected' : '' }}>Midtrans Pending (Belum Bayar)</option>
                        <option value="settlement" {{ in_array(request('pembayaran'), ['settlement', 'success']) ? 'selected' : '' }}>Midtrans Settlement (Lunas)</option>
                        <option value="failed" {{ request('pembayaran') === 'failed' ? 'selected' : '' }}>Batal / Expired</option>
                    </select>
                </form>

                {{-- Reset Filter Button --}}
                <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-500 hover:bg-[#FAF7F2] transition shadow-2xs" title="Reset filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- TABLE ORDERS TERPADU: PESANAN, PEMBAYARAN MIDTRANS, & PENGIRIMAN RAJAONGKIR --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/70 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-5">ORDER ID &amp; WAKTU</th>
                            <th class="py-3.5 px-5">PELANGGAN &amp; TUJUAN</th>
                            <th class="py-3.5 px-5">ITEM PESANAN</th>
                            <th class="py-3.5 px-5">PEMBAYARAN MIDTRANS</th>
                            <th class="py-3.5 px-5">PENGIRIMAN &amp; RESI RAJAONGKIR</th>
                            <th class="py-3.5 px-5 text-center">STATUS &amp; AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($orders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $itemCount = $order->items->count();
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62'.substr($cleanPhone, 1);
                                }

                                $statusStyle = match ($order->status) {
                                    'Diproses' => 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]',
                                    'Dikirim' => 'bg-[#EDE7F6] text-[#512DA8] border-[#D1C4E9]',
                                    'Selesai' => 'bg-[#E8F5E9] text-[#2E7D32] border-[#C8E6C9]',
                                    'Dibatalkan', 'Batal' => 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]',
                                    default => 'bg-[#FAF0E4] text-[#A86544] border-[#E8D9C5]',
                                };

                                $isSettled = in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']);
                                $isCancelled = in_array($order->status, ['Dibatalkan', 'Batal']);

                                $paymentStatusPill = match (true) {
                                    $isSettled => ['● Settlement (Lunas)', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                    $isCancelled => ['● Batal / Expired', 'bg-rose-50 text-rose-800 border-rose-200'],
                                    default => ['● Midtrans Pending', 'bg-amber-50 text-amber-800 border-amber-200'],
                                };

                                $methodName = $order->payment_method ?? 'Midtrans Gateway';
                                $methodBadge = match (true) {
                                    str_contains(strtolower($methodName), 'qris') => 'Midtrans QRIS',
                                    str_contains(strtolower($methodName), 'mandiri') => 'Midtrans Mandiri VA',
                                    str_contains(strtolower($methodName), 'bni') => 'Midtrans BNI VA',
                                    str_contains(strtolower($methodName), 'bri') => 'Midtrans BRI VA',
                                    str_contains(strtolower($methodName), 'bca') => 'Midtrans BCA VA',
                                    str_contains(strtolower($methodName), 'gopay') => 'Midtrans GoPay',
                                    default => 'Midtrans Snap Gateway',
                                };

                                $courierName = $order->pengiriman->ekspedisi ?? $order->shipment->courier ?? (str_contains(strtolower($order->payment_method ?? ''), 'jne') ? 'JNE Reguler' : 'JNE Reguler (RajaOngkir)');
                                $currentResi = $order->tracking_number ?? $order->pengiriman->no_resi ?? null;
                            @endphp
                            <tr class="hover:bg-[#FAF7F2]/60 transition group">
                                {{-- 1. ORDER ID & WAKTU --}}
                                <td class="py-4 px-5 font-mono align-top">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-xs text-[#1E1B18] hover:text-[#A86544] hover:underline block">
                                        #{{ $order->code }}
                                    </a>
                                    <p class="text-[10px] text-stone-400 mt-1 font-sans">
                                        {{ $order->created_at->format('d M Y') }} • {{ $order->created_at->format('H:i') }} WIB
                                    </p>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-extrabold border {{ $statusStyle }}">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 2. PELANGGAN & TUJUAN --}}
                                <td class="py-4 px-5 align-top">
                                    <p class="font-bold text-xs text-[#1E1B18]">{{ $order->customer_name }}</p>
                                    @if($cleanPhone)
                                        <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($order->customer_name) }},%20kami%20dari%20Hamzah%20Style%20mengonfirmasi%20pesanan%20%23{{ $order->code }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-mono text-[#A86544] hover:underline mt-0.5">
                                            <span>💬</span>
                                            <span>{{ $order->phone }}</span>
                                        </a>
                                    @endif
                                    <p class="text-[10px] text-stone-400 mt-1 leading-relaxed max-w-xs">
                                        📍 {{ $order->address }}
                                    </p>
                                </td>

                                {{-- 3. ITEM PESANAN --}}
                                <td class="py-4 px-5 align-top">
                                    <div class="flex items-start gap-2.5">
                                        <div class="h-11 w-11 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                            @if ($firstItem?->produk && $firstItem->produk->gambar)
                                                <img src="{{ asset('storage/'.$firstItem->produk->gambar) }}" alt="{{ $firstItem->produk_name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-[10px] font-bold text-[#A86544]">
                                                    Batik
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs text-[#1E1B18] truncate max-w-[180px]">
                                                {{ $firstItem->produk_name ?? 'Pesanan Busana Batik' }}
                                            </p>
                                            <div class="flex items-center gap-1.5 text-[10px] text-stone-400 mt-0.5">
                                                <span>{{ $firstItem->quantity ?? 1 }}x</span>
                                                @if ($itemCount > 1)
                                                    <span class="text-[#A86544] font-semibold">(+{{ $itemCount - 1 }} item lain)</span>
                                                @endif
                                            </div>
                                            <span class="inline-block rounded bg-[#FAF0E4] px-1.5 py-0.2 text-[9px] font-bold text-[#8C5E28] mt-1">
                                                Upcycled Artisan
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- 4. PEMBAYARAN MIDTRANS --}}
                                <td class="py-4 px-5 align-top">
                                    <p class="font-extrabold text-xs text-[#1E1B18]">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-block rounded bg-[#FAF7F2] px-1.5 py-0.5 text-[9px] font-bold text-stone-700 border border-[#EFECE6]">
                                            {{ $methodBadge }}
                                        </span>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold border {{ $paymentStatusPill[1] }}">
                                            {{ $paymentStatusPill[0] }}
                                        </span>

                                        {{-- 1-Click Check Midtrans Sync Button --}}
                                        <form method="POST" action="{{ route('admin.orders.sync-payment', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Cek &amp; Sinkronkan Pembayaran Midtrans" class="p-1 rounded-lg border border-[#E8E4DC] bg-white text-stone-500 hover:text-[#1E1B18] hover:bg-[#FAF7F2] transition shadow-2xs">
                                                <svg class="w-3 h-3 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                {{-- 5. PENGIRIMAN & RESI RAJAONGKIR --}}
                                <td class="py-4 px-5 align-top">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-[#1E1B18]">
                                        <span class="text-stone-400">🚚</span>
                                        <span>{{ $courierName }}</span>
                                    </div>

                                    @if ($currentResi)
                                        <div class="mt-1 flex items-center gap-1">
                                            <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-[#1E1B18] bg-[#FAF7F2] border border-[#EFECE6] px-2 py-0.5 rounded-md">
                                                <span>AWB:</span>
                                                <span>{{ $currentResi }}</span>
                                            </span>
                                            <button type="button" onclick="navigator.clipboard.writeText('{{ $currentResi }}'); alert('Nomor resi {{ $currentResi }} disalin!')" title="Salin nomor resi" class="text-stone-400 hover:text-stone-700">
                                                📋
                                            </button>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-800 border border-amber-200">
                                                <span>⚠️</span>
                                                <span>Belum Ada Resi AWB</span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Trigger Quick Resi Modal --}}
                                    <div class="mt-2">
                                        <button type="button"
                                                @click="shippingModalOpen = true; activeOrderId = {{ $order->id }}; activeOrderCode = '{{ $order->code }}'; activeCourier = '{{ addslashes($courierName) }}'; activeResi = '{{ addslashes($currentResi ?? '') }}'; activeStatus = '{{ $order->status === 'Dikirim' ? 'Dikirim' : ($order->status === 'Selesai' ? 'Diterima' : 'Diproses') }}'; activeActionUrl = '{{ route('admin.orders.update-pengiriman', $order) }}';"
                                                class="inline-flex items-center gap-1 text-[10px] font-bold text-[#A86544] hover:text-[#8C5E28] hover:underline bg-[#FAF0E4]/60 px-2 py-1 rounded-lg border border-[#E9DFD0]">
                                            <span>✏️</span>
                                            <span>{{ $currentResi ? 'Ubah Resi &amp; Logistik' : '+ Input Resi &amp; Kirim' }}</span>
                                        </button>
                                    </div>
                                </td>

                                {{-- 6. STATUS & AKSI --}}
                                <td class="py-4 px-5 text-center align-top">
                                    <div class="flex flex-col items-center gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center gap-1 rounded-xl bg-[#1E1B18] px-3 py-1.5 text-[11px] font-semibold text-white shadow-2xs hover:bg-black transition w-full">
                                            <span>Rincian</span>
                                            <span>→</span>
                                        </a>

                                        {{-- Quick Mark Status If Pending or Ready --}}
                                        @if (in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi']))
                                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="w-full">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="Diproses">
                                                <button type="submit" class="w-full text-[10px] font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 py-1 rounded-lg border border-emerald-200 transition">
                                                    ✓ Tandai Lunas
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-stone-400">
                                    Tidak ada data pesanan yang sesuai dengan filter pencarian, pembayaran, atau logistik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Pagination --}}
            <div class="p-4 sm:p-5 border-t border-[#EFECE6] bg-[#FAF7F2]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-stone-500">
                    Menampilkan {{ $orders->firstItem() ?? 1 }} - {{ $orders->lastItem() ?? $orders->count() }} dari {{ $orders->total() }} pesanan terdaftar
                </span>

                <div class="flex items-center gap-2">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        {{-- QUICK SHIPPING & RESI MODAL (ALPINE.JS) --}}
        <div x-show="shippingModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             style="display: none;"
             x-cloak>
            <div @click.away="shippingModalOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-[#EFECE6] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544]">
                            <span>LOGISTIK RAJAONGKIR</span>
                            <span>•</span>
                            <span x-text="'PESANAN #' + activeOrderCode"></span>
                        </div>
                        <h3 class="text-base font-bold text-[#1E1B18] mt-0.5">Update Nomor Resi &amp; Ekspedisi</h3>
                    </div>
                    <button type="button" @click="shippingModalOpen = false" class="text-stone-400 hover:text-stone-700 text-lg">&times;</button>
                </div>

                {{-- Shipping Modal Form --}}
                <form :action="activeActionUrl" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Ekspedisi Kurir --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                            PILIHAN EKSPEDISI RAJAONGKIR
                        </label>
                        <select name="ekspedisi" x-model="activeCourier" class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2.5 text-xs font-semibold text-[#1E1B18] focus:border-[#C59A53] focus:outline-none shadow-2xs">
                            <option value="JNE Reguler">JNE Express (Reguler / OKE / YES)</option>
                            <option value="POS Reguler">POS Indonesia (Pos Reguler / Kilat Khusus)</option>
                            <option value="TIKI Reguler">TIKI (Reguler / ONS)</option>
                            <option value="J&amp;T Express">J&amp;T Express</option>
                            <option value="SiCepat REG">SiCepat REG</option>
                            <option value="Anteraja">Anteraja</option>
                        </select>
                    </div>

                    {{-- Nomor Resi AWB --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                            NOMOR RESI / AWB PENGIRIMAN <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" name="no_resi" x-model="activeResi" required
                               placeholder="Contoh: JNE88210948271, POS1982741..."
                               class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2.5 text-xs font-mono font-bold text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                        <p class="text-[10px] text-stone-400 mt-1">Nomor resi resmi ekspedisi RajaOngkir untuk pelacakan transit otomatis.</p>
                    </div>

                    {{-- Status Pengiriman --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                                STATUS LOGISTIK
                            </label>
                            <select name="status_pengiriman" x-model="activeStatus" class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3 py-2 text-xs font-semibold text-[#1E1B18] focus:border-[#C59A53] focus:outline-none shadow-2xs">
                                <option value="Diproses">Diproses (Kemas)</option>
                                <option value="Dikirim">Dikirim (Transit Kurir)</option>
                                <option value="Diterima">Diterima (Selesai)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                                TANGGAL DISERAHKAN
                            </label>
                            <input type="date" name="tanggal_kirim" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3 py-2 text-xs font-semibold text-[#1E1B18] focus:border-[#C59A53] focus:outline-none shadow-2xs">
                        </div>
                    </div>

                    {{-- Catatan Pengiriman Opsional --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                            CATATAN PENGEMASAN (OPSIONAL)
                        </label>
                        <input type="text" name="catatan" placeholder="Contoh: Kemasan besek bambu ganda + sertifikat kurasi motif..." class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2 text-xs text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] shadow-2xs">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#EFECE6]">
                        <button type="button" @click="shippingModalOpen = false" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-stone-600 hover:bg-[#FAF7F2] transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-black transition">
                            <svg class="w-4 h-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Resi &amp; Perbarui Pengiriman</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- BOTTOM BANNER: KEMASAN NOL SAMPAH & LOGISTIK TERPADU --}}
        <div class="rounded-2xl border border-[#E9DFD0] bg-[#FAF0E4]/70 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53] shadow-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#8C5E28] mb-0.5">
                        <span>Sirkularitas Logistik &amp; Verifikasi Pembayaran Terpadu</span>
                        <span class="rounded bg-[#F8EDE2] px-1.5 py-0.2 text-[#A86544] font-extrabold">Eko-Artisan</span>
                    </div>
                    <p class="text-xs text-[#5A544E] mt-1 max-w-2xl leading-relaxed">
                        Fitur pembayaran otomatis via gateway Midtrans IPN dan ekspedisi logistik RajaOngkir (JNE, POS, TIKI) kini dikelola langsung dalam satu pintu fitur Pesanan. Setiap pesanan diverifikasi dan dikemas ramah lingkungan dari atelier Surakarta.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Panduan pengemasan ramah lingkungan Hamzah Style: gunakan anyaman besek bambu dan tali serat rami alami.')" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    Panduan Pengemasan
                </button>
                <form method="POST" action="{{ route('admin.orders.sync-all-payments') }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-black transition">
                        Sinkronisasi Midtrans
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
