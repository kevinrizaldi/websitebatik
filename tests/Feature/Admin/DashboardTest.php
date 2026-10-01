<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(string $name, int $stock): Produk
    {
        return Produk::create([
            'nama' => $name,
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'kategori' => 'Batik',
            'harga' => 100000,
            'stok' => $stock,
            'status' => Produk::statusForStock($stock),
        ]);
    }

    private function createOrder(User $customer, string $code, string $status, float $total, Carbon $createdAt): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'code' => $code,
            'customer_name' => $customer->name,
            'phone' => '081234567890',
            'address' => 'Jl. Batik No. 1',
            'total_price' => $total,
            'payment_method' => 'Transfer Bank',
            'status' => $status,
        ]);

        $order->forceFill(['created_at' => $createdAt])->save();

        return $order;
    }

    private function addOrderItem(Order $order, Produk $product, int $quantity): void
    {
        $order->items()->create([
            'produk_id' => $product->id,
            'produk_name' => $product->nama,
            'price' => $product->harga,
            'quantity' => $quantity,
            'subtotal' => $product->harga * $quantity,
        ]);
    }

    public function test_admin_dashboard_shows_sales_operations_and_review_metrics(): void
    {
        $this->travelTo(Carbon::parse('2026-10-21 12:00:00'));
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $topProduct = $this->createProduct('Batik Terlaris', 30);
        $lowStockProduct = $this->createProduct('Batik Stok Menipis', 8);
        $outOfStockProduct = $this->createProduct('Batik Stok Habis', 0);

        $todayOrder = $this->createOrder($customer, 'ORD-DASH-TODAY', 'Selesai', 200000, now());
        $this->addOrderItem($todayOrder, $topProduct, 2);

        $recentOrder = $this->createOrder($customer, 'ORD-DASH-RECENT', 'Selesai', 100000, now()->subDays(3));
        $this->addOrderItem($recentOrder, $topProduct, 3);

        $previousOrder = $this->createOrder($customer, 'ORD-DASH-PREVIOUS', 'Selesai', 50000, now()->subDays(8));
        $this->addOrderItem($previousOrder, $lowStockProduct, 10);

        $processingOrder = $this->createOrder($customer, 'ORD-DASH-PROCESSING', 'Diproses', 70000, now());
        $this->addOrderItem($processingOrder, $topProduct, 1);

        $cancelledOrder = $this->createOrder($customer, 'ORD-DASH-CANCELLED', 'Dibatalkan', 900000, now());
        $this->addOrderItem($cancelledOrder, $topProduct, 99);

        Ulasan::create([
            'produk_id' => $topProduct->id,
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'rating' => 5,
            'comment' => 'Bagus sekali.',
            'status' => 'Disetujui',
        ]);
        Ulasan::create([
            'produk_id' => $topProduct->id,
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'rating' => 2,
            'comment' => 'Menunggu moderasi.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard', [
            'period' => '7d',
            'metric' => 'average',
        ]));

        $response->assertOk()
            ->assertViewHas('salesToday', 270000.0)
            ->assertViewHas('salesThisMonth', 420000.0)
            ->assertViewHas('currentPeriodSales', 370000.0)
            ->assertViewHas('previousPeriodSales', 50000.0)
            ->assertViewHas('salesChangePercent', 640.0)
            ->assertViewHas('totalOrders', 5)
            ->assertViewHas('ordersToProcess', 1)
            ->assertViewHas('totalProducts', 3)
            ->assertViewHas('lowStockProducts', 1)
            ->assertViewHas('outOfStockProducts', 1)
            ->assertViewHas('averageRating', 5.0)
            ->assertViewHas('reviewsToApprove', 1)
            ->assertViewHas('metric', 'average')
            ->assertViewHas('chartSeries', fn (array $series): bool => count($series) === 7)
            ->assertSee('Dashboard Admin')
            ->assertSee('Batik Terlaris');

        $this->assertSame(6, $response->viewData('topProducts')->first()->units_sold);
    }

    public function test_dashboard_period_filter_limits_top_products_and_chart_series(): void
    {
        $this->travelTo(Carbon::parse('2026-10-21 12:00:00'));
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $recentProduct = $this->createProduct('Batik Terlaris Baru', 20);
        $olderProduct = $this->createProduct('Batik Terlaris Lama', 20);

        $recentOrder = $this->createOrder($customer, 'ORD-DASH-RECENT-TOP', 'Selesai', 100000, now());
        $this->addOrderItem($recentOrder, $recentProduct, 2);
        $olderOrder = $this->createOrder($customer, 'ORD-DASH-OLD-TOP', 'Selesai', 500000, now()->subMonths(4));
        $this->addOrderItem($olderOrder, $olderProduct, 10);

        $weekResponse = $this->actingAs($admin)->get(route('dashboard', ['period' => '7d']));
        $yearResponse = $this->get(route('dashboard', ['period' => '1y', 'metric' => 'transactions']));

        $this->assertSame('Batik Terlaris Baru', $weekResponse->viewData('topProducts')->first()->produk_name);
        $this->assertSame('Batik Terlaris Lama', $yearResponse->viewData('topProducts')->first()->produk_name);
        $this->assertCount(7, $weekResponse->viewData('chartSeries'));
        $this->assertCount(12, $yearResponse->viewData('chartSeries'));
        $this->assertSame('transactions', $yearResponse->viewData('metric'));
    }

    public function test_dashboard_low_stock_count_uses_the_same_ten_unit_threshold(): void
    {
        User::factory()->create(['role' => 'admin']);
        $this->createProduct('Batik Batas Menipis', 10);
        $this->createProduct('Batik Batas Aman', 11);
        $this->createProduct('Batik Habis', 0);

        $response = $this->actingAs(User::where('role', 'admin')->first())
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('lowStockProducts', 1)
            ->assertViewHas('outOfStockProducts', 1);
    }

    public function test_customer_dashboard_remains_the_customer_welcome_page(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Anda login sebagai Pelanggan.')
            ->assertDontSee('aria-label="Navigasi admin"', false)
            ->assertDontSee('Dashboard Admin');
    }
}
