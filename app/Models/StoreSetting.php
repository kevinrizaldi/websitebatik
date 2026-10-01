<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $fillable = [
        'nama_toko',
        'kota_asal',
        'provinsi_asal',
        'kode_pos_asal',
        'alamat_asal',
        'no_telepon_toko',
        'email_toko',
        'deskripsi_toko',
    ];

    /**
     * Get or create the singleton store setting instance.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'nama_toko' => 'Hamzah Style Official',
            'kota_asal' => 'Kota Surakarta (Solo)',
            'provinsi_asal' => 'Jawa Tengah',
            'kode_pos_asal' => '57141',
            'alamat_asal' => 'Jl. Slamet Riyadi No. 120, Laweyan, Surakarta, Jawa Tengah',
            'no_telepon_toko' => '0812-3456-7890',
            'email_toko' => 'official@hamzahstyle.com',
            'deskripsi_toko' => 'Produsen Busana Batik Nusantara & Kerajinan Upcycled Olahan Kain Sisa Berkualitas Tinggi.',
        ]);
    }
}
