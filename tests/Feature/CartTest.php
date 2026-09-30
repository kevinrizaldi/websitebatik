<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleProduct(): Produk
    {
        return Produk::create([
            'nama' => 'Batik Tulis Sogan Klasik',
            'sku' => 'BTK-'.uniqid(),
            'kategori' => 'Kain Batik',
            'harga' => 350000,
            'stok' => 10,
            'status' => 'Tersedia',
            'deskripsi' => 'Batik tulis autentik nusantara',
            'material' => 'Katun Primissima',
        ]);
    }

    /**
     * Test viewing the cart page.
     */
    public function test_can_view_cart_page(): void
    {
        $response = $this->get(route('keranjang.index'));

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja');
    }

    /**
     * Test adding product to cart in database.
     */
    public function test_can_add_product_to_cart_in_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        $response = $this->postJson(route('keranjang.store'), [
            'produk_id' => $produk->id,
            'qty' => 2,
            'ukuran' => 'L',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'ukuran' => 'L',
            'qty' => 2,
        ]);
    }

    /**
     * Test updating quantity of cart item in database.
     */
    public function test_can_update_cart_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->patchJson(route('keranjang.update', $cartItem), [
            'qty' => 3,
            'selected' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'qty' => 3,
            'selected' => false,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'qty' => 3,
            'selected' => false,
        ]);
    }

    /**
     * Test removing cart item from database.
     */
    public function test_can_delete_cart_item(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 1,
        ]);

        $response = $this->deleteJson(route('keranjang.destroy', $cartItem));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test checkout creates an order in orders and order_items tables.
     */
    public function test_checkout_creates_order_in_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->postJson(route('keranjang.checkout'), [
            'customer_name' => 'Kevin Rizaldi Test',
            'phone' => '081234567890',
            'address' => 'Jl. Malioboro No. 45 Yogyakarta',
            'payment_method' => 'Transfer Bank BCA',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Kevin Rizaldi Test',
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test viewing checkout page with selected cart items.
     */
    public function test_can_view_checkout_page_with_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Selesaikan Pembayaran');
        $response->assertSee('Ringkasan Pesanan');
        $response->assertSee('Alamat Pengiriman');
        $response->assertSee($produk->nama);
    }

    /**
     * Test submitting checkout via checkout.store route.
     */
    public function test_submitting_checkout_store_creates_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $produk = $this->createSampleProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 2,
            'selected' => true,
        ]);

        $response = $this->postJson(route('checkout.store'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '0812-3456-7890',
            'address' => 'Jl. Senopati No. 42 Jakarta',
            'shipping_option' => 'JNE Reguler',
            'shipping_cost' => 20000,
            'payment_method' => 'Transfer Bank Manual (BCA)',
            'notes' => 'Tolong bungkus aman',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Budi Santoso',
            'phone' => '0812-3456-7890',
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }
}
