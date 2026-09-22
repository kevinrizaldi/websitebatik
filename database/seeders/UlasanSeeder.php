<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UlasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada produk minimal untuk dikaitkan
        $produks = Produk::all();
        if ($produks->isEmpty()) {
            $defaultProduk = Produk::create([
                'nama' => 'Kemeja Batik Parang Kusumo Pria',
                'sku' => 'BTK-PRG-001',
                'kategori' => 'Baju Batik',
                'harga' => 250000,
                'stok' => 20,
                'status' => 'Tersedia',
                'deskripsi' => 'Kemeja batik katun prima halus motif parang kusumo',
                'material' => 'Katun Prima',
            ]);
            $produks = collect([$defaultProduk]);
        }

        $customer = User::where('role', 'customer')->first();
        $customerId = $customer ? $customer->id : null;

        // Kosongkan tabel ulasan sebelum seed ulang
        Ulasan::truncate();

        $reviews = [
            [
                'customer_name' => 'Siti Rahmawati',
                'rating' => 5,
                'comment' => 'Batik tulisnya sangat halus dan motifnya presisi sekali. Pas dipakai sangat nyaman dan tampak elegan untuk acara formal!',
                'status' => 'Disetujui',
                'days_ago' => 12,
            ],
            [
                'customer_name' => 'Budi Santoso',
                'rating' => 5,
                'comment' => 'Kualitas kain katun prima adem dan nyaman dipakai seharian di kantor. Jahitannya rapi dan potongan pas di badan.',
                'status' => 'Disetujui',
                'days_ago' => 10,
            ],
            [
                'customer_name' => 'Dewi Anggraini',
                'rating' => 4,
                'comment' => 'Bagus sekali bajunya, warnanya persis seperti di foto katalog. Pengiriman cepat dan dikemas sangat rapi.',
                'status' => 'Disetujui',
                'days_ago' => 9,
            ],
            [
                'customer_name' => 'Hendra Wijaya',
                'rating' => 5,
                'comment' => 'Motif Megamendung sangat tajam dan warnanya cerah. Sudah dicuci 2x warna tetap awet dan tidak luntur sama sekali.',
                'status' => 'Disetujui',
                'days_ago' => 8,
            ],
            [
                'customer_name' => 'Rina Puspita',
                'rating' => 3,
                'comment' => 'Bahan bagus dan motif cantik, tapi ukuran L terasa agak pas di bagian pundak. Saran saya naikkan satu ukuran kalau suka agak longgar.',
                'status' => 'Menunggu',
                'days_ago' => 7,
            ],
            [
                'customer_name' => 'Agus Prasetyo',
                'rating' => 5,
                'comment' => 'Pelayanan seller sangat responsif menjawab pertanyaan soal ukuran. Kemasan box-nya eksklusif cocok untuk hadiah.',
                'status' => 'Disetujui',
                'days_ago' => 6,
            ],
            [
                'customer_name' => 'Fitri Handayani',
                'rating' => 4,
                'comment' => 'Motif Kawung klasik favorit saya. Bahannya jatuh dan adem. Ada aroma lilin malam khas batik asli yang menenangkan.',
                'status' => 'Disetujui',
                'days_ago' => 5,
            ],
            [
                'customer_name' => 'Doni Setiawan',
                'rating' => 2,
                'comment' => 'Pengiriman kurir agak lambat sampainya 5 hari, dan jahitan kancing cadangan terlepas di dalam kantong.',
                'status' => 'Ditolak',
                'days_ago' => 4,
            ],
            [
                'customer_name' => 'Maya Indah',
                'rating' => 5,
                'comment' => 'Sangat puas! Saya beli untuk kado ulang tahun suami dan langsung pas dipakainya. Bakal langganan pesan di sini lagi.',
                'status' => 'Disetujui',
                'days_ago' => 3,
            ],
            [
                'customer_name' => 'Rizky Pratama',
                'rating' => 4,
                'comment' => 'Kain batiknya lembut tidak kaku seperti batik pasar murah. Cocok dipakai seharian meeting kantor tanpa gerah.',
                'status' => 'Disetujui',
                'days_ago' => 2,
            ],
            [
                'customer_name' => 'Lestari Wulandari',
                'rating' => 5,
                'comment' => 'Ini pesanan kedua saya di Batik Store. Kualitas jahitan dan kepekatan warnanya selalu konsisten bintang lima. Terima kasih!',
                'status' => 'Menunggu',
                'days_ago' => 1,
            ],
        ];

        $produkCount = $produks->count();

        foreach ($reviews as $index => $item) {
            $assignedProduk = $produks[$index % $produkCount];

            Ulasan::create([
                'produk_id' => $assignedProduk->id,
                'user_id' => $customerId,
                'customer_name' => $item['customer_name'],
                'rating' => $item['rating'],
                'comment' => $item['comment'],
                'status' => $item['status'],
                'created_at' => Carbon::now()->subDays($item['days_ago'])->subHours(rand(1, 10)),
                'updated_at' => Carbon::now()->subDays($item['days_ago']),
            ]);
        }
    }
}
