<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const LOW_STOCK_THRESHOLD = 10;

    protected $table = 'products';

    protected $fillable = [
        'admin_id',
        'category_id',
        'kategori_id',
        'name',
        'nama',
        'product_type',
        'jenis_produk',
        'sku',
        'category',
        'kategori',
        'price',
        'harga',
        'stock',
        'stok',
        'status',
        'description',
        'deskripsi',
        'material',
        'size',
        'ukuran',
        'size_stock',
        'stok_ukuran',
        'image',
        'gambar',
        'other_images',
        'gambar_lainnya',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'harga' => 'decimal:2',
        'stock' => 'integer',
        'stok' => 'integer',
        'size_stock' => 'array',
        'stok_ukuran' => 'array',
        'other_images' => 'array',
        'gambar_lainnya' => 'array',
    ];

    public static function statusForStock(int $stock): string
    {
        return match (true) {
            $stock <= 0 => 'Habis',
            $stock <= self::LOW_STOCK_THRESHOLD => 'Stok Menipis',
            default => 'Tersedia',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Product $p): void {
            $name = $p->attributes['name'] ?? $p->attributes['nama'] ?? '';
            $p->attributes['name'] = $name;
            $p->attributes['nama'] = $name;

            $price = $p->attributes['price'] ?? $p->attributes['harga'] ?? 0;
            $p->attributes['price'] = $price;
            $p->attributes['harga'] = $price;

            $stock = $p->attributes['stock'] ?? $p->attributes['stok'] ?? 0;
            $p->attributes['stock'] = $stock;
            $p->attributes['stok'] = $stock;

            $category = $p->attributes['category'] ?? $p->attributes['kategori'] ?? '';
            $p->attributes['category'] = $category;
            $p->attributes['kategori'] = $category;

            $catId = $p->attributes['category_id'] ?? $p->attributes['kategori_id'] ?? null;
            $p->attributes['category_id'] = $catId;
            $p->attributes['kategori_id'] = $catId;

            $desc = $p->attributes['description'] ?? $p->attributes['deskripsi'] ?? null;
            $p->attributes['description'] = $desc;
            $p->attributes['deskripsi'] = $desc;

            $size = $p->attributes['size'] ?? $p->attributes['ukuran'] ?? null;
            $p->attributes['size'] = $size;
            $p->attributes['ukuran'] = $size;

            $ptype = $p->attributes['product_type'] ?? $p->attributes['jenis_produk'] ?? null;
            $p->attributes['product_type'] = $ptype;
            $p->attributes['jenis_produk'] = $ptype;

            $img = $p->attributes['image'] ?? $p->attributes['gambar'] ?? null;
            $p->attributes['image'] = $img;
            $p->attributes['gambar'] = $img;

            $rawStock = $p->attributes['size_stock'] ?? $p->attributes['stok_ukuran'] ?? null;
            if ($rawStock !== null) {
                $encStock = is_array($rawStock) ? json_encode($rawStock) : $rawStock;
                $p->attributes['size_stock'] = $encStock;
                $p->attributes['stok_ukuran'] = $encStock;
            }

            $rawImgs = $p->attributes['other_images'] ?? $p->attributes['gambar_lainnya'] ?? null;
            if ($rawImgs !== null) {
                $encImgs = is_array($rawImgs) ? json_encode($rawImgs) : $rawImgs;
                $p->attributes['other_images'] = $encImgs;
                $p->attributes['gambar_lainnya'] = $encImgs;
            }
        });
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $otherCol = match ($column) {
            'stok' => 'stock',
            'stock' => 'stok',
            default => null,
        };

        $result = parent::decrement($column, $amount, $extra);

        if ($otherCol !== null) {
            parent::decrement($otherCol, $amount, $extra);
        }

        return $result;
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $otherCol = match ($column) {
            'stok' => 'stock',
            'stock' => 'stok',
            default => null,
        };

        $result = parent::increment($column, $amount, $extra);

        if ($otherCol !== null) {
            parent::increment($otherCol, $amount, $extra);
        }

        return $result;
    }

    // ── English Relations ───────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ── Indonesian Compatibility Relations ─────────────────────────────────────

    public function kategoriRelasi(): BelongsTo
    {
        return $this->category();
    }

    public function ulasans(): HasMany
    {
        return $this->reviews();
    }

    // ── Mutators (setting either updates both) ──────────────────────────────────

    public function setNamaAttribute(mixed $value): void
    {
        $this->attributes['nama'] = $value;
        $this->attributes['name'] = $value;
    }

    public function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['nama'] = $value;
    }

    public function setHargaAttribute(mixed $value): void
    {
        $this->attributes['harga'] = $value;
        $this->attributes['price'] = $value;
    }

    public function setPriceAttribute(mixed $value): void
    {
        $this->attributes['price'] = $value;
        $this->attributes['harga'] = $value;
    }

    public function setStokAttribute(mixed $value): void
    {
        $this->attributes['stok'] = $value;
        $this->attributes['stock'] = $value;
    }

    public function setStockAttribute(mixed $value): void
    {
        $this->attributes['stock'] = $value;
        $this->attributes['stok'] = $value;
    }

    public function setKategoriAttribute(mixed $value): void
    {
        $this->attributes['kategori'] = $value;
        $this->attributes['category'] = $value;
    }

    public function setCategoryAttribute(mixed $value): void
    {
        $this->attributes['category'] = $value;
        $this->attributes['kategori'] = $value;
    }

    public function setKategoriIdAttribute(mixed $value): void
    {
        $this->attributes['kategori_id'] = $value;
        $this->attributes['category_id'] = $value;
    }

    public function setCategoryIdAttribute(mixed $value): void
    {
        $this->attributes['category_id'] = $value;
        $this->attributes['kategori_id'] = $value;
    }

    public function setDeskripsiAttribute(mixed $value): void
    {
        $this->attributes['deskripsi'] = $value;
        $this->attributes['description'] = $value;
    }

    public function setDescriptionAttribute(mixed $value): void
    {
        $this->attributes['description'] = $value;
        $this->attributes['deskripsi'] = $value;
    }

    public function setGambarAttribute(mixed $value): void
    {
        $this->attributes['gambar'] = $value;
        $this->attributes['image'] = $value;
    }

    public function setImageAttribute(mixed $value): void
    {
        $this->attributes['image'] = $value;
        $this->attributes['gambar'] = $value;
    }

    public function setUkuranAttribute(mixed $value): void
    {
        $this->attributes['ukuran'] = $value;
        $this->attributes['size'] = $value;
    }

    public function setSizeAttribute(mixed $value): void
    {
        $this->attributes['size'] = $value;
        $this->attributes['ukuran'] = $value;
    }

    public function setJenisProdukAttribute(mixed $value): void
    {
        $this->attributes['jenis_produk'] = $value;
        $this->attributes['product_type'] = $value;
    }

    public function setProductTypeAttribute(mixed $value): void
    {
        $this->attributes['product_type'] = $value;
        $this->attributes['jenis_produk'] = $value;
    }

    public function setStokUkuranAttribute(mixed $value): void
    {
        $encoded = is_array($value) ? json_encode($value) : $value;
        $this->attributes['size_stock'] = $encoded;
        $this->attributes['stok_ukuran'] = $encoded;
    }

    public function setSizeStockAttribute(mixed $value): void
    {
        $encoded = is_array($value) ? json_encode($value) : $value;
        $this->attributes['size_stock'] = $encoded;
        $this->attributes['stok_ukuran'] = $encoded;
    }

    public function getStokUkuranAttribute(): mixed
    {
        return $this->size_stock;
    }

    public function getSizeStockAttribute(): mixed
    {
        $raw = $this->attributes['size_stock'] ?? $this->attributes['stok_ukuran'] ?? null;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
        }

        return $raw;
    }

    public function setGambarLainnyaAttribute(mixed $value): void
    {
        $encoded = is_array($value) ? json_encode($value) : $value;
        $this->attributes['other_images'] = $encoded;
        $this->attributes['gambar_lainnya'] = $encoded;
    }

    public function setOtherImagesAttribute(mixed $value): void
    {
        $encoded = is_array($value) ? json_encode($value) : $value;
        $this->attributes['other_images'] = $encoded;
        $this->attributes['gambar_lainnya'] = $encoded;
    }

    public function getGambarLainnyaAttribute(): mixed
    {
        return $this->other_images;
    }

    public function getOtherImagesAttribute(): mixed
    {
        $raw = $this->attributes['other_images'] ?? $this->attributes['gambar_lainnya'] ?? null;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
        }

        return $raw;
    }

    // ── Helper Attributes ──────────────────────────────────────────────────────

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? $this->kategoriRelasi?->nama_kategori ?? $this->category ?? $this->kategori ?? 'Koleksi Batik';
    }

    public function getAvgRatingAttribute(): float
    {
        return (float) ($this->reviews()->where('status', 'Disetujui')->avg('rating') ?: 0.0);
    }

    public function getImageUrlAttribute(): string
    {
        $image = (string) ($this->image ?? $this->gambar ?? '');

        if (str_starts_with($image, 'http')) {
            return $image;
        }

        if ($image !== '' && file_exists(public_path($image))) {
            return asset($image);
        }

        if ($image !== '' && file_exists(storage_path('app/public/'.$image))) {
            return asset('storage/'.$image);
        }

        $haystack = strtolower(($this->category ?? $this->kategori ?? '').' '.($this->name ?? $this->nama ?? ''));

        if (str_contains($haystack, 'hampers') || str_contains($haystack, 'gift') || str_contains($haystack, 'box')) {
            return asset('images/beranda/gift-box.jpg');
        }

        if (str_contains($haystack, 'tas') || str_contains($haystack, 'tote') || str_contains($haystack, 'pouch')
            || str_contains($haystack, 'dompet') || str_contains($haystack, 'sling') || str_contains($haystack, 'passport')
            || str_contains($haystack, 'olahan') || str_contains($haystack, 'sisa')) {
            return asset('images/beranda/bags-accessories.jpg');
        }

        if (str_contains($haystack, 'blouse') || str_contains($haystack, 'dress') || str_contains($haystack, 'gamis')
            || str_contains($haystack, 'daster') || str_contains($haystack, 'wanita')) {
            return asset('images/beranda/woman-blouse.jpg');
        }

        if (str_contains($haystack, 'kain')) {
            return asset('images/beranda/cloth-fabric.jpg');
        }

        return asset('images/beranda/folded-shirts.jpg');
    }

    public function getGambarUrlAttribute(): string
    {
        return $this->image_url;
    }

    // ── Stock per Size Methods ─────────────────────────────────────────────────

    /**
     * @return array<string, int>
     */
    public function getSizeStockArray(): array
    {
        $sizeStock = $this->size_stock ?? $this->stok_ukuran;
        if (is_array($sizeStock) && ! empty($sizeStock)) {
            $result = [];
            foreach ($sizeStock as $size => $stock) {
                $result[(string) $size] = max(0, (int) $stock);
            }

            return $result;
        }

        $cat = (string) ($this->category ?? $this->kategori ?? '');
        $nm = (string) ($this->name ?? $this->nama ?? '');

        $isClothing = in_array($cat, ['Baju Batik', 'Seragam ASN', 'Wanita', 'Pria'])
            || str_contains(strtolower($nm), 'kemeja')
            || str_contains(strtolower($nm), 'blouse')
            || str_contains(strtolower($nm), 'dress')
            || str_contains(strtolower($nm), 'outer');

        $total = (int) ($this->stock ?? $this->stok ?? 0);

        if ($isClothing) {
            if ($total <= 0) {
                return ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, 'XXL' => 0, 'All Size' => 0];
            }

            $base = intdiv($total, 5);
            $remainder = $total % 5;

            return [
                'S' => $base + ($remainder > 0 ? 1 : 0),
                'M' => $base + ($remainder > 1 ? 1 : 0),
                'L' => $base + ($remainder > 2 ? 1 : 0),
                'XL' => $base + ($remainder > 3 ? 1 : 0),
                'XXL' => $base,
                'All Size' => $base,
            ];
        }

        return ['All Size' => max(0, $total)];
    }

    public function getStokUkuranArray(): array
    {
        return $this->getSizeStockArray();
    }

    public function getStockForSize(?string $size): int
    {
        $arr = $this->getSizeStockArray();

        if (empty($size) || ! isset($arr[$size])) {
            if (count($arr) === 1) {
                return (int) reset($arr);
            }

            return (int) (($this->stock ?? $this->stok) ?: array_sum($arr));
        }

        return (int) $arr[$size];
    }

    public function getStokForUkuran(?string $ukuran): int
    {
        return $this->getStockForSize($ukuran);
    }

    public function isSizeAvailable(?string $size): bool
    {
        return $this->getStockForSize($size) > 0;
    }

    public function isUkuranAvailable(?string $ukuran): bool
    {
        return $this->isSizeAvailable($ukuran);
    }

    public function decrementStockForSize(?string $size, int $qty): void
    {
        $arr = $this->getSizeStockArray();

        if ($size && isset($arr[$size])) {
            $arr[$size] = max(0, ((int) $arr[$size]) - $qty);
            $this->size_stock = $arr;
            $this->stok_ukuran = $arr;
            $newTotal = array_sum($arr);
            $this->stock = $newTotal;
            $this->stok = $newTotal;
        } else {
            $newTotal = max(0, ((int) ($this->stock ?? $this->stok ?? 0)) - $qty);
            $this->stock = $newTotal;
            $this->stok = $newTotal;
            $currentSizeStock = $this->size_stock ?? $this->stok_ukuran;
            if (! empty($currentSizeStock) && is_array($currentSizeStock)) {
                $firstKey = array_key_first($currentSizeStock);
                if ($firstKey !== null) {
                    $currentSizeStock[$firstKey] = max(0, ((int) $currentSizeStock[$firstKey]) - $qty);
                    $this->size_stock = $currentSizeStock;
                    $this->stok_ukuran = $currentSizeStock;
                }
            }
        }

        $totalStock = (int) ($this->stock ?? $this->stok);
        if ($totalStock == 0) {
            $this->status = 'Habis';
        } elseif ($totalStock <= 15) {
            $this->status = 'Stok Menipis';
        } else {
            $this->status = 'Tersedia';
        }

        $this->save();
    }

    public function decrementStokForUkuran(?string $ukuran, int $qty): void
    {
        $this->decrementStockForSize($ukuran, $qty);
    }

    public function incrementStockForSize(?string $size, int $qty): void
    {
        $arr = $this->getSizeStockArray();

        if ($size && isset($arr[$size])) {
            $arr[$size] = ((int) $arr[$size]) + $qty;
            $this->size_stock = $arr;
            $this->stok_ukuran = $arr;
            $newTotal = array_sum($arr);
            $this->stock = $newTotal;
            $this->stok = $newTotal;
        } else {
            $currentVal = ($this->stock !== null && $this->stok !== null) ? min((int) $this->stock, (int) $this->stok) : ((int) ($this->stok ?? $this->stock ?? 0));
            $newTotal = $currentVal + $qty;
            $this->stock = $newTotal;
            $this->stok = $newTotal;
            $currentSizeStock = $this->size_stock ?? $this->stok_ukuran;
            if (! empty($currentSizeStock) && is_array($currentSizeStock)) {
                $firstKey = array_key_first($currentSizeStock);
                if ($firstKey !== null) {
                    $currentSizeStock[$firstKey] = ((int) $currentSizeStock[$firstKey]) + $qty;
                    $this->size_stock = $currentSizeStock;
                    $this->stok_ukuran = $currentSizeStock;
                }
            }
        }

        $totalStock = (int) ($this->stock ?? $this->stok);
        if ($totalStock == 0) {
            $this->status = 'Habis';
        } elseif ($totalStock <= 15) {
            $this->status = 'Stok Menipis';
        } else {
            $this->status = 'Tersedia';
        }

        $this->save();
    }

    public function incrementStokForUkuran(?string $ukuran, int $qty): void
    {
        $this->incrementStockForSize($ukuran, $qty);
    }
}
