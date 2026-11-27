<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PERIODS = [
        '7d' => '7 hari',
        '1m' => '1 bulan',
        '3m' => '3 bulan',
        '1y' => '1 tahun',
    ];

    private const METRICS = [
        'sales' => 'Total penjualan',
        'transactions' => 'Jumlah transaksi',
        'average' => 'Rata-rata nilai transaksi',
    ];

    private const CANCELLED_STATUSES = ['Batal', 'Dibatalkan'];

    private const PAID_STATUSES = ['Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai'];

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'period' => 'nullable|in:'.implode(',', array_keys(self::PERIODS)),
            'metric' => 'nullable|in:'.implode(',', array_keys(self::METRICS)),
        ]);

        $period = $validated['period'] ?? '7d';
        $metric = $validated['metric'] ?? 'sales';
        $today = Carbon::today();
        $startDate = match ($period) {
            '1m' => $today->copy()->subMonth()->startOfDay(),
            '3m' => $today->copy()->subMonths(3)->startOfDay(),
            '1y' => $today->copy()->subMonths(11)->startOfMonth(),
            default => $today->copy()->subDays(6)->startOfDay(),
        };
        $endDate = $today->copy()->endOfDay();
        $previousStart = match ($period) {
            '1m' => $startDate->copy()->subMonth(),
            '3m' => $startDate->copy()->subMonths(3),
            '1y' => $startDate->copy()->subYear(),
            default => $startDate->copy()->subDays(7),
        };
        $previousEnd = $startDate->copy()->subSecond();

        $currentPeriodSales = $this->salesTotal($startDate, $endDate);
        $previousPeriodSales = $this->salesTotal($previousStart, $previousEnd);
        $salesChangePercent = $previousPeriodSales > 0
            ? round((($currentPeriodSales - $previousPeriodSales) / $previousPeriodSales) * 100, 1)
            : null;

        $salesToday = $this->salesTotal($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $salesThisMonth = $this->salesTotal($today->copy()->startOfMonth(), $today->copy()->endOfMonth());
        $totalOrders = Order::count();
        $ordersToProcess = Order::whereIn('status', ['Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi'])->count();
        $totalProducts = Product::count();
        $newProductsThisMonth = Product::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        $lowStockProducts = Product::whereBetween('stok', [1, Product::LOW_STOCK_THRESHOLD])->count();
        $outOfStockProducts = Product::where('stok', '<=', 0)->count();
        $averageRating = (float) (Review::where('status', 'Disetujui')->avg('rating') ?? 0);
        $reviewsToApprove = Review::where('status', 'Menunggu')->count();

        // Real order growth: this month vs last month
        $ordersThisMonth = Order::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        $ordersLastMonth = Order::whereMonth('created_at', $today->copy()->subMonth()->month)->whereYear('created_at', $today->copy()->subMonth()->year)->count();
        $orderGrowthPercent = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100)
            : ($ordersThisMonth > 0 ? 100 : 0);

        // Real repeat buyers percentage
        $buyersCount = Order::distinct('user_id')->whereNotNull('user_id')->count('user_id');
        $repeatBuyersCount = Order::select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
        $repeatBuyerPercent = $buyersCount > 0 ? round(($repeatBuyersCount / $buyersCount) * 100) : 0;

        // Real sales target percentage
        $monthlySalesTarget = 50000000;
        $targetPercentage = $monthlySalesTarget > 0 ? min(100, round(($salesThisMonth / $monthlySalesTarget) * 100, 1)) : 0;

        // Real best-selling categories
        $categorySales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.produk_id')
            ->where(function ($q): void {
                $q->where('orders.payment_status', Order::PAYMENT_PAID)
                    ->orWhereIn('orders.status', self::PAID_STATUSES);
            })
            ->selectRaw('COALESCE(products.kategori, "Baju Batik") as kategori_nama')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy('kategori_nama')
            ->orderByDesc('units_sold')
            ->get();

        $totalCategoryUnits = (int) $categorySales->sum('units_sold');
        if ($totalCategoryUnits === 0) {
            $categorySales = Product::query()
                ->selectRaw('COALESCE(kategori, "Baju Batik") as kategori_nama')
                ->selectRaw('SUM(stok) as units_sold')
                ->groupBy('kategori_nama')
                ->orderByDesc('units_sold')
                ->get();
            $totalCategoryUnits = (int) $categorySales->sum('units_sold');
        }

        $categoriesData = $categorySales->take(3)->values()->map(function ($cat, $index) use ($totalCategoryUnits): array {
            $samples = Product::where('kategori', $cat->kategori_nama)->pluck('nama')->take(2)->implode(', ');
            $color = match ($index) {
                0 => '#1E1B18',
                1 => '#A86544',
                default => '#C59A53',
            };

            return [
                'nama' => $cat->kategori_nama,
                'units_sold' => (int) $cat->units_sold,
                'percentage' => $totalCategoryUnits > 0 ? round(($cat->units_sold / $totalCategoryUnits) * 100) : 0,
                'samples' => $samples ?: 'Koleksi busana pilihan',
                'color' => $color,
            ];
        });

        // Real restock recommendation
        $lowStockProduct = Product::where('stok', '<=', Product::LOW_STOCK_THRESHOLD)
            ->orderBy('stok', 'asc')
            ->first();

        // Real 6-month trend data
        $trendMonths = [];
        $rawTrendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $today->copy()->subMonths($i);
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();

            $batikSales = (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_items.produk_id')
                ->where(function ($q): void {
                    $q->where('orders.payment_status', Order::PAYMENT_PAID)
                        ->orWhereIn('orders.status', self::PAID_STATUSES);
                })
                ->whereBetween('orders.created_at', [$mStart, $mEnd])
                ->where(function ($q): void {
                    $q->where('products.kategori', 'like', '%baju%')
                        ->orWhereNull('products.kategori');
                })
                ->sum('order_items.subtotal');

            $otherSales = (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_items.produk_id')
                ->where(function ($q): void {
                    $q->where('orders.payment_status', Order::PAYMENT_PAID)
                        ->orWhereIn('orders.status', self::PAID_STATUSES);
                })
                ->whereBetween('orders.created_at', [$mStart, $mEnd])
                ->where('products.kategori', 'like', '%kain%')
                ->sum('order_items.subtotal');

            $upcycledSales = (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('products', 'products.id', '=', 'order_items.produk_id')
                ->where(function ($q): void {
                    $q->where('orders.payment_status', Order::PAYMENT_PAID)
                        ->orWhereIn('orders.status', self::PAID_STATUSES);
                })
                ->whereBetween('orders.created_at', [$mStart, $mEnd])
                ->where(function ($q): void {
                    $q->where('products.kategori', 'like', '%olahan%')
                        ->orWhere('products.kategori', 'like', '%sisa%');
                })
                ->sum('order_items.subtotal');

            $totalMonthSales = (float) Order::where(function ($q): void {
                $q->where('payment_status', Order::PAYMENT_PAID)
                    ->orWhereIn('status', self::PAID_STATUSES);
            })->whereBetween('created_at', [$mStart, $mEnd])->sum('total_price');

            $rawTrendData[] = [
                'label' => $monthDate->translatedFormat('M Y').($i === 0 ? ' (Berjalan)' : ''),
                'total_sales' => $totalMonthSales,
                'batik_sales' => $batikSales,
                'other_sales' => $otherSales,
                'upcycled_sales' => $upcycledSales,
            ];
        }

        $maxTrendSales = max(1, ...array_column($rawTrendData, 'total_sales'));
        $trendPoints = [];
        foreach ($rawTrendData as $idx => $m) {
            $xCenter = 75 + ($idx * 110);
            $bar1Height = $m['batik_sales'] > 0 ? max(8, round(($m['batik_sales'] / $maxTrendSales) * 140)) : 0;
            $bar2Height = $m['other_sales'] > 0 ? max(6, round(($m['other_sales'] / $maxTrendSales) * 140)) : 0;
            $lineY = $m['total_sales'] > 0 ? round(190 - (($m['total_sales'] / $maxTrendSales) * 130)) : 190;

            $trendPoints[] = [
                'x' => $xCenter,
                'y' => $lineY,
            ];

            $trendMonths[] = [
                'label' => $m['label'],
                'total_sales' => $m['total_sales'],
                'x_bar1' => $xCenter - 10,
                'y_bar1' => 190 - $bar1Height,
                'h_bar1' => $bar1Height,
                'x_bar2' => $xCenter + 4,
                'y_bar2' => 190 - $bar2Height,
                'h_bar2' => $bar2Height,
                'x_point' => $xCenter,
                'y_point' => $lineY,
            ];
        }

        // Generate SVG path for the trend line
        $trendPath = '';
        $trendAreaPath = '';
        if (count($trendPoints) > 0) {
            $trendPath = 'M '.$trendPoints[0]['x'].' '.$trendPoints[0]['y'];
            for ($k = 1; $k < count($trendPoints); $k++) {
                $prev = $trendPoints[$k - 1];
                $curr = $trendPoints[$k];
                $midX = ($prev['x'] + $curr['x']) / 2;
                $trendPath .= ' Q '.$prev['x'].' '.$prev['y'].' '.$midX.' '.(($prev['y'] + $curr['y']) / 2);
                $trendPath .= ' T '.$curr['x'].' '.$curr['y'];
            }
            $lastP = end($trendPoints);
            $firstP = $trendPoints[0];
            $trendAreaPath = $trendPath.' L '.$lastP['x'].' 190 L '.$firstP['x'].' 190 Z';
        }

        $chartSeries = $this->salesSeries($period, $startDate, $endDate);
        $chartPoints = $this->chartPoints($chartSeries, $metric);
        $chartPath = implode(' ', array_map(
            fn (array $point, int $index): string => ($index === 0 ? 'M ' : 'L ').$point['x'].' '.$point['y'],
            $chartPoints,
            array_keys($chartPoints)
        ));
        $firstPoint = $chartPoints[0] ?? ['x' => 44, 'y' => 200];
        $lastPoint = $chartPoints[array_key_last($chartPoints)] ?? ['x' => 756, 'y' => 200];
        $chartAreaPath = $chartPath.' L '.$lastPoint['x'].' 200 L '.$firstPoint['x'].' 200 Z';

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where(function ($q): void {
                $q->where('orders.payment_status', Order::PAYMENT_PAID)
                    ->orWhereIn('orders.status', self::PAID_STATUSES);
            })
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('order_items.produk_name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->selectRaw('SUM(order_items.subtotal) as sales_total')
            ->groupBy('order_items.produk_name')
            ->orderByDesc('units_sold')
            ->orderBy('order_items.produk_name')
            ->limit(5)
            ->get();

        $topProducts->each(function ($item): void {
            $produk = Product::where('nama', $item->produk_name)->first();
            $item->produk = $produk;
            $item->stok = $produk?->stok ?? 0;
            $item->kategori_nama = $produk?->kategoriRelasi?->nama_kategori ?? $produk?->kategori ?? 'Koleksi Batik';
            $item->gambar = $produk?->gambar;
            $item->gambar_url = $produk?->gambar_url ?? asset('images/beranda/cloth-fabric.jpg');
        });

        if ($topProducts->count() < 3) {
            $existingNames = $topProducts->pluck('produk_name')->toArray();
            $allTimeTop = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where(function ($q): void {
                    $q->where('orders.payment_status', Order::PAYMENT_PAID)
                        ->orWhereIn('orders.status', self::PAID_STATUSES);
                })
                ->whereNotIn('order_items.produk_name', $existingNames)
                ->select('order_items.produk_name')
                ->selectRaw('SUM(order_items.quantity) as units_sold')
                ->selectRaw('SUM(order_items.subtotal) as sales_total')
                ->groupBy('order_items.produk_name')
                ->orderByDesc('units_sold')
                ->limit(3 - $topProducts->count())
                ->get();

            $allTimeTop->each(function ($item): void {
                $produk = Product::where('nama', $item->produk_name)->first();
                $item->produk = $produk;
                $item->stok = $produk?->stok ?? 0;
                $item->kategori_nama = $produk?->kategoriRelasi?->nama_kategori ?? $produk?->kategori ?? 'Koleksi Batik';
                $item->gambar = $produk?->gambar;
                $item->gambar_url = $produk?->gambar_url ?? asset('images/beranda/cloth-fabric.jpg');
            });

            foreach ($allTimeTop as $item) {
                $topProducts->push($item);
            }
        }

        $totalCustomers = User::where(function ($q): void {
            $q->where('role', '!=', 'admin')->orWhereNull('role');
        })->count();

        $pendingActionOrders = Order::with(['items.produk'])
            ->whereIn('status', ['Menunggu Verifikasi', 'Menunggu Pembayaran', 'Belum Dibayar', 'Diproses', 'Menunggu Konfirmasi'])
            ->latest()
            ->limit(4)
            ->get();

        if ($pendingActionOrders->isEmpty()) {
            $pendingActionOrders = Order::with(['items.produk'])->latest()->limit(4)->get();
        }

        return view('dashboard', [
            'isAdminDashboard' => true,
            'period' => $period,
            'periodOptions' => self::PERIODS,
            'metric' => $metric,
            'metricOptions' => self::METRICS,
            'periodLabel' => $this->periodLabel($period),
            'salesToday' => $salesToday,
            'salesThisMonth' => $salesThisMonth,
            'currentPeriodSales' => $currentPeriodSales,
            'previousPeriodSales' => $previousPeriodSales,
            'salesChangePercent' => $salesChangePercent,
            'totalOrders' => $totalOrders,
            'ordersToProcess' => $ordersToProcess,
            'totalProducts' => $totalProducts,
            'newProductsThisMonth' => $newProductsThisMonth,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'averageRating' => $averageRating,
            'reviewsToApprove' => $reviewsToApprove,
            'chartSeries' => $chartSeries,
            'chartPoints' => $chartPoints,
            'chartPath' => $chartPath,
            'chartAreaPath' => $chartAreaPath,
            'topProducts' => $topProducts,
            'totalCustomers' => $totalCustomers,
            'pendingActionOrders' => $pendingActionOrders,
            'orderGrowthPercent' => $orderGrowthPercent,
            'ordersThisMonth' => $ordersThisMonth,
            'ordersLastMonth' => $ordersLastMonth,
            'repeatBuyerPercent' => $repeatBuyerPercent,
            'monthlySalesTarget' => $monthlySalesTarget,
            'targetPercentage' => $targetPercentage,
            'categoriesData' => $categoriesData,
            'lowStockProduct' => $lowStockProduct,
            'trendMonths' => $trendMonths,
            'trendPath' => $trendPath,
            'trendAreaPath' => $trendAreaPath,
        ]);
    }

    private function salesTotal(Carbon $startDate, Carbon $endDate): float
    {
        return (float) Order::where(function ($q): void {
            $q->where('payment_status', Order::PAYMENT_PAID)
                ->orWhereIn('status', self::PAID_STATUSES);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');
    }

    /**
     * @return array<int, array{label: string, sales: float, transactions: int, average: float}>
     */
    private function salesSeries(string $period, Carbon $startDate, Carbon $endDate): array
    {
        $bucketType = in_array($period, ['7d', '1m'], true) ? 'day' : ($period === '3m' ? 'week' : 'month');
        $buckets = [];
        $cursor = $startDate->copy();

        while ($cursor <= $endDate) {
            $key = match ($bucketType) {
                'week' => $cursor->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'month' => $cursor->format('Y-m'),
                default => $cursor->toDateString(),
            };
            $label = match ($bucketType) {
                'week' => $cursor->copy()->startOfWeek(Carbon::MONDAY)->format('d M'),
                'month' => $cursor->format('M Y'),
                default => $cursor->format('d M'),
            };
            $buckets[$key] ??= [
                'label' => $label,
                'sales' => 0.0,
                'transactions' => 0,
                'average' => 0.0,
            ];

            match ($bucketType) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        $orders = Order::query()
            ->where(function ($q): void {
                $q->where('payment_status', Order::PAYMENT_PAID)
                    ->orWhereIn('status', self::PAID_STATUSES);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['created_at', 'total_price']);

        foreach ($orders as $order) {
            $key = match ($bucketType) {
                'week' => $order->created_at->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'month' => $order->created_at->format('Y-m'),
                default => $order->created_at->toDateString(),
            };

            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['sales'] += (float) $order->total_price;
            $buckets[$key]['transactions']++;
        }

        foreach ($buckets as &$bucket) {
            $bucket['average'] = $bucket['transactions'] > 0
                ? $bucket['sales'] / $bucket['transactions']
                : 0.0;
        }
        unset($bucket);

        return array_values($buckets);
    }

    /**
     * @param  array<int, array{label: string, sales: float, transactions: int, average: float}>  $series
     * @return array<int, array{label: string, value: float|int, x: float, y: float}>
     */
    private function chartPoints(array $series, string $metric): array
    {
        $values = array_column($series, $metric);
        $maximum = max(1, ...$values);
        $count = count($series);

        return array_map(function (array $item, int $index) use ($metric, $maximum, $count): array {
            $value = $item[$metric];
            $x = $count > 1 ? 44 + ($index * 712 / ($count - 1)) : 400;
            $y = 200 - ((float) $value / $maximum * 160);

            return [
                'label' => $item['label'],
                'value' => $value,
                'x' => round($x, 2),
                'y' => round($y, 2),
            ];
        }, $series, array_keys($series));
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            '1m' => '1 bulan terakhir',
            '3m' => '3 bulan terakhir',
            '1y' => '1 tahun terakhir',
            default => '7 hari terakhir',
        };
    }
}
