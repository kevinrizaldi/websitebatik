<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StlcVerificationTest extends TestCase
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

    private function createProductWithStock(int $stock = 5, ?array $sizeStock = null): Product
    {
        $sizeStock = $sizeStock ?? ['M' => $stock];

        return Product::create([
            'nama' => 'Batik Sutra Parang Kusumo',
            'sku' => 'BTK-TEST-'.uniqid(),
            'kategori' => 'Kain Batik',
            'harga' => 250000,
            'stok' => $stock,
            'stok_ukuran' => $sizeStock,
            'status' => 'Tersedia',
            'deskripsi' => 'Batik eksklusif',
            'material' => 'Sutra Alam',
        ]);
    }

    /**
     * TC-STLC-01: Verifikasi perlindungan manipulasi ongkos kirim.
     * Pembeli tidak boleh mendapatkan ongkir Rp 0 jika belanjaan di bawah Rp 955.000.
     */
    public function test_shipping_cost_tampering_is_prevented(): void
    {
        $customer = $this->createCustomer();
        $this->actingAs($customer);

        $product = $this->createProductWithStock(10);

        // Tambah ke keranjang
        $this->postJson(route('keranjang.store'), [
            'produk_id' => $product->id,
            'qty' => 1,
            'ukuran' => 'M',
        ])->assertStatus(200);

        // Checkout dengan mencoba memanipulasi shipping_cost menjadi 0
        $response = $this->post(route('checkout.process'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Slamet Riyadi No. 12, Solo',
            'shipping_cost' => 0, // Manipulasi nilai 0
            'shipping_option' => 'JNE Reguler',
        ]);

        $order = Order::where('customer_name', 'Budi Santoso')->first();
        $this->assertNotNull($order);
        // Ongkir harus otomatis dipaksa ke tarif default Rp 20.000, bukan Rp 0!
        $this->assertEquals(20000, (float) $order->shipping_cost);
        $this->assertEquals(270000, (float) $order->total_price); // 250.000 + 20.000
    }

    /**
     * TC-STLC-02: Verifikasi pemulihan stok batik saat Admin membatalkan pesanan.
     */
    public function test_admin_cancellation_restores_inventory(): void
    {
        $customer = $this->createCustomer();
        $admin = $this->createAdmin();

        $product = $this->createProductWithStock(5, ['M' => 5]);

        // Checkout produk (stok berkurang dari 5 menjadi 4)
        $this->actingAs($customer);
        $this->postJson(route('keranjang.store'), [
            'produk_id' => $product->id,
            'qty' => 1,
            'ukuran' => 'M',
        ]);

        $this->post(route('checkout.process'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Slamet Riyadi No. 12, Solo',
        ]);

        $product->refresh();
        $this->assertEquals(4, $product->getStokForUkuran('M'));

        $order = Order::where('user_id', $customer->id)->latest()->first();

        // Admin membatalkan pesanan
        $this->actingAs($admin);
        $this->patch(route('admin.orders.cancel', $order));

        $order->refresh();
        $product->refresh();

        $this->assertEquals('Dibatalkan', $order->status);
        $this->assertEquals(Order::PAYMENT_CANCELLED, $order->payment_status);
        // Stok harus dipulihkan kembali menjadi 5
        $this->assertEquals(5, $product->getStokForUkuran('M'));
    }

    /**
     * TC-STLC-03: Verifikasi webhook Midtrans menolak signature key palsu (HTTP 403).
     */
    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $order = Order::create([
            'user_id' => $this->createCustomer()->id,
            'code' => 'ORD-TEST-SIGNATURE',
            'customer_name' => 'Test User',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'total_price' => 100000,
            'status' => 'Menunggu Pembayaran',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        $fakePayload = [
            'order_id' => $order->code,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'FAKE_INVALID_SIGNATURE_KEY_12345',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $fakePayload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature.']);
    }

    /**
     * TC-STLC-04: Verifikasi webhook status expire otomatis membatalkan order dan memulihkan stok seketika.
     */
    public function test_midtrans_expire_webhook_cancels_order_and_restores_stock(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProductWithStock(3, ['M' => 3]);

        $this->actingAs($customer);
        $this->postJson(route('keranjang.store'), [
            'produk_id' => $product->id,
            'qty' => 1,
            'ukuran' => 'M',
        ]);

        $this->post(route('checkout.process'), [
            'customer_name' => 'Pelanggan Expire',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
        ]);

        $product->refresh();
        $this->assertEquals(2, $product->getStokForUkuran('M'));

        $order = Order::where('customer_name', 'Pelanggan Expire')->first();

        // Buat percobaan payment
        $midtransOrderId = $order->code.'-attempt1';
        $payment = Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $midtransOrderId,
            'snap_token' => 'SNAP_TEST_TOKEN',
            'status' => Payment::STATUS_PENDING,
            'gross_amount' => $order->total_price,
            'expires_at' => now()->addHours(24),
        ]);

        $serverKey = config('midtrans.server_key');
        $statusCode = '202';
        $grossAmount = number_format((float) $order->total_price, 2, '.', '');
        $validSignature = hash('sha512', $midtransOrderId.$statusCode.$grossAmount.$serverKey);

        $expirePayload = [
            'order_id' => $midtransOrderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'expire',
        ];

        $response = $this->postJson(route('midtrans.notification'), $expirePayload);
        $response->assertStatus(200);

        $order->refresh();
        $product->refresh();
        $payment->refresh();

        $this->assertEquals('Dibatalkan', $order->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $payment->status);
        // Stok harus seketika dipulihkan kembali ke 3
        $this->assertEquals(3, $product->getStokForUkuran('M'));
    }
}
