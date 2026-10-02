<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_category_validation_rejects_symbols_and_numbers(): void
    {
        $admin = $this->admin();

        // Reject symbol
        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Kategori @#$ 123',
        ]);
        $response->assertSessionHasErrors('nama_kategori');

        // Accept valid letters and spaces
        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Kain Tradisional Sutra',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kategoris', ['nama_kategori' => 'Kain Tradisional Sutra']);
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

    public function test_admin_resi_validation_rejects_symbols(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'ORD-RESI-001',
            'customer_name' => $user->name,
            'phone' => '081234567890',
            'address' => 'Jl. Batik No. 1, Surakarta',
            'total_price' => 100000,
            'payment_method' => 'Midtrans',
            'status' => 'Diproses',
        ]);

        // Simbol ditolak
        $response = $this->actingAs($admin)->patch(route('admin.orders.update-status', $order), [
            'status' => 'Dikirim',
            'tracking_number' => 'RESI@#$!*123',
        ]);
        $response->assertSessionHasErrors('tracking_number');

        // Huruf dan angka diterima dan disimpan huruf kapital
        $responseValid = $this->actingAs($admin)->patch(route('admin.orders.update-status', $order), [
            'status' => 'Dikirim',
            'tracking_number' => 'jne987654321',
        ]);
        $responseValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Dikirim',
            'tracking_number' => 'JNE987654321',
        ]);
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

    public function test_admin_profile_uses_dashboard_layout(): void
    {
        $response = $this->actingAs($this->admin())->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profile Admin');
        $response->assertSee('Profile Information');
        $response->assertSee('Update Password');
        $response->assertDontSee('Profil Saya');
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

    private function fakeReviewImage(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'batik.gif',
            base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
        );
    }

    public function test_review_rejected_without_completed_purchase(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('A');

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'rating' => 5,
            'comment' => 'Bagus sekali bahannya.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }

    public function test_review_rejected_when_order_not_completed(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('B');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-001', 'Diproses');

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Bagus sekali bahannya.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }

    public function test_review_accepted_after_completed_purchase(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('C');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-002', 'Selesai');

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kain nyaman dan jahitan rapi.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('ulasans', [
            'produk_id' => $produk->id,
            'user_id' => $user->id,
            'rating' => 5,
            'status' => 'Menunggu',
        ]);
    }

    public function test_second_review_for_same_product_is_rejected(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('C2');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-012', 'Selesai');

        $payload = [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kain nyaman dan jahitan rapi.',
            'image' => $this->fakeReviewImage(),
        ];

        $this->actingAs($user)->post(route('ulasan.store'), $payload, ['Accept' => 'application/json'])->assertOk();

        $response = $this->actingAs($user)->post(route('ulasan.store'), $payload, ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $response->assertJsonPath('message', 'Anda sudah memberi ulasan untuk produk ini pada pesanan #'.$order->code.'.');
        $this->assertSame(1, Ulasan::where('user_id', $user->id)->where('produk_id', $produk->id)->count());
    }

    public function test_reviewed_product_shows_sudah_diulas_badge(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('C3');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-013', 'Selesai');

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 4,
            'comment' => 'Cukup bagus dan nyaman.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json'])->assertOk();

        $response = $this->actingAs($user)->get(route('pesanan.index'));

        $response->assertOk();
        $response->assertSee('Sudah diulas');
    }

    public function test_profile_lists_my_reviews(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('C7');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-030', 'Selesai');

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Ulasan tampil di halaman ulasan saya.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json'])->assertOk();

        $response = $this->actingAs($user)->get(route('ulasan.index'));

        $response->assertOk();
        $response->assertSee('Ulasan Saya (1)');
        $response->assertSee('Ulasan tampil di halaman ulasan saya.');
        $response->assertSee($produk->nama);
    }

    public function test_two_different_products_each_get_one_review(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produkA = $this->sampleProduk('C4');
        $produkB = $this->sampleProduk('C5');
        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'ORD-ULAS-014',
            'customer_name' => $user->name,
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 1, Surakarta',
            'total_price' => 200000,
            'payment_method' => 'Midtrans (QRIS)',
            'status' => 'Selesai',
        ]);
        foreach ([$produkA, $produkB] as $produk) {
            $order->items()->create([
                'produk_id' => $produk->id,
                'produk_name' => $produk->nama,
                'price' => 100000,
                'quantity' => 1,
                'subtotal' => 100000,
            ]);
        }

        foreach ([$produkA, $produkB] as $i => $produk) {
            $response = $this->actingAs($user)->post(route('ulasan.store'), [
                'produk_id' => $produk->id,
                'order_code' => $order->code,
                'rating' => 5,
                'comment' => 'Ulasan produk ke-'.($i + 1).' sangat memuaskan.',
                'image' => $this->fakeReviewImage(),
            ], ['Accept' => 'application/json']);

            $response->assertOk()->assertJson(['success' => true]);
        }

        // Ulasan kedua untuk produk yang sama tetap ditolak.
        $repeat = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produkA->id,
            'order_code' => $order->code,
            'rating' => 4,
            'comment' => 'Coba ulas lagi produk pertama.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

        $repeat->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame(1, Ulasan::where('user_id', $user->id)->where('produk_id', $produkA->id)->count());
        $this->assertSame(1, Ulasan::where('user_id', $user->id)->where('produk_id', $produkB->id)->count());
    }

    public function test_same_product_rebought_can_be_reviewed_again(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('C6');
        $order1 = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-020', 'Selesai');
        $order2 = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-021', 'Selesai');

        $payloadFor = fn (Order $order, string $comment) => [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => $comment,
            'image' => $this->fakeReviewImage(),
        ];

        $this->actingAs($user)->post(route('ulasan.store'), $payloadFor($order1, 'Pembelian pertama sangat memuaskan.'), ['Accept' => 'application/json'])->assertOk();

        $response = $this->actingAs($user)->post(route('ulasan.store'), $payloadFor($order2, 'Beli lagi dan tetap memuaskan.'), ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame(2, Ulasan::where('user_id', $user->id)->where('produk_id', $produk->id)->count());
    }

    public function test_customer_can_attach_an_image_to_a_review(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('IMAGE');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-IMAGE', 'Selesai');

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kain nyaman dan jahitan rapi.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);
        $imagePath = $response->json('ulasan.image_path');

        $this->assertNotEmpty($imagePath);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertDatabaseHas('ulasans', [
            'produk_id' => $produk->id,
            'image_path' => $imagePath,
        ]);
    }

    public function test_review_accepted_without_image_as_it_is_optional(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('NOIMAGE');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-NOIMAGE', 'Selesai');

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
            'image_path' => null,
        ]);
    }

    public function test_customer_cannot_attach_a_non_image_to_a_review(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('NONIMAGE');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-NONIMAGE', 'Selesai');

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kain nyaman dan jahitan rapi.',
            'image' => UploadedFile::fake()->create('dokumen.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json']);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.image.0', 'File ulasan harus berupa gambar.');
        $this->assertDatabaseMissing('ulasans', ['produk_id' => $produk->id]);
    }

    private function createPendingReview(User $user, Produk $produk, Order $order): Ulasan
    {
        Storage::fake('public');

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 4,
            'comment' => 'Awalnya cukup bagus.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json'])->assertOk();

        return Ulasan::where('user_id', $user->id)->where('produk_id', $produk->id)->firstOrFail();
    }

    public function test_customer_can_update_own_pending_review(): void
    {
        $user = $this->customer();
        $produk = $this->sampleProduk('EDIT');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-EDIT', 'Selesai');
        $ulasan = $this->createPendingReview($user, $produk, $order);

        $response = $this->actingAs($user)->put(route('ulasan.update', $ulasan), [
            'rating' => 5,
            'comment' => 'Setelah dicuci ternyata sangat bagus.',
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Setelah dicuci ternyata sangat bagus.', $ulasan->refresh()->comment);
        $this->assertSame(5, $ulasan->refresh()->rating);
    }

    public function test_customer_cannot_update_approved_review(): void
    {
        $user = $this->customer();
        $admin = $this->admin();
        $produk = $this->sampleProduk('EDIT2');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-EDIT2', 'Selesai');
        $ulasan = $this->createPendingReview($user, $produk, $order);
        $this->actingAs($admin)->patch(route('admin.ulasans.update-status', $ulasan), ['status' => 'Disetujui']);

        $response = $this->actingAs($user)->put(route('ulasan.update', $ulasan), [
            'rating' => 1,
            'comment' => 'Berubah pikiran total.',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame(4, $ulasan->refresh()->rating);
    }

    public function test_customer_cannot_update_other_users_review(): void
    {
        $owner = $this->customer();
        $intruder = $this->customer();
        $produk = $this->sampleProduk('EDIT3');
        $order = $this->createOrderWithProduct($owner, $produk, 'ORD-ULAS-EDIT3', 'Selesai');
        $ulasan = $this->createPendingReview($owner, $produk, $order);

        $response = $this->actingAs($intruder)->put(route('ulasan.update', $ulasan), [
            'rating' => 1,
            'comment' => 'Ulasan jahat dari orang lain.',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(403);
    }

    public function test_customer_can_delete_own_pending_review(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $produk = $this->sampleProduk('DEL');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-DEL', 'Selesai');
        $ulasan = $this->createPendingReview($user, $produk, $order);
        $imagePath = $ulasan->image_path;
        $this->assertNotEmpty($imagePath);

        $response = $this->actingAs($user)->delete(route('ulasan.destroy', $ulasan), [], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('ulasans', ['id' => $ulasan->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_customer_cannot_delete_approved_review(): void
    {
        $user = $this->customer();
        $admin = $this->admin();
        $produk = $this->sampleProduk('DEL2');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-DEL2', 'Selesai');
        $ulasan = $this->createPendingReview($user, $produk, $order);
        $this->actingAs($admin)->patch(route('admin.ulasans.update-status', $ulasan), ['status' => 'Disetujui']);

        $response = $this->actingAs($user)->delete(route('ulasan.destroy', $ulasan), [], ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseHas('ulasans', ['id' => $ulasan->id]);
    }

    public function test_review_rejected_for_other_users_order(): void
    {
        $owner = $this->customer();
        $intruder = $this->customer();
        $produk = $this->sampleProduk('D');
        $order = $this->createOrderWithProduct($owner, $produk, 'ORD-ULAS-003', 'Selesai');

        $response = $this->actingAs($intruder)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 1,
            'comment' => 'Jelek sekali.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json']);

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

    public function test_customer_address_validation_rejects_symbols(): void
    {
        $user = $this->customer();

        // Testing rejection of symbols like "##" from the user request
        $response = $this->actingAs($user)->postJson(route('alamat.store'), [
            'penerima' => 'kevin rizaldi',
            'label_alamat' => 'da##',
            'no_telepon' => '##',
            'kota' => '##',
            'provinsi' => '##',
            'kode_pos' => '##',
            'alamat_lengkap' => '##',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['label_alamat', 'no_telepon', 'kota', 'provinsi', 'kode_pos', 'alamat_lengkap']);

        // Testing acceptance of valid address
        $validResponse = $this->actingAs($user)->postJson(route('alamat.store'), [
            'penerima' => 'Kevin Rizaldi',
            'label_alamat' => 'Rumah Utama',
            'no_telepon' => '081234567890',
            'kota' => 'Kota Pekalongan',
            'provinsi' => 'Jawa Tengah',
            'kode_pos' => '51111',
            'alamat_lengkap' => 'Jl. Urip Sumoharjo No. 10, RT 02/RW 03',
        ]);

        $validResponse->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('alamats', [
            'user_id' => $user->id,
            'penerima' => 'Kevin Rizaldi',
            'kota' => 'Kota Pekalongan',
        ]);
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
        $admin = $this->admin();
        $produk = $this->sampleProduk('R');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ULAS-010', 'Selesai');

        Storage::fake('public');

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $produk->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kainnya adem dan jahitannya rapi.',
            'image' => $this->fakeReviewImage(),
        ], ['Accept' => 'application/json'])->assertOk();

        // Sebelum disetujui admin, ulasan belum tampil.
        $this->get(route('produk.detail', ['id' => $produk->id]))
            ->assertOk()
            ->assertDontSee('Kainnya adem dan jahitannya rapi.');

        $ulasan = Ulasan::where('user_id', $user->id)->where('produk_id', $produk->id)->first();
        $this->actingAs($admin)->patch(route('admin.ulasans.update-status', $ulasan), ['status' => 'Disetujui'])
            ->assertRedirect();

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

    public function test_admin_cannot_cancel_paid_order(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        $produk = $this->sampleProduk('V');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ADM-002', 'Diproses');

        $response = $this->actingAs($admin)->patch(route('admin.orders.cancel', $order));

        $response->assertSessionHasErrors('status');
        $this->assertSame('Diproses', $order->refresh()->status);
    }

    public function test_admin_can_still_cancel_unpaid_order(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        $produk = $this->sampleProduk('W');
        $order = $this->createOrderWithProduct($user, $produk, 'ORD-ADM-003', 'Menunggu Pembayaran');

        $response = $this->actingAs($admin)->patch(route('admin.orders.cancel', $order));

        $response->assertSessionHasNoErrors();
        $this->assertSame('Dibatalkan', $order->refresh()->status);
    }
}
