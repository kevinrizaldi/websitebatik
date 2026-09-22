<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ulasan>
 */
class UlasanFactory extends Factory
{
    protected $model = Ulasan::class;

    public function definition(): array
    {
        $batikComments = [
            'Batik tulisnya sangat halus dan motifnya presisi sekali. Pas dipakai sangat nyaman dan tampak elegan!',
            'Kualitas kain katun prima adem dan nyaman dipakai seharian di kantor. Jahitannya sangat rapi.',
            'Bagus sekali bajunya, warnanya persis seperti di foto katalog. Pengiriman cepat dan dikemas aman.',
            'Motif Megamendung sangat tajam dan warnanya cerah. Sudah dicuci warna tetap awet dan tidak luntur.',
            'Bahan bagus dan motif cantik, jahitan rapi sekali. Recommended seller!',
            'Pelayanan seller sangat responsif menjawab pertanyaan soal ukuran. Kemasan box-nya eksklusif.',
            'Motif Kawung klasik favorit saya. Bahannya jatuh dan adem dipakai.',
            'Kain batiknya lembut tidak kaku seperti batik murahan. Sangat puas dengan kualitasnya.',
            'Pesanan sesuai ekspektasi, potongan kemeja pas di badan dan motif simetris.',
            'Batik cap kualitas premium, warna tidak pudar saat pencucian pertama.',
            'Pengiriman cepat, packing rapi, dan produk sesuai dengan deskripsi.',
            'Bahan agak tipis tapi motif sangat cantik dan jahitan kuat.',
        ];

        return [
            'produk_id' => Produk::inRandomOrder()->first()?->id ?? 1,
            'user_id' => User::where('role', 'customer')->inRandomOrder()->first()?->id,
            'customer_name' => fake('id_ID')->name(),
            'rating' => fake()->randomElement([5, 5, 5, 4, 4, 4, 3, 2]),
            'comment' => fake()->randomElement($batikComments),
            'status' => fake()->randomElement(['Disetujui', 'Disetujui', 'Disetujui', 'Menunggu', 'Ditolak']),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * State untuk ulasan yang sudah disetujui
     */
    public function disetujui(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Disetujui',
        ]);
    }

    /**
     * State untuk ulasan yang masih menunggu moderasi
     */
    public function menunggu(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Menunggu',
        ]);
    }

    /**
     * State untuk ulasan yang ditolak
     */
    public function ditolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Ditolak',
        ]);
    }
}
