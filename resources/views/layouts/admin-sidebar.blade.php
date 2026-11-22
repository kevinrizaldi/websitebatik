@php
    $isDashboard = request()->routeIs('dashboard');
    $isProduk = request()->routeIs('produk.*') && !request()->has('stok_menipis');
    $isKategori = request()->routeIs('admin.kategori.*');
    $isPesanan = request()->routeIs('admin.orders.*') || request()->routeIs('admin.pembayaran.*') || request()->routeIs('admin.pengiriman.*');
    $isPelanggan = request()->routeIs('admin.pelanggan.*');
    $isUlasan = request()->routeIs('admin.ulasans.*');
    $isLaporan = request()->routeIs('admin.laporan.*');

    $navStyle = function(bool $active) {
        return [
            'link' => $active
                ? 'bg-[#1E1B18] !text-white hover:!text-white focus:!text-white active:!text-white shadow-sm'
                : 'text-[#5A544E] hover:bg-[#FAF7F2] hover:text-[#1E1B18] focus:bg-[#FAF7F2] focus:text-[#1E1B18] active:bg-[#1E1B18] active:!text-white',
            'icon' => $active
                ? '!text-white !stroke-white'
                : 'text-[#8C827A] group-hover:text-[#1E1B18] group-active:!text-white',
            'text' => $active
                ? '!text-white font-bold'
                : 'text-[#5A544E] group-hover:text-[#1E1B18] group-active:!text-white font-semibold',
            'style' => $active
                ? 'color: #ffffff !important; background-color: #1E1B18 !important;'
                : '',
            'textStyle' => $active
                ? 'color: #ffffff !important;'
                : '',
        ];
    };
@endphp

<div class="flex flex-col flex-1 overflow-y-auto">
    {{-- LOGO & BRANDING --}}
    <div class="border-b border-[#EFECE6]/80 p-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex flex-col gap-1">
                <img src="{{ asset('images/logo.png') }}" alt="HAMZAH STYLE OFFICIAL" class="h-7 w-auto object-contain object-left">
                <span class="inline-block w-fit rounded bg-[#F7EFE6] px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#A86544]">
                    ADMIN PORTAL
                </span>
                <span class="sr-only">Panel Toko</span>
            </a>
            @if(!empty($isMobile))
                <button type="button" @click="mobileNavOpen = false" class="lg:hidden p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-[#FAF7F2] transition" aria-label="Tutup Navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        {{-- PORTAL MODE BADGE --}}
        <div class="mt-4 flex items-center justify-between rounded-xl bg-[#FAF7F2] px-3 py-1.5 border border-[#EFECE6]/80 text-[11px]">
            <span class="font-semibold text-stone-500 uppercase tracking-wider text-[10px]">PORTAL MODE</span>
            <span class="font-medium text-[#A86544] bg-[#F7EFE6] px-2 py-0.5 rounded-md text-[10px]">Kurasi Batik</span>
        </div>
    </div>

    {{-- MENU ITEMS --}}
    <div class="px-3 py-4 flex-1">
        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-stone-400">
            MANAJEMEN TOKO
        </p>
        <nav class="space-y-1">
            {{-- 1. Dashboard --}}
            @php $st = $navStyle($isDashboard); @endphp
            <a href="{{ route('dashboard') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="2"/>
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Dashboard</span>
            </a>

            {{-- 2. Produk --}}
            @php $st = $navStyle($isProduk); @endphp
            <a href="{{ route('produk.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Produk</span>
            </a>

            {{-- 3. Kategori --}}
            @php $st = $navStyle($isKategori); @endphp
            <a href="{{ route('admin.kategori.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Kategori</span>
            </a>

            {{-- 4. Pesanan --}}
            @php $st = $navStyle($isPesanan); @endphp
            <a href="{{ route('admin.orders.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Pesanan</span>
                <span class="sr-only">Kelola Pesanan</span>
            </a>

            {{-- 5. Pelanggan --}}
            @php $st = $navStyle($isPelanggan); @endphp
            <a href="{{ route('admin.pelanggan.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Pelanggan</span>
            </a>

            {{-- 6. Ulasan --}}
            @php $st = $navStyle($isUlasan); @endphp
            <a href="{{ route('admin.ulasans.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Ulasan</span>
            </a>

            {{-- 7. Laporan --}}
            @php $st = $navStyle($isLaporan); @endphp
            <a href="{{ route('admin.laporan.index') }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition focus:outline-none {{ $st['link'] }}"
               style="{{ $st['style'] }}">
                <svg class="w-4 h-4 shrink-0 transition-colors {{ $st['icon'] }}" style="{{ $st['textStyle'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="transition-colors {{ $st['text'] }}" style="{{ $st['textStyle'] }}">Laporan</span>
            </a>
        </nav>
    </div>
</div>

{{-- USER PROFILE / LOGOUT BOTTOM CARD --}}
<div class="border-t border-[#EFECE6] p-4 bg-white">
    <div class="flex items-center justify-between rounded-xl p-2 hover:bg-[#FAF7F2] transition">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 min-w-0 flex-1 group">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E1B18] text-[#C59A53] text-xs font-bold group-hover:scale-105 transition-transform">
                {{ strtoupper(substr(Auth::user()->name ?? 'AK', 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-bold text-[#1E1B18] group-hover:text-[#A86544] transition-colors">{{ Auth::user()->name ?? 'Admin Kurator' }}</p>
                <p class="text-[10px] text-stone-400">Super Admin</p>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar Portal" class="p-2 text-stone-400 hover:text-stone-700 transition" aria-label="Keluar Portal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
</div>

