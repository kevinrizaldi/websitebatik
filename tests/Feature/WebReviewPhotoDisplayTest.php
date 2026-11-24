<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WebReviewPhotoDisplayTest extends TestCase
{
    use RefreshDatabase;

    private string $photoFile = 'reviews/web-display-test.jpg';

    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(storage_path('app/public/reviews'));
        file_put_contents(storage_path('app/public/'.$this->photoFile), 'fake-jpeg-bytes');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/public/'.$this->photoFile));

        parent::tearDown();
    }

    private function productWithApprovedReviewPhoto(): Product
    {
        $product = Product::create([
            'name' => 'Batik Uji Tampil Web',
            'sku' => 'SKU-WEB-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Produk untuk uji tampil foto ulasan di web',
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'customer_name' => 'Pembeli Web',
            'rating' => 5,
            'comment' => 'Komentar uji tampil foto web',
            'photos' => [$this->photoFile],
            'status' => 'Disetujui',
        ]);

        return $product;
    }

    public function test_produk_detail_menampilkan_foto_ulasan_dengan_url_media(): void
    {
        $product = $this->productWithApprovedReviewPhoto();

        $response = $this->get('/produk-detail/'.$product->id);

        $response->assertOk();
        $response->assertSee('api/media/storage/'.$this->photoFile, false);
        $response->assertDontSee('src="reviews/', false);
    }

    public function test_admin_ulasans_menampilkan_foto_ulasan_dengan_url_media(): void
    {
        $this->productWithApprovedReviewPhoto();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ulasans.index'));

        $response->assertOk();
        $response->assertSee('api/media/storage/'.$this->photoFile, false);
        $response->assertDontSee('src="reviews/', false);
    }

    public function test_produk_detail_menampilkan_foto_ulasan_image_path_via_media_url(): void
    {
        $product = $this->productWithApprovedReviewWithImagePath();

        $response = $this->get('/produk-detail/'.$product->id);

        $response->assertOk();
        $response->assertSee('api/media/storage/'.$this->photoFile, false);
        $response->assertDontSee('src="http://localhost/storage/', false);
    }

    private function productWithApprovedReviewWithImagePath(): Product
    {
        $product = Product::create([
            'name' => 'Batik Uji Tampil Web Image Path',
            'sku' => 'SKU-WEB-IMG-'.uniqid(),
            'category' => 'Kain Batik',
            'price' => 100000,
            'stock' => 10,
            'status' => 'Tersedia',
            'description' => 'Produk untuk uji foto ulasan via image_path di web',
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'customer_name' => 'Pembeli Web',
            'rating' => 5,
            'comment' => 'Komentar uji foto via image_path',
            'image_path' => $this->photoFile,
            'status' => 'Disetujui',
        ]);

        return $product;
    }
}