<x-app-layout>
    <div class="py-8 bg-orange-50/30 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert Notifikasi Sukses --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-xl flex justify-between items-center text-sm">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
                </div>
            @endif

            <!-- Tab Navigation Status -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-4 mb-4 border-b border-orange-100">

                {{-- Tab Semua --}}
                <a href="{{ route('admin.orders.index', request()->only('search')) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2 transition {{ !request('status') ? 'bg-stone-800 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    <span>Semua</span>
                    <span class="{{ !request('status') ? 'bg-stone-700 text-white' : 'bg-gray-100 text-gray-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ $counts['all'] }}
                    </span>
                </a>

                {{-- Tab Menunggu Pembayaran --}}
                <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), ['status' => 'Menunggu Konfirmasi'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2 transition {{ request('status') === 'Menunggu Konfirmasi' ? 'bg-stone-800 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    <span>Menunggu Pembayaran</span>
                    <span class="{{ request('status') === 'Menunggu Konfirmasi' ? 'bg-stone-700 text-white' : 'bg-gray-100 text-gray-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ $counts['menunggu'] }}
                    </span>
                </a>

                {{-- Tab Diproses --}}
                <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), ['status' => 'Diproses'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2 transition {{ request('status') === 'Diproses' ? 'bg-stone-800 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    <span>Diproses</span>
                    <span class="{{ request('status') === 'Diproses' ? 'bg-stone-700 text-white' : 'bg-gray-100 text-gray-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ $counts['diproses'] }}
                    </span>
                </a>

                {{-- Tab Dikirim --}}
                <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), ['status' => 'Dikirim'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2 transition {{ request('status') === 'Dikirim' ? 'bg-stone-800 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    <span>Dikirim</span>
                    <span class="{{ request('status') === 'Dikirim' ? 'bg-stone-700 text-white' : 'bg-gray-100 text-gray-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ $counts['dikirim'] }}
                    </span>
                </a>

                {{-- Tab Selesai --}}
                <a href="{{ route('admin.orders.index', array_merge(request()->only('search'), ['status' => 'Selesai'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2 transition {{ request('status') === 'Selesai' ? 'bg-stone-800 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                    <span>Selesai</span>
                    <span class="{{ request('status') === 'Selesai' ? 'bg-stone-700 text-white' : 'bg-gray-100 text-gray-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ $counts['selesai'] }}
                    </span>
                </a>

            </div>

            <!-- Form Pencarian -->
            <form action="{{ route('admin.orders.index') }}" method="GET" class="mb-6">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Order ID, Nama Pembeli, No. WhatsApp... (Tekan Enter)" class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-amber-500 shadow-sm">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-stone-800 hover:bg-stone-900 text-white text-sm font-medium rounded-xl shadow-sm transition">
                        Cari
                    </button>

                    @if(request('search'))
                        <a href="{{ route('admin.orders.index', request()->only('status')) }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-stone-700 text-sm font-medium rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Tabel Utama Pesanan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-orange-50/60 text-gray-500 font-semibold uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4">Order ID & Waktu</th>
                            <th class="py-3 px-4">Data Pelanggan</th>
                            <th class="py-3 px-4">Ringkasan Item Pesanan</th>
                            <th class="py-3 px-4">Total Tagihan</th>
                            <th class="py-3 px-4">Status Pembayaran</th>
                            <th class="py-3 px-4">Aksi & Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition">

                            <!-- ORDER ID & WAKTU -->
                            <td class="py-4 px-4 align-top">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-amber-700 text-sm hover:underline flex items-center gap-1">
                                    #{{ $order->code }}
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                                <div class="text-gray-400 mt-1 leading-relaxed">
                                    {{ $order->created_at->format('d M Y') }}<br>
                                    {{ $order->created_at->format('H:i') }} WIB
                                </div>
                            </td>

                            <!-- DATA PELANGGAN -->
                            <td class="py-4 px-4 align-top">
                                <div class="font-bold text-stone-900 text-sm">{{ $order->customer_name }}</div>
                                <div class="text-gray-500 mt-1 flex items-center gap-1">
                                    <span>📞 {{ $order->phone }}</span>
                                </div>
                                <div class="text-gray-400 mt-0.5 truncate max-w-[140px]" title="{{ $order->address }}">
                                    {{ $order->address }}
                                </div>
                            </td>

                            <!-- RINGKASAN ITEM -->
                            <td class="py-4 px-4 align-top">
                                @foreach ($order->items as $item)
                                    <div class="flex gap-3 mb-2 last:mb-0">
                                        <div class="w-10 h-10 bg-amber-100 rounded-lg overflow-hidden flex-shrink-0">
                                            @if($item->produk && $item->produk->foto)
                                                <img src="{{ asset('storage/' . $item->produk->foto) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-[10px] text-amber-800 font-bold">Batik</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-stone-800">{{ $item->produk_name }}</div>
                                            <div class="text-gray-400 text-[11px]">Qty: {{ $item->quantity }}x</div>
                                        </div>
                                    </div>
                                @endforeach
                            </td>

                            <!-- TOTAL TAGIHAN -->
                            <td class="py-4 px-4 align-top">
                                <div class="font-bold text-stone-900 text-sm">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                <div class="text-gray-500 text-[11px] mt-0.5">{{ $order->payment_method ?? 'Transfer Bank' }}</div>
                            </td>

                            <!-- STATUS PEMBAYARAN -->
                            <td class="py-4 px-4 align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Lunas
                                </span>
                            </td>

                            <!-- AKSI & STATUS PESANAN -->
                            <td class="py-4 px-4 align-top">
                                <div class="space-y-2">

                                    <!-- Tombol Lihat Detail Pesanan -->
                                    <a href="{{ route('admin.orders.show', $order) }}" class="w-full bg-stone-900 hover:bg-black text-white text-[11px] font-semibold py-1.5 px-3 rounded-lg shadow-sm transition text-center flex items-center justify-center gap-1.5">
                                        👁️ Lihat Detail
                                    </a>

                                    <!-- Form Ubah Status Quick Update -->
                                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex items-center gap-1 pt-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="text-[11px] py-1 px-2 border border-gray-200 rounded-lg bg-gray-50 focus:ring-1 focus:ring-amber-500 focus:bg-white w-full">
                                            @foreach (['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'] as $st)
                                                <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-semibold px-2 py-1 rounded-lg shadow-sm transition">
                                            Simpan
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">Tidak ada data pesanan yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
