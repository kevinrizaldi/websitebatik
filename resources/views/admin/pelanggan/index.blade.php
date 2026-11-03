<x-app-layout>
    <div class="space-y-8">
        {{-- HEADER BREADCRUMB & TITLE --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                {{-- Breadcrumbs --}}
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-stone-700">DASHBOARD</a>
                    <span>/</span>
                    <span class="text-[#A86544]">PELANGGAN</span>
                    <span>/</span>
                    <span>DIR-CST.2025</span>
                </div>

                {{-- Title & Subtitle --}}
                <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-[#1E1B18] tracking-tight">
                    Manajemen Pelanggan & Patron Adibusana
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-[#5A544E] max-w-2xl leading-relaxed">
                    Pantau data direktori pembeli terdaftar, histori belanja sirkular, tier loyalitas Lestari Patron, dan komunikasi langsung via WhatsApp terenkripsi.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Mempersiapkan unduhan berkas CSV direktori pelanggan...')" class="inline-flex items-center gap-2 rounded-xl border border-[#E8E4DC] bg-white px-3.5 py-2 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    <svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Direktori (CSV)</span>
                </button>

                <button type="button" onclick="alert('Formulir penambahan patron adibusana via kurasi artisanal.')" class="inline-flex items-center gap-2 rounded-xl bg-[#1E1B18] px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-black">
                    <span>+</span>
                    <span>Tambah Patron Baru</span>
                </button>
            </div>
        </div>

        {{-- 4 KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. TOTAL TERDAFTAR --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">TOTAL TERDAFTAR</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ number_format($totalPelanggan, 0, ',', '.') }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Pembeli</span>
                </div>
                <div class="mt-4 flex items-center gap-1 text-[11px] font-semibold text-[#A86544]">
                    <span>↗</span>
                    <span>+18% vs bulan lalu</span>
                </div>
                <div class="mt-2 h-1 w-full rounded-full bg-[#FAF0E4] overflow-hidden">
                    <div class="h-full rounded-full bg-[#A86544]" style="width: 65%"></div>
                </div>
            </div>

            {{-- 2. REPEAT ORDER RATE --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">REPEAT ORDER RATE</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#1E1B18]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $repeatRate }}%
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Retensi</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] text-stone-500 font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#1E1B18]"></span>
                    <span>Patron belanja > 2 kali</span>
                </div>
                <div class="mt-2 h-1 w-full rounded-full bg-[#F3EFEA] overflow-hidden">
                    <div class="h-full rounded-full bg-[#1E1B18]" style="width: 46.8%"></div>
                </div>
            </div>

            {{-- 3. LESTARI ADIBUSANA --}}
            <div class="rounded-2xl border border-[#EFECE6] bg-white p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider">LESTARI ADIBUSANA</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FAF7F2] text-[#A86544]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-[#1E1B18]">
                        {{ $vipCount }}
                    </p>
                    <span class="text-xs text-[#5A544E] font-medium">Patron VIP</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] text-stone-500 font-medium">
                    <strong class="font-bold text-[#1E1B18]">Tier Utama</strong>
                    <span>•</span>
                    <span>Akses Bespoke & Pratinjau</span>
                </div>
                <div class="mt-2 h-1 w-full rounded-full bg-[#FAF0E4] overflow-hidden">
                    <div class="h-full rounded-full bg-[#C59A53]" style="width: 75%"></div>
                </div>
            </div>

            {{-- 4. DAMPAK LINGKUNGAN (DARK CARD) --}}
            <div class="rounded-2xl border border-[#2B2622] bg-[#1E1B18] text-white p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between text-stone-400 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">DAMPAK LINGKUNGAN</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10 text-[#C59A53]">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold text-white">
                        342.6
                    </p>
                    <span class="text-xs text-stone-300 font-medium">kg kain</span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-[11px] text-stone-300 font-medium">
                    <svg class="w-3 h-3 text-[#C59A53]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Limbah perca diselamatkan</span>
                </div>
                <div class="mt-2 h-1 w-full rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full rounded-full bg-[#C59A53]" style="width: 82%"></div>
                </div>
            </div>
        </div>

        {{-- SEARCH, FILTER & QUICK PILLS BAR --}}
        <div class="space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                {{-- Search Box --}}
                <form action="{{ route('admin.pelanggan.index') }}" method="GET" class="flex-1 max-w-md relative">
                    <input type="hidden" name="tier" value="{{ $tier }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ $search }}"
                           placeholder="Cari nama pelanggan, nomor WhatsApp, kota, atau ID Patron..."
                           class="w-full rounded-2xl border border-[#E8E4DC] bg-[#FAF0E4]/30 py-2.5 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                </form>

                {{-- Status & Sorting Dropdowns --}}
                <div class="flex items-center gap-2.5">
                    {{-- Status Dropdown --}}
                    <form action="{{ route('admin.pelanggan.index') }}" method="GET" id="filterForm" class="flex items-center gap-2">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="tier" onchange="document.getElementById('filterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                            <option value="all" {{ $tier === 'all' ? 'selected' : '' }}>Status: Semua Patron</option>
                            <option value="vip" {{ $tier === 'vip' ? 'selected' : '' }}>Status: Lestari Patron VIP</option>
                            <option value="verified" {{ $tier === 'verified' ? 'selected' : '' }}>Status: Pelanggan Terverifikasi</option>
                            <option value="new" {{ $tier === 'new' ? 'selected' : '' }}>Status: Pembeli Baru</option>
                        </select>

                        <select name="sort" onchange="document.getElementById('filterForm').submit()" class="rounded-xl border border-[#E8E4DC] bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-2xs focus:border-[#C59A53] focus:ring-0">
                            <option value="spend_desc" {{ $sort === 'spend_desc' ? 'selected' : '' }}>Urutkan: Total Belanja Tertinggi</option>
                            <option value="spend_asc" {{ $sort === 'spend_asc' ? 'selected' : '' }}>Urutkan: Total Belanja Terendah</option>
                            <option value="orders_desc" {{ $sort === 'orders_desc' ? 'selected' : '' }}>Urutkan: Pesanan Terbanyak</option>
                            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Urutkan: Nama A-Z</option>
                        </select>

                        <a href="{{ route('admin.pelanggan.index') }}" class="p-2 rounded-xl border border-[#E8E4DC] bg-white text-stone-500 hover:bg-[#FAF7F2] transition shadow-2xs" title="Reset filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </a>
                    </form>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mr-1">FILTER CEPAT:</span>
                <a href="{{ route('admin.pelanggan.index', ['tier' => 'all']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $tier === 'all',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $tier !== 'all',
                   ])>
                    Semua ({{ $totalPelanggan }})
                </a>
                <a href="{{ route('admin.pelanggan.index', ['tier' => 'vip']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $tier === 'vip',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $tier !== 'vip',
                   ])>
                    Lestari Patron VIP ({{ $vipCount }})
                </a>
                <a href="{{ route('admin.pelanggan.index', ['tier' => 'verified']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $tier === 'verified',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $tier !== 'verified',
                   ])>
                    Pelanggan Terverifikasi (642)
                </a>
                <a href="{{ route('admin.pelanggan.index', ['tier' => 'new']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $tier === 'new',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $tier !== 'new',
                   ])>
                    Pembeli Baru (454)
                </a>
                <a href="{{ route('admin.pelanggan.index', ['tier' => 'eco']) }}"
                   @class([
                       'px-3.5 py-1.5 rounded-full font-bold transition shadow-2xs',
                       'bg-[#1E1B18] text-white' => $tier === 'eco',
                       'bg-white text-stone-600 border border-[#EFECE6] hover:bg-[#FAF7F2]' => $tier !== 'eco',
                   ])>
                    &gt; 3kg Perca Sirkular (89)
                </a>
            </div>
        </div>

        {{-- TABLE PATRON DIRECTORY --}}
        <div class="rounded-2xl border border-[#EFECE6] bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EFECE6] bg-[#FAF7F2]/60 text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <th class="py-3.5 px-6">ID PATRON</th>
                            <th class="py-3.5 px-6">NAMA & WILAYAH</th>
                            <th class="py-3.5 px-6">KONTAK & SALURAN</th>
                            <th class="py-3.5 px-6">TIER LOYALITAS</th>
                            <th class="py-3.5 px-6 text-center">PESANAN</th>
                            <th class="py-3.5 px-6 text-right">TOTAL AKUMULASI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EFECE6]/70">
                        @forelse ($patrons as $p)
                            <tr class="hover:bg-[#FAF7F2] transition">
                                {{-- ID PATRON --}}
                                <td class="py-4 px-6 font-mono font-bold text-[#1E1B18]">
                                    {{ $p->patron_code }}
                                </td>

                                {{-- NAMA & WILAYAH --}}
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#FAF0E4] text-[#A86544] font-extrabold text-xs">
                                            {{ $p->initials }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-[#1E1B18]">{{ $p->name }}</p>
                                            <p class="text-[11px] text-stone-400 flex items-center gap-1">
                                                <span>📍</span>
                                                <span>{{ $p->city }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- KONTAK & SALURAN --}}
                                <td class="py-4 px-6 font-mono text-[11px]">
                                    <p class="text-stone-500">{{ $p->email }}</p>
                                    <a href="https://wa.me/{{ $p->clean_phone }}" target="_blank" class="inline-flex items-center gap-1 text-[#A86544] hover:underline font-bold mt-0.5">
                                        <span>💬</span>
                                        <span>{{ $p->phone }}</span>
                                    </a>
                                </td>

                                {{-- TIER LOYALITAS --}}
                                <td class="py-4 px-6">
                                    <span class="inline-block rounded-full px-3 py-1 text-[10px] font-bold border {{ $p->tier_badge_class }}">
                                        {{ $p->tier_name }}
                                    </span>
                                    <p class="text-[10px] text-stone-400 mt-1">{{ $p->tier_sub }}</p>
                                </td>

                                {{-- PESANAN --}}
                                <td class="py-4 px-6 text-center">
                                    <p class="text-sm font-extrabold text-[#1E1B18]">{{ $p->orders_count }}</p>
                                    @if ($p->has_pending)
                                        <p class="text-[10px] text-[#A86544] font-bold">1 Menunggu Bayar</p>
                                    @else
                                        <p class="text-[10px] text-stone-400">Semua Selesai</p>
                                    @endif
                                </td>

                                {{-- TOTAL AKUMULASI --}}
                                <td class="py-4 px-6 text-right">
                                    <p class="text-xs font-bold text-[#1E1B18]">
                                        Rp {{ number_format($p->total_spent, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-[#A86544] font-medium flex items-center justify-end gap-1 mt-0.5">
                                        <span>♻</span>
                                        <span>{{ $p->perca_kg }} kg diselamatkan</span>
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-xs text-stone-400">
                                    Tidak ada data patron yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer / Pagination --}}
            <div class="p-4 sm:p-5 border-t border-[#EFECE6] bg-[#FAF7F2]/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <span class="text-stone-500">
                    Menampilkan 1 - {{ min(5, $patrons->count()) }} dari {{ $totalPelanggan }} pelanggan terdaftar
                </span>

                <div class="flex items-center gap-1">
                    <button class="h-8 w-8 rounded-lg border border-[#E8E4DC] bg-white text-stone-400 flex items-center justify-center hover:bg-stone-50">‹</button>
                    <button class="h-8 w-8 rounded-lg bg-[#1E1B18] text-white font-bold flex items-center justify-center">1</button>
                    <button class="h-8 w-8 rounded-lg border border-[#E8E4DC] bg-white text-stone-700 flex items-center justify-center hover:bg-stone-50">2</button>
                    <button class="h-8 w-8 rounded-lg border border-[#E8E4DC] bg-white text-stone-700 flex items-center justify-center hover:bg-stone-50">3</button>
                    <span class="px-1 text-stone-400">...</span>
                    <button class="h-8 w-8 rounded-lg border border-[#E8E4DC] bg-white text-stone-700 flex items-center justify-center hover:bg-stone-50">256</button>
                    <button class="h-8 w-8 rounded-lg border border-[#E8E4DC] bg-white text-stone-700 flex items-center justify-center hover:bg-stone-50">›</button>
                </div>
            </div>
        </div>

        {{-- BOTTOM BANNER: WAWASAN SIRKULAR & APRESIASI --}}
        <div class="rounded-2xl border border-[#E9DFD0] bg-[#FAF0E4]/70 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#1E1B18] text-[#C59A53] shadow-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-[#8C5E28] mb-0.5">
                        <span>WAWASAN SIRKULAR & APRESIASI</span>
                        <span>•</span>
                        <span class="text-emerald-700">Program Aktif</span>
                    </div>
                    <h3 class="text-base font-bold text-[#1E1B18]">Program Apresiasi Patron Lestari</h3>
                    <p class="text-xs text-[#5A544E] mt-1 max-w-2xl leading-relaxed">
                        Berikan kupon konsultasi fitting pola gratis kepada pelanggan yang telah menyelamatkan > 3kg limbah kain sisa untuk menjaga hubungan personal artisanal.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="alert('Detail metrik efisiensi upcycled patron.')" class="rounded-xl border border-[#E8E4DC] bg-white px-4 py-2.5 text-xs font-semibold text-[#1E1B18] shadow-2xs hover:bg-[#FAF7F2] transition">
                    Detail Metrik
                </button>
                <button type="button" onclick="alert('Kupon konsultasi fitting pola berhasil dijadwalkan ke patron setia.')" class="rounded-xl bg-[#1E1B18] px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-black transition">
                    Kirimkan Kupon Massal
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

