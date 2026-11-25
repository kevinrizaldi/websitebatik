<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageProductCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_carousel_uses_newest_and_highest_rated_products(): void
    {
        $newestProduct = $this->createProduct('Batik Terbaru', 'CAROUSEL-NEW', now(), 300000);
        $secondNewestProduct = $this->createProduct('Batik Baru Kedua', 'CAROUSEL-NEW-2', now()->subDay());
        $highestRatedProduct = $this->createProduct('Batik Rating Lima', 'CAROUSEL-RATING-5', now()->subDays(3));
        $secondHighestRatedProduct = $this->createProduct('Batik Rating Empat', 'CAROUSEL-RATING-4', now()->subDays(4));
        $unapprovedRatingProduct = $this->createProduct('Batik Rating Belum Disetujui', 'CAROUSEL-PENDING', now()->subDays(5));

        $this->createReview($highestRatedProduct, 5, 'Disetujui');
        $this->createReview($secondHighestRatedProduct, 4, 'Disetujui');
        $this->createReview($unapprovedRatingProduct, 5, 'Menunggu');

        $response = $this->get(route('home'));
        $response
            ->assertOk()
            ->assertSeeInOrder([
                'Batik Terbaru',
                'Batik Baru Kedua',
                'Batik Rating Lima',
                'Batik Rating Empat',
            ]);

        $this->assertSame(4, substr_count($response->getContent(), 'aria-roledescription="slide"'));

        foreach ([$newestProduct, $secondNewestProduct, $highestRatedProduct, $secondHighestRatedProduct] as $product) {
            $response->assertSee(route('produk.detail', ['id' => $product->id]));
        }

        $response->assertSee('Rp 300.000')
            ->assertSee('Rp 150.000')
            ->assertSee('50% OFF');
    }

    private function createProduct(string $name, string $sku, mixed $createdAt, ?int $crossedOutPrice = null): Product
    {
        return Product::create([
            'nama' => $name,
            'sku' => $sku,
            'kategori' => 'Batik',
            'harga' => 150000,
            'harga_coret' => $crossedOutPrice,
            'stok' => 10,
            'status' => 'Tersedia',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createReview(Product $product, int $rating, string $status): void
    {
        Review::create([
            'produk_id' => $product->id,
            'customer_name' => 'Pelanggan Uji',
            'rating' => $rating,
            'comment' => 'Produk sangat bagus.',
            'status' => $status,
        ]);
    }
}
