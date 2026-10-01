<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen text-stone-800 text-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex justify-between items-center text-sm shadow-sm">
                    <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-lg leading-none">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex justify-between items-center text-sm shadow-sm">
                    <span class="flex items-center gap-2"><span>⚠</span> {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- Breadcrumb + Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wider mb-1">
                        <span>Pesanan Saya</span> /
                        <span class="text-stone-700 font-bold">#{{ $order->code }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-extrabold text-stone-900">Pembayaran Pesanan</h1>
                        @php
                            $payBadge = match($order->payment_status) {
                                'paid'      => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'expired'   => 'bg-rose-100 text-rose-800 border-rose-200',
                                'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
                                default     => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                            $payLabel = match($order->payment_status) {
                                'paid'      => 'Lunas',
                                'expired'   => 'Kedaluwarsa',
                                'cancelled' => 'Dibatalkan',
                                default     => 'Menunggu Pembayaran',
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $payBadge }}">
                            {{ $payLabel }}
                        </span>
                    </div>
                    <div class="text-gray-400 text-[11px] mt-1">
                        📅 Pesanan dibuat: {{ $order->created_at->format('d F Y, H:i') }} WIB
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-stone-700 hover:bg-gray-50 shadow-sm transition">
                    ← Kembali ke Dashboard
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- KOLOM KIRI --}}
                <div class="lg:col-span-7 space-y-5">

                    {{-- ═══════════════════════════════════════════════════════════
                         BLOK 1: STATUS PEMBAYARAN & TOMBOL AKSI
                    ═══════════════════════════════════════════════════════════ --}}
                    @if ($order->payment_status === 'paid')
                        {{-- LUNAS --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">✓</div>
                                <div>
                                    <h3 class="font-extrabold text-stone-900 text-sm">Pembayaran Berhasil</h3>
                                    <p class="text-gray-400 text-[11px]">Pesanan ini sudah lunas dan sedang diproses.</p>
                                </div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-emerald-900 text-xs font-semibold flex items-center gap-2">
                                🎉 Terima kasih! Pesanan kamu sedang dikemas oleh toko.
                            </div>
                            @if ($order->paid_at)
                                <div class="mt-3 text-[11px] text-gray-400">
                                    Dibayar pada: {{ $order->paid_at->format('d F Y, H:i') }} WIB
                                </div>
                            @endif
                        </div>

                    @elseif ($order->status === 'Batal' || $order->payment_status === 'expired')
                        {{-- BATAL / KEDALUWARSA --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-lg">✕</div>
                                <div>
                                    <h3 class="font-extrabold text-stone-900 text-sm">Pesanan Dibatalkan / Kedaluwarsa</h3>
                                    <p class="text-gray-400 text-[11px]">Batas waktu pembayaran telah lewat.</p>
                                </div>
                            </div>
                            <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 text-rose-900 text-xs font-semibold flex items-center gap-2">
                                ❌ Pesanan ini tidak dapat dibayar lagi. Silakan buat pesanan baru.
                            </div>
                        </div>

                    @elseif ($activePayment && $activePayment->isReusable())
                        {{-- ADA PERCOBAAN AKTIF (PENDING + BELUM EXPIRED) --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-stone-100 text-stone-700 flex items-center justify-center text-lg">💳</div>
                                <div>
                                    <h3 class="font-extrabold text-stone-900 text-sm">Menunggu Pembayaran</h3>
                                    <p class="text-gray-400 text-[11px]">Selesaikan pembayaran sebelum batas waktu habis.</p>
                                </div>
                            </div>

                            {{-- Batas Waktu --}}
                            @if ($activePayment->expires_at)
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] text-amber-600 font-bold uppercase tracking-wider mb-0.5">BATAS WAKTU BAYAR</div>
                                        <div class="font-extrabold text-stone-900 text-sm" id="deadline-display">
                                            {{ $activePayment->expires_at->format('d M Y, H:i') }} WIB
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-400 mb-0.5">SISA WAKTU</div>
                                        <div class="font-bold text-amber-700 text-sm" id="countdown">--:--:--</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Tombol Aksi --}}
                            <div class="flex gap-3 pt-1">
                                <button
                                    id="btn-pay"
                                    onclick="openSnapPopup()"
                                    class="flex-1 bg-stone-900 hover:bg-black text-white font-bold py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                                    💳 Lanjutkan Pembayaran
                                </button>
                                <button
                                    id="btn-change"
                                    onclick="changeMethod()"
                                    class="px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-stone-700 font-semibold rounded-xl shadow-sm transition text-sm">
                                    🔄 Ganti Metode
                                </button>
                            </div>

                            {{-- Instruksi Pembayaran Tersimpan --}}
                            @if ($activePayment->payment_details && $activePayment->payment_type)
                                @include('pesanan.partials.payment-instructions', ['payment' => $activePayment])
                            @else
                                <div class="text-[11px] text-gray-400 italic px-1">
                                    Instruksi pembayaran akan tampil di sini setelah kamu memilih metode di popup.
                                    Tekan <strong>Lanjutkan Pembayaran</strong> untuk memilih metode.
                                </div>
                            @endif
                        </div>

                    @else
                        {{-- TIDAK ADA PERCOBAAN AKTIF — DEADLINE PESANAN BELUM LEWAT --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-lg">🕐</div>
                                <div>
                                    <h3 class="font-extrabold text-stone-900 text-sm">Pembayaran Belum Dimulai</h3>
                                    <p class="text-gray-400 text-[11px]">Percobaan pembayaran sebelumnya sudah kedaluwarsa.</p>
                                </div>
                            </div>
                            @if ($order->payment_deadline)
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-[11px] text-amber-800">
                                    ⏳ Batas waktu pesanan: <strong>{{ $order->payment_deadline->format('d M Y, H:i') }} WIB</strong>
                                </div>
                            @endif
                            <button
                                id="btn-pay"
                                onclick="openSnapPopup()"
                                class="w-full bg-stone-900 hover:bg-black text-white font-bold py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                                ✨ Buat Pembayaran Baru
                            </button>
                        </div>
                    @endif

                    {{-- ═══════════════════════════════════════════════════════════
                         BLOK 2: DAFTAR ITEM PESANAN
                    ═══════════════════════════════════════════════════════════ --}}
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-stone-900 text-sm">Rincian Pesanan</h3>
                            <span class="bg-stone-100 text-stone-700 font-bold text-[10px] px-2 py-0.5 rounded-full">{{ $order->items->count() }} ITEM</span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <div class="py-3 flex gap-4 items-center">
                                    <div class="w-14 h-14 bg-stone-100 rounded-xl overflow-hidden flex-shrink-0 border border-stone-200/50">
                                        @if ($item->produk && $item->produk->foto)
                                            <img src="{{ asset('storage/' . $item->produk->foto) }}" class="w-full h-full object-cover" alt="{{ $item->produk_name }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[10px] font-bold text-stone-500">Batik</div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-stone-900 text-sm">{{ $item->produk_name }}</div>
                                        <div class="text-gray-400 text-[11px] mt-0.5">{{ $item->quantity }}x · Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="font-bold text-stone-900 text-sm">
                                        Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- Rincian Total --}}
                        <div class="pt-3 border-t border-gray-100 space-y-1.5 text-stone-600">
                            @php $subtotal = $order->items->sum(fn($i) => $i->price * $i->quantity); @endphp
                            <div class="flex justify-between">
                                <span>Subtotal Produk</span>
                                <span class="font-semibold text-stone-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->shipping_cost && $order->shipping_cost > 0)
                                <div class="flex justify-between">
                                    <span>Ongkos Kirim</span>
                                    <span class="font-semibold text-stone-800">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-3 border-t border-gray-100 text-sm font-extrabold text-stone-900">
                                <span>Total Tagihan</span>
                                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN --}}
                <div class="lg:col-span-5 space-y-5">

                    {{-- Kartu Info Pesanan --}}
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                        <h3 class="font-bold text-stone-900 text-sm border-b border-gray-100 pb-2">Informasi Pesanan</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-400">No. Pesanan</span>
                                <span class="font-bold text-stone-900">#{{ $order->code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Tanggal</span>
                                <span class="font-semibold text-stone-800">{{ $order->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status</span>
                                <span class="font-semibold text-stone-800">{{ $order->status }}</span>
                            </div>
                            @if ($order->payment_deadline)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Deadline Pesanan</span>
                                    <span class="font-semibold text-stone-800">{{ $order->payment_deadline->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Kartu Info Pengiriman --}}
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                        <h3 class="font-bold text-stone-900 text-sm border-b border-gray-100 pb-2">Alamat Pengiriman</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-stone-200 font-bold text-stone-700 text-sm flex items-center justify-center">
                                {{ strtoupper(substr($order->customer_name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-stone-900 text-sm">{{ $order->customer_name }}</div>
                                <div class="text-gray-400 text-[11px]">{{ $order->phone }}</div>
                            </div>
                        </div>
                        <p class="text-stone-600 text-[11px] leading-relaxed pl-1">{{ $order->address }}</p>
                    </div>

                    {{-- Kartu Unduh QR (jika tersedia) --}}
                    @if ($activePayment && $activePayment->isReusable())
                        @php
                            $qrAvailable = false;
                            $details = $activePayment->payment_details ?? [];
                            foreach (($details['actions'] ?? []) as $action) {
                                if (is_array($action) && isset($action['name'], $action['url'])
                                    && str_contains(strtolower($action['name']), 'qr')) {
                                    $qrAvailable = true;
                                    break;
                                }
                            }
                        @endphp
                        @if ($qrAvailable)
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                                <h3 class="font-bold text-stone-900 text-sm border-b border-gray-100 pb-2">QR Code Pembayaran</h3>
                                <p class="text-[11px] text-gray-500">Scan QR ini lewat aplikasi bank atau e-wallet kamu.</p>
                                <a
                                    href="{{ route('payment.qr', $order) }}"
                                    class="w-full flex items-center justify-center gap-2 bg-stone-900 hover:bg-black text-white font-bold py-2.5 rounded-xl transition text-sm"
                                    download>
                                    📲 Unduh QR Code
                                </a>
                            </div>
                        @endif
                    @endif

                    {{-- Bantuan --}}
                    <div class="bg-stone-50 border border-stone-100 rounded-2xl p-4 text-[11px] text-stone-500 space-y-1">
                        <div class="font-bold text-stone-700 mb-2">ℹ️ Catatan Penting</div>
                        <p>• Status pesanan diperbarui otomatis setelah pembayaran berhasil.</p>
                        <p>• Kamu dapat menutup popup dan kembali ke halaman ini kapan saja.</p>
                        <p>• Nomor VA / kode QRIS tersimpan dan tetap bisa dilihat di sini.</p>
                        <p>• Jika status belum berubah, coba <strong>refresh halaman</strong> beberapa saat.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ═══ JAVASCRIPT: Snap Popup + Change Method + Countdown ═══ --}}
    @if (
        $order->payment_status === \App\Models\Order::PAYMENT_PENDING
        && $order->status !== 'Batal'
    )
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            const ORDER_ID = {{ $order->id }};
            const TOKEN_URL = "{{ route('payment.token') }}";
            const CHANGE_URL = "{{ route('payment.change-method') }}";
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;

            @if ($activePayment && $activePayment->isReusable())
            // Reuse existing snap token directly.
            let cachedToken = @json($activePayment->snap_token);
            @else
            let cachedToken = null;
            @endif

            async function openSnapPopup() {
                const btn = document.getElementById('btn-pay');
                if (btn) { btn.disabled = true; btn.textContent = 'Memuat...'; }

                try {
                    const token = cachedToken ?? await fetchToken();
                    if (!token) return;

                    window.snap.pay(token, {
                        onSuccess: () => { location.reload(); },
                        onPending: () => { location.reload(); },
                        onError:   () => { location.reload(); },
                        onClose:   () => {
                            if (btn) { btn.disabled = false; btn.textContent = '💳 Lanjutkan Pembayaran'; }
                        },
                    });
                } catch (e) {
                    alert('Gagal memuat halaman pembayaran. Silakan coba lagi.');
                    if (btn) { btn.disabled = false; btn.textContent = '💳 Lanjutkan Pembayaran'; }
                }
            }

            async function fetchToken() {
                const res = await fetch(TOKEN_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ order_id: ORDER_ID }),
                });
                const data = await res.json();
                if (!res.ok) { alert(data.message ?? 'Gagal mendapatkan token pembayaran.'); return null; }
                cachedToken = data.snap_token;
                return cachedToken;
            }

            async function changeMethod() {
                const btn = document.getElementById('btn-change');
                if (!confirm('Ganti metode pembayaran? Percobaan saat ini akan dibatalkan.')) return;
                if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }

                try {
                    const res = await fetch(CHANGE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ order_id: ORDER_ID }),
                    });
                    const data = await res.json();

                    if (res.status === 409) {
                        // Already paid — reload to show paid state
                        location.reload();
                        return;
                    }
                    if (!res.ok) {
                        alert(data.message ?? 'Gagal mengganti metode. Coba lagi nanti.');
                        if (btn) { btn.disabled = false; btn.textContent = '🔄 Ganti Metode'; }
                        return;
                    }

                    // Open popup with new token
                    cachedToken = data.snap_token;
                    window.snap.pay(data.snap_token, {
                        onSuccess: () => { location.reload(); },
                        onPending: () => { location.reload(); },
                        onError:   () => { location.reload(); },
                        onClose:   () => { location.reload(); },
                    });
                } catch (e) {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    if (btn) { btn.disabled = false; btn.textContent = '🔄 Ganti Metode'; }
                }
            }

            // ── Countdown timer ────────────────────────────────────────────────
            @if ($activePayment && $activePayment->expires_at)
            (function () {
                const deadline = new Date({{ $activePayment->expires_at->timestamp }} * 1000);
                const el = document.getElementById('countdown');
                if (!el) return;

                function tick() {
                    const diff = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
                    if (diff === 0) { el.textContent = 'KEDALUWARSA'; el.classList.add('text-rose-600'); location.reload(); return; }
                    const h = String(Math.floor(diff / 3600)).padStart(2, '0');
                    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                    const s = String(diff % 60).padStart(2, '0');
                    el.textContent = `${h}:${m}:${s}`;
                }
                tick();
                setInterval(tick, 1000);
            })();
            @endif
        </script>
    @endif
</x-app-layout>
