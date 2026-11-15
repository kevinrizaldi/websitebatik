<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiCheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Batik Checkout Test',
            'sku' => 'SKU-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Deskripsi produk uji checkout',
        ], $attributes));
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('mobile-app')->plainTextToken;
    }

    private function fakeMidtrans(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'SNAP-TEST-TOKEN',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/SNAP-TEST-TOKEN/redirect',
            ], 200),
            'https://api.sandbox.midtrans.com/*' => Http::response([], 404),
        ]);
    }

    private function seedSelectedCartItem(User $user, Product $product, int $qty = 2, ?string $size = 'M'): CartItem
    {
        return CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'size' => $size,
            'quantity' => $qty,
            'selected' => true,
        ]);
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1, Kota Yogyakarta',
            'shipping_option' => 'JNE Reguler',
            'shipping_cost' => 10000,
            'payment_method' => 'Midtrans Gateway',
        ], $overrides);
    }

    public function test_checkout_creates_order_and_returns_snap(): void
    {
        $this->fakeMidtrans();

        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 10]);
        $this->seedSelectedCartItem($user, $product, 2);

        $response = $this->withToken($token)->postJson('/api/checkout', $this->checkoutPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'code',
                    'status',
                    'total_price',
                    'shipping_cost',
                    'payment_deadline',
                    'order' => ['id', 'code', 'status', 'total_price', 'shipping_cost', 'payment_deadline'],
                    'snap_token',
                    'snap_redirect_url',
                ],
                'totals' => ['subtotal', 'shipping', 'grand_total'],
                'snap_token',
            ])
            ->assertJsonPath('data.snap_token', 'SNAP-TEST-TOKEN')
            ->assertJsonPath('snap_token', 'SNAP-TEST-TOKEN')
            ->assertJsonPath('data.status', 'Menunggu Pembayaran');

        $json = $response->json();

        // Plan-canonical nested order must mirror the flat legacy payload Flutter reads.
        $this->assertSame($json['data']['id'], $json['data']['order']['id']);
        $this->assertSame($json['data']['code'], $json['data']['order']['code']);
        $this->assertNotEmpty($json['data']['snap_redirect_url']);
        $this->assertSame('SNAP-TEST-TOKEN', $json['data']['snap_token']);

        // Legacy totals block kept for checkout_screen.dart.
        $this->assertSame(200000.0, (float) $json['totals']['subtotal']);
        $this->assertSame(10000.0, (float) $json['totals']['shipping']);
        $this->assertSame(210000.0, (float) $json['totals']['grand_total']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'Menunggu Pembayaran',
        ]);
    }

    public function test_checkout_accepts_plan_recipient_name_alias(): void
    {
        $this->fakeMidtrans();

        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 10]);
        $this->seedSelectedCartItem($user, $product, 1);

        $payload = $this->checkoutPayload([
            'recipient_name' => 'Siti Rahayu',
            'courier' => 'J&T Express',
            'note' => 'Titip di depan',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55271',
        ]);
        unset($payload['customer_name'], $payload['shipping_option'], $payload['notes']);

        $response = $this->withToken($token)->postJson('/api/checkout', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.order.customer_name', 'Siti Rahayu')
            ->assertJsonPath('data.customer_name', 'Siti Rahayu');
    }

    public function test_checkout_fails_when_stock_insufficient(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 2]);
        $this->seedSelectedCartItem($user, $product, 5);

        $response = $this->withToken($token)->postJson('/api/checkout', $this->checkoutPayload());

        $response->assertStatus(422)
            ->assertJsonStructure(['message'])
            ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'tidak mencukupi'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_orders_list_only_own(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = $this->tokenFor($user);

        Order::factory()->for($user)->diproses()->create(['code' => 'ORD-MINE-001']);
        Order::factory()->for($other)->dikirim()->create(['code' => 'ORD-THEIRS-001']);

        $response = $this->withToken($token)->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'code', 'status']]])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ORD-MINE-001');

        // Plan contract: optional status filter.
        $this->withToken($token)->getJson('/api/orders?status=Diproses')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ORD-MINE-001');

        $this->withToken($token)->getJson('/api/orders?status=Selesai')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_order_detail_403_for_other_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $strangerToken = $this->tokenFor($stranger);

        $order = Order::factory()->for($owner)->diproses()->create();

        $this->withToken($strangerToken)
            ->getJson("/api/orders/{$order->id}")
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->getJson("/api/orders/{$order->id}/payment")
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->postJson("/api/orders/{$order->id}/confirm")
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertStatus(403);
    }

    public function test_order_detail_includes_payment_block(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->diproses()->create();

        $response = $this->withToken($token)->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'status',
                    'items',
                    'payment' => ['status'],
                ],
            ])
            ->assertJsonPath('data.payment.status', $order->refresh()->payment_status);
    }

    public function test_payment_info_returns_snap_data(): void
    {
        $this->fakeMidtrans();

        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->create(['status' => 'Menunggu Pembayaran']);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->code,
            'snap_token' => 'SNAP-EXISTING',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/SNAP-EXISTING/redirect',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->withToken($token)->getJson("/api/orders/{$order->id}/payment");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['payment_status', 'snap_token', 'snap_redirect_url'],
            ])
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonPath('data.snap_token', 'SNAP-EXISTING')
            ->assertJsonPath('data.snap_redirect_url', 'https://app.sandbox.midtrans.com/snap/v1/transactions/SNAP-EXISTING/redirect');
    }

    public function test_payment_info_derives_redirect_from_token_when_missing(): void
    {
        $this->fakeMidtrans();

        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->create(['status' => 'Menunggu Pembayaran']);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->code,
            'snap_token' => 'SNAP-NO-REDIRECT',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->withToken($token)->getJson("/api/orders/{$order->id}/payment");

        $response->assertOk()
            ->assertJsonPath('data.snap_token', 'SNAP-NO-REDIRECT');

        $redirect = $response->json('data.snap_redirect_url');
        $this->assertNotEmpty($redirect);
        $this->assertStringContainsString('SNAP-NO-REDIRECT', $redirect);
    }

    public function test_confirm_received_changes_status(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->dikirim()->create();

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/confirm");

        $response->assertOk()
            ->assertJsonStructure(['message', 'data' => ['id', 'status']])
            ->assertJsonPath('data.status', 'Selesai');

        $this->assertSame('Selesai', $order->refresh()->status);
        $this->assertSame('Diterima', $order->pengiriman->refresh()->status_pengiriman);
    }

    public function test_confirm_received_invalid_state_returns_422(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->diproses()->create();

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/confirm");

        $response->assertStatus(422)->assertJsonStructure(['message']);
        $this->assertSame('Diproses', $order->refresh()->status);
    }

    public function test_cancel_restores_stock(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 3]);

        $order = Order::factory()->for($user)->belumDibayar()->create();
        $order->items()->create([
            'produk_id' => $product->id,
            'produk_name' => 'Batik Checkout Test',
            'price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonStructure(['message', 'data' => ['id', 'status']])
            ->assertJsonPath('data.status', 'Dibatalkan');

        $this->assertSame('Dibatalkan', $order->refresh()->status);
        $this->assertSame(5, $product->refresh()->stock);
        $this->assertSame('cancelled', $order->payment_status);
    }

    // Spec pindah: pesanan dibayar (Diproses) kini mengajukan pembatalan
    // yang disetujui admin — lihat ApiOrderCancellationTest.
}
