<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @if ($isAdminDashboard ?? false)
                        Dashboard Admin
                    @else
                        {{ __('Dashboard') }}
                    @endif
                </h2>
                @if ($isAdminDashboard ?? false)
                    <p class="mt-1 text-sm text-gray-500">Ringkasan kinerja toko dan aktivitas terbaru.</p>
                @endif
            </div>
            @if ($isAdminDashboard ?? false)
                <p class="text-xs font-medium text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
            @endif
        </div>
    </x-slot>

    @if ($isAdminDashboard ?? false)
        @php
            $chartMetricLabel = match ($metric) {
                'transactions' => 'Jumlah transaksi',
                'average' => 'Rata-rata nilai transaksi',
                default => 'Total penjualan',
            };
            $chartValue = match ($metric) {
                'transactions' => array_sum(array_column($chartSeries, 'transactions')),
                'average' => count($chartSeries) > 0 ? array_sum(array_column($chartSeries, 'sales')) / max(1, array_sum(array_column($chartSeries, 'transactions'))) : 0,
                default => array_sum(array_column($chartSeries, 'sales')),
            };
        @endphp

        <div class="bg-gray-50 py-7 sm:py-9">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section aria-labelledby="sales-overview-title">
                    <div class="mb-3 flex items-end justify-between gap-3">
                        <div>
                            <h3 id="sales-overview-title" class="text-sm font-semibold text-gray-900">Penjualan</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Nilai penjualan tidak memasukkan pesanan batal.</p>
                        </div>
                        <a href="{{ route('admin.laporan.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">Buka laporan</a>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-medium text-gray-500">Penjualan hari ini</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">Rp {{ number_format($salesToday, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ now()->format('d M Y') }}</p>
                        </article>
                        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-medium text-gray-500">Penjualan bulan ini</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">Rp {{ number_format($salesThisMonth, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ now()->translatedFormat('F Y') }}</p>
                        </article>
                        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-medium text-gray-500">Perubahan · {{ $periodLabel }}</p>
                            @if ($salesChangePercent === null)
                                <p class="mt-2 text-lg font-semibold text-gray-900">Belum ada pembanding</p>
                                <p class="mt-1 text-xs text-gray-500">Belum ada penjualan pada periode sebelumnya.</p>
                            @else
                                <p @class([
                                    'mt-2 text-2xl font-semibold',
                                    'text-emerald-700' => $salesChangePercent >= 0,
                                    'text-rose-700' => $salesChangePercent < 0,
                                ])>
                                    {{ $salesChangePercent >= 0 ? '+' : '' }}{{ number_format($salesChangePercent, 1, ',', '.') }}%
                                </p>
                                <p class="mt-1 text-xs text-gray-500">Dibanding Rp {{ number_format($previousPeriodSales, 0, ',', '.') }} pada periode sebelumnya.</p>
                            @endif
                        </article>
                    </div>
                </section>

                <section aria-labelledby="operations-title">
                    <div class="mb-3">
                        <h3 id="operations-title" class="text-sm font-semibold text-gray-900">Operasional toko</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                        <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300">
                            <p class="text-xs font-medium text-gray-500">Total pesanan</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalOrders, 0, ',', '.') }}</p>
                            <span class="mt-2 inline-block text-xs font-semibold text-indigo-700">Lihat pesanan</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'Diproses']) }}" class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:border-amber-300">
                            <p class="text-xs font-medium text-amber-900">Pesanan perlu diproses</p>
                            <p class="mt-2 text-2xl font-semibold text-amber-950">{{ number_format($ordersToProcess, 0, ',', '.') }}</p>
                            <span class="mt-2 inline-block text-xs font-semibold text-amber-900">Buka antrean</span>
                        </a>
                        <a href="{{ route('produk.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300">
                            <p class="text-xs font-medium text-gray-500">Total produk</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalProducts, 0, ',', '.') }}</p>
                            <span class="mt-2 inline-block text-xs font-semibold text-indigo-700">Kelola produk</span>
                        </a>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('produk.index', ['stok_menipis' => 1]) }}" class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:border-amber-300">
                                <p class="text-xs font-medium text-amber-900">Stok menipis</p>
                                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ number_format($lowStockProducts, 0, ',', '.') }}</p>
                            </a>
                            <a href="{{ route('produk.index', ['search' => 'Habis']) }}" class="rounded-lg border border-rose-200 bg-rose-50 p-4 shadow-sm transition hover:border-rose-300">
                                <p class="text-xs font-medium text-rose-900">Stok habis</p>
                                <p class="mt-2 text-2xl font-semibold text-rose-950">{{ number_format($outOfStockProducts, 0, ',', '.') }}</p>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.8fr)_minmax(280px,1fr)]">
                    <div class="min-w-0 rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Grafik penjualan</h3>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $periodLabel }} · {{ $chartMetricLabel }}</p>
                            </div>
                            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-end gap-2">
                                <div>
                                    <label for="period" class="mb-1 block text-[11px] font-medium text-gray-500">Rentang</label>
                                    <select id="period" name="period" class="rounded-md border-gray-300 py-1.5 pl-2 pr-7 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($periodOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="metric" class="mb-1 block text-[11px] font-medium text-gray-500">Tampilkan</label>
                                    <select id="metric" name="metric" class="rounded-md border-gray-300 py-1.5 pl-2 pr-7 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($metricOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($metric === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="h-[34px] rounded-md bg-gray-900 px-3 text-xs font-semibold text-white transition hover:bg-black">Terapkan</button>
                            </form>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="mb-3 flex items-baseline gap-2">
                                @if ($metric === 'transactions')
                                    <span class="text-2xl font-semibold text-gray-900">{{ number_format((int) $chartValue, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-500">transaksi</span>
                                @else
                                    <span class="text-2xl font-semibold text-gray-900">Rp {{ number_format($chartValue, 0, ',', '.') }}</span>
                                @endif
                            </div>
                            <div class="overflow-hidden">
                                <svg viewBox="0 0 800 240" class="h-56 w-full" role="img" aria-label="Grafik {{ strtolower($chartMetricLabel) }} {{ $periodLabel }}">
                                    @foreach ([40, 80, 120, 160, 200] as $gridY)
                                        <line x1="44" y1="{{ $gridY }}" x2="756" y2="{{ $gridY }}" stroke="#e5e7eb" stroke-dasharray="3 5" />
                                    @endforeach
                                    <path d="{{ $chartAreaPath }}" fill="#d1fae5" opacity="0.65" />
                                    <path d="{{ $chartPath }}" fill="none" stroke="#047857" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" />
                                    @foreach ($chartPoints as $index => $point)
                                        @if ($index === 0 || $index === count($chartPoints) - 1 || $index % max(1, (int) floor(count($chartPoints) / 6)) === 0)
                                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#047857" stroke="white" stroke-width="2">
                                                <title>{{ $point['label'] }}: {{ $metric === 'transactions' ? number_format((int) $point['value'], 0, ',', '.') : 'Rp '.number_format((float) $point['value'], 0, ',', '.') }}</title>
                                            </circle>
                                            <text x="{{ $point['x'] }}" y="226" text-anchor="middle" fill="#6b7280" font-size="11">{{ $point['label'] }}</text>
                                        @endif
                                    @endforeach
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Produk terlaris</h3>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $periodLabel }}</p>
                            </div>
                            <a href="{{ route('admin.laporan.index') }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">Laporan</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse ($topProducts as $index => $product)
                                <div class="flex items-center gap-3 px-4 py-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">{{ $index + 1 }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $product->produk_name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">{{ number_format((int) $product->units_sold, 0, ',', '.') }} terjual</p>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold text-gray-700">Rp {{ number_format((float) $product->sales_total, 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-500">Belum ada penjualan pada rentang ini.</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section aria-labelledby="reviews-title">
                    <div class="mb-3">
                        <h3 id="reviews-title" class="text-sm font-semibold text-gray-900">Ulasan pelanggan</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <a href="{{ route('admin.ulasans.index', ['status' => 'Disetujui']) }}" class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Rating rata-rata</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($averageRating, 1, ',', '.') }} <span class="text-amber-500">★</span></p>
                            </div>
                            <span class="text-xs font-semibold text-indigo-700">Lihat ulasan</span>
                        </a>
                        <a href="{{ route('admin.ulasans.index', ['status' => 'Menunggu']) }}" class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:border-amber-300">
                            <div>
                                <p class="text-xs font-medium text-amber-900">Perlu disetujui</p>
                                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ number_format($reviewsToApprove, 0, ',', '.') }}</p>
                            </div>
                            <span class="text-xs font-semibold text-amber-900">Moderasi ulasan</span>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    @else
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4 text-gray-700">{{ __("Selamat datang di Batik Store! Anda login sebagai Pelanggan.") }}</p>
                        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-black transition shadow-sm">
                            {{ __('Mulai Belanja') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
