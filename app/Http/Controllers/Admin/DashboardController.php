<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\Ulasan;
use Carbon\Carbon;
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

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            return view('dashboard');
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
        $totalProducts = Produk::count();
        $lowStockProducts = Produk::whereBetween('stok', [1, Produk::LOW_STOCK_THRESHOLD])->count();
        $outOfStockProducts = Produk::where('stok', '<=', 0)->count();
        $averageRating = (float) (Ulasan::where('status', 'Disetujui')->avg('rating') ?? 0);
        $reviewsToApprove = Ulasan::where('status', 'Menunggu')->count();

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
            ->whereNotIn('orders.status', self::CANCELLED_STATUSES)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('order_items.produk_name')
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->selectRaw('SUM(order_items.subtotal) as sales_total')
            ->groupBy('order_items.produk_name')
            ->orderByDesc('units_sold')
            ->orderBy('order_items.produk_name')
            ->limit(5)
            ->get();

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
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'averageRating' => $averageRating,
            'reviewsToApprove' => $reviewsToApprove,
            'chartSeries' => $chartSeries,
            'chartPoints' => $chartPoints,
            'chartPath' => $chartPath,
            'chartAreaPath' => $chartAreaPath,
            'topProducts' => $topProducts,
        ]);
    }

    private function salesTotal(Carbon $startDate, Carbon $endDate): float
    {
        return (float) Order::whereNotIn('status', self::CANCELLED_STATUSES)
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
            ->whereNotIn('status', self::CANCELLED_STATUSES)
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
