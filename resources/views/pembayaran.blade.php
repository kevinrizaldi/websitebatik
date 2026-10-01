<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Midtrans - Hamzah Style Official</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Midtrans Snap JS -->
    <script src="{{ $snapJsUrl ?? 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey ?? config('midtrans.client_key') }}"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF7F2; color: #26211D; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col"
      x-data="{
          toastMessage: '',
          mobileMenuOpen: false,
          snapToken: '{{ $snapToken ?? '' }}',
          orderCode: '{{ $order->code ?? '' }}',
          isPaying: false,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3500);
          },

          copyText(txt, label) {
              navigator.clipboard.writeText(txt);
              this.showToast(label + ' berhasil disalin!');
          },

          payWithMidtrans() {
              if (!this.snapToken) {
                  this.showToast('Token pembayaran Midtrans tidak valid.');
                  return;
              }

              this.isPaying = true;

              // Check if snap library is loaded
              if (typeof window.snap !== 'undefined' && !this.snapToken.startsWith('MOCK_SNAP_')) {
                  window.snap.pay(this.snapToken, {
                      onSuccess: (result) => {
                          window.location.href = '{{ route('midtrans.finish') }}?order_id=' + encodeURIComponent(this.orderCode) + '&status=success';
                      },
                      onPending: (result) => {
                          this.showToast('Menunggu pembayaran Anda diselesaikan.');
                          setTimeout(() => {
                              window.location.href = '{{ route('pesanan.index') }}';
                          }, 1500);
                      },
                      onError: (result) => {
                          this.showToast('Pembayaran gagal atau dibatalkan.');
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

    <!-- Toast -->
    <div x-cloak x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 bg-[#FAF7F2]/90 backdrop-blur-md border-b border-[#ECE4D8] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-lg bg-[#201A17] flex items-center justify-center text-[#E5C38E] shadow-sm group-hover:bg-stone-800 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold tracking-wider text-sm sm:text-base text-stone-900 uppercase font-sans">
                                HAMZAH STYLE
                            </span>
                            <span class="text-[10px] text-stone-500 tracking-widest uppercase -mt-1 font-medium">
                                OFFICIAL
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Step Indicator -->
                <div class="hidden md:flex items-center gap-2 text-xs font-semibold text-stone-500">
                    <span class="text-stone-400">1. Keranjang</span>
                    <span class="text-stone-300">/</span>
                    <span class="text-stone-400">2. Checkout</span>
                    <span class="text-stone-300">/</span>
                    <span class="text-[#B58742] font-bold">3. Pembayaran Digital Midtrans</span>
                </div>

                <!-- Back to Orders -->
                <div>
                    <a href="{{ route('pesanan.index') }}" class="text-xs font-semibold text-stone-700 hover:text-stone-900 flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Daftar Pesanan</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Title Header -->
    <div class="bg-white border-b border-[#ECE4D8] py-8 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold uppercase tracking-wider mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Midtrans Payment Gateway</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-serif-title">
                    Selesaikan Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-stone-600 mt-1">
                    Pesanan Anda telah tercatat dengan aman. Selesaikan pembayaran secara instan tanpa perlu unggah bukti transfer.
                </p>
            </div>

            <!-- Total Price Badge -->
            <div class="bg-[#FAF7F2] p-4 sm:p-5 rounded-2xl border border-[#ECE4D8] shrink-0 text-right">
                <div class="text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1">TOTAL TAGIHAN</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#201A17]">
                    Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-stone-500 mt-0.5">Termasuk PPN & Biaya Layanan</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow py-8 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
            
            <!-- Left Column: Midtrans Payment Action -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Main Payment Card -->
                <div class="bg-white rounded-3xl border border-[#ECE4D8] p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#201A17] flex items-center justify-center text-[#E5C38E]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-stone-900 text-lg">Pembayaran Digital Terpadu</h2>
                                <p class="text-xs text-stone-500">Virtual Account, QRIS, E-Wallet, & Kartu</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-800 text-[10px] font-bold tracking-wider rounded-lg border border-emerald-200 uppercase">
                            OTOMATIS & AMAN
                        </span>
                    </div>

                    <!-- Order Code & Customer -->
                    <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-[#ECE4D8] mb-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-stone-500">Nomor Pesanan</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-stone-900 text-sm tracking-wide">{{ $order->code }}</span>
                                <button @click="copyText('{{ $order->code }}', 'Nomor Pesanan')" class="text-stone-400 hover:text-stone-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-stone-500">Nama Penerima</span>
                            <span class="font-semibold text-stone-800 text-xs">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-stone-500">Status Pembayaran</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $order->status === 'Diproses' || $order->status === 'Sudah Dibayar' ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $order->status === 'Diproses' || $order->status === 'Sudah Dibayar' ? 'bg-emerald-600' : 'bg-amber-600 animate-ping' }}"></span>
                                {{ $order->pembayaran->status_pembayaran ?? $order->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Payment Channels Preview Icons -->
                    <div class="border border-[#ECE4D8] rounded-2xl p-5 mb-6">
                        <div class="text-xs font-bold text-stone-700 mb-3">Metode Pembayaran Yang Didukung:</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-stone-50 border border-stone-200 flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">📱</span>
                                <span class="text-[11px] font-bold text-stone-800">QRIS</span>
                                <span class="text-[9px] text-stone-500">BCA, GoPay, Dana, dll</span>
                            </div>
                            <div class="p-3 rounded-xl bg-stone-50 border border-stone-200 flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">🏦</span>
                                <span class="text-[11px] font-bold text-stone-800">Virtual Account</span>
                                <span class="text-[9px] text-stone-500">BCA, BNI, Mandiri, BRI</span>
                            </div>
                            <div class="p-3 rounded-xl bg-stone-50 border border-stone-200 flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">👛</span>
                                <span class="text-[11px] font-bold text-stone-800">E-Wallet</span>
                                <span class="text-[9px] text-stone-500">GoPay, ShopeePay</span>
                            </div>
                            <div class="p-3 rounded-xl bg-stone-50 border border-stone-200 flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">💳</span>
                                <span class="text-[11px] font-bold text-stone-800">Kartu Kredit</span>
                                <span class="text-[9px] text-stone-500">Visa, Mastercard, JCB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button: Pay With Midtrans -->
                    @if($order->status === 'Menunggu Pembayaran' || $order->status === 'Belum Dibayar')
                        <div class="space-y-3">
                            <button type="button" 
                                    @click="payWithMidtrans()" 
                                    :disabled="isPaying"
                                    class="w-full py-4 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white font-bold text-base flex items-center justify-center gap-2.5 transition shadow-lg hover:shadow-xl active:scale-[0.99] disabled:opacity-50">
                                <svg class="w-5 h-5 text-[#E5C38E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span x-text="isPaying ? 'Membuka Midtrans...' : 'Bayar Sekarang via Midtrans'"></span>
                            </button>

                            <!-- Fallback simulasi: hanya tampil bila token Midtrans asli tidak tersedia -->
                            @if (str_starts_with($snapToken ?? '', 'MOCK_SNAP_'))
                                <a href="{{ route('midtrans.finish', ['order_id' => $order->code, 'mock' => 1]) }}"
                                   class="w-full py-2.5 rounded-xl border border-stone-300 hover:bg-stone-100 text-stone-700 font-semibold text-xs flex items-center justify-center gap-2 transition">
                                    <span>Simulasikan Pembayaran Berhasil (Sandbox)</span>
                                </a>
                                <p class="text-center text-[11px] text-stone-400">Mode simulasi aktif karena server Midtrans tidak terjangkau. Periksa API key bila ini muncul terus.</p>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-emerald-900 text-sm">Pembayaran Telah Dikonfirmasi</h4>
                                <p class="text-xs text-emerald-700">Pesanan Anda telah dibayar via Midtrans dan sedang diproses pengiriman.</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex items-start gap-2.5 bg-stone-50 border border-stone-200 rounded-xl p-3.5 text-xs text-stone-500">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Sistem otomatis mendeteksi status pembayaran dari Midtrans. Anda tidak perlu mengunggah bukti transfer manual.</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Order Summary & Workflow -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Status Flow Card -->
                <div class="bg-white rounded-3xl border border-[#ECE4D8] p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center text-stone-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-stone-900 text-sm">Alur Pesanan Otomatis</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50/70 border border-amber-200">
                            <div class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">1</div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-xs">Pilih Metode Pembayaran di Midtrans</h4>
                                <p class="text-[10px] text-stone-600 mt-0.5">Pilih QRIS, VA, atau e-wallet pada jendela Midtrans</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-stone-50 border border-stone-200">
                            <div class="w-5 h-5 rounded-full bg-stone-300 text-stone-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-xs">Sistem Memvalidasi Otomatis</h4>
                                <p class="text-[10px] text-stone-500 mt-0.5">Midtrans mengirimkan sinyal konfirmasi seketika</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-stone-50 border border-stone-200">
                            <div class="w-5 h-5 rounded-full bg-stone-300 text-stone-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">3</div>
                            <div>
                                <h4 class="font-bold text-stone-900 text-xs">Pesanan Dikemas &amp; Dikirim</h4>
                                <p class="text-[10px] text-stone-500 mt-0.5">Admin menginput nomor resi untuk pemantauan pengiriman</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Summary Card -->
                <div class="bg-white rounded-3xl border border-[#ECE4D8] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-100">
                        <h3 class="font-bold text-stone-500 text-xs uppercase tracking-wider">
                            Rincian Produk ({{ count($order->items ?? []) }} Item)
                        </h3>
                    </div>

                    <div class="space-y-4 mb-5 max-h-60 overflow-y-auto pr-1">
                        @foreach($order->items ?? [] as $item)
                        <div class="flex gap-3 items-center">
                            <div class="w-12 h-12 rounded-lg bg-stone-100 border border-stone-200 shrink-0 overflow-hidden">
                                <img src="{{ $item->produk && $item->produk->gambar ? (str_starts_with($item->produk->gambar, 'http') ? $item->produk->gambar : asset('storage/' . $item->produk->gambar)) : asset('images/batik-placeholder.jpg') }}" 
                                     alt="{{ $item->produk_name ?? 'Produk' }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow min-w-0">
                                <h4 class="font-bold text-stone-900 text-xs truncate">{{ $item->produk_name ?? 'Produk Batik' }}</h4>
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
    </main>

    <!-- Footer -->
    <footer class="bg-[#FAF7F2] border-t border-[#ECE4D8] py-8 text-stone-600 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-stone-900 uppercase">HAMZAH STYLE OFFICIAL</span>
                <span>&bull;</span>
                <span>E-Commerce Batik &amp; Olahan Kain Sisa</span>
            </div>
            <p>&copy; {{ date('Y') }} Hamzah Style Official. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>
