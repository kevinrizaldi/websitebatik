<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAddressReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Batik Review Test',
            'sku' => 'SKU-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Deskripsi produk uji ulasan',
        ], $attributes));
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('mobile-app')->plainTextToken;
    }

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'full_address' => 'Jl. Melati No. 10, Yogyakarta',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55271',
            'is_primary' => true,
        ], $overrides);
    }

    public function test_address_crud(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);

        // Create (201 + data wrapper).
        $store = $this->withToken($token)->postJson('/api/addresses', $this->addressPayload());

        $store->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'label', 'recipient_name', 'phone', 'full_address', 'is_primary']])
            ->assertJsonPath('data.recipient_name', 'Budi Santoso');

        $addressId = $store->json('data.id');

        // Read (data list wrapper).
        $this->withToken($token)
            ->getJson('/api/addresses')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'recipient_name', 'full_address']]])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $addressId);

        // Update via PUT (plan) and PATCH (Flutter) on the same URI.
        $this->withToken($token)
            ->putJson("/api/addresses/{$addressId}", [
                'label' => 'Kantor',
                'full_address' => 'Jl. Sudirman No. 5, Yogyakarta',
                'province_id' => 34,
                'city_id' => 3471,
            ])
            ->assertOk()
            ->assertJsonStructure(['message', 'data' => ['id', 'label', 'full_address']])
            ->assertJsonPath('data.label', 'Kantor')
            ->assertJsonPath('data.full_address', 'Jl. Sudirman No. 5, Yogyakarta');

        $this->withToken($token)
            ->patchJson("/api/addresses/{$addressId}", ['recipient_name' => 'Budi Dirubah'])
            ->assertOk()
            ->assertJsonPath('data.recipient_name', 'Budi Dirubah');

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'user_id' => $user->id,
            'label' => 'Kantor',
            'province_id' => 34,
            'city_id' => 3471,
        ]);

        // Delete.
        $this->withToken($token)
            ->deleteJson("/api/addresses/{$addressId}")
            ->assertOk();

        $this->assertDatabaseMissing('addresses', ['id' => $addressId]);
    }

    public function test_address_403_other_user(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $strangerToken = $this->tokenFor($stranger);

        $store = $this->withToken($this->tokenFor($owner))
            ->postJson('/api/addresses', $this->addressPayload());

        $addressId = $store->json('data.id');

        // The sanctum guard caches the first resolved user for the whole test;
        // flush it when switching tokens (same pattern as ApiAuthTest).
        $this->app->make('auth')->forgetGuards();

        $this->withToken($strangerToken)
            ->putJson("/api/addresses/{$addressId}", ['label' => 'Hacked'])
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->patchJson("/api/addresses/{$addressId}", ['label' => 'Hacked'])
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->deleteJson("/api/addresses/{$addressId}")
            ->assertStatus(403);

        $this->assertDatabaseHas('addresses', ['id' => $addressId, 'label' => 'Rumah']);
    }

    public function test_reviews_list_public(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->create();

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'customer_name' => 'Pembeli Setia',
            'rating' => 5,
            'comment' => 'Batiknya sangat bagus',
            'status' => 'Disetujui',
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'customer_name' => 'Belum Disetujui',
            'rating' => 1,
            'comment' => 'Menunggu moderasi admin',
            'status' => 'Menunggu',
        ]);

        // Public endpoint — no auth token required; only approved reviews listed.
        $response = $this->getJson("/api/products/{$product->id}/reviews");

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'rating', 'comment', 'status']]])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'Disetujui')
            ->assertJsonPath('data.0.comment', 'Batiknya sangat bagus');
    }

    public function test_review_requires_completed_order(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct();

        $order = Order::factory()->for($user)->dikirim()->create();
        $order->items()->create([
            'produk_id' => $product->id,
            'produk_name' => 'Batik Review Test',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        // Not Selesai yet → 422.
        $this->withToken($token)
            ->postJson("/api/orders/{$order->id}/reviews", [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Barangnya mantap sekali',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseCount('reviews', 0);

        // Stranger cannot review someone else's order → 403.
        $this->app->make('auth')->forgetGuards();
        $stranger = User::factory()->create();
        $this->withToken($this->tokenFor($stranger))
            ->postJson("/api/orders/{$order->id}/reviews", [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Nyasar',
            ])
            ->assertStatus(403);

        // Selesai → 201 with data wrapper.
        $order->update(['status' => 'Selesai']);
        $this->app->make('auth')->forgetGuards();

        $response = $this->withToken($token)
            ->postJson("/api/orders/{$order->id}/reviews", [
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Barangnya mantap sekali',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'rating', 'comment']])
            ->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_review_once_per_order_product(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct();

        $order = Order::factory()->for($user)->selesai()->create();
        $order->items()->create([
            'produk_id' => $product->id,
            'produk_name' => 'Batik Review Test',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        $this->withToken($token)
            ->postJson("/api/orders/{$order->id}/reviews", [
                'product_id' => $product->id,
                'rating' => 4,
                'comment' => 'Sudah oke dan lumayan',
            ])
            ->assertStatus(201);

        $this->withToken($token)
            ->postJson("/api/orders/{$order->id}/reviews", [
                'product_id' => $product->id,
                'rating' => 1,
                'comment' => 'Mencoba mengulas dua kali',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseCount('reviews', 1);
    }
}
