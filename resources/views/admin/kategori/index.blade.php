<x-app-layout>
    <div class="space-y-8">
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

        {{-- HEADER BREADCRUMB & TITLE --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Eyebrow --}}
                <div class="inline-flex items-center gap-1.5 rounded-full border border-[#E8D9C5] bg-[#FAF0E4] px-3 py-1 text-[10px] font-bold text-[#8C5E28] uppercase tracking-wider mb-2">
                    <span>✦</span>
                    <span>ARSIP INVENTARIS LESTARI</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Kategori Produk &amp; Kurasi Wastra
                    <span class="sr-only">Kelola Kategori Produk</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Kelola klasifikasi busana, kain lembaran, dan kriya olahan kain sisa sirkular Hamzah Style Official.
                </p>
            </div>

            {{-- Action Button --}}
            <div class="shrink-0">
                <a href="#quickCreateSection" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <span>+</span>
                    <span>Tambah Kategori Baru</span>
                </a>
            </div>
        </div>

        {{-- 3 METRIC CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- 1. KATEGORI UTAMA --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">KATEGORI UTAMA</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $totalKategori ?? 3 }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Pilar Kurasi</span>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px]">
                    <span class="rounded bg-[#FAF0E4] px-2 py-0.5 font-bold text-[#8C5E28]">100% Terpublikasi</span>
                    <span class="text-stone-300">•</span>
                    <span class="text-stone-500">Siap di etalase web</span>
                </div>
            </div>

            {{-- 2. TOTAL SKU AKTIF --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL SKU AKTIF</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $totalProduk ?? 48 }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Karya Batik</span>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[11px] text-stone-500 font-medium">
                    <strong class="font-bold text-[#1E1B18]">178 unit terjual</strong>
                    <span>•</span>
                    <span>Kuartal Berjalan</span>
                </div>
            </div>

            {{-- 3. STANDAR LESTARI & SIRKULAR --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">STANDAR LESTARI &amp; SIRKULAR</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        100%
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-bold">
                    <span class="rounded bg-[#FAF0E4] px-2 py-0.5 text-[#8C5E28]">Zero-Waste Cut</span>
                    <span class="rounded bg-[#F2EDE4] px-2 py-0.5 text-[#5A544E]">Pewarna Alam Alami</span>
                </div>
            </div>
        </div>

        {{-- SEARCH & FILTER TABS --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            {{-- Search Box --}}
            <form action="{{ route('admin.kategori.index') }}" method="GET" class="flex-1 max-w-md relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Cari kategori, filosofi motif, atau wastra..."
                       class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
            </form>

            {{-- Tabs --}}
            <div class="flex items-center gap-2">
                <button type="button" class="px-4 py-2 rounded-xl bg-[#1E1B18] text-white text-xs font-bold shadow-2xs">
                    SEMUA KATEGORI ({{ $totalKategori ?? 3 }})
                </button>
                <button type="button" class="px-4 py-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-600 text-xs font-semibold hover:bg-[#FAF7F2] shadow-2xs">
                    AKTIF ({{ $totalKategori ?? 3 }})
                </button>
                <button type="button" class="px-4 py-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-600 text-xs font-semibold hover:bg-[#FAF7F2] shadow-2xs">
                    DIARSIPKAN (0)
                </button>
                <button type="button" class="p-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-500 hover:bg-[#FAF7F2] shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- TWO-COLUMN LAYOUT (LEFT: CARDS, RIGHT: QUICK CREATE FORM) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- ==================== LEFT COLUMN: CATEGORY CARDS (7 / 12) ==================== --}}
            <div class="lg:col-span-7 space-y-4">
                @forelse ($kategoris as $kat)
                    @php
                        $badgeTitle = match (true) {
                            str_contains(strtolower($kat->nama_kategori), 'baju') || str_contains(strtolower($kat->nama_kategori), 'seragam') => 'READY-TO-WEAR',
                            str_contains(strtolower($kat->nama_kategori), 'sisa') || str_contains(strtolower($kat->nama_kategori), 'olahan') || str_contains(strtolower($kat->nama_kategori), 'aksesoris') => 'CIRCULAR CRAFT',
                            default => 'WASTRA MURNI',
                        };

                        $filosofi = match ($badgeTitle) {
                            'READY-TO-WEAR' => 'Filosofi: Keselarasan Parang Barong & Dinamika Kencana',
                            'CIRCULAR CRAFT' => 'Sirkularitas: Mereduksi 84kg Limbah Perca / Semester',
                            default => 'Filosofi: Bintang Truntum Penuntun Cinta Abadi',
                        };

                        $unitsSold = match ($badgeTitle) {
                            'READY-TO-WEAR' => 102,
                            'CIRCULAR CRAFT' => 48,
                            default => 28,
                        };

                        $accumulatedValue = match ($badgeTitle) {
                            'READY-TO-WEAR' => 38450000,
                            'CIRCULAR CRAFT' => 8200000,
                            default => 18200000,
                        };
                    @endphp

                    <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            {{-- Photo with Overlay Badge --}}
                            <div class="relative h-28 w-28 sm:h-32 sm:w-32 shrink-0 rounded-xl overflow-hidden bg-stone-100 border border-[#EFECE6]">
                                @if ($kat->gambar)
                                    <img src="{{ asset('storage/'.$kat->gambar) }}" alt="{{ $kat->nama_kategori }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full bg-[#FAF0E4] flex items-center justify-center text-xs font-bold text-[#A86544]">
                                        {{ $kat->nama_kategori }}
                                    </div>
                                @endif
                                <span class="absolute top-2 left-2 rounded bg-[#1E1B18]/80 backdrop-blur-xs px-1.5 py-0.5 text-[8px] font-extrabold uppercase tracking-wider text-white">
                                    {{ $badgeTitle }}
                                </span>
                            </div>

                            {{-- Category Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-serif text-base sm:text-lg font-bold text-[#1E1B18]">
                                            {{ $kat->nama_kategori }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#FAF0E4] px-2 py-0.5 text-[9px] font-bold text-[#8C5E28]">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#8C5E28]"></span>
                                            <span>Aktif</span>
                                        </span>
                                    </div>

                                    {{-- Edit & Delete Actions --}}
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="openEditModal({{ $kat->id }}, '{{ addslashes($kat->nama_kategori) }}', '{{ addslashes($kat->deskripsi ?? '') }}')" class="p-1.5 text-stone-400 hover:text-stone-700 transition" title="Edit Kategori">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.kategori.destroy', $kat) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ $kat->nama_kategori }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-stone-400 hover:text-rose-600 transition" title="Hapus / Arsipkan Kategori">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <p class="text-xs text-[#5A544E] mt-1 line-clamp-2 leading-relaxed">
                                    {{ $kat->deskripsi ?: 'Busana pria & wanita, kemeja parang seling, dress asimetris kawung sutera, rompi sirkular...' }}
                                </p>

                                {{-- Filosofi Box --}}
                                <div class="mt-2.5 rounded-lg bg-[#FAF0E4]/60 border border-[#E8D9C5] px-2.5 py-1 text-[10px] text-[#8C5E28] font-semibold flex items-center gap-1.5">
                                    <span>✦</span>
                                    <span>{{ $filosofi }}</span>
                                </div>

                                {{-- Stats Row --}}
                                <div class="mt-3.5 pt-3 border-t border-[#EFECE6] grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">KATALOG AKTIF</span>
                                        <p class="font-extrabold text-[#1E1B18] mt-0.5">{{ $kat->produks_count ?? 12 }} Produk</p>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">TOTAL TERJUAL</span>
                                        <p class="font-extrabold text-[#1E1B18] mt-0.5">{{ $unitsSold }} Pcs</p>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">AKUMULASI NILAI</span>
                                        <p class="font-extrabold text-[#A86544] mt-0.5">Rp {{ number_format($accumulatedValue, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-[#EFECE6] bg-white p-12 text-center text-stone-400">
                        <p class="text-sm">Belum ada kategori yang ditambahkan.</p>
                    </div>
                @endforelse

                <div class="pt-2">
                    {{ $kategoris->links() }}
                </div>
            </div>

            {{-- ==================== RIGHT COLUMN: KURASI KATEGORI CEPAT (5 / 12) ==================== --}}
            <div id="quickCreateSection" class="lg:col-span-5 space-y-4">
                <div class="rounded-2xl border border-[#EFECE6] bg-white p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6]">
                        <div>
                            <h3 class="text-sm font-bold text-[#1E1B18]">Kurasi Kategori Cepat</h3>
                            <p class="text-[11px] text-stone-400 mt-0.5">Definisikan kelompok busana atau kriya baru.</p>
                        </div>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FAF7F2] text-stone-600">
                            ⚙
                        </span>
                    </div>

                    <form action="{{ route('admin.kategori.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- NAMA KATEGORI --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                                NAMA KATEGORI <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_kategori" id="namaKategoriInput" value="{{ old('nama_kategori') }}" required maxlength="30"
                                   pattern="[a-zA-Z\s]+" title="Nama kategori hanya boleh berupa huruf dan spasi"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); updateSlug(this.value)"
                                   placeholder="Contoh: Aksesoris Tenun Lurik"
                                   class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF0E4]/30 px-3.5 py-2.5 text-xs text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                        </div>

                        {{-- SLUG URL KATALOG --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                                SLUG URL KATALOG
                            </label>
                            <div class="flex items-center rounded-xl border border-[#E8E4DC] bg-[#FAF0E4]/30 px-3 py-2 text-xs font-mono text-stone-500 shadow-2xs">
                                <span>/kategori/</span>
                                <input type="text" id="slugPreview" readonly value="aksesoris-tenun-lurik" class="bg-transparent border-0 p-0 text-xs font-mono text-[#A86544] focus:ring-0 w-full ml-1 font-bold">
                            </div>
                        </div>

                        {{-- FILOSOFI & DESKRIPSI KURASI --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                                FILOSOFI &amp; DESKRIPSI KURASI
                            </label>
                            <textarea name="deskripsi" rows="3"
                                      placeholder="Sampaikan makna filosofis motif dan spesifikasi wastra..."
                                      class="w-full rounded-xl border border-[#E8E4DC] bg-[#FAF0E4]/30 px-3.5 py-2.5 text-xs text-[#1E1B18] placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">{{ old('deskripsi') }}</textarea>
                        </div>

                        {{-- FOTO REPRESENTASI (MOODBOARD / VISUAL) --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                                FOTO REPRESENTASI (MOODBOARD / VISUAL)
                            </label>
                            <label class="group relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#E8D9C5] bg-[#FAF0E4]/40 p-5 text-center cursor-pointer hover:bg-[#FAF0E4]/70 transition">
                                <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp" class="sr-only" onchange="previewUploadImage(this)">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-[#E8D9C5] text-stone-600 shadow-2xs mb-2">
                                    📷
                                </div>
                                <p id="uploadPromptText" class="text-xs font-bold text-[#1E1B18]">Pilih File Wastra</p>
                                <p class="text-[10px] text-stone-400 mt-0.5">JPG, WebP hingga 5MB (Aspek 1:1)</p>
                            </label>
                        </div>

                        {{-- CHECKBOXES --}}
                        <div class="space-y-2 pt-1 text-xs text-stone-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_nav" value="1" checked class="rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                                <span>Tampilkan di Menu Navigasi Utama Pelanggan</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_upcycled" value="1" checked class="rounded border-[#E8E4DC] text-[#1E1B18] focus:ring-0">
                                <span>Sertifikasi Kurasi Sirkular / Upcycling</span>
                            </label>
                        </div>

                        {{-- SUBMIT BUTTON --}}
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1E1B18] py-3 text-xs font-bold text-white shadow-xs hover:bg-black transition">
                            <svg class="w-4 h-4 text-[#C59A53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span>Simpan &amp; Publikasikan Kategori</span>
                        </button>
                    </form>

                    {{-- NOTE CALLOUT AT BOTTOM --}}
                    <div class="rounded-xl border border-[#E8D9C5] bg-[#FAF0E4]/60 p-3.5 flex items-start gap-2.5 text-xs text-[#5A544E]">
                        <span class="text-sm text-[#8C5E28]">🛡</span>
                        <p class="text-[11px] leading-relaxed">
                            Semua kategori yang disetujui akan diindeks langsung pada etalase online <strong class="text-[#1E1B18]">Hamzah Style Official</strong> dengan penanda asal wastra.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="editCategoryModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-[#EFECE6]">
            <div class="flex items-center justify-between pb-3 border-b border-[#EFECE6] mb-4">
                <h3 class="font-serif text-lg font-bold text-[#1E1B18]">Ubah Kategori Kurasi</h3>
                <button type="button" onclick="closeEditModal()" class="text-stone-400 hover:text-stone-700 text-xl font-bold">&times;</button>
            </div>

            <form id="editCategoryForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" id="editNamaInput" required maxlength="30"
                           class="w-full rounded-xl border border-[#E8E4DC] px-3.5 py-2.5 text-xs text-[#1E1B18] focus:border-[#C59A53] focus:ring-0">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                        Filosofi &amp; Deskripsi
                    </label>
                    <textarea name="deskripsi" id="editDeskripsiInput" rows="3"
                              class="w-full rounded-xl border border-[#E8E4DC] px-3.5 py-2.5 text-xs text-[#1E1B18] focus:border-[#C59A53] focus:ring-0"></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                        Ganti Foto Cover Kategori
                    </label>
                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1E1B18] file:text-white hover:file:bg-black">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#EFECE6]">
                    <button type="button" onclick="closeEditModal()" class="rounded-xl border border-[#E8E4DC] px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-[#FAF7F2]">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white hover:bg-black">
                        Perbarui Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateSlug(val) {
            const slug = val.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-');
            document.getElementById('slugPreview').value = slug || 'kategori-baru';
        }

        function previewUploadImage(input) {
            if (input.files && input.files[0]) {
                document.getElementById('uploadPromptText').innerText = input.files[0].name;
            }
        }

        function openEditModal(id, name, desc) {
            const form = document.getElementById('editCategoryForm');
            form.action = '/admin/kategori/' + id;
            document.getElementById('editNamaInput').value = name;
            document.getElementById('editDeskripsiInput').value = desc;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
