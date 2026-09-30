<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Midtrans\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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

    // ── Test 1: Owner gets a snap_token; Payment attempt created ──────────────

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
        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertSame('tok-abc-123', $payment->snap_token);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
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
            'amount' => 1,
            'gross_amount' => 1,
            'total' => 999,
        ]);

        $this->assertNotNull($capturedParams);

        $grossAmount = $capturedParams['transaction_details']['gross_amount'];

        $itemSum = 0;
        foreach ($capturedParams['item_details'] as $item) {
            $itemSum += $item['price'] * $item['quantity'];
        }

        $this->assertSame($itemSum, $grossAmount);
        $this->assertSame(320000, $grossAmount);
    }

    // ── Test 4: Non-payable order => 422 ─────────────────────────────────────

    public function test_non_payable_status_returns_422(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'Menunggu Konfirmasi',
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
            'payment_status' => Order::PAYMENT_PAID,
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

        $this->assertSame(1, $callCount);
    }

    // ── Test 6: Webhook with invalid signature => 403 and no DB change ────────

    public function test_webhook_invalid_signature_returns_403(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-TEST-001',
            'gross_amount' => 200000,
        ]);

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
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-SETTLE-001',
            'gross_amount' => 200000,
        ]);

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

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);

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
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-IDEM-001',
            'gross_amount' => 200000,
        ]);

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

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);

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
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PAID,
        ]);
        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-EXPIRE-001',
            'gross_amount' => 200000,
        ]);

        $payload = [
            'order_id' => 'ORD-EXPIRE-001',
            'status_code' => '407',
            'gross_amount' => '200000.00',
            'signature_key' => $this->buildSignature('ORD-EXPIRE-001', '407', '200000.00'),
            'transaction_status' => 'expire',
        ];

        $this->postJson('/midtrans/notification', $payload)->assertStatus(200);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);

        $order->refresh();
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
            'status' => 'Diproses',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-DIPROSES-001',
            'gross_amount' => 200000,
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
        $this->assertSame('Diproses', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    // ── Test 11: Gross amount mismatch => 400 ─────────────────────────────────

    public function test_webhook_gross_amount_mismatch_returns_400(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0); // server = 200000
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-MISMATCH-001',
            'gross_amount' => 200000,
        ]);

        $tampered = '999999.00';
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

    // ── Test 13 (renamed): expire on pending attempt marks attempt expired and leaves order pending

    public function test_expire_on_pending_attempt_marks_attempt_expired_and_leaves_order_pending(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-EXP-001',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
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

        $payment->refresh();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->status);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
    }

    // ── HT-1: buildItemDetails returns correct gross_amount and item rows ─────

    public function test_build_item_details_returns_correct_gross_amount_and_rows(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 75000, 3, 10000);

        $service = new MidtransService;
        $result = $service->buildItemDetails($order);

        $this->assertSame(235000, $result['gross_amount']);
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

        $service = new MidtransService;

        $this->expectException(\InvalidArgumentException::class);
        $service->buildItemDetails($order);
    }

    // ── HT-3: webhook controller uses buildItemDetails (same source of truth) --

    public function test_webhook_gross_amount_uses_build_item_details_as_source_of_truth(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 50000, 4, 5000);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-SOT-001',
            'gross_amount' => 205000,
        ]);

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
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }

    // ── HT-4: verifySignature rejects non-string field (integer 0 status_code) -

    public function test_verify_signature_rejects_non_string_field(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-VSTR-001',
            'gross_amount' => 200000,
        ]);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-VSTR-001',
            'status_code' => 0,
            'gross_amount' => '200000.00',
            'signature_key' => 'whatever',
            'transaction_status' => 'settlement',
        ])->assertStatus(400);
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
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-RAWKEY-001',
            'gross_amount' => 200000,
        ]);

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

        $stored = $payment->fresh()->raw_notification;

        $this->assertArrayNotHasKey('signature_key', (array) $stored);
        $this->assertArrayHasKey('transaction_status', (array) $stored);
    }

    // ── HT-7: token endpoint respects throttle (429 on 11th request) ──────────

    public function test_token_endpoint_is_throttled_after_ten_requests(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $this->mockSnapToken('tok-throttle');

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);
        }

        $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id])
            ->assertStatus(429);
    }

    // ── HT-8: 422 body does not leak internal exception message ──────────────

    public function test_non_payable_order_422_body_does_not_contain_internals(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'Menunggu Konfirmasi',
            'payment_status' => Order::PAYMENT_PENDING,
            'shipping_cost' => 0,
        ]);
        OrderItem::factory()->create(['order_id' => $order->id, 'produk_id' => null]);

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(422);

        $body = $response->getContent();
        $this->assertStringNotContainsString('payment_status=', $body);
        $this->assertStringNotContainsString('Money value', $body);
        $this->assertStringNotContainsString('Order #', $body);
    }

    // ── HT-9: Webhook with fractional shipping_cost returns 400, no state change

    public function test_webhook_fractional_shipping_cost_returns_400_and_no_state_change(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_UNPAID,
            'payment_status' => Order::PAYMENT_PENDING,
            'shipping_cost' => 15000.50,
            'total_price' => 215000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'produk_id' => null,
            'produk_name' => 'Batik Test',
            'price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-FRAC-001',
            'gross_amount' => 215000,
        ]);

        $grossAmount = '215000.00';
        $sig = $this->buildSignature('ORD-FRAC-001', '200', $grossAmount);

        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-FRAC-001',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(400);

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    // ── HT-10: Webhook with fraud_status as array returns 400, not 500 ────────

    public function test_webhook_fraud_status_as_array_returns_400_not_500(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-FRAUDARR-001',
            'gross_amount' => 200000,
        ]);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-FRAUDARR-001', '200', $grossAmount);

        $response = $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-FRAUDARR-001',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'capture',
            'fraud_status' => ['accept', 'challenge'],
        ]);

        $response->assertStatus(400);

        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    // ── PART E New Tests ───────────────────────────────────────────────────────

    // ── 1. Token creates one payments row and writes nothing to orders.midtrans_order_id / snap_token ──

    public function test_token_creates_one_payments_row_and_writes_nothing_to_orders(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 120000, 2, 10000); // 120000*2 + 10000 = 250000

        $this->mockSnapToken('tok-payment-attempt-1');

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(200);

        $this->assertDatabaseCount('payments', 1);

        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertSame($order->id, $payment->order_id);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('250000.00', (string) $payment->gross_amount);
        $this->assertSame('tok-payment-attempt-1', $payment->snap_token);
        $this->assertNotEmpty($payment->midtrans_order_id);

        $order->refresh();
        $this->assertNull($order->midtrans_order_id);
        $this->assertNull($order->snap_token);
    }

    // ── 2. A second token call returns the same token and requestSnapToken is called exactly once ──

    public function test_second_token_call_returns_same_token_and_requests_snap_once(): void
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

                return 'tok-once-only';
            });

        $this->app->instance(MidtransService::class, $mock);

        $res1 = $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);
        $res2 = $this->actingAs($user)->postJson('/payment/token', ['order_id' => $order->id]);

        $res1->assertStatus(200);
        $res2->assertStatus(200);

        $this->assertSame('tok-once-only', $res1->json('snap_token'));
        $this->assertSame('tok-once-only', $res2->json('snap_token'));
        $this->assertSame(1, $callCount);
        $this->assertDatabaseCount('payments', 1);
    }

    // ── 3. requestSnapToken throws -> no payments row exists afterwards, response is 502 ──

    public function test_request_snap_token_throws_persists_no_payment_and_returns_502(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $mock = $this->getMockBuilder(MidtransService::class)
            ->onlyMethods(['requestSnapToken'])
            ->getMock();

        $mock->method('requestSnapToken')
            ->willThrowException(new \Exception('Midtrans API connection timeout'));

        $this->app->instance(MidtransService::class, $mock);

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(502);
        $this->assertDatabaseCount('payments', 0);
    }

    // ── 4. Active attempt with expires_at in the past -> a new attempt with a different midtrans_order_id is created ──

    public function test_active_attempt_expired_creates_new_attempt_with_different_midtrans_order_id(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        // Previous attempt that expired 10 minutes ago
        $oldPayment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-OLD-EXPIRED',
            'snap_token' => 'tok-old-expired',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->subMinutes(10),
        ]);

        $this->mockSnapToken('tok-new-attempt');

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(200);
        $this->assertSame('tok-new-attempt', $response->json('snap_token'));

        $this->assertDatabaseCount('payments', 2);
        $newPayment = Payment::where('id', '!=', $oldPayment->id)->first();
        $this->assertNotNull($newPayment);
        $this->assertNotSame($oldPayment->midtrans_order_id, $newPayment->midtrans_order_id);
        $this->assertSame('tok-new-attempt', $newPayment->snap_token);
        $this->assertTrue($newPayment->expires_at->isFuture());
    }

    // ── 5. Attempt with status failed -> a new attempt is created ─────────────

    public function test_attempt_with_status_failed_creates_new_attempt(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        // Failed attempt (e.g. card declined)
        $failedPayment = Payment::factory()->failed()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-FAILED-1',
            'snap_token' => 'tok-failed',
            'expires_at' => now()->addHours(24),
        ]);

        $this->mockSnapToken('tok-new-after-failed');

        $response = $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id]);

        $response->assertStatus(200);
        $this->assertSame('tok-new-after-failed', $response->json('snap_token'));

        $this->assertDatabaseCount('payments', 2);
        $newPayment = Payment::where('id', '!=', $failedPayment->id)->first();
        $this->assertNotNull($newPayment);
        $this->assertSame(Payment::STATUS_PENDING, $newPayment->status);
        $this->assertSame('tok-new-after-failed', $newPayment->snap_token);
    }

    // ── 6. Webhook pending with va_numbers and QRIS details ───────────────────

    public function test_webhook_pending_stores_payment_details_and_handles_whitelist(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-PEND-DETAILS',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-PEND-DETAILS', '201', $grossAmount);

        $payload = [
            'order_id' => 'ORD-PEND-DETAILS',
            'status_code' => '201',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'TXN-PEND-01',
            'expiry_time' => '2026-10-01 16:30:00', // Asia/Jakarta (WIB)
            'va_numbers' => [
                ['bank' => 'bca', 'va_number' => '1234567890'],
            ],
            'actions' => [
                [
                    'name' => 'generate-qr-code',
                    'method' => 'GET',
                    'url' => 'https://api.sandbox.midtrans.com/v2/qris/123/qr-code',
                ],
                [
                    'name' => 'malicious-action',
                    'method' => 'GET',
                    'url' => 'https://evil.attacker.com/steal-data',
                ],
            ],
            'qr_string' => '00020101021226680016ID.CO.MIDTRANS.WWW...',
            'fraud_status' => 'accept', // Should be ignored / dropped from payment_details
            'status_message' => 'Success, transaction is found', // Should be dropped
        ];

        $response = $this->postJson('/midtrans/notification', $payload);
        $response->assertStatus(200);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('bank_transfer', $payment->payment_type);
        $this->assertSame('TXN-PEND-01', $payment->transaction_id);

        // Check expires_at converted from WIB
        $this->assertNotNull($payment->expires_at);
        $expectedUtc = Carbon::parse('2026-10-01 16:30:00', 'Asia/Jakarta')->setTimezone(config('app.timezone'));
        $this->assertSame($expectedUtc->toDateTimeString(), $payment->expires_at->toDateTimeString());

        // Check payment_details
        $details = $payment->payment_details;
        $this->assertIsArray($details);
        $this->assertSame([['bank' => 'bca', 'va_number' => '1234567890']], $details['va_numbers']);
        $this->assertSame('00020101021226680016ID.CO.MIDTRANS.WWW...', $details['qr_string']);

        // Check malicious action is dropped, valid action is kept
        $this->assertCount(1, $details['actions']);
        $this->assertSame('https://api.sandbox.midtrans.com/v2/qris/123/qr-code', $details['actions'][0]['url']);

        // Check non-whitelisted keys not in payment_details
        $this->assertArrayNotHasKey('fraud_status', $details);
        $this->assertArrayNotHasKey('status_message', $details);
        $this->assertArrayNotHasKey('signature_key', $details);

        // Order remains unchanged
        $order->refresh();
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    // ── 7. Webhook settlement -> attempt paid, order paid, paid_at set ─────────

    public function test_webhook_settlement_marks_attempt_paid_and_order_paid_and_is_idempotent(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-SETTLE-FULL',
            'gross_amount' => 200000,
        ]);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-SETTLE-FULL', '200', $grossAmount);

        $payload = [
            'order_id' => 'ORD-SETTLE-FULL',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'TXN-QRIS-01',
        ];

        // First call
        $r1 = $this->postJson('/midtrans/notification', $payload);
        $r1->assertStatus(200);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame('qris', $payment->payment_type);
        $this->assertSame('TXN-QRIS-01', $payment->transaction_id);
        $this->assertNotNull($payment->paid_at);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertNotNull($order->paid_at);

        // Second call (idempotent)
        $r2 = $this->postJson('/midtrans/notification', $payload);
        $r2->assertStatus(200);
        $this->assertSame('OK (already paid)', $r2->json('message'));
    }

    // ── 8. Webhook deny -> attempt failed, order unchanged; then settlement -> paid ──

    public function test_webhook_deny_marks_attempt_failed_then_subsequent_settlement_marks_paid(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-DENY-THEN-PAY',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $grossAmount = '200000.00';

        // 1. Webhook deny
        $sigDeny = $this->buildSignature('ORD-DENY-THEN-PAY', '202', $grossAmount);
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-DENY-THEN-PAY',
            'status_code' => '202',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigDeny,
            'transaction_status' => 'deny',
        ])->assertStatus(200);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_FAILED, $payment->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);

        // 2. Subsequent settlement for same attempt
        $sigSettle = $this->buildSignature('ORD-DENY-THEN-PAY', '200', $grossAmount);
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-DENY-THEN-PAY',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigSettle,
            'transaction_status' => 'settlement',
            'payment_type' => 'credit_card',
            'transaction_id' => 'TXN-CC-RETRY',
        ])->assertStatus(200);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    // ── 9. Webhook expire and cancel on an active attempt -> attempt expired/cancelled, order still payable ──

    public function test_webhook_expire_and_cancel_on_active_attempt_leaves_order_payable(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $payment1 = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-CANCEL-ACTIVE',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $grossAmount = '200000.00';
        $sigCancel = $this->buildSignature('ORD-CANCEL-ACTIVE', '200', $grossAmount);

        // Cancel notification
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-CANCEL-ACTIVE',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigCancel,
            'transaction_status' => 'cancel',
        ])->assertStatus(200);

        $payment1->refresh();
        $this->assertSame(Payment::STATUS_CANCELLED, $payment1->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);

        // Expire notification on a second attempt
        $payment2 = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-EXPIRE-ACTIVE',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $sigExpire = $this->buildSignature('ORD-EXPIRE-ACTIVE', '407', $grossAmount);
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-EXPIRE-ACTIVE',
            'status_code' => '407',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigExpire,
            'transaction_status' => 'expire',
        ])->assertStatus(200);

        $payment2->refresh();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment2->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    // ── 10. Replaced attempt logic: cancel/expire ignored; settlement handles local cancellation or mismatch ──

    public function test_replaced_attempt_settlement_and_cancellation_logic(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0); // gross = 200000

        $replacedAttempt = Payment::factory()->replaced()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-REPLACED-01',
            'gross_amount' => 200000,
        ]);

        $activePendingAttempt = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-ACTIVE-PENDING',
            'gross_amount' => 200000,
            'status' => Payment::STATUS_PENDING,
        ]);

        $grossAmount = '200000.00';

        // 1. Cancel notification on replaced attempt is ignored
        $sigCancel = $this->buildSignature('ORD-REPLACED-01', '200', $grossAmount);
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-REPLACED-01',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigCancel,
            'transaction_status' => 'cancel',
        ])->assertStatus(200);

        $this->assertSame(Payment::STATUS_REPLACED, $replacedAttempt->fresh()->status);

        // 2. Settlement on replaced attempt with matching amount -> marks order paid, cancels other pending attempts
        Log::spy();

        $sigSettle = $this->buildSignature('ORD-REPLACED-01', '200', $grossAmount);
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-REPLACED-01',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sigSettle,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        Log::shouldHaveReceived('warning')
            ->with('Midtrans: cancelled pending payment attempt locally', [
                'order_id' => $order->id,
                'payment_id' => $activePendingAttempt->id,
            ]);

        $this->assertSame(Payment::STATUS_PAID, $replacedAttempt->fresh()->status);
        $this->assertSame(Payment::STATUS_CANCELLED, $activePendingAttempt->fresh()->status);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);

        // 3. Settlement whose attempt gross_amount differs from current server amount -> 400
        $order2 = $this->makePayableOrder($user, 100000, 2, 0); // server = 200000
        $differingAttempt = Payment::factory()->replaced()->create([
            'order_id' => $order2->id,
            'midtrans_order_id' => 'ORD-DIFF-01',
            'gross_amount' => 150000, // differs from server 200000
        ]);

        $sigDiff = $this->buildSignature('ORD-DIFF-01', '200', '150000.00');
        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-DIFF-01',
            'status_code' => '200',
            'gross_amount' => '150000.00',
            'signature_key' => $sigDiff,
            'transaction_status' => 'settlement',
        ])->assertStatus(400);

        $this->assertSame(Payment::STATUS_REPLACED, $differingAttempt->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order2->fresh()->payment_status);
    }

    // ── 11. Settlement for an order with status 'Batal' -> attempt paid, order untouched, warning logged ──

    public function test_settlement_for_cancelled_order_marks_attempt_paid_and_leaves_order_untouched(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update(['status' => Order::STATUS_CANCELLED]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-BATAL-SETTLE',
            'gross_amount' => 200000,
        ]);

        Log::spy();

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-BATAL-SETTLE', '200', $grossAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-BATAL-SETTLE',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        Log::shouldHaveReceived('warning')
            ->with('Midtrans: payment received for cancelled order', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $order->status); // Untouched
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    // ── 12. Settlement when order is already paid by another attempt -> attempt paid, order untouched, warning logged ──

    public function test_settlement_for_already_paid_order_marks_attempt_paid_and_logs_warning(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0);
        $order->update([
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now()->subHour(),
        ]);

        $secondPayment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-SECOND-PAID',
            'gross_amount' => 200000,
        ]);

        Log::spy();

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-SECOND-PAID', '200', $grossAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-SECOND-PAID',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        Log::shouldHaveReceived('warning')
            ->with('Midtrans: duplicate payment for paid order', [
                'order_id' => $order->id,
                'payment_id' => $secondPayment->id,
            ]);

        $grossAmount = '200000.00';
        $sig = $this->buildSignature('ORD-SECOND-PAID', '200', $grossAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-SECOND-PAID',
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
        ])->assertStatus(200);

        $secondPayment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $secondPayment->status);

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    // ── 13. Payload gross_amount differing from attempt gross_amount -> 400, no change ──

    public function test_payload_gross_amount_differing_from_attempt_gross_returns_400(): void
    {
        $this->setTestServerKey();
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user, 100000, 2, 0); // 200000
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'ORD-ATTEMPT-DIFF',
            'gross_amount' => 200000,
        ]);

        $wrongAmount = '199999.00';
        $sig = $this->buildSignature('ORD-ATTEMPT-DIFF', '200', $wrongAmount);

        $this->postJson('/midtrans/notification', [
            'order_id' => 'ORD-ATTEMPT-DIFF',
            'status_code' => '200',
            'gross_amount' => $wrongAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
        ])->assertStatus(400);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    // ── 14. payment_deadline is set on first attempt creation ─────────────────

    public function test_payment_deadline_is_set_when_first_attempt_is_created(): void
    {
        config(['midtrans.payment_deadline_hours' => 24]);
        $this->mockSnapToken('tok-deadline');
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $this->assertNull($order->payment_deadline);

        $svc = app(MidtransService::class);
        $svc->getOrCreateSnapToken($order);

        $order->refresh();
        $this->assertNotNull($order->payment_deadline);
        $this->assertTrue($order->payment_deadline->isFuture());
        // deadline should be ~24 h from now
        $this->assertEqualsWithDelta(now()->addHours(24)->timestamp, $order->payment_deadline->timestamp, 5);
    }

    public function test_expires_at_is_capped_to_payment_deadline(): void
    {
        // Deadline = 1 h, attempt expiry = 24 h → expires_at should be ≤ deadline
        config(['midtrans.payment_deadline_hours' => 1, 'midtrans.expiry_hours' => 24]);
        $this->mockSnapToken('tok-cap');
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);

        $svc = app(MidtransService::class);
        $svc->getOrCreateSnapToken($order);

        $payment = Payment::where('order_id', $order->id)->latest('id')->first();
        $order->refresh();

        $this->assertNotNull($payment);
        $this->assertNotNull($order->payment_deadline);
        // expires_at must not exceed deadline
        $this->assertFalse($payment->expires_at->gt($order->payment_deadline));
    }

    public function test_token_endpoint_rejects_order_past_payment_deadline(): void
    {
        $this->mockSnapToken('tok-old');
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->payment_deadline = now()->subMinutes(5);
        $order->save();

        $this->actingAs($user)
            ->postJson('/payment/token', ['order_id' => $order->id])
            ->assertStatus(422);
    }

    // ── 15. ExpireOverdueOrders command ───────────────────────────────────────

    public function test_expire_overdue_orders_cancels_overdue_unpaid_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->payment_deadline = now()->subMinutes(10);
        $order->save();

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->artisan('orders:expire-overdue')->assertExitCode(0);

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertSame(Order::PAYMENT_EXPIRED, $order->payment_status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => Payment::STATUS_EXPIRED,
        ]);
    }

    public function test_expire_overdue_orders_does_not_cancel_paid_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->payment_deadline = now()->subMinutes(10);
        $order->payment_status = Order::PAYMENT_PAID;
        $order->status = Order::STATUS_PAID;
        $order->save();

        $this->artisan('orders:expire-overdue')->assertExitCode(0);

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
    }

    public function test_expire_overdue_orders_does_not_cancel_order_with_future_deadline(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePayableOrder($user);
        $order->payment_deadline = now()->addHours(2);
        $order->save();

        $this->artisan('orders:expire-overdue')->assertExitCode(0);

        $order->refresh();
        $this->assertSame(Order::STATUS_UNPAID, $order->status);
    }
}
