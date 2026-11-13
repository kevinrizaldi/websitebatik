<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Batik Test',
            'sku' => 'SKU-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Deskripsi produk uji',
        ], $attributes));
    }

    public function test_products_list_returns_paginated_data(): void
    {
        $category = Category::create(['name' => 'Kain Batik']);
        $this->createProduct([
            'category_id' => $category->id,
            'size_stock' => ['S' => 3, 'M' => 4],
            'other_images' => ['https://example.com/other.jpg'],
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'price',
                    'stock',
                    'image',
                    'category',
                    'rating',
                    'review_count',
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $product = $response->json('data.0');

        // Plan-canonical aliases.
        $this->assertIsString($product['image']);
        $this->assertArrayHasKey('review_count', $product);
        $this->assertIsArray($product['sizes']);
        $this->assertArrayHasKey('S', $product['sizes']);
        $this->assertIsArray($product['category_object']);
        $this->assertSame($category->id, $product['category_object']['id']);
        $this->assertSame('Kain Batik', $product['category_object']['name']);

        // Plan-canonical category object (legacy string moved to category_name).
        $this->assertIsArray($product['category']);
        $this->assertSame($category->id, $product['category']['id']);
        $this->assertSame('Kain Batik', $product['category']['name']);

        // Legacy keys kept for Flutter.
        $this->assertArrayHasKey('image_url', $product);
        $this->assertArrayHasKey('reviews_count', $product);
        $this->assertArrayHasKey('size_stock', $product);
        $this->assertSame('Kain Batik', $product['category_name']);
        $this->assertSame($product['image'], $product['image_url']);
        $this->assertSame($product['sizes'], $product['size_stock']);
    }

    public function test_products_list_sorts_price_asc_and_price_low_alias(): void
    {
        $expensive = $this->createProduct(['price' => 300000, 'sku' => 'SKU-'.uniqid()]);
        $cheap = $this->createProduct(['price' => 100000, 'sku' => 'SKU-'.uniqid()]);
        $middle = $this->createProduct(['price' => 200000, 'sku' => 'SKU-'.uniqid()]);

        $canonical = $this->getJson('/api/products?sort=price_asc');
        $canonical->assertOk();
        $this->assertSame($cheap->id, $canonical->json('data.0.id'));
        $this->assertSame($expensive->id, $canonical->json('data.2.id'));

        $alias = $this->getJson('/api/products?sort=price_low');
        $alias->assertOk();
        $this->assertSame($cheap->id, $alias->json('data.0.id'));
        $this->assertSame($expensive->id, $alias->json('data.2.id'));

        $this->assertSame($middle->id, $canonical->json('data.1.id'));
    }

    public function test_products_list_sorts_price_desc_and_price_high_alias(): void
    {
        $expensive = $this->createProduct(['price' => 300000, 'sku' => 'SKU-'.uniqid()]);
        $this->createProduct(['price' => 100000, 'sku' => 'SKU-'.uniqid()]);
        $this->createProduct(['price' => 200000, 'sku' => 'SKU-'.uniqid()]);

        $canonical = $this->getJson('/api/products?sort=price_desc');
        $canonical->assertOk();
        $this->assertSame($expensive->id, $canonical->json('data.0.id'));

        $alias = $this->getJson('/api/products?sort=price_high');
        $alias->assertOk();
        $this->assertSame($expensive->id, $alias->json('data.0.id'));
    }

    public function test_product_detail_returns_data_wrapper(): void
    {
        $product = $this->createProduct([
            'size_stock' => ['S' => 5],
            'other_images' => ['https://example.com/other.jpg'],
        ]);

        $response = $this->getJson('/api/products/'.$product->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'stock',
                    'image',
                    'category',
                    'rating',
                    'review_count',
                    'description',
                    'other_images',
                    'sizes',
                    'size_stock',
                    'image_url',
                    'reviews_count',
                ],
            ])
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.description', 'Deskripsi produk uji');

        $data = $response->json('data');
        $this->assertSame(['https://example.com/other.jpg'], $data['other_images']);
        $this->assertSame(['S' => 5], $data['sizes']);
        $this->assertSame(['S' => 5], $data['size_stock']);
    }

    public function test_product_detail_returns_404_for_missing(): void
    {
        $this->getJson('/api/products/999999')->assertStatus(404);
    }

    public function test_categories_list_returns_data(): void
    {
        Category::create(['name' => 'Kain Batik']);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'image', 'image_url']],
            ])
            ->assertJsonPath('data.0.name', 'Kain Batik');

        $this->assertIsString($response->json('data.0.image'));
    }
}
