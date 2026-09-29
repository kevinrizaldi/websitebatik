<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Midtrans\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransSnapPaymentTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Create a payable order owned by $user with one item. */
    private function makePayableOrder(User $user, int $price = 100000, int $qty = 2, int $shipping = 0): Order
    {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_UNPAID,
            'payment_status' => Order::PAYMENT_PENDING,
            'shipping_cost' => $shipping,
            'total_price' => $price * $qty + $shipping,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'produk_id' => null,
            'produk_name' => 'Batik Test',
            'price' => $price,
            'quantity' => $qty,
            'subtotal' => $price * $qty,
        ]);

        return $order;
    }

    /** Build a valid Midtrans signature for webhook tests. */
    private function buildSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        $serverKey = config('midtrans.server_key');

        return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
    }

    /** Configure a test server key and return it. */
    private function setTestServerKey(): string
    {
        $key = 'TEST-SERVER-KEY-12345';
        config(['midtrans.server_key' => $key]);

        return $key;
    }

    /** Return a mock MidtransService that stubs requestSnapToken. */
    private function mockSnapToken(string $token = 'fake-snap-token'): void
    {
        $mock = $this->getMockBuilder(MidtransService::class)
            ->onlyMethods(['requestSnapToken'])
            ->getMock();

        $mock->method('requestSnapToken')->willReturn($token);

        $this->app->instance(MidtransService::class, $mock);
    }

    // ── Test 1: Owner gets a snap_token; midtrans_order_id and snap_token stored ──

    public function test_owner_gets_snap_token_and_token_is_stored(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $this->mockSnapToken('tok-abc-123');

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(200)
            ->assertJsonStructure(['snap_token', 'client_key']);

        $this->assertSame('tok-abc-123', $response->json('snap_token'));

        $order->refresh();
        $this->assertNotNull($order->midtrans_order_id);
        $this->assertSame('tok-abc-123', $order->snap_token);
    }

    // ── Test 2: Another user gets 403; guest is redirected ────────────────────

    public function test_other_user_gets_403(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($owner);

        $response = $this->actingAs($other)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_token_endpoint(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($owner);

        // JSON request from guest → 401 (expectsJson) or redirect
        $response = $this->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(401);
    }

    // ── Test 3: Tampered client input has no effect; gross_amount is correct ──

    public function test_tampered_client_amount_has_no_effect_and_gross_amount_matches(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 150000, 2, 20000); // 150000*2 + 20000 = 320000

        $capturedParams = null;
        $mock = $this->getMockBuilder(MidtransService::class)
            ->onlyMethods(['requestSnapToken'])
            ->getMock();

        $mock->method('requestSnapToken')
            ->willReturnCallback(function (array $params) use (&$capturedParams) {
                $capturedParams = $params;

                return 'tok-xyz';
            });

        $this->app->instance(MidtransService::class, $mock);

        // Client sends extra tampered fields — must be ignored
        $this->actingAs($user)->postJson('/payment/token', [
            'order_id' => $order->id,
            'amount' => 1,        // tampered
            'gross_amount' => 1,        // tampered
            'total' => 999,      // tampered
        ]);

        $this->assertNotNull($capturedParams);

        $grossAmount = $capturedParams['transaction_details']['gross_amount'];

        // Sum item_details
        $itemSum = 0;
        foreach ($capturedParams['item_details'] as $item) {
            $itemSum += $item['price'] * $item['quantity'];
        }

        $this->assertSame($itemSum, $grossAmount);
        $this->assertSame(320000, $grossAmount); // 150000*2 + 20000 = 320000
    }

    // ── Test 4: Non-payable order => 422 ─────────────────────────────────────

    public function test_non_payable_status_returns_422(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'Menunggu Konfirmasi', // not STATUS_UNPAID
            'payment_status' => Order::PAYMENT_PENDING,
            'shipping_cost' => 0,
        ]);
        OrderItem::factory()->create(['order_id' => $order->id, 'produk_id' => null]);

        $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id])
            ->assertStatus(422);
    }

    public function test_already_paid_order_returns_422(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_UNPAID,
            'payment_status' => Order::PAYMENT_PAID, // already paid
            'shipping_cost' => 0,
        ]);
        OrderItem::factory()->create(['order_id' => $order->id, 'produk_id' => null]);

        $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id])
            ->assertStatus(422);
    }

    // ── Test 5: Second token call returns same token, calls API only once ─────

    public function test_second_token_call_returns_same_token_without_new_api_call(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $callCount = 0;
        $mock = $this->getMockBuilder(MidtransService::class)
            ->onlyMethods(['requestSnapToken'])
            ->getMock();

        $mock->method('requestSnapToken')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;

                return 'tok-idempotent';
            });

        $this->app->instance(MidtransService::class, $mock);

        $r1 = $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);
        $r2 = $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);

        $r1->assertStatus(200);
        $r2->assertStatus(200);

        $this->assertSame('tok-idempotent', $r1->json('snap_token'));
        $this->assertSame('tok-idempotent', $r2->json('snap_token'));

        // API called only once
        $this->assertSame(1, $callCount);
    }

    // ── Test 6: Webhook with invalid signature => 403 and no DB change ────────

    public function test_webhook_invalid_signature_returns_403(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->update(['midtrans_order_id' => 'ORD-TEST-001']);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-TEST-001',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
        ])->assertStatus(403);

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    // ── Test 7: Valid settlement => paid, order status Sudah Dibayar ──────────

    public function test_valid_settlement_sets_payment_paid_and_order_status(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0); // gross = 200000
        $order->update(['midtrans_order_id' => 'ORD-SETTLE-001']);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-SETTLE-001', '200', $grossAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-SETTLE-001',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-001',
        ])->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertNotNull($order->paid_at);
    }

    // ── Test 8: Same settlement twice => both 200, state changed once ─────────

    public function test_duplicate_settlement_is_idempotent(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update(['midtrans_order_id' => 'ORD-IDEM-001']);

        $payload = [
            'order_id' => 'ORD-IDEM-001',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'signature_key' => $this->buildSignature('ORD-IDEM-001', '200', '200000.00'),
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-002',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);
        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);
    }

    // ── Test 9: expire after paid => no change ────────────────────────────────

    public function test_expire_notification_after_paid_does_not_change_state(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update([
            'midtrans_order_id' => 'ORD-EXPIRE-001',
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PAID,
        ]);

        $payload = [
            'order_id' => 'ORD-EXPIRE-001',
            'status_code' => '407',
            'gross_amount' => '200000.00',
            'signature_key' => $this->buildSignature('ORD-EXPIRE-001', '407', '200000.00'),
            'transaction_status' => 'expire',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);

        $order->refresh();
        // Must NOT change anything — payment is already final
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);
    }

    // ── Test 10: Settlement for 'Diproses' order keeps status Diproses ────────

    public function test_settlement_on_diproses_order_keeps_order_status(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update([
            'midtrans_order_id' => 'ORD-DIPROSES-001',
            'status' => 'Diproses',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        $payload = [
            'order_id' => 'ORD-DIPROSES-001',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'signature_key' => $this->buildSignature('ORD-DIPROSES-001', '200', '200000.00'),
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-003',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame('Diproses', $order->status); // NOT downgraded to Sudah Dibayar
        $this->assertNotNull($order->paid_at);
    }

    // ── Test 11: Gross amount mismatch => 400 ─────────────────────────────────

    public function test_webhook_gross_amount_mismatch_returns_400(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0); // server = 200000
        $order->update(['midtrans_order_id' => 'ORD-MISMATCH-001']);

        $tampered = '999999.00'; // wrong amount
        $payload = [
            'order_id' => 'ORD-MISMATCH-001',
            'status_code' => '200',
            'gross_amount' => $tampered,
            'signature_key' => $this->buildSignature('ORD-MISMATCH-001', '200', $tampered),
            'transaction_status' => 'settlement',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(400);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    // ── Test 12: Unknown midtrans_order_id with valid signature => 404 ─────────

    public function test_webhook_unknown_order_id_returns_404(): void
    {
        $this->setTestServerKey();

        $grossAmount = '100000.00';
        $orderId = 'ORD-UNKNOWN-999';

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $this->buildSignature($orderId, '200', $grossAmount),
            'transaction_status' => 'settlement',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(404);
    }

    // ── Test 13: expire on pending Belum Dibayar => expired + Batal ──────────

    public function test_expire_on_pending_order_sets_expired_and_batal(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update([
            'midtrans_order_id' => 'ORD-EXP-001',
            'status' => Order::STATUS_UNPAID,
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        $grossAmount = '200000.00';
        $payload = [
            'order_id' => 'ORD-EXP-001',
            'status_code' => '407',
            'gross_amount' => $grossAmount,
            'signature_key' => $this->buildSignature('ORD-EXP-001', '407', $grossAmount),
            'transaction_status' => 'expire',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);
        $this->assertSame(Order::STATUS_CANCELLED, $order->status); // 'Batal'
    }

    // ── NEW Hardening Tests ───────────────────────────────────────────────────

    // ── HT-1: buildItemDetails returns correct gross_amount and item rows ─────

    public function test_build_item_details_returns_correct_gross_amount_and_rows(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 75000, 3, 10000); // 75000*3 + 10000 = 235000

        $service = new MidtransService;
        $result = $service->buildItemDetails($order);

        $this->assertSame(235000, $result['gross_amount']);

        // Two rows: one product row + one SHIPPING row
        $this->assertCount(2, $result['item_details']);

        $productRow = $result['item_details'][0];
        $shippingRow = $result['item_details'][1];

        $this->assertSame(75000, $productRow['price']);
        $this->assertSame(3, $productRow['quantity']);

        $this->assertSame('SHIPPING', $shippingRow['id']);
        $this->assertSame(10000, $shippingRow['price']);
        $this->assertSame(1, $shippingRow['quantity']);
    }

    // ── HT-2: buildItemDetails throws when order has no items ─────────────────

    public function test_build_item_details_throws_for_order_with_no_items(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_UNPAID,
            'payment_status' => Order::PAYMENT_PENDING,
            'shipping_cost' => 0,
        ]);
        // No OrderItem created.

        $service = new MidtransService;

        $this->expectException(\InvalidArgumentException::class);
        $service->buildItemDetails($order);
    }

    // ── HT-3: webhook controller uses buildItemDetails (same source of truth) --

    public function test_webhook_gross_amount_uses_build_item_details_as_source_of_truth(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 50000, 4, 5000); // 50000*4 + 5000 = 205000
        $order->update(['midtrans_order_id' => 'ORD-SOT-001']);

        $correctAmount = '205000.00';
        $sig = $this->buildSignature('ORD-SOT-001', '200', $correctAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-SOT-001',
            'status_code' => '200',
            'gross_amount' => $correctAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-SOT-001',
        ])->assertStatus(200);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    // ── HT-4: verifySignature rejects non-string field (integer 0 status_code) -

    public function test_verify_signature_rejects_non_string_field(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->update(['midtrans_order_id' => 'ORD-VSTR-001']);

        // status_code sent as integer 0 instead of string
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-VSTR-001',
            'status_code' => 0,             // non-string
            'gross_amount' => '200000.00',
            'signature_key' => 'whatever',
            'transaction_status' => 'settlement',
        ])->assertStatus(400); // missing/invalid field
    }

    // ── HT-5: expiry_hours=0 config still sends duration >= 1 ─────────────────

    public function test_expiry_hours_zero_config_sends_minimum_duration_of_one(): void
    {
        config(['midtrans.expiry_hours' => 0]);
        $this->setTestServerKey();

        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $capturedParams = null;
        $mock = $this->getMockBuilder(MidtransService::class)
            ->onlyMethods(['requestSnapToken'])
            ->getMock();

        $mock->method('requestSnapToken')
            ->willReturnCallback(function (array $params) use (&$capturedParams) {
                $capturedParams = $params;

                return 'tok-expiry';
            });

        $this->app->instance(MidtransService::class, $mock);

        $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);

        $this->assertNotNull($capturedParams);
        $this->assertGreaterThanOrEqual(1, $capturedParams['expiry']['duration']);
    }

    // ── HT-6: raw_notification stored without signature_key ──────────────────

    public function test_raw_notification_stored_without_signature_key(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update(['midtrans_order_id' => 'ORD-RAWKEY-001']);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-RAWKEY-001', '200', $grossAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-RAWKEY-001',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-RAWKEY-001',
        ])->assertStatus(200);

        $stored = $order->fresh()->raw_notification;

        // signature_key must NOT be persisted
        $this->assertArrayNotHasKey('signature_key', (array) $stored);
        // Other fields are kept
        $this->assertArrayHasKey('transaction_status', (array) $stored);
    }

    // ── HT-7: token endpoint respects throttle (429 on 11th request) ──────────

    public function test_token_endpoint_is_throttled_after_ten_requests(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $this->mockSnapToken('tok-throttle');

        // Drain the 10-request allowance
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);
        }

        // 11th request must be rate-limited
        $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id])
            ->assertStatus(429);
    }
}
