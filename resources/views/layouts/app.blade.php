<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div @class(['lg:flex' => Auth::user()->isAdmin(), 'min-h-[calc(100vh-4rem)]' => Auth::user()->isAdmin()])>
                @if (Auth::user()->isAdmin())
                    @php
                        $adminLinks = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
                            ['label' => 'Kelola Produk', 'route' => 'produk.index', 'active' => 'produk.*'],
                            ['label' => 'Kelola Pesanan', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
                            ['label' => 'Kelola Ulasan', 'route' => 'admin.ulasans.index', 'active' => 'admin.ulasans.*'],
                            ['label' => 'Laporan Penjualan', 'route' => 'admin.laporan.index', 'active' => 'admin.laporan.*'],
                            ['label' => 'Kategori', 'route' => 'admin.kategori.index', 'active' => 'admin.kategori.*'],
                        ];
                    @endphp

                    <aside class="sticky top-0 hidden h-[calc(100vh-4rem)] w-64 shrink-0 flex-col border-r border-gray-200 bg-white lg:flex" aria-label="Navigasi admin">
                        <div class="border-b border-gray-100 px-5 py-5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Administrasi</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">Panel Toko</p>
                        </div>
                        <nav class="flex-1 space-y-1 px-3 py-4">
                            @foreach ($adminLinks as $link)
                                <a href="{{ route($link['route']) }}"
                                   @class([
                                       'flex min-h-10 items-center rounded-md px-3 py-2 text-sm transition',
                                       'bg-gray-900 font-semibold text-white' => request()->routeIs($link['active']),
                                       'font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! request()->routeIs($link['active']),
                                   ])>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </nav>
                        <div class="border-t border-gray-100 px-5 py-4">
                            <p class="truncate text-xs font-medium text-gray-700">{{ Auth::user()->name }}</p>
                            <p class="mt-0.5 text-[11px] text-gray-500">Administrator</p>
                        </div>
                    </aside>
                @endif

                <div @class(['min-w-0 flex-1', 'lg:ml-0' => Auth::user()->isAdmin()])>
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
