<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_can_create_product_with_valid_price_and_stock_limits(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Tulis Premium',
            'sku' => 'BTK-001',
            'kategori' => 'Kain Batik',
            'harga' => 1000000,
            'stok' => 1000,
            'status' => 'Tersedia',
        ]);

        $response->assertRedirect(route('produk.index'));
        $this->assertDatabaseHas('produks', [
            'sku' => 'BTK-001',
            'harga' => 1000000,
            'stok' => 1000,
        ]);
    }

    public function test_can_create_product_with_zero_price_and_stock(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Sampel Gratis',
            'sku' => 'BTK-002',
            'kategori' => 'Kain Batik',
            'harga' => 0,
            'stok' => 0,
            'status' => 'Habis',
        ]);

        $response->assertRedirect(route('produk.index'));
        $this->assertDatabaseHas('produks', [
            'sku' => 'BTK-002',
            'harga' => 0,
            'stok' => 0,
        ]);
    }

    public function test_create_fails_when_price_exceeds_one_million(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Mahal',
            'sku' => 'BTK-003',
            'kategori' => 'Kain Batik',
            'harga' => 1000001,
            'stok' => 10,
            'status' => 'Tersedia',
        ]);

        $response->assertSessionHasErrors(['harga']);
    }

    public function test_create_fails_when_price_is_negative(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Minus',
            'sku' => 'BTK-004',
            'kategori' => 'Kain Batik',
            'harga' => -5000,
            'stok' => 10,
            'status' => 'Tersedia',
        ]);

        $response->assertSessionHasErrors(['harga']);
    }

    public function test_create_fails_when_stock_exceeds_one_thousand(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Stok Melimpah',
            'sku' => 'BTK-005',
            'kategori' => 'Kain Batik',
            'harga' => 50000,
            'stok' => 1001,
            'status' => 'Tersedia',
        ]);

        $response->assertSessionHasErrors(['stok']);
    }

    public function test_create_fails_when_stock_is_negative(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Stok Minus',
            'sku' => 'BTK-006',
            'kategori' => 'Kain Batik',
            'harga' => 50000,
            'stok' => -1,
            'status' => 'Tersedia',
        ]);

        $response->assertSessionHasErrors(['stok']);
    }

    public function test_update_fails_when_price_or_stock_out_of_bounds(): void
    {
        $produk = Produk::create([
            'nama' => 'Batik Awal',
            'sku' => 'BTK-007',
            'kategori' => 'Kain Batik',
            'harga' => 200000,
            'stok' => 50,
            'status' => 'Tersedia',
        ]);

        // Price > 1jt
        $response = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Batik Awal',
            'sku' => 'BTK-007',
            'kategori' => 'Kain Batik',
            'harga' => 1200000,
            'stok' => 50,
            'status' => 'Tersedia',
        ]);
        $response->assertSessionHasErrors(['harga']);

        // Price < 0
        $response = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Batik Awal',
            'sku' => 'BTK-007',
            'kategori' => 'Kain Batik',
            'harga' => -100,
            'stok' => 50,
            'status' => 'Tersedia',
        ]);
        $response->assertSessionHasErrors(['harga']);

        // Stock > 1000
        $response = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Batik Awal',
            'sku' => 'BTK-007',
            'kategori' => 'Kain Batik',
            'harga' => 200000,
            'stok' => 1005,
            'status' => 'Tersedia',
        ]);
        $response->assertSessionHasErrors(['stok']);

        // Stock < 0
        $response = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Batik Awal',
            'sku' => 'BTK-007',
            'kategori' => 'Kain Batik',
            'harga' => 200000,
            'stok' => -10,
            'status' => 'Tersedia',
        ]);
        $response->assertSessionHasErrors(['stok']);
    }

    public function test_can_update_product_within_valid_limits(): void
    {
        $produk = Produk::create([
            'nama' => 'Batik Valid',
            'sku' => 'BTK-008',
            'kategori' => 'Kain Batik',
            'harga' => 200000,
            'stok' => 50,
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Batik Valid Diperbarui',
            'sku' => 'BTK-008',
            'kategori' => 'Kain Batik',
            'harga' => 1000000,
            'stok' => 1000,
            'status' => 'Tersedia',
        ]);

        $response->assertRedirect(route('produk.index'));
        $this->assertDatabaseHas('produks', [
            'id' => $produk->id,
            'harga' => 1000000,
            'stok' => 1000,
        ]);
    }

    public function test_can_create_and_update_product_with_size_stock(): void
    {
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Kemeja Batik Modern',
            'sku' => 'KMT-001',
            'kategori' => 'Baju Batik',
            'harga' => 350000,
            'stok_ukuran' => [
                'S' => 2,
                'M' => 5,
                'L' => 0,
                'XL' => 3,
                'XXL' => 0,
            ],
        ]);

        $response->assertRedirect(route('produk.index'));
        $produk = Produk::where('sku', 'KMT-001')->first();
        $this->assertNotNull($produk);
        $this->assertSame(10, $produk->stok);
        $this->assertSame('Stok Menipis', $produk->status);
        $this->assertSame(5, $produk->getStokForUkuran('M'));
        $this->assertSame(0, $produk->getStokForUkuran('L'));
        $this->assertTrue($produk->isUkuranAvailable('M'));
        $this->assertFalse($produk->isUkuranAvailable('L'));

        // Update with modified sizes
        $updateResp = $this->actingAs($this->admin)->put(route('produk.update', $produk), [
            'nama' => 'Kemeja Batik Modern V2',
            'sku' => 'KMT-001',
            'kategori' => 'Baju Batik',
            'harga' => 375000,
            'stok_ukuran' => [
                'S' => 0,
                'M' => 4,
                'L' => 6,
                'XL' => 0,
                'XXL' => 0,
            ],
        ]);

        $updateResp->assertRedirect(route('produk.index'));
        $produk->refresh();
        $this->assertSame(10, $produk->stok);
        $this->assertSame(0, $produk->getStokForUkuran('S'));
        $this->assertSame(6, $produk->getStokForUkuran('L'));
    }

    public function test_cannot_add_to_cart_out_of_stock_size(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $produk = Produk::create([
            'nama' => 'Kemeja Eksklusif Sutra',
            'sku' => 'SUTRA-001',
            'kategori' => 'Baju Batik',
            'harga' => 450000,
            'stok' => 4,
            'stok_ukuran' => [
                'S' => 0,
                'M' => 4,
                'L' => 0,
            ],
            'status' => 'Stok Menipis',
        ]);

        // Attempt adding size S (stock 0)
        $response = $this->actingAs($customer)->postJson(route('keranjang.store'), [
            'produk_id' => $produk->id,
            'qty' => 1,
            'ukuran' => 'S',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // Add size M (stock 4)
        $responseOk = $this->actingAs($customer)->postJson(route('keranjang.store'), [
            'produk_id' => $produk->id,
            'qty' => 2,
            'ukuran' => 'M',
        ]);

        $responseOk->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_checkout_deducts_size_specific_stock_accurately(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $produk = Produk::create([
            'nama' => 'Kemeja Katun Klasik',
            'sku' => 'KLASIK-001',
            'kategori' => 'Baju Batik',
            'harga' => 250000,
            'stok' => 7,
            'stok_ukuran' => [
                'S' => 2,
                'M' => 5,
            ],
            'status' => 'Stok Menipis',
        ]);

        $cartItem = CartItem::create([
            'user_id' => $customer->id,
            'session_id' => 'test-checkout-size',
            'produk_id' => $produk->id,
            'ukuran' => 'M',
            'qty' => 2,
            'selected' => true,
        ]);

        $response = $this->actingAs($customer)->postJson(route('checkout.store'), [
            'customer_name' => 'Aditya Pratama',
            'phone' => '081298765432',
            'address' => 'Jl. Slamet Riyadi No. 50, Solo',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $produk->refresh();
        $this->assertSame(5, $produk->stok);
        $this->assertSame(3, $produk->getStokForUkuran('M'));
        $this->assertSame(2, $produk->getStokForUkuran('S'));
    }
}
