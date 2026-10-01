<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    private const REPORT_TITLES = [
        'sales_daily' => 'Penjualan Harian',
        'sales_weekly' => 'Penjualan Mingguan',
        'sales_monthly' => 'Penjualan Bulanan',
        'sales_yearly' => 'Penjualan Tahunan',
    ];

    private const REPORT_GROUPS = [
        'Laporan Penjualan' => [
            'sales_daily' => 'Harian',
            'sales_weekly' => 'Mingguan',
            'sales_monthly' => 'Bulanan',
            'sales_yearly' => 'Tahunan',
        ],
    ];

    private const CANCELLED_STATUSES = ['Batal', 'Dibatalkan'];

    public function index(Request $request): View
    {
        return view('admin.laporan.index', $this->buildReport($request));
    }

    public function print(Request $request): View
    {
        return view('admin.laporan.print', $this->buildReport($request));
    }

    public function export(Request $request, string $format): Response|StreamedResponse|View
    {
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        $report = $this->buildReport($request);

        if ($format === 'pdf') {
            return view('admin.laporan.print', $report);
        }

        $filename = Str::slug($report['title']).'-'.now()->format('Ymd-His');

        if ($format === 'excel') {
            return response()->view('admin.laporan.excel', $report)
                ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'.xls"');
        }

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_column($report['columns'], 'label'), ',', '"', '');

            foreach ($report['rows'] as $row) {
                $values = array_map(function (array $column) use ($row): mixed {
                    $value = $row[$column['key']] ?? '';

                    if (is_string($value) && preg_match('/^[=+@\-\t\r]/u', $value)) {
                        return "'".$value;
                    }

                    return $value;
                }, $report['columns']);

                fputcsv($output, $values, ',', '"', '');
            }

            fclose($output);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{
     *     reportType: string,
     *     reportOptions: array<string, array<string, string>>,
     *     title: string,
     *     columns: array<int, array{key: string, label: string, type?: string}>,
     *     rows: array<int, array<string, int|float|string>>,
     *     startDate: ?string,
     *     endDate: ?string,
     *     status: ?string,
     *     recordCount: int,
     *     totalRevenue: ?float
     * }
     */
    private function buildReport(Request $request): array
    {
        $validated = $request->validate([
            'report' => 'nullable|in:'.implode(',', array_keys(self::REPORT_TITLES)),
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status' => 'nullable|string|max:50',
        ]);

        $reportType = $validated['report'] ?? 'sales_daily';
        $today = Carbon::today();
        $defaultStartDate = match ($reportType) {
            'sales_weekly' => $today->copy()->startOfWeek(Carbon::MONDAY),
            'sales_monthly' => $today->copy()->startOfMonth(),
            'sales_yearly' => $today->copy()->startOfYear(),
            default => $today->copy(),
        };
        $startDate = $validated['start_date'] ?? $defaultStartDate->toDateString();
        $endDate = $validated['end_date'] ?? $today->toDateString();
        $status = $validated['status'] ?? null;

        $reportData = $this->salesReport($reportType, $startDate, $endDate, $status);

        return array_merge($reportData, [
            'reportType' => $reportType,
            'reportOptions' => self::REPORT_GROUPS,
            'title' => self::REPORT_TITLES[$reportType],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'recordCount' => count($reportData['rows']),
        ]);
    }

    /**
     * @return array{
     *     columns: array<int, array{key: string, label: string, type?: string}>,
     *     rows: array<int, array<string, int|float|string>>,
     *     totalRevenue: ?float
     * }
     */
    private function salesReport(string $reportType, ?string $startDate, ?string $endDate, ?string $status): array
    {
        $orders = $this->applyDateFilters(Order::query(), 'orders.created_at', $startDate, $endDate)
            ->when($status, fn ($query) => $query->where('orders.status', $status))
            ->orderBy('orders.created_at')
            ->get(['orders.status', 'orders.total_price', 'orders.created_at']);

        $rows = $orders->groupBy(function (Order $order) use ($reportType): string {
            $date = $order->created_at;

            return match ($reportType) {
                'sales_daily' => $date->format('Y-m-d'),
                'sales_weekly' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                'sales_monthly' => $date->format('Y-m'),
                default => $date->format('Y'),
            };
        })->map(function ($periodOrders, string $periodKey) use ($reportType): array {
            $periodDate = Carbon::parse($periodKey);
            $periodLabel = match ($reportType) {
                'sales_daily' => $periodDate->format('d/m/Y'),
                'sales_weekly' => $periodDate->format('d/m/Y').' s.d. '.$periodDate->copy()->endOfWeek(Carbon::SUNDAY)->format('d/m/Y'),
                'sales_monthly' => $periodDate->format('m/Y'),
                default => $periodDate->format('Y'),
            };
            $revenue = $periodOrders
                ->reject(fn (Order $order): bool => in_array($order->status, self::CANCELLED_STATUSES, true))
                ->sum(fn (Order $order): float => (float) $order->total_price);

            return [
                'period' => $periodLabel,
                'order_count' => $periodOrders->count(),
                'revenue' => $revenue,
            ];
        })->values()->all();

        return [
            'columns' => [
                ['key' => 'period', 'label' => 'Periode'],
                ['key' => 'order_count', 'label' => 'Jumlah Pesanan'],
                ['key' => 'revenue', 'label' => 'Pendapatan', 'type' => 'currency'],
            ],
            'rows' => $rows,
            'totalRevenue' => array_sum(array_column($rows, 'revenue')),
        ];
    }

    private function applyDateFilters(Builder $query, string $column, ?string $startDate, ?string $endDate): Builder
    {
        return $query
            ->when($startDate, fn ($query) => $query->whereDate($column, '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate($column, '<=', $endDate));
    }
}
