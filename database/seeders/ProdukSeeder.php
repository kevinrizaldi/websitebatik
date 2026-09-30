<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produks = [
            [
                'sku' => 'BTK-PRG-001',
                'nama' => 'Kemeja Batik Parang Kusumo Pria',
                'kategori' => 'Baju Batik',
                'harga' => 375000,
                'stok' => 20,
                'status' => 'Tersedia',
                'material' => 'Katun Primissima Halus (Furing Katun Nyaman)',
                'deskripsi' => 'Kemeja pria lengan panjang berdesain eksklusif dengan motif Parang Kusumo khas Solo yang berwibawa dan bernilai seni tinggi.',
                'gambar' => 'produk/batik-kemeja-pria.jpg',
            ],
            [
                'sku' => 'BTK-MGM-002',
                'nama' => 'Blouse Batik Megamendung Modern',
                'kategori' => 'Baju Batik',
                'harga' => 285000,
                'stok' => 15,
                'status' => 'Tersedia',
                'material' => 'Rayon Viscose Organik Lembut & Adem',
                'deskripsi' => 'Blouse wanita berpotongan kasual elegan dengan motif Megamendung Cirebon gradasi halus untuk suasana kerja maupun santai.',
                'gambar' => 'produk/batik-blouse-modern.jpg',
            ],
            [
                'sku' => 'BTK-KWG-003',
                'nama' => 'Kain Batik Tulis Motif Kawung Solo',
                'kategori' => 'Kain Batik',
                'harga' => 450000,
                'stok' => 12,
                'status' => 'Tersedia',
                'material' => 'Mori Primissima Halus 100% Katun (2.2m x 1.15m)',
                'deskripsi' => 'Lembaran kain batik canting asli karya pengrajin Solo dengan motif Kawung bermakna kesucian, umur panjang, dan kebijaksanaan.',
                'gambar' => 'produk/batik-kain-parang.jpg',
            ],
            [
                'sku' => 'BTK-TRN-004',
                'nama' => 'Dress Batik Tulis Motif Truntum',
                'kategori' => 'Baju Batik',
                'harga' => 590000,
                'stok' => 10,
                'status' => 'Stok Menipis',
                'material' => 'Sutra Crepe Halus dengan Lapisan Furing Lembut',
                'deskripsi' => 'Gaun pesta panjang siluet anggun berhias motif Truntum simbol cinta abadi, sangat memukau untuk perayaan resepsi dan formal.',
                'gambar' => 'produk/batik-dress-kawung.jpg',
            ],
            [
                'sku' => 'BTK-TNN-005',
                'nama' => 'Outer Batik Kombinasi Tenun Etnik',
                'kategori' => 'Baju Batik',
                'harga' => 320000,
                'stok' => 18,
                'status' => 'Tersedia',
                'material' => 'Katun Batik Cap & Tenun Ikat ATBM',
                'deskripsi' => 'Cardigan kimono modern unisex memadukan kain batik cap dan aksen tenun ikat artisan yang unik dan penuh karakter.',
                'gambar' => 'produk/batik-blouse-modern.jpg',
            ],
            [
                'sku' => 'BTK-SDM-006',
                'nama' => 'Sarung Batik Sido Mulyo Klasik',
                'kategori' => 'Kain Batik',
                'harga' => 240000,
                'stok' => 25,
                'status' => 'Tersedia',
                'material' => 'Katun Gamelan Halus & Tebal',
                'deskripsi' => 'Sarung batik cap khas nusantara dengan warna sogan tradisional yang awet, adem, dan nyaman dipakai ibadah serta kegiatan adat.',
                'gambar' => 'produk/batik-kain-parang.jpg',
            ],
            [
                'sku' => 'BTK-OLH-007',
                'nama' => 'Artisan Patchwork Batik Canvas Tote Bag',
                'kategori' => 'Olahan Kain Sisa',
                'harga' => 245000,
                'stok' => 16,
                'status' => 'Tersedia',
                'material' => 'Kanvas Alami 14oz & Potongan Kain Batik Sirkular Upcycled',
                'deskripsi' => 'Tas jinjing berkonsep zero-waste artisan dengan perca batik pilihan berkualitas tinggi, tali jinjing kuat muat laptop 14 inch.',
                'gambar' => 'produk/batik-tas-patchwork.jpg',
            ],
            [
                'sku' => 'BTK-OLH-008',
                'nama' => 'Dompet & Zipper Pouch Batik Multifungsi',
                'kategori' => 'Olahan Kain Sisa',
                'harga' => 155000,
                'stok' => 30,
                'status' => 'Tersedia',
                'material' => 'Kain Perca Batik Cap Halus & Busa Pelindung',
                'deskripsi' => 'Pouch serbaguna beresleting YKK halus untuk menyimpan alat tulis, kosmetik, gadget, hingga aksesoris perjalanan harian Anda.',
                'gambar' => 'produk/batik-tas-patchwork.jpg',
            ],
            [
                'sku' => 'BTK-PRG-009',
                'nama' => 'Kemeja Batik Parang Seling Slim Fit',
                'kategori' => 'Baju Batik',
                'harga' => 385000,
                'stok' => 14,
                'status' => 'Tersedia',
                'material' => 'Katun Primissima Halus (Furing Katun Hero)',
                'deskripsi' => 'Kemeja formal pria potongan slim-fit dengan kancing tertutup dan motif Parang Seling simetris presisi tinggi.',
                'gambar' => 'produk/batik-kemeja-pria.jpg',
            ],
            [
                'sku' => 'BTK-DRS-010',
                'nama' => 'Dress Asimetris Kawung Gold Foil Pesta',
                'kategori' => 'Baju Batik',
                'harga' => 620000,
                'stok' => 8,
                'status' => 'Stok Menipis',
                'material' => 'Sutra Crepe Halus & Gold Foil Ornamen',
                'deskripsi' => 'Gaun pesta asimetris mewah dengan drape lembut dan kilau aksen emas halus untuk tampilan berkelas di acara istimewa.',
                'gambar' => 'produk/batik-dress-kawung.jpg',
            ],
            [
                'sku' => 'BTK-KAN-011',
                'nama' => 'Kain Batik Cap Parang Kusumo Tradisional',
                'kategori' => 'Kain Batik',
                'harga' => 280000,
                'stok' => 22,
                'status' => 'Tersedia',
                'material' => 'Katun Mori Prima Halus (2m x 1.15m)',
                'deskripsi' => 'Bahan kain batik cap berkualitas dengan ketahanan warna prima, siap dijahit menjadi kemeja, blouse, maupun rok lilit.',
                'gambar' => 'produk/batik-kain-parang.jpg',
            ],
            [
                'sku' => 'BTK-BLS-012',
                'nama' => 'Blouse Batik Cap Kontemporer V-Neck',
                'kategori' => 'Baju Batik',
                'harga' => 340000,
                'stok' => 19,
                'status' => 'Tersedia',
                'material' => 'Rayon Viscose Ringan & Nyaman Seharian',
                'deskripsi' => 'Atasan wanita chic dengan kerah V berpotongan santai yang modis dipadankan dengan celana kulot maupun rok polos.',
                'gambar' => 'produk/batik-blouse-modern.jpg',
            ],
            [
                'sku' => 'BTK-OLH-013',
                'nama' => 'Sling Bag & Passport Case Kulit Batik',
                'kategori' => 'Olahan Kain Sisa',
                'harga' => 180000,
                'stok' => 24,
                'status' => 'Tersedia',
                'material' => 'Perca Batik Upcycle & Tali Kulit Sintetis Premium',
                'deskripsi' => 'Tas selempang praktis dilengkapi dompet paspor untuk mobilitas aktif Anda saat travelling maupun bepergian santai.',
                'gambar' => 'produk/batik-tas-patchwork.jpg',
            ],
            [
                'sku' => 'BTK-KAN-014',
                'nama' => 'Kain Batik Tulis Motif Parang Lereng Solo',
                'kategori' => 'Kain Batik',
                'harga' => 495000,
                'stok' => 11,
                'status' => 'Tersedia',
                'material' => 'Mori Primissima Halus 100% Katun Asli Solo',
                'deskripsi' => 'Kain batik canting halus dengan pewarnaan soga klasik tradisional Jawa yang kaya akan filosofi dan nilai seni tinggi.',
                'gambar' => 'produk/batik-kain-parang.jpg',
            ],
            [
                'sku' => 'BTK-OLH-015',
                'nama' => 'Hampers Gift Box Batik & Pouch Artisan',
                'kategori' => 'Olahan Kain Sisa',
                'harga' => 225000,
                'stok' => 17,
                'status' => 'Tersedia',
                'material' => 'Hardbox Etnik Pita, Pouch Batik, & Masker Etnik',
                'deskripsi' => 'Set bingkisan kado elegan berisi pouch batik serbaguna, dompet koin, dan kartu ucapan kustom untuk momen spesial kerabat.',
                'gambar' => 'produk/batik-hampers-box.jpg',
            ],
        ];

        foreach ($produks as $item) {
            Produk::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}
