<?php

namespace Tests\Feature;

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
}
