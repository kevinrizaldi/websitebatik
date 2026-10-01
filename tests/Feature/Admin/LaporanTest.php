<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function customer(string $name = 'Pelanggan Laporan'): User
    {
        return User::factory()->create(['name' => $name, 'role' => 'customer']);
    }

    private function order(User $customer, string $code, string $status = 'Selesai', float $total = 100000): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'code' => $code,
            'customer_name' => $customer->name,
            'phone' => '081234567890',
            'address' => 'Jl. Batik No. 1',
            'total_price' => $total,
            'payment_method' => 'Transfer Bank',
            'status' => $status,
        ]);
    }

    public function test_admin_can_open_all_report_types(): void
    {
        $reportTitles = [
            'sales_daily' => 'Penjualan Harian',
            'sales_weekly' => 'Penjualan Mingguan',
            'sales_monthly' => 'Penjualan Bulanan',
            'sales_yearly' => 'Penjualan Tahunan',
        ];

        $this->actingAs($this->admin());

        foreach ($reportTitles as $report => $title) {
            $this->get(route('admin.laporan.index', ['report' => $report]))
                ->assertOk()
                ->assertViewHas('title', $title)
                ->assertSee('Laporan Penjualan');
        }
    }

    public function test_sales_reports_group_orders_by_day_week_month_and_year(): void
    {
        $customer = $this->customer();
        $baseDate = now()->subMonths(3)->startOfMonth()->addDays(10)->startOfWeek();
        $dates = [
            $baseDate->copy()->subYear(),
            $baseDate->copy(),
            $baseDate->copy()->addDay(),
            $baseDate->copy()->addMonth(),
        ];

        foreach ($dates as $index => $date) {
            $order = $this->order($customer, 'ORD-LAPORAN-WAKTU-'.$index, 'Selesai', ($index + 1) * 50000);
            $order->forceFill(['created_at' => $date])->save();
        }

        $this->actingAs($this->admin());
        $dateRange = [
            'start_date' => $dates[0]->toDateString(),
            'end_date' => $dates[3]->toDateString(),
        ];

        $daily = $this->get(route('admin.laporan.index', array_merge($dateRange, ['report' => 'sales_daily'])))->viewData('rows');
        $weekly = $this->get(route('admin.laporan.index', array_merge($dateRange, ['report' => 'sales_weekly'])))->viewData('rows');
        $monthly = $this->get(route('admin.laporan.index', array_merge($dateRange, ['report' => 'sales_monthly'])))->viewData('rows');
        $yearly = $this->get(route('admin.laporan.index', array_merge($dateRange, ['report' => 'sales_yearly'])))->viewData('rows');

        $this->assertCount(4, $daily);
        $this->assertCount(3, $weekly);
        $this->assertCount(3, $monthly);
        $this->assertCount(2, $yearly);
        $this->assertSame(500000.0, array_sum(array_column($daily, 'revenue')));
    }

    public function test_report_date_defaults_match_the_selected_period(): void
    {
        $today = now();
        $periodStarts = [
            'sales_daily' => $today->toDateString(),
            'sales_weekly' => $today->copy()->startOfWeek()->toDateString(),
            'sales_monthly' => $today->copy()->startOfMonth()->toDateString(),
            'sales_yearly' => $today->copy()->startOfYear()->toDateString(),
        ];
        $this->actingAs($this->admin());

        foreach ($periodStarts as $report => $expectedStartDate) {
            $response = $this->get(route('admin.laporan.index', ['report' => $report]));

            $this->assertSame($expectedStartDate, $response->viewData('startDate'));
            $this->assertSame($today->toDateString(), $response->viewData('endDate'));
        }
    }

    public function test_manually_selected_report_dates_are_preserved(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.laporan.index', [
            'report' => 'sales_monthly',
            'start_date' => '2026-08-05',
            'end_date' => '2026-08-20',
        ]));

        $response->assertOk();
        $this->assertSame('2026-08-05', $response->viewData('startDate'));
        $this->assertSame('2026-08-20', $response->viewData('endDate'));
    }

    public function test_report_page_only_lists_sales_periods(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.laporan.index'));

        $response->assertOk()
            ->assertSee('Harian')
            ->assertSee('Mingguan')
            ->assertSee('Bulanan')
            ->assertSee('Tahunan')
            ->assertDontSee('Laporan Produk')
            ->assertDontSee('Laporan Pelanggan')
            ->assertDontSee('Pelanggan Baru')
            ->assertDontSee('Produk Terlaris');
    }

    public function test_product_and_customer_report_types_are_rejected(): void
    {
        $this->actingAs($this->admin());

        foreach (['products_best_selling', 'customers_new'] as $report) {
            $this->from(route('admin.laporan.index'))
                ->get(route('admin.laporan.index', ['report' => $report]))
                ->assertRedirect(route('admin.laporan.index'))
                ->assertSessionHasErrors('report');
        }
    }

    public function test_admin_can_export_reports_as_csv_excel_and_printable_pdf(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $csvResponse = $this->get(route('admin.laporan.export', ['format' => 'csv', 'report' => 'sales_daily']));
        $csvResponse->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Periode', $csvResponse->streamedContent());

        $this->get(route('admin.laporan.export', ['format' => 'excel', 'report' => 'sales_monthly']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('Periode');

        $this->get(route('admin.laporan.export', ['format' => 'pdf', 'report' => 'sales_yearly']))
            ->assertOk()
            ->assertSee('Penjualan Tahunan')
            ->assertSee('Simpan sebagai PDF');
    }

    public function test_non_admin_cannot_access_report_exports(): void
    {
        $this->actingAs($this->customer())
            ->get(route('admin.laporan.index'))
            ->assertForbidden();

    }

    public function test_admin_features_are_rendered_in_the_left_sidebar(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.laporan.index'));

        $response->assertOk()
            ->assertSee('aria-label="Navigasi admin"', false)
            ->assertSee(route('admin.orders.index'), false)
            ->assertSee(route('admin.laporan.index'), false)
            ->assertSee('Kelola Pesanan')
            ->assertSee('Panel Toko')
            ->assertDontSee('sm:-my-px sm:ms-10 sm:flex', false);
    }
}
