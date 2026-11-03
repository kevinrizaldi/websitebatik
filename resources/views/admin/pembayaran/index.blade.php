<x-app-layout>
    <div class="space-y-8">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        {{-- HEADER BREADCRUMB & TITLE --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Breadcrumbs --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-stone-700">DASHBOARD</a>
                    <span>/</span>
                    <span class="text-[#A86544]">FINANSIAL &amp; GATEWAY MIDTRANS</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Pembayaran &amp; Gateway Midtrans
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Validasi status pembayaran Midtrans gateway (Snap, QRIS, &amp; Virtual Account), rekonsiliasi otomatis via webhook, dan mutasi kas masuk studio Hamzah Style.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Mempersiapkan unduhan rekap kas masuk CSV...')" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Rekap (CSV)</span>
                </button>

                <form method="POST" action="{{ route('admin.pembayaran.sync') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                        <svg class="h-4 w-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Sinkronisasi Gateway Midtrans</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- 4 METRIC CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. PERLU TINDAKAN (PENDING) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">PERLU TINDAKAN</span>
                    <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-bold text-[#8C5E28]">
                        Midtrans Pending
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    Rp {{ number_format($pendingAmount, 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="font-extrabold text-[#A86544]">{{ $pendingCount }} Transaksi</span>
                    <span class="text-stone-400">• Menunggu pembayaran</span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>Expiry Snap: 24 Jam</span>
                    <span>⏱</span>
                </div>
            </div>

            {{-- 2. GATEWAY TERPADU (SETTLEMENT) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">GATEWAY MIDTRANS</span>
                    <span class="rounded bg-[#FAF7F2] px-2 py-0.5 text-[9px] font-bold text-stone-600">
                        ✓ Otomatis (Settlement)
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    Rp {{ number_format($gatewayAmount, 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="font-extrabold text-[#1E1B18]">84 Transaksi</span>
                    <span class="text-stone-400">• QRIS &amp; Virtual Account</span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>Webhook IPN Midtrans Realtime</span>
                    <span>☁</span>
                </div>
            </div>

            {{-- 3. BANK TRANSFER (VA) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">VIRTUAL ACCOUNT (VA)</span>
                    <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-bold text-[#8C5E28]">
                        Midtrans VA
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    Rp {{ number_format($successAmount, 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="font-extrabold text-[#1E1B18]">{{ $successCount }} Transaksi</span>
                    <span class="text-stone-400">• Lunas terverifikasi</span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>BCA, Mandiri, BNI, BRI VA</span>
                    <span>🏦</span>
                </div>
            </div>

            {{-- 4. DISPUTE & BATAL --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">EXPIRED &amp; BATAL</span>
                    <span class="rounded bg-[#FFEBEE] px-2 py-0.5 text-[9px] font-bold text-[#C62828]">
                        ⚠ Expired / Batal
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18]">
                    Rp {{ number_format($failedAmount, 0, ',', '.') }}
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="font-extrabold text-rose-600">{{ $failedCount }} Transaksi</span>
                    <span class="text-stone-400">• Kedaluwarsa di Gateway</span>
                </div>
                <div class="mt-4 pt-3 border-t border-[#EFECE6] flex items-center justify-between text-[11px] text-stone-500">
                    <span>Notifikasi WhatsApp terkirim</span>
                    <span>⇅</span>
                </div>
            </div>
        </div>

        {{-- FILTER TOOLBAR & PILLS --}}
        <div class="space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- Search Box --}}
                <form action="{{ route('admin.pembayaran.index') }}" method="GET" class="flex-1 max-w-md relative">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Cari ID Pembayaran, Order ID, nama pembeli, atau Midtrans ID..."
                           class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                </form>

                {{-- Dropdowns --}}
                <div class="flex items-center gap-2">
                    <select class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option>Semua Saluran Midtrans</option>
                        <option>Midtrans QRIS / GoPay</option>
                        <option>Midtrans BCA Virtual Account</option>
                        <option>Midtrans Mandiri Bill / VA</option>
                        <option>Midtrans BNI Virtual Account</option>
                        <option>Midtrans BRI Virtual Account</option>
                        <option>Midtrans Kartu Kredit/Debit</option>
                    </select>

                    <div class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs">
                        <span>📅</span>
                        <span>{{ now()->translatedFormat('F Y') }}</span>
                    </div>

                    <a href="{{ route('admin.pembayaran.index') }}" class="p-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-500 hover:bg-[#FAF7F2] shadow-2xs" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                <a href="{{ route('admin.pembayaran.index', ['status' => 'all']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'all',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $statusFilter !== 'all',
                   ])>
                    Semua ({{ $totalPayments }})
                </a>
                <a href="{{ route('admin.pembayaran.index', ['status' => 'pending']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'pending',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $statusFilter !== 'pending',
                   ])>
                    Menunggu Pembayaran ({{ $pendingCount }})
                </a>
                <a href="{{ route('admin.pembayaran.index', ['status' => 'success']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'success',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $statusFilter !== 'success',
                   ])>
                    Berhasil / Settlement ({{ $successCount }})
                </a>
                <a href="{{ route('admin.pembayaran.index', ['status' => 'failed']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $statusFilter === 'failed',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $statusFilter !== 'failed',
                   ])>
                    Dibatalkan / Expired ({{ $failedCount }})
                </a>
            </div>
        </div>

        {{-- TABLE PEMBAYARAN & MUTASI --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/60 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-6">ID TRANSAKSI &amp; WAKTU</th>
                            <th class="py-3.5 px-6">ORDER ID TERKAIT</th>
                            <th class="py-3.5 px-6">DATA PEMBAYAR</th>
                            <th class="py-3.5 px-6">SALURAN MIDTRANS</th>
                            <th class="py-3.5 px-6">NOMINAL TRANSAKSI</th>
                            <th class="py-3.5 px-6">STATUS GATEWAY</th>
                            <th class="py-3.5 px-6">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($orders as $order)
                            @php
                                $payCode = '#PAY-'.$order->created_at->format('Ymd').'-'.str_pad((string)$order->id, 3, '0', STR_PAD_LEFT);
                                $isPending = in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Verifikasi', 'Menunggu Konfirmasi']);
                                $isSuccess = in_array($order->status, ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai']);
                                $isFailed = in_array($order->status, ['Batal', 'Dibatalkan']);

                                $statusPill = match (true) {
                                    $isPending => ['Menunggu Pembayaran (Midtrans Pending)', 'bg-[#FAF0E4] text-[#8C5E28] border-[#E9DFD0]'],
                                    $isSuccess => ['Berhasil (Lunas Otomatis)', 'bg-[#FAF7F2] text-stone-700 border-[#EFECE6]'],
                                    default => ['Dibatalkan / Expired', 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]'],
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
                            @endphp
                            <tr class="hover:bg-[#FAF7F2] transition group">
                                {{-- ID PEMBAYARAN & WAKTU --}}
                                <td class="py-4 px-6 font-mono">
                                    <p class="font-extrabold text-xs text-[#1E1B18]">{{ $payCode }}</p>
                                    <p class="text-[10px] text-stone-400 mt-0.5 font-sans">
                                        {{ $order->created_at->format('d M Y, H:i') }} WIB
                                    </p>
                                </td>

                                {{-- ORDER ID TERKAIT --}}
                                <td class="py-4 px-6">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-extrabold text-xs text-[#1E1B18] hover:text-[#A86544] hover:underline">
                                        #{{ $order->code }}
                                    </a>
                                    <p class="text-[11px] text-stone-400 mt-0.5 truncate max-w-xs">
                                        {{ $order->items->first()->produk_name ?? 'Batik Sogan Klasik 2.5m' }}
                                    </p>
                                </td>

                                {{-- DATA PEMBAYAR --}}
                                <td class="py-4 px-6">
                                    <p class="font-bold text-xs text-[#1E1B18]">{{ $order->customer_name }}</p>
                                    <p class="text-[10px] text-stone-400 mt-0.5 font-mono">
                                        Midtrans Ref: MID-{{ substr(md5($order->code), 0, 8) }}
                                    </p>
                                </td>

                                {{-- METODE / SALURAN MIDTRANS --}}
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-[#FAF0E4] px-2.5 py-1 text-[10px] font-bold text-[#8C5E28] border border-[#E9DFD0]">
                                        <span>⚡</span>
                                        <span>{{ $methodBadge }}</span>
                                    </span>
                                </td>

                                {{-- NOMINAL TRANSAKSI --}}
                                <td class="py-4 px-6">
                                    <p class="font-bold text-xs text-[#1E1B18]">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-emerald-700 font-semibold mt-0.5 flex items-center gap-1">
                                        <span>✓</span>
                                        <span>Termasuk Ongkir RajaOngkir</span>
                                    </p>
                                </td>

                                {{-- STATUS GATEWAY --}}
                                <td class="py-4 px-6">
                                    @if ($isSuccess)
                                        <div class="flex items-center gap-1.5 text-[11px] text-stone-700 font-medium">
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>Midtrans Settlement</span>
                                        </div>
                                        <p class="text-[9px] text-stone-400">Terverifikasi Webhook Otomatis</p>
                                    @elseif ($isPending)
                                        <div class="flex items-center gap-1.5 text-[11px] text-[#A86544] font-medium">
                                            <span>⏳</span>
                                            <span>Menunggu Pembayaran</span>
                                        </div>
                                        <p class="text-[9px] text-stone-400">Snap Token Aktif (24 Jam)</p>
                                    @else
                                        <div class="flex items-center gap-1.5 text-[11px] text-rose-600 font-medium">
                                            <span>✕</span>
                                            <span>Kedaluwarsa / Dibatalkan</span>
                                        </div>
                                        <p class="text-[9px] text-stone-400">Midtrans Expire State</p>
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td class="py-4 px-6">
                                    <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $statusPill[1] }}">
                                        {{ $statusPill[0] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-stone-400">
                                    Tidak ada catatan mutasi pembayaran yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Pagination --}}
            <div class="p-4 sm:p-5 border-t border-[#EFECE6] bg-[#FAF7F2]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-4 text-stone-500">
                    <span>Menampilkan {{ $orders->firstItem() ?? 1 }} - {{ $orders->lastItem() ?? $orders->count() }} dari {{ $totalPayments }} catatan pembayaran</span>
                    <span>•</span>
                    <span>Baris per halaman: 5</span>
                </div>

                <div class="flex items-center gap-2">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        {{-- BOTTOM SOP VALIDASI CALLOUT --}}
        <div class="rounded-2xl border border-[#E9DFD0] bg-[#FAF0E4]/70 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
            <div class="flex items-center gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53] shadow-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-[#1E1B18]">SOP Rekonsiliasi Gateway Midtrans Hamzah Style</h3>
                    <p class="text-[11px] text-[#5A544E] mt-0.5 leading-relaxed">
                        Seluruh transaksi diproses otomatis melalui Midtrans Snap Gateway (QRIS, VA Bank &amp; E-Wallet). Notifikasi webhook IPN memperbarui pesanan secara real-time. Klik "Sinkronisasi Gateway Midtrans" untuk memeriksa status transaksi server Midtrans sewaktu-waktu.
                    </p>
                </div>
            </div>

            <button type="button" onclick="alert('Membuka Panduan Finansial & Gateway Midtrans...')" class="text-xs font-bold text-[#8C5E28] hover:text-[#1E1B18] shrink-0 flex items-center gap-1">
                <span>Buka Panduan Gateway</span>
                <span>→</span>
            </button>
        </div>
    </div>
</x-app-layout>
