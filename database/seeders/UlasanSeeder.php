<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UlasanSeeder extends Seeder
{
    /**
     * Seed customer reviews and product testimonials.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $userMap = User::pluck('id', 'email')->toArray();
        $budiId = $userMap['customer@webstorebatik.com'] ?? 2;
        $anshrId = $userMap['alanshorrr43@gmail.com'] ?? 3;
        $dewiId = $userMap['dewi.laksmi@email.com'] ?? 4;
        $ratnaId = $userMap['ratna.k@email.com'] ?? 6;

        $reviews = [
            // Al anshr reviews with images
            [
                'product_id' => 2,
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'rating' => 5,
                'comment' => 'Batiknya sangat nyaman dipakai, motif Megamendung sangat rapi dan adem di kulit!',
                'image_path' => 'ulasan/Xw6TWGLdu7knfaFvxseIkc33xYd7InZE2SJSIlw2.png',
                'status' => 'Disetujui',
                'created_at' => Carbon::parse('2026-10-01 20:05:41'),
            ],
            [
                'product_id' => 3,
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'rating' => 5,
                'comment' => 'Kain batik tulis Kawung Solo sangat autentik. Harum lilin malam alami terasa jelas.',
                'image_path' => 'ulasan/L0HBvIHDlM6WejG8C7qapzGS41uwwYGXAEqU6Y6l.png',
                'status' => 'Disetujui',
                'created_at' => Carbon::parse('2026-10-01 20:07:30'),
            ],

            // Verified customer reviews
            [
                'product_id' => 1,
                'user_id' => $budiId,
                'customer_name' => 'Budi Santoso',
                'rating' => 5,
                'comment' => 'Kualitas kain katun prima adem dan nyaman dipakai seharian di kantor. Jahitannya rapi dan potongan pas di badan.',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(10),
            ],
            [
                'product_id' => 3,
                'user_id' => $ratnaId,
                'customer_name' => 'Ratna Kusuma',
                'rating' => 5,
                'comment' => 'Koleksi mahakarya truntum yang sangat memukau. Detail canting tangan sangat teliti dan berkarakter.',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(8),
            ],
            [
                'product_id' => 7,
                'user_id' => $dewiId,
                'customer_name' => 'Dewi Laksmi',
                'rating' => 5,
                'comment' => 'Tote bag upcycled patchwork-nya sangat kokoh dan estetik! Senang sekali bisa mendukung produk zero waste.',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(5),
            ],
            [
                'product_id' => 1,
                'user_id' => null,
                'customer_name' => 'Siti Rahmawati',
                'rating' => 5,
                'comment' => 'Batik tulisnya sangat halus dan motifnya presisi sekali. Pas dipakai sangat nyaman dan tampak elegan untuk acara formal!',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(12),
            ],
            [
                'product_id' => 2,
                'user_id' => null,
                'customer_name' => 'Hendra Wijaya',
                'rating' => 5,
                'comment' => 'Motif Megamendung sangat tajam dan warnanya cerah. Sudah dicuci 2x warna tetap awet dan tidak luntur sama sekali.',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(7),
            ],
            [
                'product_id' => 4,
                'user_id' => null,
                'customer_name' => 'Fitri Handayani',
                'rating' => 4,
                'comment' => 'Motif Kawung klasik favorit saya. Bahannya jatuh dan adem. Ada aroma lilin malam khas batik asli yang menenangkan.',
                'image_path' => null,
                'status' => 'Disetujui',
                'created_at' => now()->subDays(4),
            ],
        ];

        foreach ($reviews as $rev) {
            Review::firstOrCreate(
                [
                    'product_id' => $rev['product_id'],
                    'customer_name' => $rev['customer_name'],
                ],
                $rev
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}
