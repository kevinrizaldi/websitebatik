<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): User
    {
        return User::factory()->create([
            'role' => 'customer',
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createProductWithStock(int $stock = 5): Product
    {
        return Product::create([
            'nama' => 'Batik Pembatalan Uji',
            'sku' => 'BTK-CANCEL-'.uniqid(),
            'kategori' => 'Kain Batik',
            'harga' => 250000,
            'stok' => $stock,
            'stok_ukuran' => ['M' => $stock],
            'status' => 'Tersedia',
            'deskripsi' => 'Batik uji alur pembatalan',
            'material' => 'Sutra Alam',
        ]);
    }

    private function requestedPaidOrderFor(
        User $customer,
        string $status = 'Diproses',
        ?Product $product = null
    ): Order {
        $order = Order::factory()->for($customer)->create([
            'status' => $status,
            'payment_status' => Order::PAYMENT_PAID,
            'cancel_requested_at' => now(),
        ]);

        if ($product) {
            $order->items()->create([
                'produk_id' => $product->id,
                'produk_name' => 'Batik Pembatalan Uji (M)',
                'price' => 250000,
                'quantity' => 2,
                'subtotal' => 500000,
            ]);
        }

        return $order;
    }

    public function test_admin_approval_cancels_paid_order_and_restores_stock(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $product = $this->createProductWithStock(5);
        $order = $this->requestedPaidOrderFor($customer, 'Diproses', $product);

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.cancel', $order));

        $response->assertSessionHas('success');

        $order->refresh();
        $product->refresh();

        $this->assertSame('Dibatalkan', $order->status);
        $this->assertSame(Order::PAYMENT_CANCELLED, $order->payment_status);
        $this->assertNull($order->cancel_requested_at);
        $this->assertSame(7, $product->getStokForUkuran('M'));
    }

    public function test_admin_can_approve_request_with_raw_status_sudah_dibayar(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $order = $this->requestedPaidOrderFor($customer, 'Sudah Dibayar');

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.cancel', $order));

        $response->assertSessionHas('success');
        $this->assertSame('Dibatalkan', $order->refresh()->status);
    }

    public function test_admin_reject_clears_request_and_keeps_order(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $product = $this->createProductWithStock(5);
        $order = $this->requestedPaidOrderFor($customer, 'Diproses', $product);

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.reject-cancellation', $order));

        $response->assertSessionHas('success');

        $order->refresh();
        $product->refresh();

        $this->assertSame('Diproses', $order->status);
        $this->assertNull($order->cancel_requested_at);
        $this->assertSame(5, $product->getStokForUkuran('M'));
    }

    public function test_admin_reject_without_request_returns_error(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $order = Order::factory()->for($customer)->diproses()->create();

        $response = $this->actingAs($admin)
            ->patch(route('admin.orders.reject-cancellation', $order));

        $response->assertSessionHas('errors');
        $this->assertSame('Diproses', $order->refresh()->status);
    }

    public function test_admin_orders_index_shows_cancellation_request_badge(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $this->requestedPaidOrderFor($customer);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertOk()->assertSee('Ajukan Pembatalan');
    }

    public function test_admin_show_page_offers_approve_and_reject_buttons(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $order = $this->requestedPaidOrderFor($customer);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk()
            ->assertSee('Setujui Pembatalan')
            ->assertSee('Tolak Pembatalan');
    }

    public function test_unpaid_cancel_button_on_show_page_is_red(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();
        $order = Order::factory()->for($customer)->belumDibayar()->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk()
            ->assertSee('Batalkan Pesanan (Expire)')
            ->assertSee('bg-[#C62828]', false);
    }
}
