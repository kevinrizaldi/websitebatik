<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';

    protected $fillable = [
        'nama',
        'sku',
        'kategori',
        'harga',
        'stok',
        'status',
        'deskripsi',
        'material',
        'gambar',
    ];

    /**
     * Relasi ke ulasan produk
     */
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'produk_id');
    }
}
