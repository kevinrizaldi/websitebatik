<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name' => 'Batik Uji Foto',
            'sku' => 'SKU-FOTO-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Produk untuk uji unggah foto ulasan',
        ]);
    }

    private function completedOrder(User $user, Product $product): Order
    {
        $order = Order::factory()->for($user)->create([
            'status' => 'Selesai',
            'code' => 'ORD-FOTO-'.uniqid(),
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

    private function tokenFor(User $user): string
    {
        return $user->createToken('mobile-app')->plainTextToken;
    }

    public function test_review_tanpa_foto_tetap_tersimpan(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($user, $product);

        $response = $this->withToken($this->tokenFor($user))->postJson(
            "/api/orders/{$order->id}/reviews",
            ['product_id' => $product->id, 'rating' => 5, 'comment' => str_repeat('a', 40)],
        );

        $response->assertStatus(201)->assertJsonPath('data.rating', 5);
        $this->assertNull(\App\Models\Review::where('order_id', $order->id)->first()->photos);
    }

    public function test_tiga_foto_tersimpan_dan_url_dikembalikan(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($user, $product);
        $files = [
            UploadedFile::fake()->image('photo_0.jpg', 80, 80),
            UploadedFile::fake()->image('photo_1.jpg', 80, 80),
            UploadedFile::fake()->image('photo_2.jpg', 80, 80),
        ];

        $response = $this->withToken($this->tokenFor($user))->post(
            "/api/orders/{$order->id}/reviews",
            [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => str_repeat('b', 40),
                'photos' => $files,
            ],
        );

        $response->assertStatus(201)->assertJsonCount(3, 'data.photos');
        $urls = $response->json('data.photos');
        foreach ($urls as $url) {
            $this->assertStringContainsString('api/media/storage/reviews/', $url);
        }
        $stored = \App\Models\Review::where('order_id', $order->id)->first()->photos;
        $this->assertCount(3, $stored);
        foreach ($stored as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_lebih_dari_tiga_foto_ditolak(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($user, $product);
        $files = [];
        for ($i = 0; $i < 4; $i++) {
            $files[] = UploadedFile::fake()->image("photo_$i.jpg", 80, 80);
        }

        $this->withToken($this->tokenFor($user))->post(
            "/api/orders/{$order->id}/reviews",
            ['product_id' => $product->id, 'rating' => 5, 'comment' => str_repeat('c', 40), 'photos' => $files],
        )->assertStatus(422)->assertJsonValidationErrors('photos');
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($user, $product);

        $this->withToken($this->tokenFor($user))->post(
            "/api/orders/{$order->id}/reviews",
            [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => str_repeat('d', 40),
                'photos' => [UploadedFile::fake()->create('catatan.txt', 4, 'text/plain')],
            ],
        )->assertStatus(422)->assertJsonValidationErrors('photos.0');
    }

    public function test_daftar_review_produk_menyertakan_url_foto(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($user, $product);

        $this->withToken($this->tokenFor($user))->post(
            "/api/orders/{$order->id}/reviews",
            [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => str_repeat('f', 40),
                'photos' => [
                    UploadedFile::fake()->image('a.jpg', 80, 80),
                    UploadedFile::fake()->image('b.jpg', 80, 80),
                    UploadedFile::fake()->image('c.jpg', 80, 80),
                ],
            ],
        )->assertStatus(201);

        $response = $this->getJson("/api/products/{$product->id}/reviews");
        $response->assertOk()->assertJsonCount(3, 'data.0.photos');
        foreach ($response->json('data.0.photos') as $url) {
            $this->assertStringContainsString('api/media/storage/reviews/', $url);
        }
    }

    public function test_review_lama_tanpa_foto_mengembalikan_array_kosong(): void
    {
        $product = $this->product();
        \App\Models\Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'customer_name' => 'Pembeli Lama',
            'rating' => 4,
            'comment' => str_repeat('g', 40),
            'status' => 'Disetujui',
        ]);

        $this->getJson("/api/products/{$product->id}/reviews")
            ->assertOk()
            ->assertJsonPath('data.0.photos', []);
    }

    public function test_user_lain_tidak_bisa_mengulas_pesanan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->product();
        $order = $this->completedOrder($owner, $product);

        $this->withToken($this->tokenFor($stranger))->postJson(
            "/api/orders/{$order->id}/reviews",
            ['product_id' => $product->id, 'rating' => 5, 'comment' => str_repeat('e', 40)],
        )->assertStatus(403);
    }
}
