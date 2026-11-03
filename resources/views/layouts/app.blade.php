<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hamzah Style') }}</title>

        <!-- Fonts: Playfair Display (Serif) & Plus Jakarta Sans (Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[#1E1B18]">
        @if (Auth::check() && Auth::user()->isAdmin())
            {{-- HAMZAH STYLE ADMIN PORTAL SHELL --}}
            <div class="min-h-screen bg-[#FAF7F2] lg:flex" x-data="{ mobileNavOpen: false }">
                {{-- MOBILE DRAWER BACKDROP & SIDEBAR --}}
                <div x-show="mobileNavOpen"
                     x-transition:enter="transition-opacity ease-linear duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex lg:hidden"
                     style="display: none;"
                     x-cloak>
                    <div @click="mobileNavOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>
                    <aside class="relative flex w-72 max-w-[85vw] flex-col justify-between bg-white shadow-2xl z-10" aria-label="Navigasi admin mobile">
                        @include('layouts.admin-sidebar', ['isMobile' => true])
                    </aside>
                </div>

                {{-- DESKTOP SIDEBAR NAVIGASI --}}
                <aside class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col justify-between border-r border-[#EFECE6] bg-white lg:flex z-30" aria-label="Navigasi admin">
                    @include('layouts.admin-sidebar', ['isMobile' => false])
                </aside>

                {{-- MAIN CONTENT AREA --}}
                <div class="flex-1 min-w-0 flex flex-col bg-[#FAF7F2]">
                    {{-- TOP HEADER BAR --}}
                    <header class="sticky top-0 z-20 bg-[#FAF7F2]/95 backdrop-blur-xs border-b border-[#EFECE6] px-4 sm:px-8 py-3.5 flex items-center justify-between gap-4">
                        {{-- Mobile Hamburger Toggle --}}
                        <button type="button"
                                @click="mobileNavOpen = true"
                                class="lg:hidden p-2 -ml-2 rounded-xl text-stone-600 hover:bg-[#EFECE6] hover:text-[#1E1B18] transition focus:outline-none"
                                aria-label="Buka Navigasi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- Search Input --}}
                        <div class="flex-1 max-w-lg">
                            <form action="{{ route('admin.orders.index') }}" method="GET" class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="search" name="search"
                                       placeholder="Cari nomor pesanan, SKU kain, pelanggan..."
                                       class="w-full rounded-2xl border border-[#E8E4DC] bg-white py-2 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                            </form>
                        </div>

                        {{-- Right Status & User Actions --}}
                        <div class="flex items-center gap-3">
                            {{-- Operational Status Pill --}}
                            <div class="hidden sm:inline-flex items-center gap-2 rounded-full border border-[#E9DFD0] bg-[#FDF9F3] px-3.5 py-1.5 text-xs font-semibold text-[#8C5E28] shadow-2xs">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span>TOKO AKTIF / OPERASIONAL</span>
                            </div>

                            {{-- Notification Bell --}}
                            <a href="{{ route('admin.orders.index', ['status' => 'Menunggu Verifikasi']) }}" class="relative rounded-full border border-[#EFECE6] bg-white p-2 text-stone-600 hover:bg-[#FAF7F2] transition shadow-2xs" title="Notifikasi Verifikasi Pesanan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#A86544] text-[9px] font-bold text-white">
                                    3
                                </span>
                            </a>

                            {{-- User Avatar & Info --}}
                            <div class="flex items-center gap-2 pl-2 border-l border-[#EFECE6]">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1E1B18] text-[#C59A53] text-xs font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-xs font-bold text-[#1E1B18] leading-tight">Admin Curator</p>
                                    <p class="text-[10px] text-stone-400">Master Artisan Studio</p>
                                </div>
                            </div>
                        </div>
                    </header>

                    {{-- Optional Header slot --}}
                    @isset($header)
                        <div class="px-4 sm:px-8 pt-6 pb-2">
                            {{ $header }}
                        </div>
                    @endisset

                    {{-- Main Page Slot --}}
                    <main class="flex-1 p-4 sm:p-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @else
            {{-- STANDARD CUSTOMER LAYOUT --}}
            <div class="min-h-screen bg-gray-100">
                @include('layouts.navigation')

                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        @endif
    </body>
</html>
