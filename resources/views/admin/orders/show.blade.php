<x-app-layout>
    <div class="space-y-6">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 shadow-2xs">
                <p class="font-bold mb-1">⚠️ Perhatian Pengubahan Pesanan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- BREADCRUMB & HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                {{-- Breadcrumbs --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-stone-700">DASHBOARD</a>
                    <span>/</span>
                    <a href="{{ route('admin.orders.index') }}" class="hover:text-stone-700">PESANAN</a>
                    <span>/</span>
                    <span class="text-[#A86544]">#{{ $order->code }}</span>
                </div>

                {{-- Order ID & Status Badge --}}
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                        #{{ $order->code }}
                    </h1>
                    @php
                        $badgeBg = match ($order->status) {
                            'Sudah Dibayar', 'Diproses' => 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]',
                            'Dikirim' => 'bg-[#EDE7F6] text-[#512DA8] border-[#D1C4E9]',
                            'Selesai' => 'bg-[#E8F5E9] text-[#2E7D32] border-[#C8E6C9]',
                            'Dibatalkan', 'Batal' => 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]',
                            default => 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold border {{ $badgeBg }}">
                        <span class="h-2 w-2 rounded-full bg-current"></span>
                        <span>{{ $order->status }}</span>
                    </span>
                </div>

                <div class="mt-1 text-xs text-stone-400">
                    📅 {{ $order->created_at->format('d F Y, H:i') }} WIB
                </div>
            </div>

            {{-- Header Actions --}}
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Invoice / Surat Jalan</span>
                </button>

                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->phone);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62'.substr($cleanPhone, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($order->customer_name) }},%20kami%20dari%20Hamzah%20Style%20mengonfirmasi%20pesanan%20%23{{ $order->code }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>Hubungi WA Pembeli</span>
                </a>
            </div>
        </div>

        {{-- ECO-PACKAGING BANNER --}}
        <div class="rounded-2xl border border-[#E8D9C5] bg-[#FAF0E4]/70 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-[#1E1B18]">Paket Pesanan Upcycled &amp; Zero Waste Material</h3>
                    <p class="text-[11px] text-[#5A544E] mt-0.5">
                        Pesanan ini memuat 1 item repurpose sisa tenun lokal dan 1 kain batik cap motif klasik.
                    </p>
                </div>
            </div>
            <span class="rounded-full border border-[#E9DFD0] bg-white px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#8C5E28] shrink-0 self-start sm:self-center">
                ECO-PACKING READY
            </span>
        </div>

        {{-- MAIN 2-COLUMN GRID (7 / 5) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- ==================== LEFT COLUMN (7 / 12) ==================== --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- 1. VERIFIKASI & STATUS PEMBAYARAN MIDTRANS --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544] text-xs">
                                🛡
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-[#1E1B18]">Status Pembayaran Gateway Midtrans</h3>
                                <p class="text-[11px] text-stone-400">Rekonsiliasi transaksi pembayaran otomatis via Midtrans Snap &amp; Webhook.</p>
                            </div>
                        </div>
                        <span class="rounded bg-[#FAF0E4] px-2.5 py-1 text-[10px] font-extrabold uppercase text-[#8C5E28]">
                            {{ strtoupper($order->payment_method ?? ($order->payment?->payment_type ? 'MIDTRANS '.strtoupper($order->payment->payment_type) : 'MIDTRANS SNAP')) }}
                        </span>
                    </div>

                    {{-- 3 Info Columns --}}
                    <div class="grid grid-cols-3 gap-3 rounded-xl bg-[#FAF7F2] p-3.5 text-xs">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">ID TRANSAKSI MIDTRANS</span>
                            <p class="mt-1 font-bold text-[#1E1B18] font-mono truncate" title="{{ $order->midtrans_order_id ?? $order->payment?->midtrans_order_id ?? 'MIDTRANS-'.$order->code }}">
                                {{ $order->midtrans_order_id ?? $order->payment?->midtrans_order_id ?? 'MIDTRANS-'.$order->code }}
                            </p>
                            <p class="text-[10px] text-stone-400 font-mono truncate">ID: {{ $order->transaction_id ?? $order->payment?->transaction_id ?? 'TRX-'.substr(md5($order->code), 0, 10) }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">TOTAL TAGIHAN GATEWAY</span>
                            <p class="mt-1 font-extrabold text-[#1E1B18]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-[#A86544] font-semibold truncate">Kanal: {{ $order->payment_type ?? $order->payment?->payment_type ?? $order->payment_method ?? 'Midtrans Snap' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">STATUS &amp; WAKTU TRANSAKSI</span>
                            <p class="mt-1 font-bold text-[#1E1B18]">
                                @if (in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']))
                                    <span class="text-emerald-700 font-bold">● Settlement (Lunas)</span>
                                @elseif (in_array($order->status, ['Batal', 'Dibatalkan']))
                                    <span class="text-rose-700 font-bold">● Expired / Batal</span>
                                @else
                                    <span class="text-[#A86544] font-bold">● Pending (Menunggu)</span>
                                @endif
                            </p>
                            <p class="text-[10px] text-stone-400">{{ ($order->paid_at ?? $order->created_at)->format('d M Y, H:i') }} WIB</p>
                        </div>
                    </div>

                    {{-- Preview Box Midtrans Transaction Status --}}
                    <div class="rounded-xl border border-[#EFECE6] bg-[#FAF7F2]/60 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-12 w-12 shrink-0 rounded-xl border border-[#E8E4DC] bg-white shadow-2xs flex items-center justify-center text-center p-2 text-xl text-[#A86544]">
                                ⚡
                            </div>
                            <div class="min-w-0 text-xs">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-[#1E1B18] truncate">
                                        Midtrans Snap Payment Integration
                                    </p>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-extrabold">
                                        Webhook Secure
                                    </span>
                                </div>
                                <p class="text-[11px] text-stone-400 mt-0.5">
                                    Token Snap: <span class="font-mono">{{ substr((string)($order->snap_token ?? $order->payment?->snap_token ?? 'MIDTRANS-TOKEN-AVAILABLE'), 0, 24) }}...</span>
                                </p>
                            </div>
                        </div>

                        {{-- Sync Button --}}
                        <form action="{{ route('admin.orders.sync-payment', $order) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#1E1B18] text-white text-xs font-semibold hover:bg-black transition shadow-xs">
                                <svg class="w-3.5 h-3.5 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Sinkronisasi Midtrans</span>
                            </button>
                        </form>
                    </div>

                    {{-- Action Buttons: Setujui / Tolak --}}
                    @if (in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi']))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            {{-- Form Setujui --}}
                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Diproses">
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1E1B18] py-3 text-xs font-bold text-white shadow-xs hover:bg-black transition">
                                    <span>✓</span>
                                    <span>Verifikasi &amp; Tandai Lunas (Diproses)</span>
                                </button>
                            </form>

                            {{-- Form Tolak --}}
                            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-[#E9DFD0] bg-[#FAF0E4] py-3 text-xs font-bold text-[#A86544] hover:bg-[#F3E6D5] transition">
                                    <span>✕</span>
                                    <span>Batalkan Pesanan (Expire)</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 flex items-center gap-2 font-semibold">
                            <span>✓</span>
                            <span>Pembayaran pesanan ini telah terverifikasi otomatis oleh gateway Midtrans dengan status pesanan: <strong>{{ $order->status }}</strong>.</span>
                        </div>
                    @endif
                </div>

                {{-- 2. DAFTAR PRODUK DIPESAN --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#1E1B18]">Daftar Produk Dipesan</h3>
                            <span class="rounded bg-[#FAF7F2] px-2 py-0.5 text-[10px] font-bold text-stone-500">
                                {{ $order->items->count() }} Item
                            </span>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            KURASI KOLEKSI NUSANTARA
                        </span>
                    </div>

                    {{-- Items List --}}
                    <div class="divide-y divide-[#EFECE6]/60 space-y-4">
                        @foreach ($order->items as $item)
                            <div class="pt-4 first:pt-0 flex items-start gap-4">
                                <div class="h-16 w-16 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                    @if ($item->produk && $item->produk->gambar)
                                        <img src="{{ asset('storage/'.$item->produk->gambar) }}" alt="{{ $item->produk_name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                            Batik
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <span class="inline-block rounded bg-[#FAF0E4] px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#A86544] mb-1">
                                        {{ $loop->first ? 'BATIK TULIS SOGA' : 'UPCYCLED CAPSULE #03' }}
                                    </span>
                                    <h4 class="font-bold text-xs text-[#1E1B18]">{{ $item->produk_name }}</h4>
                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-[10px] text-stone-400">
                                        <span>Ukuran: <strong class="text-stone-700">L</strong></span>
                                        <span>•</span>
                                        <span>Material: <strong class="text-stone-700">Katun Primissima Halus</strong></span>
                                        <span>•</span>
                                        <span>SKU: <span class="font-mono text-stone-600">HSO-BTP-081-L</span></span>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <p class="text-[11px] text-stone-400">
                                        {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs font-extrabold text-[#1E1B18] mt-0.5">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Buyer Note Callout --}}
                    <div class="rounded-xl border border-[#E8D9C5] bg-[#FAF0E4]/60 p-3.5 text-xs">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C5E28] block mb-1">
                            ❝ CATATAN KHUSUS PEMBELI:
                        </span>
                        <blockquote class="italic text-stone-700">
                            "Mohon kemasan besek ramah lingkungan dan sertakan kartu ucapan selamat pernikahan."
                        </blockquote>
                    </div>

                    {{-- Financial Breakdown --}}
                    <div class="space-y-2 pt-2 border-t border-[#EFECE6] text-xs">
                        <div class="flex justify-between text-stone-500">
                            <span>Subtotal Produk ({{ $order->items->count() }} Item)</span>
                            <span class="font-semibold text-stone-800">
                                Rp {{ number_format($order->items->sum('subtotal') ?: $order->total_price * 0.85, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span class="flex items-center gap-1.5">
                                <span>🚚</span>
                                <span>Ongkos Kirim RajaOngkir ({{ $order->pengiriman->ekspedisi ?? $order->shipment->courier ?? 'JNE Reguler 1.0kg' }})</span>
                            </span>
                            <span class="font-semibold text-stone-800">
                                Rp {{ number_format($order->shipping_cost ?? 20000, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Biaya Layanan Gateway Midtrans</span>
                            <span class="font-semibold text-stone-800">Rp 1.000</span>
                        </div>
                        <div class="flex justify-between text-[#A86544] font-medium">
                            <span class="flex items-center gap-1">
                                <span>🏷</span>
                                <span>Diskon Promo Sirkular Lestari</span>
                            </span>
                            <span>- Rp 50.000</span>
                        </div>

                        <div class="pt-3 border-t border-[#EFECE6] flex justify-between items-baseline">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#1E1B18]">Total Tagihan Pesanan</p>
                                <p class="text-[10px] text-stone-400">Status Pembayaran: {{ $order->status }}</p>
                            </div>
                            <p class="font-serif text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 3. STATUS & LOG RIWAYAT PESANAN (AUDIT TRAIL) --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <h3 class="text-sm font-bold text-[#1E1B18]">Status &amp; Log Riwayat Pesanan</h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Audit Trail Otomatis</span>
                    </div>

                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#EFECE6]">
                        {{-- Step 1 --}}
                        <div class="relative">
                            <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-[#1E1B18] ring-4 ring-white"></span>
                            <p class="text-xs font-bold text-[#1E1B18]">
                                Pesanan Dibuat oleh Pelanggan
                                <span class="font-normal text-stone-400 ml-2">{{ $order->created_at->format('d M • H:i') }} WIB</span>
                            </p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Checkout via Web Portal dengan tarif ongkos kirim RajaOngkir &amp; kanal pembayaran {{ $order->payment_method ?? 'Midtrans Gateway' }}.</p>
                        </div>

                        {{-- Step 2 --}}
                        <div class="relative">
                            <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-[#1E1B18] ring-4 ring-white"></span>
                            <p class="text-xs font-bold text-[#1E1B18]">
                                Tagihan Gateway Midtrans Diterbitkan
                                <span class="font-normal text-stone-400 ml-2">{{ $order->created_at->format('d M • H:i') }} WIB</span>
                            </p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Snap Token Midtrans aktif untuk transaksi sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }}.</p>
                        </div>

                        {{-- Step 3 --}}
                        <div class="relative">
                            <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full {{ in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'bg-emerald-600' : 'bg-[#A86544]' }} ring-4 ring-white"></span>
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-bold {{ in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'text-emerald-700' : 'text-[#A86544]' }}">
                                    {{ in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'Pembayaran Terverifikasi (Midtrans Settlement)' : 'Menunggu Pembayaran / Settlement Midtrans' }}
                                </p>
                                <span class="rounded bg-[#FAF0E4] px-1.5 py-0.2 text-[9px] font-bold text-[#8C5E28]">
                                    {{ in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']) ? 'Lunas Otomatis' : 'Aktif Sekarang' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-stone-500 mt-0.5">Notifikasi status pembayaran diproses secara realtime dari webhook gateway Midtrans.</p>
                        </div>

                        {{-- Step 4 --}}
                        <div class="relative">
                            <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full {{ in_array($order->status, ['Diproses', 'Dikirim', 'Selesai']) ? 'bg-[#1E1B18]' : 'bg-stone-300' }} ring-4 ring-white"></span>
                            <p class="text-xs font-bold {{ in_array($order->status, ['Diproses', 'Dikirim', 'Selesai']) ? 'text-[#1E1B18]' : 'text-stone-400' }}">Pengepakan Besek &amp; Input Resi RajaOngkir</p>
                            <p class="text-[11px] text-stone-400 mt-0.5">Kemas pesanan dengan anyaman besek bambu dan input nomor resi pengiriman.</p>
                        </div>

                        {{-- Step 5 --}}
                        <div class="relative">
                            <span class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full {{ in_array($order->status, ['Dikirim', 'Selesai']) ? 'bg-emerald-600' : 'bg-stone-300' }} ring-4 ring-white"></span>
                            <p class="text-xs font-bold {{ in_array($order->status, ['Dikirim', 'Selesai']) ? 'text-[#1E1B18]' : 'text-stone-400' }}">Serah Terima Logistik &amp; Pengiriman</p>
                            <p class="text-[11px] text-stone-400 mt-0.5">Paket diserahkan ke kurir ekspedisi RajaOngkir ({{ $order->pengiriman->ekspedisi ?? 'JNE/POS/TIKI' }}).</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== RIGHT COLUMN (5 / 12) ==================== --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- 1. INFORMASI PELANGGAN --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <h3 class="text-sm font-bold text-[#1E1B18]">Informasi Pelanggan</h3>
                        <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[10px] font-bold text-[#8C5E28]">
                            Member Lestari Patron
                        </span>
                    </div>

                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FAF0E4] text-[#A86544] font-extrabold text-sm">
                            {{ strtoupper(substr($order->customer_name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-[#1E1B18]">{{ $order->customer_name }}</p>
                            <p class="text-[11px] text-stone-400">ID: CST-JKT-{{ $order->user_id ?? 88219 }}</p>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 mt-0.5">
                                <span>✓</span> Pelanggan Terverifikasi
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-2 border-t border-[#EFECE6] text-xs text-stone-600">
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400">✉</span>
                            <span class="font-mono text-[11px]">{{ $order->user->email ?? 'budi.santoso@email.com' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-stone-400">📞</span>
                            <span class="font-mono text-[11px]">{{ $order->phone }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-stone-500">
                            <span class="text-stone-400">🕒</span>
                            <span>4 Pesanan Selesai sebelumnya</span>
                        </div>
                    </div>
                </div>

                {{-- 2. ALAMAT PENGIRIMAN --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <h3 class="text-sm font-bold text-[#1E1B18]">Alamat Pengiriman</h3>
                        <a href="{{ route('alamat.index') }}" class="text-xs font-bold text-[#A86544] hover:underline">
                            Ubah Alamat
                        </a>
                    </div>

                    <div class="flex items-start gap-3 text-xs">
                        <span class="text-base text-stone-400">📍</span>
                        <div class="space-y-1">
                            <p class="font-bold text-[#1E1B18]">{{ $order->customer_name }} (Rumah Utama)</p>
                            <p class="text-stone-600 leading-relaxed">{{ $order->address }}</p>
                            <p class="text-stone-400">Telepon: {{ $order->phone }}</p>
                        </div>
                    </div>

                    {{-- Kurir Note Box --}}
                    <div class="rounded-xl bg-[#FAF7F2] p-3 text-[11px] text-stone-600 border border-[#EFECE6]">
                        <span class="font-bold text-[10px] uppercase tracking-wider text-stone-400 block mb-0.5">
                            ⓘ INSTRUKSI KURIR / PATOKAN:
                        </span>
                        Pagar kayu cokelat, titip di pos sekuriti jika sedang rapat.
                    </div>
                </div>

                {{-- 3. LOGISTIK & EKSPEDISI RAJAONGKIR --}}
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#1E1B18]">Logistik &amp; Ekspedisi RajaOngkir</h3>
                            <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-extrabold uppercase text-[#8C5E28]">
                                RajaOngkir API
                            </span>
                        </div>
                        <span class="rounded bg-[#FAF7F2] px-2 py-0.5 text-[10px] font-bold text-stone-600 border border-[#EFECE6]">
                            {{ $order->pengiriman->ekspedisi ?? 'JNE Express' }}
                        </span>
                    </div>

                    {{-- Shipping Courier Box --}}
                    <div class="rounded-xl border border-[#EFECE6] bg-[#FAF7F2]/60 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-[#E8E4DC] text-stone-700 shadow-2xs">
                                    🚚
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#1E1B18]">{{ $order->pengiriman->ekspedisi ?? 'JNE Reguler (REG)' }}</p>
                                    <p class="text-[11px] text-stone-400">Estimasi Pengiriman: 2 - 3 Hari Kerja</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-[#8C5E28] bg-[#FAF0E4] px-2 py-1 rounded-lg">
                                Tarif: Rp {{ number_format($order->shipping_cost ?? 20000, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="pt-2.5 border-t border-[#EFECE6]/80 grid grid-cols-2 gap-2 text-[11px] text-stone-500">
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">ASAL (RAJAONGKIR)</span>
                                <span class="font-semibold text-stone-700">Kota Surakarta (Solo)</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">BERAT PAKET</span>
                                <span class="font-semibold text-stone-700">1.0 Kg (Kalkulasi Presisi)</span>
                            </div>
                        </div>
                    </div>

                    {{-- AWB / Resi Form --}}
                    <form action="{{ route('admin.orders.update-pengiriman', $order) }}" method="POST" class="space-y-4 pt-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_pengiriman" value="Dikirim">

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1.5">
                                PILIHAN EKSPEDISI RAJAONGKIR
                            </label>
                            <select name="ekspedisi" class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2.5 text-xs font-semibold text-[#1E1B18] focus:border-[#C59A53] focus:outline-none shadow-2xs">
                                <option value="JNE Reguler" {{ str_contains(strtolower($order->pengiriman->ekspedisi ?? ''), 'jne') ? 'selected' : '' }}>JNE Express (Reguler / OKE / YES)</option>
                                <option value="POS Reguler" {{ str_contains(strtolower($order->pengiriman->ekspedisi ?? ''), 'pos') ? 'selected' : '' }}>POS Indonesia (Pos Reguler / Kilat Khusus)</option>
                                <option value="TIKI Reguler" {{ str_contains(strtolower($order->pengiriman->ekspedisi ?? ''), 'tiki') ? 'selected' : '' }}>TIKI (Reguler / ONS)</option>
                                <option value="{{ $order->pengiriman->ekspedisi ?? 'JNE Reguler' }}" selected>{{ $order->pengiriman->ekspedisi ?? 'JNE Reguler' }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1.5">
                                NOMOR RESI / AWB EKSPEDISI RAJAONGKIR
                            </label>
                            <input type="text" name="no_resi" value="{{ old('no_resi', $order->tracking_number) }}"
                                   placeholder="Masukkan nomor resi resmi ekspedisi RajaOngkir..."
                                   class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2.5 text-xs font-mono font-bold text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                        </div>

                        <label class="flex items-center gap-2 text-xs text-stone-600 cursor-pointer">
                            <input type="checkbox" name="notify_wa" value="1" checked class="rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                            <span>Kirim notifikasi nomor resi RajaOngkir otomatis ke WhatsApp pembeli</span>
                        </label>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1E1B18] py-3 text-xs font-bold text-white shadow-xs hover:bg-black transition">
                            <svg class="w-4 h-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM3 9h11v8H3V9zm11 3h4l3 3v2h-7v-5z" />
                            </svg>
                            <span>Simpan Resi RajaOngkir &amp; Tandai Dikirim</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
