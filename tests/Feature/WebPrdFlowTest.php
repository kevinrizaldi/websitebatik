<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Produk;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $response->assertSee('Daftar Alamat');
        $response->assertDontSee('Keamanan Akun');
        $response->assertDontSee('Alamat Utama');
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
        $response->assertJsonStructure(['success', 'order_code', 'snap_token', 'redirect_url']);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'Midtrans Gateway',
            'status' => 'Menunggu Pembayaran',
        ]);
        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertSame(route('pembayaran', ['id' => $order->code]), $response->json('redirect_url'));
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

    public function test_koleksi_shows_database_categories_as_pills(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Kain Batik', 'slug' => 'kain-batik']);
        Produk::create([
            'nama' => 'Kain Batik Test Pill',
            'sku' => 'BTK-'.uniqid(),
            'kategori' => 'Kain Batik',
            'kategori_id' => $kategori->id,
            'harga' => 100000,
            'stok' => 5,
            'status' => 'Tersedia',
        ]);

        $response = $this->get(route('koleksi.index'));

        $response->assertOk();
        $response->assertSee('Kain Batik (1)');
    }

    private function settlementPayload(Order $order, string $gross = '100000.00'): array
    {
        $serverKey = (string) config('midtrans.server_key');

        return [
            'order_id' => $order->code,
            'status_code' => '200',
            'gross_amount' => $gross,
            'signature_key' => hash('sha512', $order->code.'200'.$gross.$serverKey),
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-'.uniqid(),
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ];
    }

    public function test_webhook_marks_legacy_order_diproses_on_settlement(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('W');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-WH-001', 'Menunggu Pembayaran');

        $response = $this->postJson(route('midtrans.notification'), $this->settlementPayload($order));

        $response->assertOk();
        $this->assertSame('Diproses', $order->refresh()->status);
        $this->assertSame('Berhasil', $order->pembayaran->refresh()->status_pembayaran);
    }

    public function test_refresh_from_midtrans_updates_order_on_settlement(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('X');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-RF-001', 'Menunggu Pembayaran');

        Http::fake([
            '*' => Http::response($this->settlementPayload($order), 200),
        ]);

        $result = app(MidtransService::class)->refreshFromMidtrans($order);

        $this->assertNotNull($result);
        $this->assertSame('Diproses', $order->refresh()->status);
    }

    public function test_refresh_from_midtrans_ignores_pending(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('Y');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-RF-002', 'Menunggu Pembayaran');

        $payload = $this->settlementPayload($order);
        $payload['transaction_status'] = 'pending';
        Http::fake(['*' => Http::response($payload, 200)]);

        app(MidtransService::class)->refreshFromMidtrans($order);

        $this->assertSame('Menunggu Pembayaran', $order->refresh()->status);
    }

    public function test_customer_confirm_received_marks_order_selesai(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('M');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-TERIMA-001', 'Dikirim');

        $response = $this->actingAs($user)->patch(route('pesanan.terima', $order));

        $response->assertRedirect(route('pesanan.index'));
        $this->assertSame('Selesai', $order->refresh()->status);
        $this->assertSame('Diterima', $order->pengiriman->refresh()->status_pengiriman);
    }

    public function test_customer_cannot_confirm_unshipped_order(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('N');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-TERIMA-002', 'Diproses');

        $response = $this->actingAs($user)->patch(route('pesanan.terima', $order));

        $response->assertSessionHas('error');
        $this->assertSame('Diproses', $order->refresh()->status);
    }

    public function test_customer_can_cancel_unpaid_order_and_stock_restored(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('K');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-BATAL-010', 'Menunggu Pembayaran');
        $produk->decrement('stok', 1);

        $response = $this->actingAs($user)->patch(route('pesanan.batal', $order));

        $response->assertRedirect(route('pesanan.index'));
        $this->assertSame('Dibatalkan', $order->refresh()->status);
        $this->assertSame(5, $produk->refresh()->stok);
    }

    public function test_cancelled_order_appears_under_dibatalkan(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('L');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-BATAL-011', 'Menunggu Pembayaran');

        $this->actingAs($user)->patch(route('pesanan.batal', $order));

        $response = $this->actingAs($user)->get(route('pesanan.index'));
        $response->assertOk();
        $response->assertSee('Dibatalkan');
        $response->assertSee($order->code);
    }

    public function test_checkout_lists_saved_addresses_for_selection(): void
    {
        $user = $this->customer();
        $user->alamats()->create([
            'label_alamat' => 'Rumah',
            'penerima' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'alamat_lengkap' => 'Jl. Mawar No. 1',
            'kota' => 'Surakarta',
            'is_utama' => true,
        ]);
        $user->alamats()->create([
            'label_alamat' => 'Kantor',
            'penerima' => 'Budi Santoso',
            'no_telepon' => '081234567891',
            'alamat_lengkap' => 'Jl. Melati No. 2',
            'kota' => 'Surakarta',
            'is_utama' => false,
        ]);
        $produk = $this->sampleProduk('J');
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-select',
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Ubah Alamat');
        $response->assertSee('Pilih Alamat Tersimpan');
        $response->assertSee('Tambah Alamat Baru');
        $response->assertSee('Jl. Mawar No. 1');
    }

    public function test_diproses_order_shows_sedang_diproses_tab_and_label(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('T');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-PROSES-001', 'Diproses');

        $response = $this->actingAs($user)->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertSee('Sedang Diproses');
        $response->assertSee($order->code);
    }

    public function test_admin_can_view_order_detail(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        $produk = $this->sampleProduk('U');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ADM-001', 'Diproses');

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee($order->code);
        $response->assertSee($produk->nama);
    }

    public function test_empty_stock_cannot_be_added_to_cart(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('O');
        $produk->update(['stok' => 0, 'status' => 'Habis']);

        $response = $this->actingAs($user)->postJson(route('keranjang.store'), [
            'produk_id' => $produk->id,
            'qty' => 1,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id, 'produk_id' => $produk->id]);
    }

    public function test_checkout_rejected_when_stock_insufficient(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('P');
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-stok',
            'produk_id' => $produk->id,
            'qty' => 3,
            'selected' => true,
        ]);
        $produk->update(['stok' => 0, 'status' => 'Habis']);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 1, Surakarta',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }

    public function test_cart_update_removes_item_when_stock_empty(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('Q');
        $item = CartItem::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-update',
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);
        $produk->update(['stok' => 0, 'status' => 'Habis']);

        $response = $this->actingAs($user)->patchJson(route('keranjang.update', $item), ['qty' => 2]);

        $response->assertStatus(422)->assertJson(['removed' => true]);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_produk_detail_shows_approved_reviews_from_database(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('R');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-010', 'Selesai');

        $this->actingAs($user)->postJson(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kainnya adem dan jahitannya rapi.',
        ])->assertOk();

        $response = $this->get(route('produk.detail', ['id' => $produk->id]));

        $response->assertOk();
        $response->assertSee('Kainnya adem dan jahitannya rapi.');
        $response->assertSee($user->name);
        $response->assertSee('1 Ulasan Pelanggan');
    }

    public function test_produk_detail_shows_empty_state_without_reviews(): void
    {
        $produk = $this->sampleProduk('S');

        $response = $this->get(route('produk.detail', ['id' => $produk->id]));

        $response->assertOk();
        $response->assertSee('Belum ada ulasan untuk produk ini');
        $response->assertDontSee('Hendra W.');
    }
}
