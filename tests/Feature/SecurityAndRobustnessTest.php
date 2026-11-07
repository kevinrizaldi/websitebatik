<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndRobustnessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pastikan role tidak dapat di-injeksi melalui mass-assignment.
     */
    public function test_user_role_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Attacker User',
            'email' => 'attacker@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin', // Mencoba menginjeksi role admin
        ]);

        $this->assertEquals('customer', $user->fresh()->role);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    /**
     * Route /produk-detail dengan ID invalid harus mengembalikan 404, bukan fallback ke produk lain.
     */
    public function test_product_detail_returns_404_when_not_found(): void
    {
        Product::create([
            'nama' => 'Batik Tulis Solo',
            'sku' => 'BTK-SKU-'.uniqid(),
            'harga' => 250000,
            'stok' => 10,
            'kategori' => 'Pria',
            'deskripsi' => 'Batik tulis halus solo',
            'size_stock' => ['M' => 10],
        ]);

        // Akses ID yang tidak ada
        $response = $this->get('/produk-detail/999999');
        $response->assertStatus(404);

        // Akses tanpa ID
        $responseEmpty = $this->get('/produk-detail');
        $responseEmpty->assertStatus(404);
    }

    /**
     * Response web harus memiliki security headers perlindungan (X-Frame-Options, nosniff).
     */
    public function test_security_headers_are_applied(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Midtrans webhook fallback harus menolak gross_amount yang tidak cocok dengan total tagihan.
     */
    public function test_midtrans_webhook_rejects_gross_amount_mismatch(): void
    {
        config(['midtrans.server_key' => 'SB-Mid-server-test-key-mock']);

        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id,
            'code' => 'ORD-TEST-1234',
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl Solo No 1',
            'total_price' => 150000,
            'shipping_cost' => 20000,
            'payment_method' => 'Midtrans',
            'status' => 'Menunggu Pembayaran',
        ]);

        // Attacker mencoba bayar Rp 10.000 untuk order seharga Rp 150.000
        $tamperedAmount = '10000.00';
        $signature = hash('sha512', $order->code.'200'.$tamperedAmount.config('midtrans.server_key'));

        $payload = [
            'order_id' => $order->code,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        // Harus ditolak (404/400) dan order status tetap Menunggu Pembayaran
        $this->assertNotEquals(200, $response->getStatusCode());
        $this->assertEquals('Menunggu Pembayaran', $order->fresh()->status);
    }
}
