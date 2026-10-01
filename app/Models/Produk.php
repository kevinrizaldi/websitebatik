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
        'gambar',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
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
     * Get average rating attribute
     */
    public function getAvgRatingAttribute(): float
    {
        return (float) ($this->ulasans()->where('status', 'approved')->avg('rating') ?: 5.0);
    }
}
