<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesSanityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Product $product;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->customer = User::factory()->create(['role' => 'customer', 'name' => 'Customer User']);

        $this->category = Category::create([
            'nama_kategori' => 'Kain Batik',
            'slug' => 'kain-batik',
            'deskripsi' => 'Kategori batik pilihan',
        ]);

        $this->product = Product::create([
            'nama' => 'Batik Tulis Parang Kencana',
            'sku' => 'BTK-PRG-001',
            'kategori' => 'Kain Batik',
            'kategori_id' => $this->category->id,
            'harga' => 250000,
            'stok' => 15,
            'status' => 'Tersedia',
            'deskripsi' => 'Batik tulis premium',
        ]);

        Review::create([
            'produk_id' => $this->product->id,
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'rating' => 5,
            'comment' => 'Kualitas sangat istimewa!',
            'status' => 'Disetujui',
        ]);

        Address::create([
            'user_id' => $this->customer->id,
            'label_alamat' => 'Rumah',
            'penerima' => 'Customer User',
            'no_telepon' => '081234567890',
            'alamat_lengkap' => 'Jl. Malioboro No. 12',
            'kota' => 'Yogyakarta',
            'provinsi' => 'DI Yogyakarta',
            'is_utama' => true,
        ]);
    }

    public function test_all_admin_pages_render_without_error(): void
    {
        $adminRoutes = [
            'dashboard',
            'produk.index',
            'produk.create',
            'admin.kategori.index',
            'admin.orders.index',
            'admin.pelanggan.index',
            'admin.pembayaran.index',
            'admin.pengiriman.index',
            'admin.pengaturan.index',
            'admin.laporan.index',
            'admin.stok.index',
            'admin.ulasans.index',
        ];

        foreach ($adminRoutes as $routeName) {
            $response = $this->actingAs($this->admin)->get(route($routeName));
            $response->assertOk();
        }

        // Test edit page with product parameter
        $editResponse = $this->actingAs($this->admin)->get(route('produk.edit', $this->product));
        $editResponse->assertOk();
    }

    public function test_all_storefront_and_customer_pages_render_without_error(): void
    {
        $guestRoutes = [
            'home',
            'koleksi.index',
        ];

        foreach ($guestRoutes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertOk();
        }

        // Product detail page
        $detailResponse = $this->get(route('produk.detail', ['id' => $this->product->id]));
        $detailResponse->assertOk();

        \App\Models\CartItem::create([
            'user_id' => $this->customer->id,
            'session_id' => 'sanity-session',
            'product_id' => $this->product->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $customerRoutes = [
            'keranjang.index',
            'checkout.index',
            'pesanan.index',
            'alamat.index',
            'ulasan.index',
            'profile.edit',
        ];

        foreach ($customerRoutes as $routeName) {
            $response = $this->actingAs($this->customer)->get(route($routeName));
            $response->assertOk();
        }
    }

    public function test_admin_create_and_edit_product_forms_contain_berat_input_and_save_correctly(): void
    {
        $createPage = $this->actingAs($this->admin)->get(route('produk.create'));
        $createPage->assertOk();
        $createPage->assertSee('Berat Produk (Gram)');
        $createPage->assertSee('name="berat"', false);

        $editPage = $this->actingAs($this->admin)->get(route('produk.edit', $this->product));
        $editPage->assertOk();
        $editPage->assertSee('Berat Produk (Gram)');
        $editPage->assertSee('name="berat"', false);

        // Test store product with berat
        $response = $this->actingAs($this->admin)->post(route('produk.store'), [
            'nama' => 'Batik Tulis Mega Mendung Spesial',
            'sku' => 'BTK-MGM-' . uniqid(),
            'kategori' => 'Kain Batik',
            'harga' => 450000,
            'harga_coret' => 500000,
            'berat' => 420,
            'stok' => 5,
        ]);
        $response->assertRedirect(route('produk.index'));

        $this->assertDatabaseHas('products', [
            'nama' => 'Batik Tulis Mega Mendung Spesial',
            'berat' => 420,
            'weight' => 420,
        ]);
    }
}
