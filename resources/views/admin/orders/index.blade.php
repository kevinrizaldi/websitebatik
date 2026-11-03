<x-app-layout>
    <div class="space-y-8">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 shadow-2xs">
                <p class="font-bold mb-1">⚠️ Perhatian Perubahan Status Pesanan:</p>
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
                {{-- Eyebrow --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544] mb-1">
                    <span>KONSOL KURASI PESANAN</span>
                    <span>•</span>
                    <span class="text-emerald-700">Sinkronisasi Real-Time</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Pesanan Pelanggan
                    <span class="sr-only">Kelola Pesanan</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Kelola verifikasi transaksi gateway Midtrans, status pengerjaan kurasi, dan pengiriman ekspedisi RajaOngkir ke seluruh Nusantara.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Label Massal</span>
                </button>

                <a href="{{ route('admin.laporan.export', 'csv') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <svg class="h-4 w-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Ekspor Data Pesanan</span>
                </a>
            </div>
        </div>

        {{-- 4 METRIC CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. PERLU VERIFIKASI --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">PERLU VERIFIKASI</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $counts['menunggu_pembayaran'] }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Transaksi</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] font-semibold text-[#A86544]">
                    <span>●</span>
                    <span>Menunggu Pembayaran (Midtrans Pending)</span>
                </div>
            </div>

            {{-- 2. SEDANG DIKEMAS --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">SEDANG DIKEMAS</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $counts['diproses'] }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Paket</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] text-stone-500 font-medium">
                    <span>📦</span>
                    <span>Siap serah terima kurir</span>
                </div>
            </div>

            {{-- 3. DALAM PERJALANAN --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">DALAM PERJALANAN</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM3 9h11v8H3V9zm11 3h4l3 3v2h-7v-5z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $counts['dikirim'] }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Kargo</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] text-stone-500 font-medium">
                    <span>🚚</span>
                    <span>Ekspedisi RajaOngkir (JNE, POS &amp; TIKI)</span>
                </div>
            </div>

            {{-- 4. OMZET BULAN INI --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">OMZET BULAN INI</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        Rp 84,2 jt
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-1 text-[11px] font-semibold text-[#A86544]">
                    <span>↗</span>
                    <span>+14.6% vs Jan</span>
                </div>
            </div>
        </div>

        {{-- FILTER TABS BAR --}}
        @php
            $activeStatus = request('status', '');
            $tabs = [
                '' => ['label' => 'Semua', 'count' => $counts['all']],
                'Menunggu Pembayaran' => ['label' => 'Menunggu Pembayaran', 'count' => $counts['menunggu_pembayaran']],
                'Diproses' => ['label' => 'Diproses', 'count' => $counts['diproses']],
                'Dikirim' => ['label' => 'Dikirim', 'count' => $counts['dikirim']],
                'Selesai' => ['label' => 'Selesai', 'count' => $counts['selesai']],
            ];
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#EFECE6] pb-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @foreach ($tabs as $key => $tab)
                    @php $isActive = ($activeStatus === (string) $key); @endphp
                    <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), $key !== '' ? ['status' => $key] : [])) }}"
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

            <div class="flex items-center gap-2">
                <span class="text-xs text-stone-400">Tampilan:</span>
                <button type="button" class="p-1.5 rounded-lg border border-[#E8E4DC] bg-white text-stone-600 hover:bg-[#FAF7F2] shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- FILTER SEARCH TOOLBAR --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            {{-- Search Box --}}
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex-1 max-w-md relative">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Cari Order ID, Nama Pembeli, No. WhatsApp..."
                       class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
            </form>

            {{-- Controls --}}
            <div class="flex items-center gap-2">
                <div class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs">
                    <span>📅</span>
                    <span>{{ now()->translatedFormat('F Y') }}</span>
                </div>

                <select onchange="window.location.href=this.value" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                    <option value="{{ route('admin.orders.index') }}">Semua Metode Pembayaran</option>
                    <option value="{{ route('admin.orders.index', ['status' => 'Menunggu Pembayaran']) }}">Menunggu Bayar (Midtrans Pending)</option>
                    <option value="{{ route('admin.orders.index', ['status' => 'Diproses']) }}">Midtrans Gateway (Settlement)</option>
                </select>

                <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-500 hover:bg-[#FAF7F2] transition shadow-2xs" title="Reset filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- TABLE ORDERS --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/60 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-6">ORDER ID &amp; WAKTU</th>
                            <th class="py-3.5 px-6">DATA PELANGGAN</th>
                            <th class="py-3.5 px-6">RINGKASAN ITEM PESANAN</th>
                            <th class="py-3.5 px-6">TOTAL TAGIHAN</th>
                            <th class="py-3.5 px-6">STATUS PEMBAYARAN</th>
                            <th class="py-3.5 px-6 text-center">STATUS PESANAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($orders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $itemCount = $order->items->count();
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$order->phone);
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

                                $paymentStatusPill = match (true) {
                                    in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']) => ['● Lunas Otomatis (Midtrans Settlement)', 'bg-[#FAF7F2] text-stone-700 border-[#EFECE6]'],
                                    in_array($order->status, ['Dibatalkan', 'Batal']) => ['● Batal / Expired', 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]'],
                                    default => ['● Menunggu Bayar (Midtrans Pending)', 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]'],
                                };

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
                            <tr class="hover:bg-[#FAF7F2] transition group">
                                {{-- ORDER ID & WAKTU --}}
                                <td class="py-4 px-6 font-mono">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-xs text-[#1E1B18] hover:text-[#A86544] hover:underline block">
                                        #{{ $order->code }}
                                    </a>
                                    <p class="text-[10px] text-stone-400 mt-1 font-sans">
                                        {{ $order->created_at->format('d M Y') }} • {{ $order->created_at->format('H:i') }} WIB
                                    </p>
                                </td>

                                {{-- DATA PELANGGAN --}}
                                <td class="py-4 px-6">
                                    <p class="font-bold text-xs text-[#1E1B18]">{{ $order->customer_name }}</p>
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-mono text-[#A86544] hover:underline mt-0.5">
                                        <span>💬</span>
                                        <span>{{ $order->phone }}</span>
                                    </a>
                                    <p class="text-[10px] text-stone-400 mt-0.5 truncate max-w-xs">
                                        📍 {{ $order->address }}
                                    </p>
                                </td>

                                {{-- RINGKASAN ITEM PESANAN --}}
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                            @if ($firstItem?->produk && $firstItem->produk->gambar)
                                                <img src="{{ asset('storage/'.$firstItem->produk->gambar) }}" alt="{{ $firstItem->produk_name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                                    Kain
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs text-[#1E1B18] truncate max-w-xs">
                                                {{ $firstItem->produk_name ?? 'Pesanan Busana Batik' }}
                                                @if ($itemCount > 1)
                                                    <span class="text-stone-400 font-normal">(+ {{ $itemCount - 1 }} item)</span>
                                                @endif
                                            </p>
                                            <span class="inline-block rounded bg-[#FAF0E4] px-1.5 py-0.5 text-[9px] font-bold text-[#8C5E28] mt-1">
                                                Upcycled Cotton Cut
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- TOTAL TAGIHAN --}}
                                <td class="py-4 px-6">
                                    <p class="font-bold text-xs text-[#1E1B18]">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </p>
                                    <span class="inline-block rounded bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-bold text-[#8C5E28] border border-[#E9DFD0] mt-1">
                                        {{ $methodBadge }}
                                    </span>
                                </td>

                                {{-- STATUS PEMBAYARAN --}}
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $paymentStatusPill[1] }}">
                                        {{ $paymentStatusPill[0] }}
                                    </span>
                                </td>

                                {{-- STATUS PESANAN --}}
                                <td class="py-4 px-6 text-center">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10px] font-extrabold border {{ $statusStyle }} hover:scale-105 transition">
                                        <span>{{ $order->status }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-stone-400">
                                    Tidak ada data transaksi pesanan yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Pagination --}}
            <div class="p-4 sm:p-5 border-t border-[#EFECE6] bg-[#FAF7F2]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-stone-500">
                    Menampilkan {{ $orders->firstItem() ?? 1 }} - {{ $orders->lastItem() ?? $orders->count() }} dari {{ $orders->total() }} pesanan
                </span>

                <div class="flex items-center gap-2">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        {{-- BOTTOM BANNER: KEMASAN NOL SAMPAH --}}
        <div class="rounded-2xl border border-[#E9DFD0] bg-[#FAF0E4]/70 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53] shadow-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#8C5E28] mb-0.5">
                        <span>Protokol Keberlanjutan &amp; Kemasan Nol Sampah</span>
                        <span class="rounded bg-[#F8EDE2] px-1.5 py-0.2 text-[#A86544] font-extrabold">Eko-Artisan</span>
                    </div>
                    <p class="text-xs text-[#5A544E] mt-1 max-w-2xl leading-relaxed">
                        Seluruh paket dikemas dengan kertas samson daur ulang, tali serat rami alami, serta sertifikat keaslian motif batik tulis berpola tangan asli. Dihitung akurat via RajaOngkir API.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Panduan pengemasan ramah lingkungan Hamzah Style.')" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    Panduan Pengemasan
                </button>
                <button type="button" onclick="alert('Permintaan penjemputan kurir logistik RajaOngkir diproses.')" class="rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-black transition">
                    Hubungi Kurir Penjemputan
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
