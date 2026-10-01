<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

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
        'stok_ukuran',
        'gambar',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'stok_ukuran' => 'array',
    ];

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

    /**
     * Dapatkan daftar stok per ukuran dalam bentuk array asosiatif [ukuran => stok].
     *
     * @return array<string, int>
     */
    public function getStokUkuranArray(): array
    {
        if (is_array($this->stok_ukuran) && ! empty($this->stok_ukuran)) {
            $result = [];
            foreach ($this->stok_ukuran as $size => $stock) {
                $result[(string) $size] = max(0, (int) $stock);
            }

            return $result;
        }

        // Jika kategori pakaian atau nama busana
        $isClothing = in_array($this->kategori, ['Baju Batik', 'Seragam ASN', 'Wanita', 'Pria'])
            || str_contains(strtolower($this->nama), 'kemeja')
            || str_contains(strtolower($this->nama), 'blouse')
            || str_contains(strtolower($this->nama), 'dress')
            || str_contains(strtolower($this->nama), 'outer');

        if ($isClothing) {
            $total = (int) $this->stok;
            if ($total <= 0) {
                return ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, 'XXL' => 0];
            }

            // Distribusi seimbang antar ukuran default S, M, L, XL, XXL
            $base = intdiv($total, 5);
            $remainder = $total % 5;

            return [
                'S' => $base + ($remainder > 0 ? 1 : 0),
                'M' => $base + ($remainder > 1 ? 1 : 0),
                'L' => $base + ($remainder > 2 ? 1 : 0),
                'XL' => $base + ($remainder > 3 ? 1 : 0),
                'XXL' => $base,
            ];
        }

        return ['All Size' => max(0, (int) $this->stok)];
    }

    /**
     * Dapatkan sisa stok untuk ukuran tertentu.
     */
    public function getStokForUkuran(?string $ukuran): int
    {
        $arr = $this->getStokUkuranArray();

        if (empty($ukuran) || ! isset($arr[$ukuran])) {
            if (count($arr) === 1) {
                return (int) reset($arr);
            }

            return (int) ($arr[$ukuran] ?? $this->stok);
        }

        return (int) $arr[$ukuran];
    }

    /**
     * Cek apakah suatu ukuran tersedia (stok > 0).
     */
    public function isUkuranAvailable(?string $ukuran): bool
    {
        return $this->getStokForUkuran($ukuran) > 0;
    }

    /**
     * Kurangi stok untuk ukuran tertentu dan perbarui stok global & status produk.
     */
    public function decrementStokForUkuran(?string $ukuran, int $qty): void
    {
        $arr = $this->getStokUkuranArray();

        if ($ukuran && isset($arr[$ukuran])) {
            $arr[$ukuran] = max(0, ((int) $arr[$ukuran]) - $qty);
            $this->stok_ukuran = $arr;
            $this->stok = array_sum($arr);
        } else {
            $this->stok = max(0, ((int) $this->stok) - $qty);
            if (! empty($this->stok_ukuran) && is_array($this->stok_ukuran)) {
                $firstKey = array_key_first($this->stok_ukuran);
                if ($firstKey !== null) {
                    $stokUkuran = $this->stok_ukuran;
                    $stokUkuran[$firstKey] = max(0, ((int) $stokUkuran[$firstKey]) - $qty);
                    $this->stok_ukuran = $stokUkuran;
                }
            }
        }

        if ($this->stok == 0) {
            $this->status = 'Habis';
        } elseif ($this->stok <= 15) {
            $this->status = 'Stok Menipis';
        } else {
            $this->status = 'Tersedia';
        }

        $this->save();
    }

    /**
     * Kembalikan / tambah stok untuk ukuran tertentu dan perbarui stok global & status produk.
     */
    public function incrementStokForUkuran(?string $ukuran, int $qty): void
    {
        $arr = $this->getStokUkuranArray();

        if ($ukuran && isset($arr[$ukuran])) {
            $arr[$ukuran] = ((int) $arr[$ukuran]) + $qty;
            $this->stok_ukuran = $arr;
            $this->stok = array_sum($arr);
        } else {
            $this->stok = ((int) $this->stok) + $qty;
            if (! empty($this->stok_ukuran) && is_array($this->stok_ukuran)) {
                $firstKey = array_key_first($this->stok_ukuran);
                if ($firstKey !== null) {
                    $stokUkuran = $this->stok_ukuran;
                    $stokUkuran[$firstKey] = ((int) $stokUkuran[$firstKey]) + $qty;
                    $this->stok_ukuran = $stokUkuran;
                }
            }
        }

        if ($this->stok == 0) {
            $this->status = 'Habis';
        } elseif ($this->stok <= 15) {
            $this->status = 'Stok Menipis';
        } else {
            $this->status = 'Tersedia';
        }

        $this->save();
    }
}
