<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPrdFlowTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_checkout_redirects_to_login(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_open_alamat_page(): void
    {
        $response = $this->actingAs($this->customer())->get(route('alamat.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Alamat Saya');
        $response->assertSee('Tambah Alamat Baru');
    }

    public function test_customer_can_store_alamat_and_it_becomes_default(): void
    {
        $user = $this->customer();

        $response = $this->actingAs($user)->post(route('alamat.store'), [
            'label_alamat' => 'Rumah',
            'penerima' => 'Hamzah',
            'no_telepon' => '081234567890',
            'alamat_lengkap' => 'Jl. Batik No. 1',
            'kota' => 'Surakarta',
            'provinsi' => 'Jawa Tengah',
            'kode_pos' => '57141',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alamats', [
            'user_id' => $user->id,
            'kota' => 'Surakarta',
            'is_utama' => true,
        ]);
    }

    public function test_customer_cannot_open_other_customer_payment_page(): void
    {
        $owner = $this->customer();
        $intruder = $this->customer();

        $order = Order::create([
            'user_id' => $owner->id,
            'code' => 'ORD-TEST-001',
            'customer_name' => 'Owner',
            'phone' => '081234567890',
            'address' => 'Jl. Batik No. 1, Surakarta',
            'total_price' => 150000,
            'payment_method' => 'Midtrans Gateway',
            'status' => 'Menunggu Pembayaran',
        ]);

        $response = $this->actingAs($intruder)->get(route('pembayaran', ['id' => $order->code]));

        $response->assertStatus(403);
    }

    public function test_admin_can_open_kategori_and_pengaturan_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.kategori.index'))
            ->assertStatus(200)
            ->assertSee('Kelola Kategori');

        $this->actingAs($admin)->get(route('admin.pengaturan.index'))
            ->assertStatus(200)
            ->assertSee('Pengaturan Toko');
    }

    public function test_customer_cannot_open_admin_kategori_page(): void
    {
        $response = $this->actingAs($this->customer())->get(route('admin.kategori.index'));

        $response->assertStatus(403);
    }

    public function test_produk_create_form_uses_dynamic_categories(): void
    {
        Kategori::create(['nama_kategori' => 'Seragam ASN', 'slug' => 'seragam-asn']);

        $response = $this->actingAs($this->admin())->get(route('produk.create'));

        $response->assertStatus(200);
        $response->assertSee('Seragam ASN');
    }

    public function test_confirm_received_updates_pengiriman_to_diterima(): void
    {
        $user = $this->customer();
        $produk = Produk::create([
            'nama' => 'Batik Produk Terima',
            'sku' => 'BTK-'.uniqid(),
            'kategori' => 'Baju Batik',
            'harga' => 100000,
            'stok' => 5,
            'status' => 'Tersedia',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'ORD-TEST-002',
            'customer_name' => $user->name,
            'phone' => '081234567890',
            'address' => 'Jl. Batik No. 2, Surakarta',
            'total_price' => 121000,
            'payment_method' => 'Midtrans (QRIS)',
            'status' => 'Dikirim',
        ]);
        $order->items()->create([
            'produk_id' => $produk->id,
            'produk_name' => $produk->nama,
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($user)->patch(route('pesanan.terima', $order));

        $response->assertRedirect(route('pesanan.index'));
        $this->assertDatabaseHas('orders', ['code' => 'ORD-TEST-002', 'status' => 'Selesai']);
        $this->assertDatabaseHas('pengirimans', ['order_id' => $order->id, 'status_pengiriman' => 'Diterima']);
    }

    public function test_profile_page_shows_prd_sections_without_poin_or_voucher(): void
    {
        $response = $this->actingAs($this->customer())->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profil Saya');
        $response->assertSee('Informasi Akun');
        $response->assertSee('Alamat Utama');
        $response->assertDontSee('Poin Kriya');
        $response->assertDontSee('Voucher');
    }

    public function test_profile_update_persists_phone(): void
    {
        $user = $this->customer();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
        $this->assertSame('081234567890', $user->refresh()->phone);
    }

    public function test_checkout_defaults_to_midtrans_gateway(): void
    {
        $user = $this->customer();
        $produk = Produk::create([
            'nama' => 'Batik Checkout Midtrans',
            'sku' => 'BTK-'.uniqid(),
            'kategori' => 'Baju Batik',
            'harga' => 200000,
            'stok' => 5,
            'status' => 'Tersedia',
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 1, Surakarta',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'Midtrans Gateway',
            'status' => 'Menunggu Pembayaran',
        ]);
    }

    private function createOrderWithProduct(User $user, Produk $produk, string $code, string $status): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'code' => $code,
            'customer_name' => $user->name,
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 1, Surakarta',
            'total_price' => 100000,
            'payment_method' => 'Midtrans (QRIS)',
            'status' => $status,
        ]);
        $order->items()->create([
            'produk_id' => $produk->id,
            'produk_name' => $produk->nama,
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        return $order;
    }

    private function sampleProduk(string $suffix): Produk
    {
        return Produk::create([
            'nama' => 'Batik Ulasan '.$suffix,
            'sku' => 'BTK-'.uniqid(),
            'kategori' => 'Baju Batik',
            'harga' => 100000,
            'stok' => 5,
            'status' => 'Tersedia',
        ]);
    }

    public function test_review_rejected_without_completed_purchase(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('A');

        $response = $this->actingAs($user)->postJson(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'rating' => 5,
            'comment' => 'Bagus sekali bahannya.',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }

    public function test_review_rejected_when_order_not_completed(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('B');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-001', 'Diproses');

        $response = $this->actingAs($user)->postJson(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Bagus sekali bahannya.',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }

    public function test_review_accepted_after_completed_purchase(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('C');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-002', 'Selesai');

        $response = $this->actingAs($user)->postJson(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kain nyaman dan jahitan rapi.',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('ulasans', [
            'produk_id' => $produk->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_review_rejected_for_other_users_order(): void
    {
        $owner = $this->customer();
        $intruder = $this->customer();
        $produk = $this->sampleProduk('D');
        $order = $this->createOrderWithProduct($owner, $produk, 'ORD-ULAS-003', 'Selesai');

        $response = $this->actingAs($intruder)->postJson(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 1,
            'comment' => 'Jelek sekali.',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }
}
