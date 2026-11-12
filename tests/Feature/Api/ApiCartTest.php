<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCartTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Batik Cart Test',
            'sku' => 'SKU-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Deskripsi produk uji keranjang',
        ], $attributes));
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('mobile-app')->plainTextToken;
    }

    public function test_add_and_list_cart(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct();

        $add = $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'size' => 'M',
            'quantity' => 2,
        ]);

        $add->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'product_id', 'quantity', 'selected']])
            ->assertJsonPath('data.quantity', 2);

        // Plan-canonical keys (dual-shape alongside Flutter's quantity/size/unit_price).
        $item = $add->json('data');
        $this->assertSame(2, $item['qty']);
        $this->assertSame('M', $item['ukuran']);
        $this->assertSame(200000.0, (float) $item['subtotal']);
        $this->assertIsArray($item['product']);
        $this->assertSame($product->id, $item['product']['id']);
        $this->assertSame('Batik Cart Test', $item['product']['name']);
        $this->assertArrayHasKey('price', $item['product']);
        $this->assertArrayHasKey('image', $item['product']);

        // Legacy keys Flutter reads must stay (unit_price is per-unit: Flutter multiplies by quantity).
        $this->assertSame('M', $item['size']);
        $this->assertSame(100000.0, (float) $item['unit_price']);
        $this->assertArrayHasKey('image_url', $item);

        $list = $this->withToken($token)->getJson('/api/cart');

        $list->assertOk()
            ->assertJsonStructure(['data' => [['id', 'product_id', 'quantity', 'selected']], 'subtotal'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $item['id']);
    }

    public function test_update_qty(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'size' => 'L',
            'quantity' => 1,
            'selected' => true,
        ]);

        $response = $this->withToken($token)->patchJson("/api/cart/{$cartItem->id}", [
            'quantity' => 3,
            'selected' => false,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'data' => ['id', 'quantity', 'selected']])
            ->assertJsonPath('data.quantity', 3)
            ->assertJsonPath('data.selected', false)
            ->assertJsonPath('data.qty', 3);

        $this->assertSame(3, $cartItem->refresh()->quantity);
    }

    public function test_delete_item(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'selected' => true,
        ]);

        $response = $this->withToken($token)->deleteJson("/api/cart/{$cartItem->id}");

        // Plan contract: DELETE returns 204 with empty body (Flutter ignores the body).
        $response->assertStatus(204);
        $this->assertSame('', $response->getContent());
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_cannot_touch_other_users_cart(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $strangerToken = $this->tokenFor($stranger);
        $product = $this->createProduct();

        $cartItem = CartItem::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'selected' => true,
        ]);

        $this->withToken($strangerToken)
            ->patchJson("/api/cart/{$cartItem->id}", ['quantity' => 5])
            ->assertStatus(403);

        $this->withToken($strangerToken)
            ->deleteJson("/api/cart/{$cartItem->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id, 'quantity' => 1]);
    }

    public function test_add_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 3]);

        $response = $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'size' => 'M',
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message'])
            ->assertJsonPath('message', fn ($message) => str_contains((string) $message, 'Stok'));

        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    }

    public function test_add_accepts_plan_qty_ukuran_aliases(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $product = $this->createProduct(['stock' => 10]);

        $response = $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'ukuran' => 'XL',
            'qty' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.qty', 2)
            ->assertJsonPath('data.ukuran', 'XL')
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.size', 'XL');
    }

    public function test_cart_requires_authentication(): void
    {
        $this->getJson('/api/cart')->assertStatus(401);
    }
}
