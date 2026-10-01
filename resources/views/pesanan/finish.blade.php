<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen text-stone-800 text-xs">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">

            @php
                $isPaid = $order && $order->payment_status === 'paid';
                $isPending = $order && $order->payment_status === 'pending';
                $isCancelled = $order && in_array($order->payment_status, ['cancelled', 'expired', 'failed']);
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Top Banner --}}
                <div class="px-8 py-10 text-center
                    {{ $isPaid ? 'bg-emerald-50' : ($isCancelled ? 'bg-rose-50' : 'bg-amber-50') }}">
                    <div class="text-5xl mb-4">
                        {{ $isPaid ? '✅' : ($isCancelled ? '❌' : '⏳') }}
                    </div>
                    <h1 class="text-xl font-extrabold text-stone-900 mb-1">
                        @if ($isPaid)
                            Pembayaran Berhasil!
                        @elseif ($isCancelled)
                            Pembayaran Gagal / Dibatalkan
                        @else
                            Menunggu Pembayaran
                        @endif
                    </h1>
                    <p class="text-sm text-stone-500">
                        @if ($isPaid)
                            Terima kasih! Pesananmu sedang diproses oleh toko.
                        @elseif ($isCancelled)
                            Pembayaran tidak berhasil. Kamu dapat mencoba kembali dari halaman pesanan.
                        @else
                            Pembayaranmu masih dalam proses. Status akan diperbarui otomatis.
                        @endif
                    </p>
                </div>

                {{-- Detail Pesanan --}}
                <div class="px-8 py-6 space-y-4">
                    @if ($order)
                        <div class="space-y-2 text-[12px]">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-400">No. Pesanan</span>
                                <span class="font-bold text-stone-900">#{{ $order->code }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-400">Status Pembayaran</span>
                                @php
                                    $badge = match($order->payment_status) {
                                        'paid'     => 'bg-emerald-100 text-emerald-800',
                                        'expired'  => 'bg-rose-100 text-rose-800',
                                        'failed'   => 'bg-rose-100 text-rose-800',
                                        'cancelled'=> 'bg-gray-100 text-gray-600',
                                        default    => 'bg-amber-100 text-amber-800',
                                    };
                                    $label = match($order->payment_status) {
                                        'paid'     => 'Lunas',
                                        'expired'  => 'Kedaluwarsa',
                                        'failed'   => 'Gagal',
                                        'cancelled'=> 'Dibatalkan',
                                        default    => 'Menunggu',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge }}">{{ $label }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-400">Total Tagihan</span>
                                <span class="font-bold text-stone-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->paid_at)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-400">Waktu Bayar</span>
                                    <span class="font-semibold text-stone-800">{{ $order->paid_at->format('d M Y, H:i') }} WIB</span>
                                </div>
                            @endif
                        </div>

                        {{-- Catatan status dari DB --}}
                        <div class="text-[11px] text-gray-400 text-center italic pt-2">
                            Status ini diambil langsung dari database — selalu akurat.
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="space-y-2 pt-2">
                            @if ($isPending)
                                <a href="{{ route('orders.pay', $order) }}"
                                   class="w-full flex items-center justify-center gap-2 bg-stone-900 hover:bg-black text-white font-bold py-3 rounded-xl transition text-sm">
                                    💳 Lanjutkan Pembayaran
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}"
                               class="w-full flex items-center justify-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-stone-700 font-semibold py-3 rounded-xl transition text-sm">
                                ← Kembali ke Dashboard
                            </a>
                        </div>

                    @else
                        {{-- Tidak dapat memuat detail pesanan --}}
                        <div class="text-center py-4 space-y-4">
                            <p class="text-stone-500 text-sm">Tidak dapat memuat detail pesanan.</p>
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 bg-stone-900 hover:bg-black text-white font-bold px-6 py-3 rounded-xl transition text-sm">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Auto-refresh jika masih pending --}}
            @if ($isPending)
                <div class="mt-4 text-center text-[11px] text-gray-400">
                    Halaman ini akan refresh otomatis dalam <span id="refresh-in">10</span> detik jika status masih menunggu.
                </div>
                <script>
                    let t = 10;
                    const el = document.getElementById('refresh-in');
                    setInterval(() => { t--; if (el) el.textContent = t; if (t <= 0) location.reload(); }, 1000);
                </script>
            @endif

        </div>
    </div>
</x-app-layout>
