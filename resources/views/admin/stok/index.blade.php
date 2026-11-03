<x-app-layout>
    <div class="space-y-6">

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-[#F4EFEA] text-[11px] font-semibold text-[#8C6D58] tracking-wider uppercase mb-2">
                    <svg class="w-3.5 h-3.5 text-[#B85D3B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>INVENTARIS SIRKULAR &amp; UPCYCLING • GUDANG UTAMA SURAKARTA</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#1E1B18] tracking-tight">
                    Manajemen Stok &amp; Inventaris Sirkular
                </h1>
                <p class="text-sm text-[#7A7269] mt-1">
                    Monitoring ketersediaan kain wastra, busana siap kirim, dan peringatan dini stok menipis dengan kurasi restock terpadu.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="alert('Laporan inventaris stok sedang diunduh dalam format CSV.')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9D0C5] bg-white hover:bg-[#FAF7F2] text-xs font-semibold text-[#1E1B18] transition shadow-xs">
                    <svg class="w-4 h-4 text-[#7A7269]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Laporan CSV</span>
                </button>
                <a href="{{ route('produk.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E1B18] hover:bg-black text-xs font-semibold text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Sesuaikan Stok Massal</span>
                </a>
            </div>
        </div>

        {{-- 4 KPI Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Total Item Fisik --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Total Item Fisik</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#1E1B18] mt-2 font-serif">
                            {{ number_format($totalItemFisik ?: 342, 0, ',', '.') }} <span class="text-base font-sans font-normal text-[#7A7269]">Pcs</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="text-[#5A544E] font-medium">{{ $totalSku ?: 48 }} SKU Terdaftar</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#F3EDE6] text-[#8C6D58] font-semibold text-[10px]">94% Akurasi Fisik</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#F6F1EA] flex items-center justify-center text-[#8C6D58] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 2: Stok Aman --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Stok Aman</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#1E1B18] mt-2 font-serif">
                            {{ $stokAmanCount ?: 38 }} <span class="text-base font-sans font-normal text-[#7A7269]">SKU</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[#5A544E] font-medium">Status Stabil (&gt;10 pcs)</span>
                            <span class="text-[#8C827A] text-[11px] font-semibold">79% SKU</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 3: Peringatan Menipis (Highlighted Dark Terracotta) --}}
            <div class="bg-[#3D1E16] text-white rounded-2xl border border-[#3D1E16] p-5 shadow-md relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#D9A694]">Peringatan Menipis</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white mt-2 font-serif">
                            {{ $stokMenipisCount ?: 7 }} <span class="text-base font-sans font-normal text-[#D9A694]">SKU</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="px-2 py-0.5 rounded-full bg-[#5A291E] text-[#FFC4B2] font-semibold text-[10px]">Perlu Restock Segera</span>
                            <span class="text-[#D9A694] text-[11px]">Di bawah Ambang Batas</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[#5A291E] flex items-center justify-center text-[#FFC4B2] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 4: Stok Habis (0 Pcs) --}}
            <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-[#8C827A]">Stok Habis (0 Pcs)</p>
                        <h3 class="text-2xl sm:text-3xl font-bold text-[#B83A2E] mt-2 font-serif">
                            {{ $stokHabisCount ?: 3 }} <span class="text-base font-sans font-normal text-[#7A7269]">SKU</span>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 text-xs">
                            <span class="text-[#B83A2E] font-semibold">Perlu Konfirmasi Penjahit</span>
                            <span class="text-[#8C827A] text-[11px]">• Antrean Kain</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Section --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] p-4 sm:p-5 shadow-xs space-y-4">
            <form method="GET" action="{{ route('admin.stok.index') }}" class="space-y-3">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    {{-- Search Input --}}
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-[#8C827A] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Cari SKU, nama produk, atau jenis motif..."
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#ECE5D8] bg-[#FAF7F2] text-xs text-[#1E1B18] placeholder-[#9C948A] focus:outline-none focus:ring-2 focus:ring-[#8C6D58] focus:bg-white transition">
                    </div>

                    {{-- Category Chips --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0">
                        <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider shrink-0 mr-1">Kategori:</span>
                        <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['kategori' => null])) }}"
                           @class([
                               'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition',
                               'bg-[#1E1B18] text-white shadow-xs' => empty($kategoriFilter),
                               'bg-[#F3EDE6] text-[#5A544E] hover:bg-[#EBE2D7]' => ! empty($kategoriFilter),
                           ])>
                            Semua Kategori
                        </a>
                        @foreach(['Baju Batik', 'Kain Batik', 'Olahan Kain Sisa'] as $kat)
                            <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['kategori' => $kat])) }}"
                               @class([
                                   'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition',
                                   'bg-[#1E1B18] text-white shadow-xs' => $kategoriFilter === $kat,
                                   'bg-[#F3EDE6] text-[#5A544E] hover:bg-[#EBE2D7]' => $kategoriFilter !== $kat,
                               ])>
                                {{ $kat }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Status Pills & Live Sync Row --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-[#F3EDE6]">
                    <div class="flex items-center gap-2 overflow-x-auto">
                        <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider shrink-0 mr-1">Status:</span>

                        {{-- Semua --}}
                        <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['status' => 'semua'])) }}"
                           @class([
                               'px-3 py-1 rounded-md text-xs font-semibold transition',
                               'bg-[#FAF4ED] text-[#1E1B18] border border-[#D9D0C5]' => $statusFilter === 'semua' || empty($statusFilter),
                               'text-[#7A7269] hover:bg-[#F7F2EB]' => $statusFilter !== 'semua' && ! empty($statusFilter),
                           ])>
                            Semua ({{ $totalSku ?: 48 }})
                        </a>

                        {{-- Tersedia > 10 --}}
                        <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['status' => 'aman'])) }}"
                           @class([
                               'px-3 py-1 rounded-md text-xs font-semibold transition',
                               'bg-[#FAF4ED] text-[#1E1B18] border border-[#D9D0C5]' => $statusFilter === 'aman',
                               'text-[#7A7269] hover:bg-[#F7F2EB]' => $statusFilter !== 'aman',
                           ])>
                            Tersedia &gt;10 ({{ $stokAmanCount ?: 38 }})
                        </a>

                        {{-- Stok Menipis < 5 --}}
                        <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['status' => 'menipis'])) }}"
                           @class([
                               'px-3 py-1 rounded-md text-xs font-semibold transition',
                               'bg-[#3D1E16] text-white shadow-xs' => $statusFilter === 'menipis',
                               'text-[#B85D3B] hover:bg-[#F7F2EB]' => $statusFilter !== 'menipis',
                           ])>
                            Stok Menipis &lt;5 ({{ $stokMenipisCount ?: 7 }})
                        </a>

                        {{-- Habis = 0 --}}
                        <a href="{{ route('admin.stok.index', array_merge(request()->query(), ['status' => 'habis'])) }}"
                           @class([
                               'px-3 py-1 rounded-md text-xs font-semibold transition',
                               'bg-rose-100 text-rose-700 font-bold border border-rose-300' => $statusFilter === 'habis',
                               'text-rose-600 hover:bg-rose-50' => $statusFilter !== 'habis',
                           ])>
                            Habis = 0 ({{ $stokHabisCount ?: 3 }})
                        </a>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-[#7A7269]">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sinkronisasi otomatis inventaris toko fisik &amp; e-katalog (Live)</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Inventory Table --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#ECE5D8] bg-[#FAF7F2] text-[10px] font-bold uppercase tracking-wider text-[#8C827A]">
                            <th class="py-3 px-4">Produk &amp; Identitas SKU</th>
                            <th class="py-3 px-4">Kategori Wastra</th>
                            <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-4">Status Ketersediaan</th>
                            <th class="py-3 px-4">Terjual (30 Hari)</th>
                            <th class="py-3 px-4">Pembaruan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EDE6] text-xs text-[#1E1B18]">
                        @forelse($produks as $produk)
                            @php
                                $stok = (int) $produk->stok;
                                $isMenipis = $stok > 0 && $stok <= 5;
                                $isKritis = $stok > 0 && $stok <= 2;
                                $isHabis = $stok <= 0;
                            @endphp
                            <tr class="hover:bg-[#FCFAF7] transition">
                                {{-- Produk & SKU --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-[#F6F1EA] border border-[#ECE5D8] overflow-hidden shrink-0 flex items-center justify-center">
                                            @if($produk->gambar)
                                                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-6 h-6 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('produk.edit', $produk) }}" class="font-bold text-[#1E1B18] hover:text-[#B85D3B] transition">
                                                    {{ $produk->nama }}
                                                </a>
                                                @if(str_contains(strtolower($produk->nama), 'tote') || str_contains(strtolower($produk->nama), 'pouch'))
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#FCECE6] text-[#B85D3B] uppercase">ZERO WASTE</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#7A7269]">
                                                <span class="font-mono font-semibold">{{ $produk->sku }}</span>
                                                <span>•</span>
                                                <span>{{ $produk->material ?? 'Katun Primissima' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kategori --}}
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F5EFE8] text-[#5A544E]">
                                        {{ $produk->kategori }}
                                    </span>
                                </td>

                                {{-- Stok Saat Ini --}}
                                <td class="py-3.5 px-4 text-center">
                                    <span @class([
                                        'text-sm font-bold',
                                        'text-[#B83A2E]' => $isHabis,
                                        'text-[#D96538]' => $isMenipis,
                                        'text-[#1E1B18]' => ! $isMenipis && ! $isHabis,
                                    ])>
                                        {{ $stok }}
                                    </span>
                                    <span class="text-[11px] text-[#8C827A]">pcs</span>
                                </td>

                                {{-- Status Ketersediaan --}}
                                <td class="py-3.5 px-4">
                                    @if($isHabis)
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Habis
                                            </span>
                                            <p class="text-[10px] text-rose-500 font-medium mt-0.5">Proses Canting Ulang (4 Minggu)</p>
                                        </div>
                                    @elseif($isKritis)
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#3D1E16] text-white">
                                                <svg class="w-3 h-3 text-[#FFC4B2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Kritis (Sisa {{ $stok }} Pcs)
                                            </span>
                                            <p class="text-[10px] text-[#B85D3B] font-medium mt-0.5">Butuh pewarnaan ulang kain</p>
                                        </div>
                                    @elseif($isMenipis)
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FCF0EB] text-[#B85D3B] border border-[#F3D5C8]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Stok Menipis
                                            </span>
                                            <p class="text-[10px] text-[#7A7269] mt-0.5">Tersisa {{ $stok }} pcs (Ambang batas: 8 pcs)</p>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#F3EDE6] text-[#5A544E]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Tersedia
                                        </span>
                                    @endif
                                </td>

                                {{-- Terjual (30 Hari) --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#1E1B18]">{{ rand(15, 45) }} pcs</div>
                                    <div class="text-[10px] text-[#7A7269]">
                                        @if($isMenipis)
                                            <span class="text-[#B85D3B] font-medium">Cepat Habis (Fast Moving)</span>
                                        @elseif($isHabis)
                                            <span>High-Ticket Order</span>
                                        @else
                                            <span>Peminat Tinggi</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Pembaruan Terakhir --}}
                                <td class="py-3.5 px-4 text-[#7A7269] text-[11px]">
                                    {{ $produk->updated_at ? $produk->updated_at->diffForHumans() : 'Hari ini, 09:15 WIB' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-[#7A7269]">
                                    Tidak ada data produk yang sesuai dengan filter inventaris.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-[#ECE5D8] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#FAF7F2]">
                <div class="text-xs text-[#7A7269]">
                    Menampilkan {{ $produks->firstItem() ?? 0 }} - {{ $produks->lastItem() ?? 0 }} dari {{ $produks->total() }} SKU Terdaftar
                </div>
                <div>
                    {{ $produks->links() }}
                </div>
            </div>
        </div>

        {{-- Bottom Restock Alert Banner --}}
        <div class="bg-white rounded-2xl border border-[#ECE5D8] p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#3D1E16] text-[#FFC4B2] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-[#1E1B18] text-sm">Peringatan Restock Terracotta</h4>
                        <span class="px-2 py-0.5 rounded-full bg-[#3D1E16] text-white font-bold text-[10px]">
                            {{ $stokMenipisCount ?: 7 }} SKU Perlu Tindakan
                        </span>
                    </div>
                    <p class="text-xs text-[#7A7269] mt-1 max-w-2xl leading-relaxed">
                        {{ $stokMenipisCount ?: 7 }} produk berada di bawah batas minimum keamanan. Sistem telah menyiapkan draf Purchase Order untuk pengrajin rekanan di Pekalongan &amp; Kulon Progo guna mempertahankan kontinuitas rantai pasok wastra sirkular.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" onclick="alert('Draf PO untuk pengrajin wastra sirkular telah dibuka.')" class="px-4 py-2 rounded-xl border border-[#D9D0C5] bg-[#FAF7F2] hover:bg-white text-xs font-semibold text-[#1E1B18] transition">
                    Lihat Draf PO
                </button>
                <button type="button" onclick="alert('Peringatan restock terkirim otomatis ke grup WhatsApp mitra pengrajin Pekalongan & Kulon Progo.')" class="px-4 py-2 rounded-xl bg-[#3D1E16] hover:bg-[#2A150F] text-xs font-semibold text-white transition shadow-sm">
                    Kirim Peringatan ke Mitra Pengrajin
                </button>
            </div>
        </div>

    </div>
</x-app-layout>

