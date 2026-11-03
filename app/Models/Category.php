<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'nama_kategori',
        'slug',
        'description',
        'deskripsi',
        'image',
        'gambar',
    ];

    protected $appends = [
        'image_url',
        'gambar_url',
        'has_custom_image',
        'has_custom_gambar',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            $name = $category->attributes['name'] ?? $category->attributes['nama_kategori'] ?? '';
            $category->attributes['name'] = $name;
            $category->attributes['nama_kategori'] = $name;

            $desc = $category->attributes['description'] ?? $category->attributes['deskripsi'] ?? null;
            $category->attributes['description'] = $desc;
            $category->attributes['deskripsi'] = $desc;

            $img = $category->attributes['image'] ?? $category->attributes['gambar'] ?? null;
            $category->attributes['image'] = $img;
            $category->attributes['gambar'] = $img;

            if (empty($category->slug) && ! empty($name)) {
                $category->slug = Str::slug($name);
            }
        });
    }

    // ── English Relations ───────────────────────────────────────────────────────

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // ── Indonesian Compatibility Relations ─────────────────────────────────────

    public function produks(): HasMany
    {
        return $this->products();
    }

    // ── Mutators ───────────────────────────────────────────────────────────────

    public function setNamaKategoriAttribute(mixed $value): void
    {
        $this->attributes['nama_kategori'] = $value;
        $this->attributes['name'] = $value;
    }

    public function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['nama_kategori'] = $value;
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

    // ── Image Accessors ────────────────────────────────────────────────────────

    public function getHasCustomImageAttribute(): bool
    {
        $image = (string) ($this->image ?? $this->gambar ?? '');

        if ($image === '') {
            return false;
        }

        return str_starts_with($image, 'http')
            || Storage::disk('public')->exists($image)
            || file_exists(public_path($image));
    }

    public function getHasCustomGambarAttribute(): bool
    {
        return $this->has_custom_image;
    }

    public function getImageUrlAttribute(): string
    {
        $image = (string) ($this->image ?? $this->gambar ?? '');

        if (str_starts_with($image, 'http')) {
            return $image;
        }

        if ($image !== '' && Storage::disk('public')->exists($image)) {
            return asset('storage/'.$image);
        }

        if ($image !== '' && file_exists(public_path($image))) {
            return asset($image);
        }

        $haystack = strtolower($this->name ?? $this->nama_kategori ?? '');

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

    public function getGambarUrlAttribute(): string
    {
        return $this->image_url;
    }
}
