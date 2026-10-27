<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the pesanan page renders successfully.
     */
    public function test_pesanan_page_renders_successfully(): void
    {
        $response = $this->get(route('pesanan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pesanan Saya');
        $response->assertSee('Menunggu Pembayaran');
    }

    /**
     * Test navigation contains link to pesanan across customer pages.
     */
    public function test_navigation_contains_pesanan_link(): void
    {
        $pesananUrl = route('pesanan.index');

        $this->get('/')->assertSee($pesananUrl);
        $this->get(route('koleksi.index'))->assertSee($pesananUrl);
        $this->get(route('keranjang.index'))->assertSee($pesananUrl);
    }

    /**
     * Test guest visitor sees empty orders list before login even when orders exist.
     */
    public function test_guest_sees_empty_orders_before_login(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'code' => 'ORD-GUEST-TEST-99',
            'customer_name' => 'John Doe',
        ]);

        $response = $this->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertViewHas('orders', function ($orders) {
            return $orders->isEmpty();
        });
        $response->assertSee('Anda Belum Masuk Akun');
        $response->assertSee('Pesanan Kosong');
        $response->assertDontSee($order->code);
    }

    /**
     * Test authenticated customer only sees their own orders.
     */
    public function test_authenticated_customer_sees_only_own_orders(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);

        $orderA = Order::factory()->create([
            'user_id' => $userA->id,
            'code' => 'ORD-USER-A-01',
        ]);
        $orderB = Order::factory()->create([
            'user_id' => $userB->id,
            'code' => 'ORD-USER-B-02',
        ]);

        $response = $this->actingAs($userA)->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertSee($orderA->code);
        $response->assertDontSee($orderB->code);
        $response->assertDontSee('Anda Belum Masuk Akun');
    }

    /**
     * Test authenticated customer with no orders sees empty orders list.
     */
    public function test_authenticated_customer_without_orders_sees_empty_orders(): void
    {
        $userWithOrder = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $userWithOrder->id,
            'code' => 'ORD-OTHER-01',
        ]);

        $freshUser = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($freshUser)->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertViewHas('orders', function ($orders) {
            return $orders->isEmpty();
        });
        $response->assertDontSee($order->code);
        $response->assertSee('Belum Ada Pesanan yang Sesuai');
    }

    /**
     * Test expired payment order automatically transitions to Dibatalkan and restores stock.
     */
    public function test_expired_payment_order_status_changes_to_dibatalkan_when_viewing_pesanan(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $produk = Produk::create([
            'nama' => 'Batik Tulis Sogan Klasik',
            'sku' => 'BTK-EXP-001',
            'kategori' => 'Kain Batik',
            'harga' => 150000,
            'stok' => 5,
            'status' => 'Tersedia',
            'deskripsi' => 'Batik tulis autentik nusantara',
            'material' => 'Katun Primissima',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'code' => 'ORD-EXP-001',
            'status' => 'Menunggu Pembayaran',
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_deadline' => now()->subMinutes(10),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'produk_id' => $produk->id,
            'produk_name' => $produk->nama,
            'price' => 150000,
            'quantity' => 2,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($user)->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertSee('ORD-EXP-001');
        $response->assertSee('Dibatalkan');

        $order->refresh();
        $this->assertSame('Dibatalkan', $order->status);
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);

        // Product stock restored from 5 to 7
        $produk->refresh();
        $this->assertSame(7, $produk->stok);
    }

    /**
     * Test visiting payment page for expired order redirects to pesanan.index with error.
     */
    public function test_visiting_payment_page_for_expired_order_redirects_to_pesanan_with_error(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'code' => 'ORD-EXP-002',
            'status' => 'Menunggu Pembayaran',
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_deadline' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->get(route('pembayaran', $order->code));

        $response->assertRedirect(route('pesanan.index'));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertSame('Dibatalkan', $order->status);
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);
    }
}
