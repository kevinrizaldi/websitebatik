<x-app-layout>
    <div class="py-8 bg-orange-50/20 min-h-screen text-stone-800 text-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert Notifikasi Sukses --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-xl flex justify-between items-center text-sm">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
                </div>
            @endif

            <!-- Breadcrumb & Top Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wider mb-1">
                        DASHBOARD / PESANAN / <span class="text-stone-700 font-bold">#{{ $order->code }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-extrabold text-stone-900">#{{ $order->code }}</h1>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-amber-800 border border-amber-200">
                            {{ $order->status }}
                        </span>
                    </div>
                    <div class="text-gray-400 text-[11px] mt-1">
                        📅 {{ $order->created_at->format('d F Y, H:i') }} WIB
                    </div>
                </div>
            </div>

            <!-- Grid Layout Utama (2 Kolom) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- KOLOM KIRI (7/12) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Kartu Verifikasi Bukti Transfer -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 text-amber-700 flex items-center justify-center font-bold">💳</div>
                                <div>
                                    <h3 class="font-bold text-stone-900 text-sm">Verifikasi Bukti Transfer Bank</h3>
                                    <p class="text-gray-400 text-[11px]">Mohon periksa mutasi rekening sebelum memproses pesanan ini.</p>
                                </div>
                            </div>
                            <span class="bg-orange-100/80 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded uppercase">
                                TRANSFER BANK
                            </span>
                        </div>

                        <!-- Data Pembayaran Ringkas -->
                        <div class="grid grid-cols-3 gap-2 bg-orange-50/40 rounded-xl p-3 text-[11px]">
                            <div>
                                <div class="text-gray-400">NAMA PEMBELI:</div>
                                <div class="font-bold text-stone-900">{{ $order->customer_name }}</div>
                            </div>
                            <div>
                                <div class="text-gray-400">TOTAL TRANSFER:</div>
                                <div class="font-bold text-amber-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-gray-400">WAKTU PESAN:</div>
                                <div class="font-medium text-stone-800">{{ $order->created_at->format('d Feb Y, H:i') }}</div>
                            </div>
                        </div>

                        <!-- Box File Bukti Transfer -->
                        <div class="border border-gray-100 rounded-xl p-3 flex items-center justify-between bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden border flex items-center justify-center text-gray-400">
                                    📄
                                </div>
                                <div>
                                    <div class="font-bold text-stone-800">Bukti Pembayaran Transfer</div>
                                    <div class="text-gray-400 text-[10px]">Status: {{ $order->status === 'Menunggu Konfirmasi' ? 'Perlu Diverifikasi' : 'Sudah Diverifikasi' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi Verifikasi -->
                        @if ($order->status === 'Menunggu Konfirmasi')
                            <div class="flex gap-3 pt-2">
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Diproses">
                                    <button type="submit" class="w-full bg-stone-900 hover:bg-black text-white font-bold py-2.5 rounded-xl shadow-sm transition text-center flex justify-center items-center gap-2">
                                        <span>✓</span> Verifikasi & Setujui Pembayaran
                                    </button>
                                </form>
                                <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pesanan ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold px-4 py-2.5 rounded-xl transition">
                                        🚫 Tolak
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Kartu Daftar Produk Dipesan -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-stone-900 text-sm">Daftar Produk Dipesan</h3>
                                <span class="bg-orange-100 text-amber-800 font-bold text-[10px] px-2 py-0.5 rounded-full">{{ $order->items->count() }} ITEM</span>
                            </div>
                        </div>

                        <!-- Item List -->
                        <div class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <div class="py-3 flex gap-4 items-center">
                                    <div class="w-14 h-14 bg-amber-100 rounded-xl overflow-hidden flex-shrink-0 border border-amber-200/50">
                                        @if($item->produk && $item->produk->foto)
                                            <img src="{{ asset('storage/' . $item->produk->foto) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-xs font-bold text-amber-800">Batik</div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-stone-900 text-sm">{{ $item->produk_name }}</div>
                                        <div class="text-gray-400 text-[11px] mt-0.5">Jumlah: {{ $item->quantity }}x</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-gray-400 text-[11px]">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                        <div class="font-bold text-stone-900 text-sm mt-0.5">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Rincian Biaya -->
                        <div class="pt-3 border-t border-gray-100 space-y-1.5 text-stone-600 text-[11px]">
                            <div class="flex justify-between">
                                <span>Subtotal Produk</span>
                                <span class="font-semibold text-stone-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-100 text-sm font-extrabold text-stone-900">
                                <span>Total Tagihan Pesanan</span>
                                <span class="text-amber-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- KOLOM KANAN (5/12) -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Kartu Informasi Pelanggan -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-stone-900 text-sm">Informasi Pelanggan</h3>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <div class="w-10 h-10 rounded-full bg-stone-200 font-bold text-stone-700 flex items-center justify-center">
                                {{ strtoupper(substr($order->customer_name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-stone-900 text-sm">{{ $order->customer_name }}</div>
                                <div class="text-gray-400 text-[11px]">Pelanggan Web</div>
                            </div>
                        </div>
                        <div class="space-y-1.5 pt-2 text-stone-600 text-[11px]">
                            <div class="flex items-center gap-2">
                                <span>📞</span> <span>{{ $order->phone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Alamat Pengiriman -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-stone-900 text-sm">Alamat Pengiriman</h3>
                        </div>
                        <div>
                            <div class="font-bold text-stone-900 text-xs">{{ $order->customer_name }}</div>
                            <p class="text-gray-600 leading-relaxed mt-1 text-[11px]">
                                {{ $order->address }}
                            </p>
                            <div class="text-gray-400 mt-1 text-[11px]">Telepon: {{ $order->phone }}</div>
                        </div>
                    </div>

                    <!-- Kartu Pengiriman & Resi -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                        <h3 class="font-bold text-stone-900 text-sm">Pengiriman & Resi</h3>

                        @if (in_array($order->status, ['Dikirim', 'Selesai']) || !empty($order->tracking_number))
                            <!-- KONDISI 1: JIKA STATUS DIKIRIM / SELESAI ATAU SUDAH ADA RESI -->
                            <div class="bg-orange-50/50 border border-orange-100 rounded-xl p-3.5 space-y-2">
                                <div class="text-gray-400 text-[10px] uppercase font-bold tracking-wider">NOMOR RESI PENGIRIMAN</div>
                                <div class="font-mono text-sm font-extrabold text-stone-900 tracking-wide flex items-center justify-between">
                                    <span>{{ $order->tracking_number ?? 'Resi belum diinput' }}</span>
                                    <span class="text-xs bg-emerald-100 text-emerald-800 font-sans font-bold px-2 py-0.5 rounded-full">
                                        {{ $order->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Opsi Edit Resi -->
                            <details class="text-[11px] text-gray-500">
                                <summary class="cursor-pointer hover:text-stone-800 font-medium pt-1">Ubah / Perbarui Nomor Resi</summary>
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="space-y-2 mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" class="w-full px-3 py-1.5 border border-gray-200 rounded-xl text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none" required>
                                    <button type="submit" class="w-full bg-stone-800 hover:bg-black text-white font-bold py-1.5 rounded-xl transition">
                                        Simpan Perubahan
                                    </button>
                                </form>
                            </details>
                        @elseif ($order->status === 'Diproses')
                            <!-- KONDISI 2: JIKA STATUS DIPROSES (FORM INPUT RESI BARU) -->
                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Dikirim">

                                <div>
                                    <label class="block text-stone-700 font-bold text-[11px] mb-1">MASUKKAN NOMOR RESI PENGIRIMAN</label>
                                    <input type="text" name="tracking_number" placeholder="Contoh: JNE123456789" required class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-1 focus:ring-amber-500 focus:outline-none">
                                </div>

                                <button type="submit" class="w-full bg-stone-900 hover:bg-black text-white font-bold py-2.5 rounded-xl transition shadow-sm flex justify-center items-center gap-2">
                                    🚚 Simpan Resi & Kirim Pesanan
                                </button>
                            </form>
                        @else
                            <!-- KONDISI 3: MENUNGGU PEMBAYARAN / DIBATALKAN -->
                            <p class="text-gray-400 text-xs italic">
                                Nomor resi dapat diinputkan setelah pesanan diverifikasi / diproses.
                            </p>
                        @endif
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
