<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategoris';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'gambar',
    ];

    protected $appends = [
        'gambar_url',
        'has_custom_gambar',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }

    /**
     * Cek apakah kategori memiliki gambar upload kustom.
     */
    public function getHasCustomGambarAttribute(): bool
    {
        $gambar = (string) ($this->gambar ?? '');

        if ($gambar === '') {
            return false;
        }

        return str_starts_with($gambar, 'http')
            || Storage::disk('public')->exists($gambar)
            || file_exists(public_path($gambar));
    }

    /**
     * Dapatkan URL gambar kategori, lengkap dengan fallback gambar default beranda.
     */
    public function getGambarUrlAttribute(): string
    {
        $gambar = (string) ($this->gambar ?? '');

        if (str_starts_with($gambar, 'http')) {
            return $gambar;
        }

        if ($gambar !== '' && Storage::disk('public')->exists($gambar)) {
            return asset('storage/'.$gambar);
        }

        if ($gambar !== '' && file_exists(public_path($gambar))) {
            return asset($gambar);
        }

        $haystack = strtolower($this->nama_kategori);

        if (str_contains($haystack, 'kain')) {
            return asset('images/beranda/cloth-fabric.jpg');
        }

        if (str_contains($haystack, 'gamis') || str_contains($haystack, 'wanita') || str_contains($haystack, 'dress') || str_contains($haystack, 'blouse')) {
            return asset('images/beranda/woman-blouse.jpg');
        }

        if (str_contains($haystack, 'olahan') || str_contains($haystack, 'sisa') || str_contains($haystack, 'tas') || str_contains($haystack, 'aksesoris')) {
            return asset('images/beranda/bags-accessories.jpg');
        }

        if (str_contains($haystack, 'hampers') || str_contains($haystack, 'gift') || str_contains($haystack, 'box')) {
            return asset('images/beranda/gift-box.jpg');
        }

        if (str_contains($haystack, 'anak')) {
            return asset('images/beranda/folded-shirts.jpg');
        }

        if (str_contains($haystack, 'seragam') || str_contains($haystack, 'asn')) {
            return asset('images/beranda/folded-shirts.jpg');
        }

        return asset('images/beranda/hero-couple.jpg');
    }
}
