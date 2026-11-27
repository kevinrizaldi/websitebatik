<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hamzah Style') }}</title>

        <!-- Google Fonts: Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

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
                                       placeholder="Cari nomor pesanan, kode produk, pelanggan..."
                                       class="w-full rounded-2xl border border-[#E8E4DC] bg-white py-2 pl-10 pr-4 text-xs placeholder:text-stone-400 focus:border-[#C59A53] focus:outline-none focus:ring-1 focus:ring-[#C59A53]/30 shadow-2xs">
                            </form>
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
