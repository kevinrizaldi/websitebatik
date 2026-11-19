<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List all product categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get()
            ->map(function (Category $cat): array {
                $image = MediaUrl::resolve(
                    $cat->image ?: $cat->gambar,
                    $cat->name ?: $cat->nama_kategori,
                );

                return [
                    'id' => $cat->id,
                    'name' => $cat->name ?: $cat->nama_kategori,
                    'slug' => $cat->slug,
                    'description' => $cat->description ?: $cat->deskripsi,
                    'image_url' => $image,
                    'image' => $image,
                ];
            });

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * List products with optional search, category filter, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $sort = (string) $request->query('sort', 'latest');
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));

        $query = Product::query()
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category !== '' && strtolower($category) !== 'semua', function (Builder $q) use ($category): void {
                $q->where(function (Builder $inner) use ($category): void {
                    $inner->where('category', $category)
                        ->orWhere('kategori', $category);

                    if (ctype_digit($category)) {
                        $inner->orWhere('category_id', (int) $category);
                    }
                });
            });

        match ($sort) {
            'price_asc', 'price_low' => $query->orderBy('price'),
            'price_desc', 'price_high' => $query->orderByDesc('price'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(fn (Product $p): array => $this->formatProduct($p))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show a single product with reviews.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['reviews.user:id,name,avatar', 'category:id,name'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return response()->json([
            'data' => $this->formatProduct($product, includeReviews: true),
        ]);
    }

    /**
     * Public review list for a product (only approved reviews).
     */
    public function reviews(Product $product): JsonResponse
    {
        $reviews = $product->reviews()
            ->where('status', 'Disetujui')
            ->latest()
            ->get()
            ->map(fn ($review): array => [
                'id' => $review->id,
                'user_name' => $review->user?->name ?? 'Pelanggan',
                'rating' => (int) $review->rating,
                'comment' => $review->comment ?? $review->komentar ?? '',
                'status' => $review->status,
                'created_at' => $review->created_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'data' => $reviews,
        ]);
    }

    private function formatProduct(Product $product, bool $includeReviews = false): array
    {
        $mainImage = MediaUrl::fromProduct($product);
        $rawOthers = $product->other_images ?? $product->gambar_lainnya ?? [];
        $otherUrls = collect(is_array($rawOthers) ? $rawOthers : [])
            ->map(fn ($img): ?string => MediaUrl::resolve(is_string($img) ? $img : null))
            ->filter()
            ->values()
            ->all();

        $sizeStock = $product->size_stock ?? $product->stok_ukuran ?? [];
        $sizeStock = is_array($sizeStock) ? $sizeStock : [];

        $categoryRelation = $product->relationLoaded('category') ? $product->getRelation('category') : null;
        $categoryName = $categoryRelation?->name ?: ($product->category ?: $product->kategori);

        $data = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name ?: $product->nama,
            'category' => [
                'id' => $categoryRelation?->id ?? $product->category_id ?? $product->kategori_id ?? null,
                'name' => $categoryName,
            ],
            'category_name' => $categoryName,
            'category_object' => [
                'id' => $categoryRelation?->id ?? $product->category_id ?? $product->kategori_id ?? null,
                'name' => $categoryName,
            ],
            'product_type' => $product->product_type ?: $product->jenis_produk,
            'price' => (float) ($product->price ?? $product->harga ?? 0),
            'stock' => (int) ($product->stock ?? $product->stok ?? 0),
            'status' => $product->status,
            'description' => $product->description ?: $product->deskripsi,
            'material' => $product->material,
            'size' => $product->size ?: $product->ukuran,
            'size_stock' => $sizeStock,
            'sizes' => $sizeStock,
            'image_url' => $mainImage,
            'image' => $mainImage,
            'other_images' => $otherUrls,
            'rating' => round((float) ($product->reviews_avg_rating ?? 0), 1),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'review_count' => (int) ($product->reviews_count ?? 0),
        ];

        if ($includeReviews) {
            $data['reviews'] = $product->reviews->map(fn ($review): array => [
                'id' => $review->id,
                'user_name' => $review->user?->name ?? 'Pelanggan',
                'rating' => (int) $review->rating,
                'comment' => $review->comment ?? $review->komentar ?? '',
                'created_at' => $review->created_at?->toIso8601String(),
            ])->values()->all();
        }

        return $data;
    }
}
