<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndSettingSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'Baju Batik',
                'deskripsi' => 'Koleksi busana kemeja, tunik, dan pakaian jadi batik pria dan wanita.',
            ],
            [
                'nama_kategori' => 'Kain Batik',
                'deskripsi' => 'Kain lembaran batik tulis, batik cap autentik, dan bahan katun primissima pilihan.',
            ],
            [
                'nama_kategori' => 'Seragam ASN',
                'deskripsi' => 'Koleksi batik seragam resmi kedinasan, ASN, dan instansi pemerintahan berstandar nasional.',
            ],
            [
                'nama_kategori' => 'Olahan Kain Sisa',
                'deskripsi' => 'Produk upcycled ramah lingkungan berbahan dasar sisa kain batik, seperti pouch, tote bag, dan aksesori artisan.',
            ],
        ];

        foreach ($categories as $cat) {
            $kategori = Kategori::firstOrCreate(
                ['nama_kategori' => $cat['nama_kategori']],
                [
                    'slug' => Str::slug($cat['nama_kategori']),
                    'deskripsi' => $cat['deskripsi'],
                ]
            );

            // Connect existing products to this kategori
            Produk::where('kategori', $cat['nama_kategori'])->update(['kategori_id' => $kategori->id]);
        }

        StoreSetting::current();
    }
}
