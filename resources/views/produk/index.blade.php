<x-app-layout>
    <div x-data="{
        stockModalOpen: false,
        activeProdukId: null,
        activeProdukName: '',
        activeProdukSku: '',
        activeStok: 0,
        activeStockUrl: '',
        stokS: 0,
        stokM: 0,
        stokL: 0,
        stokXL: 0,
        stokXXL: 0
    }" class="space-y-8">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-2xs">
                <span class="flex items-center gap-2"><span>✓</span> {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-base leading-none text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        {{-- 4 TOP METRIC CARDS (PRODUK & INVENTARIS STOK TERPADU) --}}
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
                        {{ $totalProduk > 0 ? $totalProduk : 48 }} SKU
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] font-semibold text-[#8C5E28]">
                    <span>✓</span>
                    <span>{{ $totalKategori ?? 4 }} Kategori Terkurasi</span>
                </div>
            </div>

            {{-- 2. TOTAL UNIT STOK FISIK (INTEGRASI FITUR STOK) --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL UNIT STOK FISIK</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ number_format($totalStokFisik ?? $produks->sum('stok'), 0, ',', '.') }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Pcs Fisik</span>
                </div>
                <div class="mt-4 flex items-center justify-between text-[11px] text-stone-500 font-medium">
                    <span>Gudang Utama Surakarta</span>
                    <span class="text-emerald-700 font-bold">{{ $stokAmanCount ?? 0 }} SKU Stabil</span>
                </div>
            </div>

            {{-- 3. PERINGATAN STOK MENIPIS / RESTOCK --}}
            <a href="{{ route('produk.index', ['stok_menipis' => 1]) }}"
               aria-label="Lihat {{ $lowStockCount }} produk dengan stok menipis"
               class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition block {{ $lowStockOnly ? 'ring-2 ring-[#A86544]' : '' }}">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">PERLU RESTOCK SEGAT</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF0E4] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#A86544]">
                        {{ $lowStockCount > 0 ? $lowStockCount : 0 }} Motif
                    </p>
                    @if ($outOfStockCount > 0)
                        <span class="text-xs text-rose-600 font-bold">({{ $outOfStockCount }} Habis)</span>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between text-[11px] font-semibold text-rose-600">
                    <span>⚠️ Stok &le; 10 unit</span>
                    <span class="text-xs text-stone-400">Filter →</span>
                </div>
            </a>

            <a href="{{ route('produk.index', ['stok_habis' => 1]) }}"
               aria-label="Lihat {{ $outOfStockCount }} produk dengan stok habis"
               class="sr-only">
                Stok habis
            </a>

            {{-- 4. NILAI VALUASI ASET ETALASE --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">NILAI VALUASI ETALASE</span>
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
                <div class="mt-4 flex items-center justify-between text-[11px] text-stone-500 font-medium">
                    <span>Aset Fisik Batik Terdata</span>
                    <span class="text-emerald-700 font-bold">Aktif</span>
                </div>
            </div>
        </div>

        {{-- HEADER TITLE & ACTION BUTTONS --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Eyebrow --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544] mb-1">
                    <span class="h-2 w-2 rounded-full bg-[#A86544]"></span>
                    <span>KATALOG PRODUK &amp; MANAJEMEN INVENTARIS STOK TERPADU</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Produk &amp; Stok
                    <span class="sr-only">Kelola Produk, Stok, &amp; SKU</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Kelola katalog kain batik tulis, cap warisan nusantara, serta ketersediaan stok fisik per ukuran (S, M, L, XL, XXL) dalam satu pintu terpadu.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Inventaris</span>
                </button>

                <a href="{{ route('produk.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <span>+</span>
                    <span>Tambah Produk Baru</span>
                </a>
            </div>
        </div>

        {{-- QUICK FILTER TABS: STATUS STOK --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#EFECE6] pb-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @php
                    $isAll = empty(request('status_stok')) && !request('stok_menipis') && !request('stok_habis');
                    $isAman = request('status_stok') === 'tersedia';
                    $isMenipis = request('stok_menipis') == 1 || request('status_stok') === 'menipis';
                    $isHabis = request('stok_habis') == 1 || request('status_stok') === 'habis';
                @endphp
                <a href="{{ route('produk.index', request()->only(['search', 'kategori', 'sort'])) }}"
                   style="{{ $isAll ? 'background-color: #1E1B18 !important; color: #ffffff !important;' : '' }}"
                   class="px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs {{ $isAll ? 'bg-[#1E1B18] text-white border border-[#1E1B18]' : 'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' }}">
                    Semua Produk ({{ $totalProduk }})
                </a>
                <a href="{{ route('produk.index', array_merge(request()->only(['search', 'kategori', 'sort']), ['status_stok' => 'tersedia'])) }}"
                   style="{{ $isAman ? 'background-color: #1E1B18 !important; color: #ffffff !important;' : '' }}"
                   class="px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs {{ $isAman ? 'bg-[#1E1B18] text-white border border-[#1E1B18]' : 'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' }}">
                    Stok Stabil ({{ $stokAmanCount ?? 0 }})
                </a>
                <a href="{{ route('produk.index', array_merge(request()->only(['search', 'kategori', 'sort']), ['stok_menipis' => 1])) }}"
                   style="{{ $isMenipis ? 'background-color: #A86544 !important; color: #ffffff !important;' : '' }}"
                   class="px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs {{ $isMenipis ? 'bg-[#A86544] text-white border border-[#A86544]' : 'bg-white text-[#A86544] border border-[#E9DFD0] hover:bg-[#FAF0E4]' }}">
                    ⚠️ Stok Menipis ({{ $lowStockCount }})
                </a>
                <a href="{{ route('produk.index', array_merge(request()->only(['search', 'kategori', 'sort']), ['stok_habis' => 1])) }}"
                   style="{{ $isHabis ? 'background-color: #991B1B !important; color: #ffffff !important;' : '' }}"
                   class="px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs {{ $isHabis ? 'bg-[#991B1B] text-white border border-[#991B1B]' : 'bg-white text-rose-700 border border-rose-200 hover:bg-rose-50' }}">
                    🚫 Stok Habis ({{ $outOfStockCount }})
                </a>
            </div>
        </div>

        {{-- FILTER TOOLBAR & DROPDOWNS --}}
        <div class="space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- Search Box --}}
                <form action="{{ route('produk.index') }}" method="GET" class="flex-1 max-w-md relative">
                    @if(request('stok_menipis'))
                        <input type="hidden" name="stok_menipis" value="{{ request('stok_menipis') }}">
                    @endif
                    @if(request('stok_habis'))
                        <input type="hidden" name="stok_habis" value="{{ request('stok_habis') }}">
                    @endif
                    @if(request('kategori'))
                        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                    @endif
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Cari nama produk, SKU, material, ukuran..."
                           class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                </form>

                {{-- Filter Dropdowns --}}
                <form action="{{ route('produk.index') }}" method="GET" id="produkFilterForm" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="search" value="{{ $search }}">
                    @if(request('stok_menipis'))
                        <input type="hidden" name="stok_menipis" value="{{ request('stok_menipis') }}">
                    @endif
                    @if(request('stok_habis'))
                        <input type="hidden" name="stok_habis" value="{{ request('stok_habis') }}">
                    @endif

                    <select name="kategori" onchange="document.getElementById('produkFilterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategorisList as $k)
                            <option value="{{ $k->nama_kategori }}" {{ request('kategori') === $k->nama_kategori ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>

                    <select name="sort" onchange="document.getElementById('produkFilterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Urutkan: Terbaru ⇅</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Urutkan: Terlama</option>
                        <option value="stock_high" {{ request('sort') === 'stock_high' ? 'selected' : '' }}>Stok Terbanyak</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                    </select>

                    @if ($search !== '' || $lowStockOnly || $outOfStockOnly || request('kategori') || request('status_stok') || request('sort'))
                        <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-1 rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs hover:bg-[#FAF7F2] transition" title="Reset filter">
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
                </div>

                <span class="text-stone-400">
                    Menampilkan {{ $produks->firstItem() ?? 1 }} - {{ $produks->lastItem() ?? $produks->count() }} dari {{ $totalProduk }} karya busana
                </span>
            </div>
        </div>

        {{-- TABLE PRODUK & STOK INVENTARIS --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/60 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-4 w-10"></th>
                            <th class="py-3.5 px-4">PRODUK &amp; SKU</th>
                            <th class="py-3.5 px-4">KATEGORI</th>
                            <th class="py-3.5 px-4">HARGA RESMI</th>
                            <th class="py-3.5 px-4">STOK &amp; RINCIAN UKURAN</th>
                            <th class="py-3.5 px-4">STATUS INVENTARIS</th>
                            <th class="py-3.5 px-4">DAMPAK MATERIAL</th>
                            <th class="py-3.5 px-4 text-center">AKSI &amp; KELOLA STOK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($produks as $p)
                            @php
                                $isLow = $p->stok > 0 && $p->stok <= \App\Models\Product::LOW_STOCK_THRESHOLD;
                                $isOut = $p->stok <= 0;
                                $statusBadge = match (true) {
                                    $isOut => 'bg-[#FFEBEE] text-[#C62828] border-[#FFCDD2]',
                                    $isLow => 'bg-[#FAF0E4] text-[#A86544] border-[#E8D9C5]',
                                    default => 'bg-[#FAF7F2] text-stone-700 border-[#EFECE6]',
                                };
                                $statusText = match (true) {
                                    $isOut => 'Habis (0 pcs)',
                                    $isLow => 'Stok Menipis (Restock)',
                                    default => 'Tersedia Stabil',
                                };

                                $ecoBadge = match (true) {
                                    str_contains(strtolower($p->nama), 'upcycled') || str_contains(strtolower($p->kategori ?? ''), 'sisa') => 'Upcycled Fabric',
                                    str_contains(strtolower($p->nama), 'sutera') || str_contains(strtolower($p->nama), 'sutra') => 'Sutera Dobi Exclusive',
                                    str_contains(strtolower($p->nama), 'truntum') => 'Sutra Canting Halus',
                                    default => 'Katun Primissima 100%',
                                };

                                $motifSub = match (true) {
                                    str_contains(strtolower($p->nama), 'parang') => 'Pola Seling Nitik',
                                    str_contains(strtolower($p->nama), 'kawung') => 'Kawung Manik Keraton',
                                    str_contains(strtolower($p->nama), 'tote') => 'Kulit Nabati & Perca',
                                    str_contains(strtolower($p->nama), 'truntum') => 'Canting Tembaga Asli',
                                    default => 'Handmade Artisan',
                                };

                                $stokUkuran = $p->stok_ukuran ?? $p->size_stock ?? [];
                            @endphp
                            <tr class="hover:bg-[#FAF7F2]/60 transition group">
                                {{-- Checkbox --}}
                                <td class="py-4 px-4 align-top">
                                    <input type="checkbox" name="selected_produks[]" value="{{ $p->id }}" class="item-checkbox rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                                </td>

                                {{-- PRODUK & SKU --}}
                                <td class="py-4 px-4 align-top">
                                    <div class="flex items-start gap-3">
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
                                                    HABIS
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
                                <td class="py-4 px-4 align-top">
                                    <span class="inline-block rounded-full bg-[#FAF0E4] px-2.5 py-1 text-[10px] font-bold text-[#8C5E28] border border-[#E9DFD0]">
                                        {{ $p->kategori ?: 'Baju Batik' }}
                                    </span>
                                </td>

                                {{-- HARGA RESMI --}}
                                <td class="py-4 px-4 align-top">
                                    <div class="font-bold text-[#1E1B18]">
                                        Rp {{ number_format($p->harga, 0, ',', '.') }}
                                    </div>
                                    @if (!empty($p->harga_coret) && (float)$p->harga_coret > (float)$p->harga)
                                        <div class="text-[10px] text-stone-400 line-through">
                                            Rp {{ number_format($p->harga_coret, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- STOK & RINCIAN UKURAN --}}
                                <td class="py-4 px-4 align-top">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-extrabold {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-[#A86544]' : 'text-[#1E1B18]') }}">
                                                {{ $p->stok }}
                                            </span>
                                            <span class="text-[11px] text-stone-500 font-medium">pcs fisik</span>
                                        </div>

                                        {{-- Rincian Ukuran jika ada --}}
                                        @if (!empty($stokUkuran) && is_array($stokUkuran))
                                            <div class="flex flex-wrap gap-1 pt-0.5">
                                                @foreach ($stokUkuran as $sizeKey => $sizeQty)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-[#FAF7F2] border border-[#EFECE6] text-[9px] font-bold text-stone-600">
                                                        <span>{{ strtoupper($sizeKey) }}:</span>
                                                        <span class="font-extrabold {{ (int)$sizeQty <= 0 ? 'text-rose-600' : 'text-[#1E1B18]' }}">{{ $sizeQty }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- STATUS INVENTARIS --}}
                                <td class="py-4 px-4 align-top">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold border {{ $statusBadge }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        <span>{{ $statusText }}</span>
                                    </span>
                                </td>

                                {{-- DAMPAK MATERIAL --}}
                                <td class="py-4 px-4 align-top">
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-[#FAF7F2] border border-[#EFECE6] px-2.5 py-1 text-[10px] font-semibold text-stone-600">
                                        <span>🍃</span>
                                        <span>{{ $ecoBadge }}</span>
                                    </span>
                                </td>

                                {{-- AKSI & KELOLA STOK --}}
                                <td class="py-4 px-4 text-center align-top">
                                    <div class="flex flex-col items-center gap-1.5">
                                        {{-- Tombol Cepat Update Stok (Integrasi Fitur Stok) --}}
                                        <button type="button"
                                                @click="stockModalOpen = true; activeProdukId = {{ $p->id }}; activeProdukName = '{{ addslashes($p->nama) }}'; activeProdukSku = '{{ addslashes($p->sku ?? '') }}'; activeStok = {{ (int)$p->stok }}; activeStockUrl = '{{ route('produk.update-stock', $p) }}'; stokS = {{ (int)($stokUkuran['S'] ?? $stokUkuran['s'] ?? 0) }}; stokM = {{ (int)($stokUkuran['M'] ?? $stokUkuran['m'] ?? 0) }}; stokL = {{ (int)($stokUkuran['L'] ?? $stokUkuran['l'] ?? 0) }}; stokXL = {{ (int)($stokUkuran['XL'] ?? $stokUkuran['xl'] ?? 0) }}; stokXXL = {{ (int)($stokUkuran['XXL'] ?? $stokUkuran['xxl'] ?? 0) }};"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#FAF0E4] hover:bg-[#F3E6D5] text-[#8C5E28] font-bold text-[10px] border border-[#E9DFD0] transition shadow-2xs">
                                            <span>⚡</span>
                                            <span>Update Stok</span>
                                        </button>

                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('produk.show', $p) }}" class="p-1 text-stone-400 hover:text-stone-700 transition" title="Lihat Detail Produk">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('produk.edit', $p) }}" class="p-1 text-stone-400 hover:text-stone-700 transition" title="Ubah Produk">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('produk.destroy', $p) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $p->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-stone-400 hover:text-rose-600 transition" title="Hapus Produk">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
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
                    Menampilkan {{ $produks->firstItem() ?? 1 }} - {{ $produks->lastItem() ?? $produks->count() }} dari {{ $totalProduk }} karya busana terdaftar
                </span>

                <div class="flex items-center gap-2">
                    {{ $produks->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL CEPAT UPDATE STOK (FITUR STOK TERPADU) --}}
        <div x-show="stockModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             style="display: none;"
             x-cloak>
            <div @click.away="stockModalOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-[#EFECE6] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#A86544]">
                            <span>INVENTARIS GUDANG</span>
                            <span>•</span>
                            <span x-text="activeProdukSku"></span>
                        </div>
                        <h3 class="text-base font-bold text-[#1E1B18] mt-0.5" x-text="'Update Stok: ' + activeProdukName"></h3>
                    </div>
                    <button type="button" @click="stockModalOpen = false" class="text-stone-400 hover:text-stone-700 text-lg">&times;</button>
                </div>

                <form :action="activeStockUrl" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Total Stok Utama --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                            TOTAL JUMLAH STOK (PCS) <span class="text-rose-600">*</span>
                        </label>
                        <input type="number" name="stok" x-model.number="activeStok" min="0" required
                               class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2.5 text-sm font-bold text-[#1E1B18] focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                        <p class="text-[10px] text-stone-400 mt-1">Total ketersediaan unit fisik produk di gudang.</p>
                    </div>

                    {{-- Breakdown Ukuran (Opsional) --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-2">
                            ALOKASI UKURAN BUSANA (OPSIONAL)
                        </label>
                        <div class="grid grid-cols-5 gap-2">
                            <div>
                                <span class="block text-center text-[10px] font-bold text-stone-500 mb-1">S</span>
                                <input type="number" name="stok_ukuran[S]" x-model.number="stokS" min="0" class="w-full rounded-lg border border-[#E8E4DC] bg-[#FAF7F2]/40 p-1.5 text-center text-xs font-bold text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                            </div>
                            <div>
                                <span class="block text-center text-[10px] font-bold text-stone-500 mb-1">M</span>
                                <input type="number" name="stok_ukuran[M]" x-model.number="stokM" min="0" class="w-full rounded-lg border border-[#E8E4DC] bg-[#FAF7F2]/40 p-1.5 text-center text-xs font-bold text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                            </div>
                            <div>
                                <span class="block text-center text-[10px] font-bold text-stone-500 mb-1">L</span>
                                <input type="number" name="stok_ukuran[L]" x-model.number="stokL" min="0" class="w-full rounded-lg border border-[#E8E4DC] bg-[#FAF7F2]/40 p-1.5 text-center text-xs font-bold text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                            </div>
                            <div>
                                <span class="block text-center text-[10px] font-bold text-stone-500 mb-1">XL</span>
                                <input type="number" name="stok_ukuran[XL]" x-model.number="stokXL" min="0" class="w-full rounded-lg border border-[#E8E4DC] bg-[#FAF7F2]/40 p-1.5 text-center text-xs font-bold text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                            </div>
                            <div>
                                <span class="block text-center text-[10px] font-bold text-stone-500 mb-1">XXL</span>
                                <input type="number" name="stok_ukuran[XXL]" x-model.number="stokXXL" min="0" class="w-full rounded-lg border border-[#E8E4DC] bg-[#FAF7F2]/40 p-1.5 text-center text-xs font-bold text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Penyesuaian --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                            CATATAN RESTOCK / MUTASI (OPSIONAL)
                        </label>
                        <input type="text" name="catatan" placeholder="Contoh: Kiriman batch baru dari penjahit atelier..." class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF7F2]/40 px-3.5 py-2 text-xs text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] shadow-2xs">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#EFECE6]">
                        <button type="button" @click="stockModalOpen = false" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-stone-600 hover:bg-[#FAF7F2] transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-black transition">
                            <span>Simpan Perubahan Stok</span>
                        </button>
                    </div>
                </form>
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
                    <h3 class="text-base font-bold text-[#1E1B18]">Integrasi Katalog &amp; Akurasi Stok Sirkular</h3>
                    <p class="text-xs text-[#5A544E] mt-1 max-w-2xl leading-relaxed">
                        Data ketersediaan unit fisik dan status stok tersinkronisasi otomatis dengan etalase web. Setiap penambahan atau pengurangan stok tercatat akurat untuk menjaga standar pelayanan Hamzah Style.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="window.print()" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    Cetak Laporan
                </button>
                <a href="{{ route('produk.create') }}" class="rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-black transition">
                    + Tambah Produk
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleSelectAll(master) {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = master.checked);
        }
    </script>
</x-app-layout>
