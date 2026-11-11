@props(['cartCount' => null])

@php
    $currentCartCount = $cartCount ?? \App\Models\CartItem::forCurrentVisitor()->sum('qty');
@endphp

<!-- ================= NAVBAR ================= -->
<header class="sticky top-0 z-40 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-[#ECE4D8] transition-all"
        x-data="{
            mobileNavOpen: false,
            userDropdownOpen: false,
            navCartCount: {{ (int) $currentCartCount }}
        }"
        x-init="if (typeof cartCount !== 'undefined') { $watch('cartCount', val => navCartCount = val); };
                window.addEventListener('cart-updated', e => { if (e.detail?.count !== undefined) navCartCount = e.detail.count; });">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Brand / Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center group">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="HAMZAH STYLE OFFICIAL" 
                         class="h-8 sm:h-9 w-auto object-contain">
                </a>
            </div>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ url('/') }}"
                   @class([
                       'text-sm font-semibold transition',
                       'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->is('/') && !request()->routeIs('koleksi.*'),
                       'text-stone-600 hover:text-stone-900' => ! (request()->is('/') && !request()->routeIs('koleksi.*')),
                   ])>
                    Home
                </a>
                <a href="{{ url('/#koleksi') }}"
                   @class([
                       'text-sm font-semibold transition',
                       'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->routeIs('koleksi.*') && request('kategori'),
                       'text-stone-600 hover:text-stone-900' => ! (request()->routeIs('koleksi.*') && request('kategori')),
                   ])>
                    Kategori
                </a>
                <a href="{{ route('koleksi.index') }}"
                   @class([
                       'text-sm font-semibold transition',
                       'text-stone-950 border-b-2 border-stone-900 pb-0.5' => request()->routeIs('koleksi.*') && ! request('kategori'),
                       'text-stone-600 hover:text-stone-900' => ! (request()->routeIs('koleksi.*') && ! request('kategori')),
                   ])>
                    Produk
                </a>
                <a href="{{ url('/#tentang-kami') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                    Tentang Kami
                </a>
            </nav>

            <!-- Action Buttons: Cart, Akun -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Cart Button with badge -->
                <a href="{{ route('keranjang.index') }}"
                   class="relative p-2 text-stone-800 hover:text-stone-950 transition"
                   title="Keranjang Belanja">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span class="absolute top-0.5 right-0.5 w-4 h-4 bg-[#B58742] text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                          x-text="navCartCount">
                        {{ $currentCartCount }}
                    </span>
                </a>

                <!-- Vertical separator -->
                <div class="h-4 w-px bg-stone-300 hidden sm:block"></div>

                <!-- Akun Pill Button -->
                <div class="relative">
                    <button @click="userDropdownOpen = !userDropdownOpen"
                            @click.away="userDropdownOpen = false"
                            class="flex items-center gap-2 pl-1.5 pr-3.5 py-1.5 rounded-full border border-stone-300 hover:border-stone-400 bg-white hover:bg-stone-50 transition shadow-2xs">
                        <div class="w-6 h-6 rounded-full bg-[#201A17] text-white flex items-center justify-center font-bold text-xs">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-stone-800">
                            Akun
                        </span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-cloak x-show="userDropdownOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-stone-200 py-2 z-50 text-sm">
                        @auth
                            <div class="px-4 py-2 border-b border-stone-100">
                                <p class="text-xs text-stone-500">Masuk sebagai</p>
                                <p class="font-semibold text-stone-800 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] text-stone-400 truncate">{{ Auth::user()->email }}</p>
                            </div>

                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    <span>Dashboard Admin</span>
                                </a>
                            @endif

                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Data Diri</span>
                            </a>

                            <a href="{{ route('alamat.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Daftar Alamat</span>
                            </a>

                            <a href="{{ route('pesanan.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span>Pesanan Saya</span>
                            </a>

                            <div class="border-t border-stone-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium text-left">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-semibold">
                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                <span>Masuk ke Akun</span>
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center gap-2.5 px-4 py-2 text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium">
                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>Daftar Akun Baru</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Mobile Menu Hamburger -->
                <button @click="mobileNavOpen = !mobileNavOpen"
                        class="md:hidden p-2 text-stone-700 hover:text-stone-950 rounded-lg hover:bg-stone-200/50 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div x-cloak x-show="mobileNavOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden border-t border-[#ECE4D8] bg-[#FAF7F2] px-4 py-4 space-y-2">
        <a href="{{ url('/') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Home</a>
        <a href="{{ url('/#koleksi') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Kategori</a>
        <a href="{{ route('koleksi.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Produk</a>
        <a href="{{ url('/#tentang-kami') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Tentang Kami</a>
        <div class="border-t border-stone-200 my-2 pt-2"></div>
        <a href="{{ route('pesanan.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Pesanan Saya</a>
        <a href="{{ route('keranjang.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Keranjang Belanja</a>
        @auth
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-stone-800 hover:bg-stone-200/40">Data Diri & Alamat</a>
        @else
            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#B58742] hover:bg-stone-200/40">Masuk ke Akun</a>
        @endauth
    </div>
</header>

