<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebReviewMultiPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): User
    {
        return User::factory()->create([
            'role' => 'pelanggan',
        ]);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'Batik Tulis Premium',
            'sku' => 'SKU-WEB-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 150000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Deskripsi produk batik',
        ]);
    }

    private function createCompletedOrder(User $user, Product $product, string $code = 'ORD-SEL-001'): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'code' => $code,
            'customer_name' => $user->name,
            'phone' => '08123456789',
            'address' => 'Jl. Uji Web No. 1',
            'total_price' => $product->price,
            'payment_method' => 'Midtrans (QRIS)',
            'status' => 'Selesai',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        return $order;
    }

    public function test_web_review_dapat_menyimpan_hingga_tiga_foto(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $order = $this->createCompletedOrder($user, $product, 'ORD-WEB-001');

        $files = [
            UploadedFile::fake()->image('review1.jpg', 400, 400),
            UploadedFile::fake()->image('review2.png', 400, 400),
            UploadedFile::fake()->image('review3.webp', 400, 400),
        ];

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Kualitas kain batik sangat luar biasa dan rapi!',
            'photos' => $files,
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);

        $review = Review::where('order_id', $order->id)->first();
        $this->assertNotNull($review);
        $this->assertCount(3, $review->photos);
        $this->assertNotNull($review->image_path);

        foreach ($review->photos as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_web_review_gagal_jika_foto_lebih_dari_tiga(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $order = $this->createCompletedOrder($user, $product, 'ORD-WEB-002');

        $files = [
            UploadedFile::fake()->image('f1.jpg', 100, 100),
            UploadedFile::fake()->image('f2.jpg', 100, 100),
            UploadedFile::fake()->image('f3.jpg', 100, 100),
            UploadedFile::fake()->image('f4.jpg', 100, 100),
        ];

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Ulasan terlalu banyak foto.',
            'photos' => $files,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos']);
    }

    public function test_web_review_tetap_kompatibel_dengan_field_single_image(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $order = $this->createCompletedOrder($user, $product, 'ORD-WEB-003');

        $singleImage = UploadedFile::fake()->image('single.jpg', 300, 300);

        $response = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 4,
            'comment' => 'Ulasan dengan single image backward compatibility.',
            'image' => $singleImage,
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true]);

        $review = Review::where('order_id', $order->id)->first();
        $this->assertNotNull($review);
        $this->assertNotNull($review->image_path);
        $this->assertCount(1, $review->photos);
        Storage::disk('public')->assertExists($review->image_path);
    }

    public function test_web_review_dapat_diupdate_dengan_foto_baru(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $order = $this->createCompletedOrder($user, $product, 'ORD-WEB-004');

        $oldFile = UploadedFile::fake()->image('old.jpg', 200, 200);

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 4,
            'comment' => 'Ulasan awal sebelum update.',
            'photos' => [$oldFile],
        ], ['Accept' => 'application/json'])->assertOk();

        $review = Review::where('order_id', $order->id)->first();
        $oldPath = $review->photos[0];
        Storage::disk('public')->assertExists($oldPath);

        $newFiles = [
            UploadedFile::fake()->image('new1.jpg', 200, 200),
            UploadedFile::fake()->image('new2.jpg', 200, 200),
        ];

        $updateResponse = $this->actingAs($user)->put(route('ulasan.update', $review), [
            'rating' => 5,
            'comment' => 'Ulasan yang sudah diperbarui dengan foto baru.',
            'photos' => $newFiles,
        ], ['Accept' => 'application/json']);

        $updateResponse->assertOk()->assertJson(['success' => true]);

        Storage::disk('public')->assertMissing($oldPath);

        $updatedReview = $review->fresh();
        $this->assertCount(2, $updatedReview->photos);
        foreach ($updatedReview->photos as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_web_review_destroy_menghapus_semua_foto_dari_storage(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();
        $order = $this->createCompletedOrder($user, $product, 'ORD-WEB-005');

        $files = [
            UploadedFile::fake()->image('del1.jpg', 200, 200),
            UploadedFile::fake()->image('del2.jpg', 200, 200),
        ];

        $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Akan segera dihapus.',
            'photos' => $files,
        ], ['Accept' => 'application/json'])->assertOk();

        $review = Review::where('order_id', $order->id)->first();
        $savedPaths = $review->photos;
        foreach ($savedPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->actingAs($user)->delete(route('ulasan.destroy', $review), [], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        foreach ($savedPaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_pesanan_dikirim_bisa_dikonfirmasi_diterima_lalu_berstatus_selesai_dan_bisa_diulas(): void
    {
        Storage::fake('public');
        $user = $this->createCustomer();
        $product = $this->createProduct();

        // Buat pesanan dengan status 'Dikirim'
        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'ORD-KIRIM-001',
            'customer_name' => $user->name,
            'phone' => '08123456789',
            'address' => 'Jl. Pengiriman No. 10',
            'total_price' => $product->price,
            'payment_method' => 'Midtrans (QRIS)',
            'status' => 'Dikirim',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        // Cek bahwa di halaman pesanan, tombol 'Pesanan Diterima' tersedia untuk pesanan ini
        $pageResponse = $this->actingAs($user)->get(route('pesanan.index'));
        $pageResponse->assertOk();
        $pageResponse->assertSee('Pesanan Diterima');

        // Customer mengonfirmasi pesanan diterima
        $confirmResponse = $this->actingAs($user)->patch(route('pesanan.terima', $order));
        $confirmResponse->assertRedirect(route('pesanan.index'));
        $confirmResponse->assertSessionHas('success');

        // Status pesanan di database kini menjadi 'Selesai'
        $this->assertEquals('Selesai', $order->fresh()->status);

        // Setelah status Selesai, customer langsung dapat mengirimkan ulasan produk
        $reviewFile = UploadedFile::fake()->image('ulasan_selesai.jpg', 300, 300);
        $reviewResponse = $this->actingAs($user)->post(route('ulasan.store'), [
            'produk_id' => $product->id,
            'order_code' => $order->code,
            'rating' => 5,
            'comment' => 'Pesanan sudah diterima dengan sangat baik, bahannya bagus!',
            'photos' => [$reviewFile],
        ], ['Accept' => 'application/json']);

        $reviewResponse->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'rating' => 5,
            'status' => 'Menunggu',
        ]);
    }
}
