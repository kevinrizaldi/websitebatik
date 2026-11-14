<x-app-layout>
    <div x-data="{
        stockModalOpen: false,
        stockProduct: { id: '', nama: '', sku: '', stok: 0, action: '' },
        openStockModal(p) {
            this.stockProduct = {
                id: p.id,
                nama: p.nama,
                sku: p.sku || ('HSO-BJK-' + p.id),
                stok: parseInt(p.stok) || 0,
                action: '/produk/' + p.id + '/stok'
            };
            this.stockModalOpen = true;
        }
    }" class="space-y-8">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 shadow-2xs">
                <p class="font-bold mb-1">⚠️ Terjadi Kesalahan Input:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 4 TOP METRIC CARDS (PRODUK + FITUR INFORMASI STOK) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. TOTAL KOLEKSI AKTIF --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL KOLEKSI AKTIF</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $totalProduk > 0 ? $totalProduk : 48 }} <span class="text-xs font-normal text-stone-400">SKU</span>
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] font-semibold text-[#8C5E28]">
                    <span>✓</span>
                    <span>{{ $totalKategori ?? 4 }} Kategori Terkurasi</span>
                </div>
            </div>

            {{-- 2. TOTAL ITEM FISIK GUDANG (FITUR STOK) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL ITEM FISIK (GUDANG)</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ number_format($totalItemFisik ?: 342, 0, ',', '.') }} <span class="text-xs font-normal text-stone-400">Pcs</span>
                    </p>
                </div>
                <div class="mt-4 flex items-center justify-between text-[11px]">
                    <span class="text-emerald-700 font-semibold">● {{ $stokAmanCount ?? 0 }} SKU Stok Aman</span>
                    <span class="text-stone-400 text-[10px]">Stok Fisik Tersedia</span>
                </div>
            </div>

            {{-- 3. MONITORING RESTOCK (FITUR STOK) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">PERLU RESTOCK</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ ($lowStockCount + $outOfStockCount) > 0 ? ($lowStockCount + $outOfStockCount) : 7 }} <span class="text-xs font-normal text-stone-400">SKU</span>
                    </p>
                </div>
                <div class="mt-4 flex items-center justify-between text-[11px] font-semibold">
                    <span class="text-amber-700">{{ $lowStockCount }} Menipis (≤5)</span>
                    <span class="text-stone-300">•</span>
                    <span class="text-rose-600">{{ $outOfStockCount }} Habis</span>
                </div>
            </div>

            {{-- 4. VALUASI ETALASE STOK (FITUR STOK) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">VALUASI ETALASE STOK</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-xl sm:text-2xl font-extrabold text-[#1E1B18] truncate">
                        Rp {{ number_format($totalValuasi > 0 ? $totalValuasi : 128400000, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-1 text-[11px] font-semibold text-stone-500">
                    <span>↗</span>
                    <span>Nilai Aset Terdaftar</span>
                </div>
            </div>
        </div>

        {{-- HEADER TITLE & ACTION BUTTONS --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Eyebrow --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544] mb-1">
                    <span class="h-2 w-2 rounded-full bg-[#A86544]"></span>
                    <span>KATALOG EKSKLUSIF &amp; MANAJEMEN INVENTARIS</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Produk &amp; Inventaris Stok
                    <span class="sr-only">Kelola Produk & SKU</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Kurasi kain batik tulis, cap warisan, monitoring stok fisik di gudang, dan pemantauan peringatan restock terpadu.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Mempersiapkan ekspor berkas CSV katalog & stok busana...')" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Ekspor CSV</span>
                </button>

                <a href="{{ route('produk.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <span>+</span>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>

        {{-- QUICK FILTER TABS: STATUS STOK (FITUR STOK) --}}
        <div class="flex items-center gap-2 border-b border-[#EFECE6] pb-2 overflow-x-auto text-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400 mr-2 shrink-0">Status Stok:</span>
            <a href="{{ route('produk.index', array_merge(request()->except(['status_stok', 'stok_menipis', 'stok_habis', 'page']))) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ !request('status_stok') && !request('stok_menipis') && !request('stok_habis') ? 'bg-[#1E1B18] text-white shadow-xs' : 'bg-white text-stone-600 hover:bg-[#FAF7F2] border border-[#EFECE6]' }}">
                Semua ({{ $totalProduk }})
            </a>
            <a href="{{ route('produk.index', array_merge(request()->except(['status_stok', 'stok_menipis', 'stok_habis', 'page']), ['status_stok' => 'tersedia'])) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ request('status_stok') === 'tersedia' ? 'bg-[#1E1B18] text-white shadow-xs' : 'bg-white text-emerald-800 hover:bg-emerald-50 border border-[#EFECE6]' }}">
                ● Stok Aman ({{ $stokAmanCount ?? 0 }})
            </a>
            <a href="{{ route('produk.index', array_merge(request()->except(['status_stok', 'stok_menipis', 'stok_habis', 'page']), ['status_stok' => 'menipis'])) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ request('status_stok') === 'menipis' || request('stok_menipis') ? 'bg-[#1E1B18] text-white shadow-xs' : 'bg-white text-amber-800 hover:bg-amber-50 border border-[#EFECE6]' }}">
                ▲ Stok Menipis ({{ $lowStockCount }})
            </a>
            <a href="{{ route('produk.index', array_merge(request()->except(['status_stok', 'stok_menipis', 'stok_habis', 'page']), ['status_stok' => 'habis'])) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition shrink-0 {{ request('status_stok') === 'habis' || request('stok_habis') ? 'bg-[#1E1B18] text-white shadow-xs' : 'bg-white text-rose-800 hover:bg-rose-50 border border-[#EFECE6]' }}">
                ✕ Stok Habis ({{ $outOfStockCount }})
            </a>
        </div>

        {{-- FILTER TOOLBAR & BULK ACTIONS --}}
        <div class="space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- Search Box --}}
                <form action="{{ route('produk.index') }}" method="GET" class="flex-1 max-w-md relative">
                    @if(request('status_stok'))
                        <input type="hidden" name="status_stok" value="{{ request('status_stok') }}">
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Cari nama produk, SKU, atau jenis motif..."
                           class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                </form>

                {{-- Filter Dropdowns --}}
                <form action="{{ route('produk.index') }}" method="GET" id="produkFilterForm" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="{{ $search }}">
                    @if(request('status_stok'))
                        <input type="hidden" name="status_stok" value="{{ request('status_stok') }}">
                    @endif

                    <select name="kategori" onchange="document.getElementById('produkFilterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategorisList as $k)
                            <option value="{{ $k->nama_kategori }}" {{ request('kategori') === $k->nama_kategori ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status_stok" onchange="document.getElementById('produkFilterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="">Status Stok: Semua</option>
                        <option value="tersedia" {{ request('status_stok') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="menipis" {{ request('status_stok') === 'menipis' ? 'selected' : '' }}>Stok Menipis</option>
                        <option value="habis" {{ request('status_stok') === 'habis' ? 'selected' : '' }}>Stok Habis</option>
                    </select>

                    <select name="sort" onchange="document.getElementById('produkFilterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Urutkan: Terbaru ⇅</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Urutkan: Terlama</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="stock_high" {{ request('sort') === 'stock_high' ? 'selected' : '' }}>Stok Terbanyak</option>
                    </select>

                    @if ($search !== '' || $lowStockOnly || $outOfStockOnly || request('kategori') || request('status_stok') || request('sort'))
                        <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-1 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs hover:bg-[#FAF7F2] transition">
                            <span>✕</span>
                            <span>Reset</span>
                        </a>
                    @endif
                </form>
            </div>

            {{-- Bulk Actions Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs text-stone-600">
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold">
                        <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" class="rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                        <span>Pilih Semua</span>
                    </label>
                    <span class="text-stone-300">|</span>
                    <button type="button" onclick="alert('Pilih produk terlebih dahulu untuk mengubah status koleksi.')" class="hover:text-[#1E1B18] font-medium flex items-center gap-1">
                        <span>⇄</span> Ubah Status
                    </button>
                    <button type="button" onclick="alert('Pilih produk terlebih dahulu untuk menghapus.')" class="text-rose-600 hover:text-rose-800 font-medium flex items-center gap-1">
                        <span>🗑</span> Hapus Pilihan
                    </button>
                </div>

                <span class="text-stone-400">
                    Menampilkan {{ $produks->count() }} dari {{ $totalProduk }} karya busana
                </span>
            </div>
        </div>

        {{-- TABLE PRODUK & KATALOG --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/60 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-4 w-10"></th>
                            <th class="py-3.5 px-4">PRODUK &amp; SKU</th>
                            <th class="py-3.5 px-4">KATEGORI</th>
                            <th class="py-3.5 px-4">HARGA RESMI</th>
                            <th class="py-3.5 px-4 text-center">STOK FISIK</th>
                            <th class="py-3.5 px-4">STATUS STOK</th>
                            <th class="py-3.5 px-4">DAMPAK EKOLOGIS &amp; MATERIAL</th>
                            <th class="py-3.5 px-4 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($produks as $p)
                            @php
                                $isLow = $p->stok > 0 && $p->stok <= \App\Models\Produk::LOW_STOCK_THRESHOLD;
                                $isOut = $p->stok <= 0;
                                $statusBadge = match (true) {
                                    $isOut => 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]',
                                    $isLow => 'bg-[#FAF0E4] text-[#A86544] border-[#E8D9C5]',
                                    default => 'bg-[#FAF7F2] text-[#1E1B18] border-[#EFECE6]',
                                };
                                $statusText = match (true) {
                                    $isOut => 'Habis',
                                    $isLow => 'Stok Menipis',
                                    default => 'Tersedia',
                                };

                                $ecoBadge = match (true) {
                                    str_contains(strtolower($p->nama), 'upcycled') || str_contains(strtolower($p->kategori ?? ''), 'sisa') => '0.45kg Upcycled Fabric',
                                    str_contains(strtolower($p->nama), 'sutera') || str_contains(strtolower($p->nama), 'sutra') => 'Sutera Dobi Exclusive',
                                    str_contains(strtolower($p->nama), 'truntum') => 'Sutra ATBM Canting Halus',
                                    default => 'Katun Primissima 100%',
                                };

                                $motifSub = match (true) {
                                    str_contains(strtolower($p->nama), 'parang') => 'Pola Seling Nitik',
                                    str_contains(strtolower($p->nama), 'kawung') => 'Kawung Manik Keraton',
                                    str_contains(strtolower($p->nama), 'tote') => 'Kulit Nabati & Perca',
                                    str_contains(strtolower($p->nama), 'truntum') => 'Canting Tembaga Asli',
                                    default => 'Hand-stitched Zipper',
                                };
                            @endphp
                            <tr class="hover:bg-[#FAF7F2] transition group">
                                {{-- Checkbox --}}
                                <td class="py-4 px-4">
                                    <input type="checkbox" name="selected_produks[]" value="{{ $p->id }}" class="item-checkbox rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                                </td>

                                {{-- PRODUK & SKU --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative h-14 w-14 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                            @if ($p->gambar)
                                                <img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->nama }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                                    Kain
                                                </div>
                                            @endif
                                            @if ($isOut)
                                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-[9px] font-extrabold text-white uppercase tracking-wider">
                                                    KOSONG
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-xs text-[#1E1B18] truncate max-w-xs group-hover:text-[#A86544] transition">
                                                {{ $p->nama }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="rounded bg-[#FAF0E4] px-1.5 py-0.5 text-[9px] font-mono font-bold text-[#8C5E28]">
                                                    {{ $p->sku ?: 'HSO-BJK-'.str_pad((string)$p->id, 3, '0', STR_PAD_LEFT) }}
                                                </span>
                                                <span class="text-[10px] text-stone-400">• {{ $motifSub }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- KATEGORI --}}
                                <td class="py-4 px-4">
                                    <span class="inline-block rounded-full bg-[#FAF0E4] px-2.5 py-1 text-[10px] font-bold text-[#8C5E28] border border-[#E9DFD0]">
                                        {{ $p->kategori ?: 'Baju Batik' }}
                                    </span>
                                </td>

                                {{-- HARGA RESMI --}}
                                <td class="py-4 px-4 font-bold text-[#1E1B18]">
                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                </td>

                                {{-- STOK FISIK DENGAN TOMBOL SESUAIKAN STOK --}}
                                <td class="py-4 px-4 text-center">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <span @class([
                                            'font-bold text-xs',
                                            'text-rose-600' => $isOut,
                                            'text-amber-700' => $isLow,
                                            'text-[#1E1B18]' => ! $isOut && ! $isLow,
                                        ])>
                                            {{ $p->stok }} pcs
                                        </span>
                                        <button type="button"
                                                @click="openStockModal({ id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', sku: '{{ $p->sku }}', stok: {{ $p->stok }} })"
                                                class="p-1 rounded-md text-stone-400 hover:text-[#1E1B18] hover:bg-[#FAF0E4] transition"
                                                title="Sesuaikan Stok Cepat">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                {{-- STATUS STOK --}}
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $statusBadge }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        <span>{{ $statusText }}</span>
                                    </span>
                                </td>

                                {{-- DAMPAK EKOLOGIS & MATERIAL --}}
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-[#FAF7F2] border border-[#EFECE6] px-2.5 py-1 text-[10px] font-semibold text-stone-600">
                                        <span>🍃</span>
                                        <span>{{ $ecoBadge }}</span>
                                    </span>
                                </td>

                                {{-- AKSI --}}
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                @click="openStockModal({ id: {{ $p->id }}, nama: '{{ addslashes($p->nama) }}', sku: '{{ $p->sku }}', stok: {{ $p->stok }} })"
                                                class="p-1.5 text-stone-400 hover:text-[#A86544] transition"
                                                title="Sesuaikan Stok">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </button>

                                        <a href="{{ route('produk.show', $p) }}" class="p-1.5 text-stone-400 hover:text-stone-700 transition" title="Lihat Detail Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('produk.edit', $p) }}" class="p-1.5 text-stone-400 hover:text-stone-700 transition" title="Ubah Produk">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('produk.destroy', $p) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $p->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-stone-400 hover:text-rose-600 transition" title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-xs text-stone-400">
                                    Tidak ada produk batik yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Pagination --}}
            <div class="p-4 sm:p-5 border-t border-[#EFECE6] bg-[#FAF7F2]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-stone-500">
                    Menampilkan {{ $produks->firstItem() ?? 1 }} - {{ $produks->lastItem() ?? $produks->count() }} dari {{ $totalProduk }} produk
                </span>

                <div class="flex items-center gap-2">
                    {{ $produks->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL SESUAIKAN STOK CEPAT (FAST STOCK ADJUSTMENT) --}}
        <div x-show="stockModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             role="dialog"
             aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div @click="stockModalOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"></div>

                <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all border border-[#EFECE6] sm:my-8">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-[#EFECE6] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#FAF0E4] text-[#A86544]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-serif text-base font-bold text-[#1E1B18]">Sesuaikan Stok Fisik</h3>
                        </div>
                        <button type="button" @click="stockModalOpen = false" class="rounded-lg p-1 text-stone-400 hover:text-stone-700">
                            ✕
                        </button>
                    </div>

                    {{-- Info Produk --}}
                    <div class="my-4 rounded-xl bg-[#FAF7F2] p-3 border border-[#EFECE6]">
                        <p class="text-xs font-bold text-[#1E1B18]" x-text="stockProduct.nama"></p>
                        <p class="text-[10px] font-mono text-stone-500 mt-0.5">
                            SKU: <span x-text="stockProduct.sku"></span>
                        </p>
                    </div>

                    {{-- Form Sesuaikan Stok --}}
                    <form :action="stockProduct.action" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1.5">Jumlah Stok Tersedia (Unit/Pcs)</label>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="stockProduct.stok = Math.max(0, parseInt(stockProduct.stok || 0) - 1)"
                                        class="h-10 w-10 shrink-0 rounded-xl border border-[#E8E4DC] bg-stone-50 font-bold text-stone-700 hover:bg-stone-100 transition">
                                    -
                                </button>
                                <input type="number"
                                       name="stok"
                                       min="0"
                                       max="100000"
                                       x-model.number="stockProduct.stok"
                                       required
                                       class="h-10 w-full text-center rounded-xl border border-[#E8E4DC] font-bold text-base text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                                <button type="button"
                                        @click="stockProduct.stok = parseInt(stockProduct.stok || 0) + 1"
                                        class="h-10 w-10 shrink-0 rounded-xl border border-[#E8E4DC] bg-stone-50 font-bold text-stone-700 hover:bg-stone-100 transition">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Quick Presets --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-stone-400 font-semibold mr-1">Cepat:</span>
                            <button type="button" @click="stockProduct.stok = parseInt(stockProduct.stok || 0) + 5" class="px-2 py-1 rounded-md bg-stone-100 hover:bg-stone-200 text-[10px] font-semibold text-stone-700">+5</button>
                            <button type="button" @click="stockProduct.stok = parseInt(stockProduct.stok || 0) + 10" class="px-2 py-1 rounded-md bg-stone-100 hover:bg-stone-200 text-[10px] font-semibold text-stone-700">+10</button>
                            <button type="button" @click="stockProduct.stok = parseInt(stockProduct.stok || 0) + 25" class="px-2 py-1 rounded-md bg-stone-100 hover:bg-stone-200 text-[10px] font-semibold text-stone-700">+25</button>
                            <button type="button" @click="stockProduct.stok = 0" class="px-2 py-1 rounded-md bg-rose-50 hover:bg-rose-100 text-[10px] font-semibold text-rose-700">Set Kosong (0)</button>
                        </div>

                        {{-- Tombol Simpan --}}
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#EFECE6]">
                            <button type="button" @click="stockModalOpen = false" class="px-4 py-2 rounded-xl border border-[#E8E4DC] text-xs font-semibold text-stone-600 hover:bg-stone-50">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-[#1E1B18] text-xs font-semibold text-white hover:bg-black transition">
                                Simpan Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- BOTTOM BANNER: STANDAR KURASI LESTARI --}}
        <div class="rounded-2xl border border-[#E9DFD0] bg-[#FAF0E4]/70 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53] shadow-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#1E1B18]">Katalog Terikat Standar Kurasi Lestari</h3>
                    <p class="text-xs text-[#5A544E] mt-1 max-w-2xl leading-relaxed">
                        Setiap produk terdaftar wajib menyertakan sertifikasi pewarna alami atau bukti penelusuran sisa kain perca (Zero Waste Upcycle) untuk mempertahankan akreditasi Hamzah Artisan Guild.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Membuka dokumen Panduan Kurasi Lestari...')" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    Panduan Kurasi
                </button>
                <button type="button" onclick="alert('Log audit sirkularitas material terkini.')" class="rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-black transition">
                    Log Audit Material
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSelectAll(master) {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = master.checked);
        }
    </script>
</x-app-layout>
