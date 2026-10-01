<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    public const LOW_STOCK_THRESHOLD = 10;

    protected $table = 'produks';

    protected $fillable = [
        'admin_id',
        'kategori_id',
        'nama',
        'jenis_produk',
        'sku',
        'kategori',
        'harga',
        'stok',
        'status',
        'deskripsi',
        'material',
        'ukuran',
        'gambar',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
    ];

    public static function statusForStock(int $stock): string
    {
        return match (true) {
            $stock <= 0 => 'Habis',
            $stock <= self::LOW_STOCK_THRESHOLD => 'Stok Menipis',
            default => 'Tersedia',
        };
    }

    /**
     * Relasi ke Kategori
     */
    public function kategoriRelasi(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi ke Admin pembuat/pengelola produk
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi ke ulasan produk
     */
    public function ulasans(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'produk_id');
    }

    /**
     * Get display category name
     */
    public function getCategoryNameAttribute(): string
    {
        return $this->kategoriRelasi?->nama_kategori ?? $this->kategori ?? 'Koleksi Batik';
    }

    /**
     * Get average rating attribute (hanya ulasan berstatus Disetujui)
     */
    public function getAvgRatingAttribute(): float
    {
        return (float) ($this->ulasans()->where('status', 'Disetujui')->avg('rating') ?: 0.0);
    }

    /**
     * URL gambar produk yang selalu valid: pakai file asli bila ada,
     * jika tidak pakai foto katalog lokal sesuai jenis produk.
     */
    public function getGambarUrlAttribute(): string
    {
        $gambar = (string) ($this->gambar ?? '');

        if (str_starts_with($gambar, 'http')) {
            return $gambar;
        }

        if ($gambar !== '' && file_exists(public_path($gambar))) {
            return asset($gambar);
        }

        if ($gambar !== '' && file_exists(storage_path('app/public/'.$gambar))) {
            return asset('storage/'.$gambar);
        }

        $haystack = strtolower($this->kategori.' '.$this->nama);

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
}
