<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed product categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Baju Batik',
                'nama_kategori' => 'Baju Batik',
                'slug' => 'baju-batik',
                'description' => 'Koleksi busana kemeja, tunik, dan pakaian jadi batik pria dan wanita.',
                'deskripsi' => 'Koleksi busana kemeja, tunik, dan pakaian jadi batik pria dan wanita.',
            ],
            [
                'name' => 'Kain Batik',
                'nama_kategori' => 'Kain Batik',
                'slug' => 'kain-batik',
                'description' => 'Kain lembaran batik tulis, batik cap autentik, dan bahan katun primissima pilihan.',
                'deskripsi' => 'Kain lembaran batik tulis, batik cap autentik, dan bahan katun primissima pilihan.',
            ],
            [
                'name' => 'Seragam ASN',
                'nama_kategori' => 'Seragam ASN',
                'slug' => 'seragam-asn',
                'description' => 'Koleksi batik seragam resmi kedinasan, ASN, dan instansi pemerintahan berstandar nasional.',
                'deskripsi' => 'Koleksi batik seragam resmi kedinasan, ASN, dan instansi pemerintahan berstandar nasional.',
            ],
            [
                'name' => 'Olahan Kain Sisa',
                'nama_kategori' => 'Olahan Kain Sisa',
                'slug' => 'olahan-kain-sisa',
                'description' => 'Produk upcycled ramah lingkungan berbahan dasar sisa kain batik, seperti pouch, tote bag, dan aksesori artisan.',
                'deskripsi' => 'Produk upcycled ramah lingkungan berbahan dasar sisa kain batik, seperti pouch, tote bag, dan aksesori artisan.',
            ],
            [
                'name' => 'Batik wanita',
                'nama_kategori' => 'Batik wanita',
                'slug' => 'batik-wanita',
                'description' => 'batik wanita yang stylish dengan zaman',
                'deskripsi' => 'batik wanita yang stylish dengan zaman',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
