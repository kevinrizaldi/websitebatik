<x-storefront-layout :title="'Pembayaran Pesanan #' . $order->code">
    <x-slot name="extraHead">
        <!-- Midtrans Snap JS -->
        <script src="{{ $snapJsUrl ?? 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey ?? config('midtrans.client_key') }}"></script>
    </x-slot>

    <div x-data="{
        snapToken: '{{ $snapToken ?? '' }}',
        orderCode: '{{ $order->code ?? '' }}',
        isPaying: false,

        copyText(txt, label) {
            navigator.clipboard.writeText(txt);
            window.dispatchEvent(new CustomEvent('show-toast', { detail: label + ' berhasil disalin!' }));
        },

        payWithMidtrans() {
            if (!this.snapToken) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Token pembayaran Midtrans tidak valid.' }));
                return;
            }

            this.isPaying = true;

            if (typeof window.snap !== 'undefined' && !this.snapToken.startsWith('MOCK_SNAP_')) {
                window.snap.pay(this.snapToken, {
                    onSuccess: (result) => {
                        window.location.href = '{{ route('midtrans.finish') }}?order_id=' + encodeURIComponent(this.orderCode) + '&status=success';
                    },
                    onPending: (result) => {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Menunggu pembayaran Anda diselesaikan.' }));
                        setTimeout(() => {
                            window.location.href = '{{ route('pesanan.index') }}';
                        }, 1500);
                    },
                    onError: (result) => {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Pembayaran gagal atau dibatalkan.' }));
                        this.isPaying = false;
                    },
                    onClose: () => {
                        this.isPaying = false;
                    }
                });
            } else {
                // Sandbox fallback simulation
                window.location.href = '{{ route('midtrans.finish') }}?order_id=' + encodeURIComponent(this.orderCode) + '&mock=1';
            }
        }
    }">

        <!-- Page Title Header -->
        <div class="bg-white border-b border-[#ECE4D8] py-8 sm:py-10">
            <div class="max-w-6xl mx-auto px-4 lg:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ in_array($order->status, ['Diproses', 'Sudah Dibayar']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            <span class="w-2 h-2 rounded-full {{ in_array($order->status, ['Diproses', 'Sudah Dibayar']) ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $order->status }}
                        </span>
                        <span class="text-xs text-stone-500">ID Pesanan: #{{ $order->code }}</span>
                    </div>
                    <h1 class="font-serif-title text-2xl sm:text-3xl font-bold text-stone-900">
                        Pembayaran Pesanan
                    </h1>
                    <p class="text-xs sm:text-sm text-stone-600 mt-1">
                        Selesaikan transaksi pesanan busana batik Anda melalui payment gateway aman.
                    </p>
                </div>

                <div class="bg-[#FAF7F2] p-4 rounded-2xl border border-[#ECE4D8] flex items-center justify-between md:justify-end gap-6">
                    <div class="text-left md:text-right">
                        <div class="text-xs text-stone-500">Total Tagihan</div>
                        <div class="text-xl sm:text-2xl font-bold text-[#B58742]">
                            Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <a href="{{ route('pesanan.index') }}" class="px-4 py-2 text-xs font-semibold text-stone-700 bg-white border border-stone-200 rounded-xl hover:bg-stone-50 transition">
                        Pesanan Saya
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 sm:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- Left Column: Payment Box -->
                <div class="lg:col-span-7 space-y-6">

                    @if(in_array($order->status, ['Diproses', 'Sudah Dibayar', 'Selesai']))
                        <!-- Already Paid State -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-sm text-center">
                            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="font-serif-title text-2xl font-bold text-stone-900 mb-2">
                                Pembayaran Berhasil!
                            </h2>
                            <p class="text-xs sm:text-sm text-stone-600 mb-6 max-w-md mx-auto">
                                Terima kasih, transaksi untuk pesanan <span class="font-bold text-stone-800">#{{ $order->code }}</span> telah kami terima. Tim kami sedang menyiapkan busana batik pesanan Anda.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('pesanan.index') }}" class="px-6 py-3 bg-[#201A17] text-white rounded-xl text-xs font-bold hover:bg-stone-800 transition">
                                    Lihat Status Pesanan
                                </a>
                                <a href="{{ route('koleksi.index') }}" class="px-6 py-3 bg-stone-100 text-stone-800 rounded-xl text-xs font-bold hover:bg-stone-200 transition">
                                    Lanjut Belanja
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Unpaid: Midtrans Gateway Trigger Card -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#ECE4D8] shadow-sm space-y-6">
                            <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                                <div>
                                    <h3 class="font-serif-title text-lg font-bold text-stone-900">
                                        Metode Pembayaran Online
                                    </h3>
                                    <p class="text-xs text-stone-500">QRIS (GoPay, OVO, ShopeePay), Transfer Bank (BCA, Mandiri, BNI, BRI), Kartu Kredit</p>
                                </div>
                                <div class="px-3 py-1 bg-stone-100 rounded-lg text-[10px] font-bold text-stone-600 uppercase tracking-widest">
                                    Midtrans
                                </div>
                            </div>

                            <!-- Snap Gateway Button -->
                            <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-[#ECE4D8] text-center space-y-4">
                                <div class="w-12 h-12 bg-[#201A17] text-[#E5C38E] rounded-xl flex items-center justify-center mx-auto shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-stone-900 text-base mb-1">Buka Jendela Pembayaran</h4>
                                    <p class="text-xs text-stone-600 max-w-sm mx-auto">
                                        Klik tombol di bawah ini untuk membuka popup pembayaran Midtrans Snap yang aman dan terenkripsi.
                                    </p>
                                </div>

                                <button @click="payWithMidtrans()"
                                        :disabled="isPaying"
                                        type="button"
                                        class="w-full sm:w-auto px-8 py-3.5 bg-[#B58742] hover:bg-[#9E7334] text-white font-bold text-sm rounded-xl transition shadow-md flex items-center justify-center gap-2 mx-auto disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span x-text="isPaying ? 'Memproses Gateway...' : 'Bayar Sekarang'"></span>
                                </button>
                            </div>

                            <!-- Petunjuk Pembayaran -->
                            <div class="space-y-3 pt-2">
                                <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">
                                    Panduan Singkat:
                                </h4>
                                <ul class="space-y-2 text-xs text-stone-600 list-disc list-inside">
                                    <li>Pilih metode pembayaran yang Anda inginkan (QRIS, VA Bank, dsb) di popup Snap.</li>
                                    <li>Jika memilih QRIS, lakukan scan QR menggunakan aplikasi e-wallet Anda.</li>
                                    <li>Jika memilih Virtual Account, salin nomor VA dan lakukan transfer dari ATM atau m-Banking.</li>
                                    <li>Setelah pembayaran berhasil, status pesanan otomatis berubah menjadi <strong>Diproses</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Right Column: Order Summary -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#ECE4D8] shadow-sm space-y-4">
                        <h3 class="font-serif-title text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                            Ringkasan Produk Pesanan
                        </h3>

                        <div class="space-y-3 max-h-80 overflow-y-auto pr-1 no-scrollbar">
                            @foreach($order->items as $item)
                            <div class="flex items-center gap-3 py-2 border-b border-stone-50 last:border-0">
                                <div class="w-12 h-12 rounded-xl bg-stone-100 overflow-hidden shrink-0">
                                    <img src="{{ $item->produk ? $item->produk->gambar_url : asset('images/beranda/folded-shirts.jpg') }}"
                                         alt="{{ $item->product_name ?? $item->produk_name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-xs text-stone-800 truncate">
                                        {{ $item->product_name ?? $item->produk_name }}
                                    </div>
                                    <div class="text-[11px] text-stone-500">{{ $item->quantity ?? 1 }} x Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="font-bold text-xs text-stone-900 shrink-0">
                                    Rp {{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', '.') }}
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="bg-[#FAF7F2] rounded-2xl p-4 space-y-2 border border-[#ECE4D8] text-xs">
                            <div class="flex justify-between text-stone-600">
                                <span>Alamat Pengiriman:</span>
                            </div>
                            <div class="text-stone-800 text-[11px] leading-relaxed pb-2 border-b border-stone-200">
                                {{ $order->address }}
                            </div>
                            <div class="flex justify-between font-bold text-stone-900 text-sm pt-1">
                                <span>Total Pembayaran</span>
                                <span>Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between text-xs text-stone-500">
                            <span>Butuh bantuan pembayaran?</span>
                            <a href="https://wa.me/6281234567890" target="_blank" class="font-bold text-[#201A17] hover:text-[#B58742] transition">
                                WhatsApp CS &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-storefront-layout>
