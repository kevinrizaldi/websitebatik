<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-[#8C827A]">
            <a href="{{ route('dashboard') }}" class="hover:text-[#1E1B18] transition">DASHBOARD</a>
            <span>/</span>
            <span class="text-[#1E1B18] font-semibold">LOGISTIK & PENGIRIMAN</span>
        </div>
    </x-slot>

    <div class="space-y-6 pb-12">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-[#F0FDF4] border border-[#DCFCE7] text-[#166534] text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-[#16a34a] hover:opacity-75">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-[#FEF2F2] border border-[#FEE2E2] text-[#991B1B] text-xs">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 text-[10px] font-bold tracking-widest text-[#A86544] uppercase mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>SIRKULARITAS LOGISTIK / STUDIO HAMZAH OFFICIAL</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif text-[#1E1B18] tracking-tight">Manajemen Pengiriman & Logistik Terintegrasi RajaOngkir</h1>
                <p class="text-xs sm:text-sm text-[#736B63] mt-1 max-w-2xl">
                    Kelola alur cetak label pengiriman, pengemasan besek bambu ramah lingkungan, input resi AWB, dan pelacakan kurir ekspedisi RajaOngkir (JNE, POS, TIKI) dari origin {{ $originInfo['city_name'] ?? 'Kota Surakarta (Solo)' }}.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9D2C7] bg-white text-xs font-semibold text-[#1E1B18] hover:bg-[#FAF7F2] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#736B63]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Label Massal</span>
                </button>
                <button type="button" onclick="alert('Permintaan pick-up berhasil dikirim ke armada kurir RajaOngkir (JNE, POS Indonesia & TIKI)!')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E1B18] text-xs font-semibold text-white hover:bg-black transition shadow-xs">
                    <svg class="w-4 h-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM3 9h11v8H3V9zm11 3h4l3 3v2h-7v-5z" />
                    </svg>
                    <span>Request Pick-up Kurir RajaOngkir</span>
                </button>
            </div>
        </div>

        {{-- 4 Top KPI Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Antrean Atelier --}}
            <div class="bg-white p-5 rounded-2xl border border-[#EBE4D8] shadow-xs relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-wider text-[#8C827A] uppercase">ANTREAN ATELIER</span>
                    <div class="w-8 h-8 rounded-xl bg-[#F5ECE2] flex items-center justify-center text-[#A86544]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-[#736B63] font-medium">Siap Dikemas</div>
                <div class="text-2xl font-serif font-bold text-[#1E1B18] mt-0.5">{{ $siapDikemasCount }} <span class="text-xs font-sans font-medium text-[#736B63]">Paket</span></div>
                <div class="mt-3 pt-3 border-t border-[#F5F0E8] flex items-center gap-1.5 text-[11px] text-[#A86544] font-medium">
                    <span>⏳</span>
                    <span>Menunggu bungkus besek</span>
                </div>
            </div>

            {{-- 2. Docking Gate --}}
            <div class="bg-white p-5 rounded-2xl border border-[#EBE4D8] shadow-xs relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-wider text-[#8C827A] uppercase">DOCKING GATE</span>
                    <div class="w-8 h-8 rounded-xl bg-[#F5ECE2] flex items-center justify-center text-[#A86544]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-[#736B63] font-medium">Siap Serah Terima</div>
                <div class="text-2xl font-serif font-bold text-[#1E1B18] mt-0.5">{{ $siapSerahCount }} <span class="text-xs font-sans font-medium text-[#736B63]">Paket</span></div>
                <div class="mt-3 pt-3 border-t border-[#F5F0E8] flex items-center justify-between text-[11px] text-[#736B63]">
                    <span>Resi AWB telah terbit</span>
                    <span class="font-bold text-[#A86544]">Pick-up 15:00</span>
                </div>
            </div>

            {{-- 3. Trans-Nusantara --}}
            <div class="bg-white p-5 rounded-2xl border border-[#EBE4D8] shadow-xs relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-wider text-[#8C827A] uppercase">TRANS-NUSANTARA</span>
                    <div class="w-8 h-8 rounded-xl bg-[#F5ECE2] flex items-center justify-center text-[#A86544]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-[#736B63] font-medium">Dalam Perjalanan</div>
                <div class="text-2xl font-serif font-bold text-[#1E1B18] mt-0.5">{{ $dalamPerjalananCount }} <span class="text-xs font-sans font-medium text-[#736B63]">Paket</span></div>
                <div class="mt-3 pt-3 border-t border-[#F5F0E8] flex items-center justify-between text-[11px] text-[#736B63]">
                    <span>JNE, POS &amp; TIKI (RajaOngkir)</span>
                    <span class="font-bold text-[#2E7D32]">AKTIF TRANSIT</span>
                </div>
            </div>

            {{-- 4. Performa Siklus --}}
            <div class="bg-white p-5 rounded-2xl border border-[#EBE4D8] shadow-xs relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-wider text-[#8C827A] uppercase">PERFORMA SIKLUS</span>
                    <div class="w-8 h-8 rounded-xl bg-[#F5ECE2] flex items-center justify-center text-[#A86544]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xs text-[#736B63] font-medium">Berhasil Terkirim</div>
                <div class="text-2xl font-serif font-bold text-[#1E1B18] mt-0.5">{{ $diterimaCount }} <span class="text-xs font-sans font-medium text-[#736B63]">Paket</span></div>
                <div class="mt-3 pt-3 border-t border-[#F5F0E8] flex items-center justify-between text-[11px] text-[#736B63]">
                    <span>Minggu ini</span>
                    <span class="font-bold text-[#2E7D32]">99.2% On-Time</span>
                </div>
            </div>
        </div>

        {{-- Sustainability Protocol Callout Banner --}}
        <div class="bg-[#FAF7F2] border border-[#EBE4D8] rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-[#F3EBE1] flex items-center justify-center text-[#A86544] shrink-0 mt-0.5 sm:mt-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-[#1E1B18] uppercase tracking-wide">Protokol Kemasan Berkelanjutan Hamzah Style</h2>
                    <p class="text-xs text-[#736B63] mt-0.5">
                        Setiap pesanan dijamin zero-plastic packaging: Anyaman besek bambu lokal Magelang, tali serat rami mentah, dan segel malam batik sisa cap.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#E8F5E9] text-[#2E7D32] text-[10px] font-bold tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32]"></span>
                    AUDIT KEMASAN: 100% LULUS
                </span>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="space-y-4">
            <form method="GET" action="{{ route('admin.pengiriman.index') }}" class="bg-white p-3 rounded-2xl border border-[#EBE4D8] flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 shadow-xs">
                <div class="relative flex-1">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari Nomor Resi, Order ID, nama pembeli, atau kota tujuan..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-[#FAF7F2] border-none text-xs text-[#1E1B18] placeholder-[#9C9389] focus:ring-1 focus:ring-[#C59A53]" />
                    <svg class="w-4 h-4 text-[#9C9389] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <div class="flex items-center gap-2">
                    <select name="ekspedisi" onchange="this.form.submit()" class="py-2 pl-3 pr-8 rounded-xl bg-[#FAF7F2] border-none text-xs text-[#1E1B18] focus:ring-1 focus:ring-[#C59A53]">
                        <option value="all" {{ $ekspedisiFilter === 'all' ? 'selected' : '' }}>Semua Ekspedisi RajaOngkir</option>
                        <option value="JNE" {{ $ekspedisiFilter === 'JNE' ? 'selected' : '' }}>JNE Express (Reguler, OKE, YES)</option>
                        <option value="POS" {{ $ekspedisiFilter === 'POS' ? 'selected' : '' }}>POS Indonesia (Kilat Khusus)</option>
                        <option value="TIKI" {{ $ekspedisiFilter === 'TIKI' ? 'selected' : '' }}>TIKI (Reguler, ONS)</option>
                    </select>

                    <button type="button" onclick="alert('Mengekspor daftar manifest pengiriman ke CSV...')" title="Ekspor CSV" class="p-2 rounded-xl border border-[#EBE4D8] bg-white text-[#736B63] hover:text-[#1E1B18] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </button>

                    <a href="{{ route('admin.pengiriman.index') }}" title="Refresh" class="p-2 rounded-xl border border-[#EBE4D8] bg-white text-[#736B63] hover:text-[#1E1B18] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                </div>
            </form>

            {{-- Status Filter Tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
                <a href="{{ route('admin.pengiriman.index', ['status' => 'all', 'search' => $search, 'ekspedisi' => $ekspedisiFilter]) }}"
                   @class([
                       'px-4 py-2 rounded-xl transition shrink-0',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'all',
                       'bg-white text-[#5A544E] border border-[#EBE4D8] hover:bg-[#FAF7F2]' => $statusFilter !== 'all',
                   ])>
                    Semua ({{ $totalShipments }})
                </a>
                <a href="{{ route('admin.pengiriman.index', ['status' => 'belum_dikirim', 'search' => $search, 'ekspedisi' => $ekspedisiFilter]) }}"
                   @class([
                       'px-4 py-2 rounded-xl transition shrink-0 flex items-center gap-1.5',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'belum_dikirim',
                       'bg-white text-[#5A544E] border border-[#EBE4D8] hover:bg-[#FAF7F2]' => $statusFilter !== 'belum_dikirim',
                   ])>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E57373]"></span>
                    Belum Dikirim / Dikemas ({{ $siapDikemasCount }})
                </a>
                <a href="{{ route('admin.pengiriman.index', ['status' => 'siap_serah', 'search' => $search, 'ekspedisi' => $ekspedisiFilter]) }}"
                   @class([
                       'px-4 py-2 rounded-xl transition shrink-0',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'siap_serah',
                       'bg-white text-[#5A544E] border border-[#EBE4D8] hover:bg-[#FAF7F2]' => $statusFilter !== 'siap_serah',
                   ])>
                    Siap Serah Terima ({{ $siapSerahCount }})
                </a>
                <a href="{{ route('admin.pengiriman.index', ['status' => 'dalam_perjalanan', 'search' => $search, 'ekspedisi' => $ekspedisiFilter]) }}"
                   @class([
                       'px-4 py-2 rounded-xl transition shrink-0',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'dalam_perjalanan',
                       'bg-white text-[#5A544E] border border-[#EBE4D8] hover:bg-[#FAF7F2]' => $statusFilter !== 'dalam_perjalanan',
                   ])>
                    Dalam Perjalanan ({{ $dalamPerjalananCount }})
                </a>
                <a href="{{ route('admin.pengiriman.index', ['status' => 'diterima', 'search' => $search, 'ekspedisi' => $ekspedisiFilter]) }}"
                   @class([
                       'px-4 py-2 rounded-xl transition shrink-0',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'diterima',
                       'bg-white text-[#5A544E] border border-[#EBE4D8] hover:bg-[#FAF7F2]' => $statusFilter !== 'diterima',
                   ])>
                    Diterima ({{ $diterimaCount }})
                </a>
            </div>
        </div>

        {{-- Shipping Directory Table --}}
        <div class="bg-white rounded-2xl border border-[#EBE4D8] shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#F5F0E8] text-[10px] font-bold text-[#8C827A] uppercase tracking-wider bg-[#FCFAF6]">
                            <th class="py-3.5 px-4">ORDER ID & TANGGAL</th>
                            <th class="py-3.5 px-4">PENERIMA & KOTA TUJUAN</th>
                            <th class="py-3.5 px-4">EKSPEDISI & LAYANAN</th>
                            <th class="py-3.5 px-4">KEMASAN KHUSUS</th>
                            <th class="py-3.5 px-4">NOMOR RESI (AWB)</th>
                            <th class="py-3.5 px-4">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F5F0E8] text-xs">
                        @forelse ($orders as $order)
                            @php
                                $resi = $order->tracking_number ?? optional($order->shipment)->no_resi;
                                $ekspedisiName = optional($order->shipment)->ekspedisi ?: 'JNE Reguler';
                                $addressText = $order->address ?: 'Jakarta Selatan, DKI Jakarta';
                                $cityText = Str::of($addressText)->afterLast(',')->trim();
                                if ($cityText->isEmpty()) {
                                    $cityText = 'Jakarta Selatan, DKI Jakarta';
                                }

                                // Format packaging label
                                $packagingOptions = [
                                    'Besek Bambu + Tali Rami + Kartu Ucapan',
                                    'Kotak Daur Ulang + Segel Lilin Batik',
                                    'Pouch Serat Alam',
                                    'Besek Edisi Pusaka Masterpiece',
                                    'Standard Zero Waste',
                                ];
                                $packaging = $packagingOptions[$order->id % 5];
                            @endphp
                            <tr class="hover:bg-[#FAF7F2] transition">
                                {{-- 1. ORDER ID & TANGGAL --}}
                                <td class="py-4 px-4 align-top">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-serif font-bold text-[#1E1B18] hover:text-[#C59A53] transition">
                                        #{{ $order->code }}
                                    </a>
                                    <div class="text-[11px] text-[#8C827A] mt-0.5">
                                        {{ $order->created_at ? $order->created_at->format('d M Y • H:i') : '18 Feb 2025 • 10:14' }} WIB
                                    </div>
                                </td>

                                {{-- 2. PENERIMA & KOTA TUJUAN --}}
                                <td class="py-4 px-4 align-top">
                                    <div class="font-semibold text-[#1E1B18]">{{ $order->customer_name }}</div>
                                    <div class="flex items-center gap-1 text-[11px] text-[#736B63] mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-[#A86544] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate max-w-[180px]">{{ $addressText }}</span>
                                    </div>
                                </td>

                                {{-- 3. EKSPEDISI & LAYANAN --}}
                                <td class="py-4 px-4 align-top">
                                    @php
                                        $ekspLower = strtolower($ekspedisiName);
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        @if (str_contains($ekspLower, 'pos'))
                                            <span class="px-1.5 py-0.5 rounded bg-[#FFEBEE] text-[#C62828] font-bold text-[10px]">POS</span>
                                            <span class="text-[11px] text-[#5A544E]">Pos Kilat Khusus</span>
                                        @elseif (str_contains($ekspLower, 'tiki'))
                                            <span class="px-1.5 py-0.5 rounded bg-[#EDE7F6] text-[#512DA8] font-bold text-[10px]">TIKI</span>
                                            <span class="text-[11px] text-[#5A544E]">Reguler Service</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded bg-[#FFF3E0] text-[#E65100] font-bold text-[10px]">JNE</span>
                                            <span class="text-[11px] text-[#5A544E]">Reguler Service</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- 4. KEMASAN KHUSUS --}}
                                <td class="py-4 px-4 align-top">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FAF7F2] border border-[#EBE4D8] text-[10px] font-medium text-[#5A544E]">
                                        <svg class="w-3 h-3 text-[#A86544]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                        <span>{{ $packaging }}</span>
                                    </div>
                                </td>

                                {{-- 5. NOMOR RESI (AWB) --}}
                                <td class="py-4 px-4 align-top">
                                    @if ($resi)
                                        <div class="flex items-center gap-1.5 font-mono text-[11px] text-[#1E1B18] font-semibold">
                                            <span>{{ $resi }}</span>
                                            <button type="button"
                                                    onclick="navigator.clipboard.writeText('{{ $resi }}'); alert('Nomor resi {{ $resi }} berhasil disalin!')"
                                                    title="Salin Resi"
                                                    class="text-[#8C827A] hover:text-[#1E1B18] transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('admin.orders.update-pengiriman', $order) }}" class="flex items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="ekspedisi" value="{{ $ekspedisiName }}" />
                                            <input type="hidden" name="status_pengiriman" value="Dikirim" />
                                            <input type="text"
                                                   name="no_resi"
                                                   placeholder="Input Resi JNE..."
                                                   class="py-1 px-2 text-[11px] rounded-lg bg-[#FAF7F2] border border-[#D9D2C7] text-[#1E1B18] placeholder-[#9C9389] focus:ring-1 focus:ring-[#C59A53] w-32" />
                                            <button type="submit" class="p-1 rounded-lg bg-[#1E1B18] text-white hover:bg-black transition" title="Simpan Resi">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                {{-- 6. STATUS --}}
                                <td class="py-4 px-4 align-top">
                                    @if ($order->status === 'Selesai')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#E8F5E9] text-[#2E7D32] text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32]"></span>
                                            Telah Diterima
                                        </span>
                                    @elseif ($order->status === 'Dikirim')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#FFF3E0] text-[#E65100] text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#E65100]"></span>
                                            Dalam Perjalanan
                                        </span>
                                        <div class="text-[10px] text-[#8C827A] mt-1">
                                            Transit di Hub Bandung Gedebage
                                        </div>
                                    @elseif ($resi)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#F3E5F5] text-[#7B1FA2] text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#7B1FA2]"></span>
                                            Siap Serah Terima
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#FFEBEE] text-[#C62828] text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#C62828]"></span>
                                            Perlu Input Resi
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-[#8C827A]">
                                    Belum ada data pengiriman yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Row --}}
            <div class="p-4 border-t border-[#F5F0E8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#736B63]">
                <div>
                    Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pengiriman
                </div>
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('admin.pengiriman.index') }}" class="flex items-center gap-1.5 text-xs text-[#736B63]">
                        <input type="hidden" name="search" value="{{ $search }}" />
                        <input type="hidden" name="status" value="{{ $statusFilter }}" />
                        <input type="hidden" name="ekspedisi" value="{{ $ekspedisiFilter }}" />
                        <span>BARIS:</span>
                        <select name="per_page" onchange="this.form.submit()" class="py-1 px-2 rounded-lg bg-[#FAF7F2] border border-[#EBE4D8] text-xs text-[#1E1B18]">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Lower Section: Distribusi Spasial & Jadwal Pick-up Kurir --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Card 1: Distribusi Spasial (2 cols) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-[#EBE4D8] p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold tracking-wider text-[#8C827A] uppercase">DISTRIBUSI SPASIAL</span>
                        <h2 class="text-base font-serif font-bold text-[#1E1B18] mt-0.5">Rute Kurir & Hub Utama Hari Ini</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E8F5E9] text-[#2E7D32] text-[10px] font-bold tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32] animate-pulse"></span>
                        Live Fleet Sensor
                    </span>
                </div>

                {{-- Status Origin Box --}}
                <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#EBE4D8] flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#A86544]"></span>
                        <span class="font-bold text-[#1E1B18]">Titik Asal Ekspedisi RajaOngkir: {{ $originInfo['city_name'] ?? 'Kota Surakarta (Solo)' }} (ID: {{ $originInfo['origin_city_id'] ?? 444 }})</span>
                    </div>
                    <div class="text-[11px] text-[#736B63]">
                        Integrasi API RajaOngkir Aktif • Cuaca: Cerah 28°C
                    </div>
                </div>

                {{-- Transit Route Graphic --}}
                <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#EBE4D8] relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 relative z-10">
                        {{-- Node 1 --}}
                        <div class="text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-[#1E1B18] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                SOC
                            </div>
                            <div class="text-[11px] font-bold text-[#1E1B18] mt-1.5">[Origin] Solo</div>
                            <div class="text-[10px] text-[#8C827A]">Studio Surakarta</div>
                        </div>

                        {{-- Dotted Arrow --}}
                        <div class="hidden sm:flex flex-1 items-center justify-center px-2">
                            <div class="w-full border-t-2 border-dashed border-[#C59A53]"></div>
                        </div>

                        {{-- Node 2 --}}
                        <div class="text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-white border-2 border-[#C59A53] text-[#1E1B18] flex items-center justify-center font-bold text-xs shadow-xs">
                                SMG
                            </div>
                            <div class="text-[11px] font-bold text-[#1E1B18] mt-1.5">Hub Semarang</div>
                            <div class="text-[10px] text-[#8C827A]">Transit Koridor 1</div>
                        </div>

                        {{-- Dotted Arrow --}}
                        <div class="hidden sm:flex flex-1 items-center justify-center px-2">
                            <div class="w-full border-t-2 border-dashed border-[#C59A53]"></div>
                        </div>

                        {{-- Node 3 --}}
                        <div class="text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-white border-2 border-[#C59A53] text-[#1E1B18] flex items-center justify-center font-bold text-xs shadow-xs">
                                BDG
                            </div>
                            <div class="text-[11px] font-bold text-[#1E1B18] mt-1.5">Hub Bandung</div>
                            <div class="text-[10px] text-[#8C827A]">Sortir Gedebage</div>
                        </div>

                        {{-- Dotted Arrow --}}
                        <div class="hidden sm:flex flex-1 items-center justify-center px-2">
                            <div class="w-full border-t-2 border-dashed border-[#C59A53]"></div>
                        </div>

                        {{-- Node 4 --}}
                        <div class="text-center">
                            <div class="w-10 h-10 mx-auto rounded-full bg-[#A86544] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                JKT
                            </div>
                            <div class="text-[11px] font-bold text-[#1E1B18] mt-1.5">[Destinasi]</div>
                            <div class="text-[10px] text-[#8C827A]">Jakarta & Surabaya</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#EBE4D8] flex items-center justify-between text-xs text-[#736B63]">
                        <div class="flex items-center gap-1.5">
                            <span>🚚</span>
                            <span>3 Truk Pengangkut Besek berangkat serentak pukul 17:00 WIB</span>
                        </div>
                        <a href="javascript:void(0)" onclick="alert('Membuka telemetri armada real-time...')" class="font-bold text-[#A86544] hover:underline">
                            Buka Peta Satelit Telemetri →
                        </a>
                    </div>
                </div>

                {{-- 3 Indicators Row --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8]">
                        <div class="text-[10px] font-bold uppercase text-[#8C827A]">Ketepatan Waktu</div>
                        <div class="text-lg font-serif font-bold text-[#1E1B18] mt-0.5">99.2%</div>
                        <div class="text-[10px] text-[#2E7D32] mt-0.5">+0.4% dari bulan lalu</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8]">
                        <div class="text-[10px] font-bold uppercase text-[#8C827A]">Rerata Durasi Kirim</div>
                        <div class="text-lg font-serif font-bold text-[#1E1B18] mt-0.5">1.8 Hari</div>
                        <div class="text-[10px] text-[#736B63] mt-0.5">Jawa & Bali</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8]">
                        <div class="text-[10px] font-bold uppercase text-[#8C827A]">Bahan Daur Ulang Digunakan</div>
                        <div class="text-lg font-serif font-bold text-[#1E1B18] mt-0.5">342 kg</div>
                        <div class="text-[10px] text-[#A86544] mt-0.5">Siklus Februari 2025</div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Jadwal Pick-up Kurir (1 col) --}}
            <div class="bg-white rounded-2xl border border-[#EBE4D8] p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-serif font-bold text-[#1E1B18]">Jadwal Pick-up Kurir</h2>
                        <span class="w-2 h-2 rounded-full bg-[#C59A53]"></span>
                    </div>
                    <p class="text-xs text-[#736B63] mt-1">
                        Konfirmasi kedatangan armada logistik pihak ketiga di Galeri Hamzah.
                    </p>

                    <div class="mt-4 space-y-3">
                        {{-- JNE --}}
                        <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#FFF3E0] text-[#E65100] font-bold text-[10px] flex items-center justify-center">
                                    JNE
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-[#1E1B18]">Armada Reguler JNE</div>
                                    <div class="text-[11px] text-[#8C827A]">Est. Tiba: 14:45 - 15:15 WIB</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-[#1E1B18] text-white text-[10px] font-bold">12 Koli</span>
                        </div>

                        {{-- POS Indonesia --}}
                        <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#FFEBEE] text-[#C62828] font-bold text-[10px] flex items-center justify-center">
                                    POS
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-[#1E1B18]">POS Indonesia (Kilat Khusus)</div>
                                    <div class="text-[11px] text-[#8C827A]">Est. Tiba: 16:30 WIB</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-[#1E1B18] text-white text-[10px] font-bold">8 Koli</span>
                        </div>

                        {{-- TIKI --}}
                        <div class="p-3 rounded-xl bg-[#FCFAF6] border border-[#F5F0E8] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#EDE7F6] text-[#512DA8] font-bold text-[10px] flex items-center justify-center">
                                    TIKI
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-[#1E1B18]">TIKI Express ONS/REG</div>
                                    <div class="text-[11px] text-[#8C827A]">Est. Tiba: 17:30 WIB</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-[#1E1B18] text-white text-[10px] font-bold">4 Koli</span>
                        </div>
                    </div>
                </div>

                {{-- Contact Footer --}}
                <div class="mt-4 pt-3 border-t border-[#F5F0E8]">
                    <a href="https://wa.me/6281234567890" target="_blank" class="w-full py-2.5 px-3 rounded-xl bg-[#FAF7F2] border border-[#EBE4D8] flex items-center justify-between text-xs text-[#1E1B18] hover:bg-[#F3EBE1] transition">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#A86544]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="font-medium">Dispatcher Logistik: Pak Danang</span>
                        </div>
                        <span class="text-[#A86544] font-bold">📞</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Bottom Dark Zero-Waste Banner --}}
        <div class="rounded-2xl bg-[#1E1B18] border border-[#C59A53]/30 p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-5 shadow-sm">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-[#C59A53] shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold font-serif text-[#FAF7F2]">100% Kemasan Tanpa Plastik Sekali Pakai</h2>
                    <p class="text-xs text-[#C5BCB2] mt-0.5 max-w-xl">
                        Semua produk Hamzah Style dikemas menggunakan besek bambu dan tali serat alami terurai hayati, mendukung keberlanjutan perajin lokal Nusantara.
                    </p>
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FAF7F2] text-[#1E1B18] text-xs font-semibold hover:bg-white transition shadow-xs">
                    <span>Lihat Laporan Dampak Hijau</span>
                    <svg class="w-3.5 h-3.5 text-[#A86544]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

