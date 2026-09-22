<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    private array $namaCustomer = [
        'Siti Rahayu', 'Budi Santoso', 'Dewi Anggraini', 'Ahmad Fauzi', 'Rina Kusuma',
        'Hendra Wijaya', 'Nur Hidayah', 'Agus Prasetyo', 'Fatimah Zahra', 'Dian Permata',
        'Rizki Ramadhan', 'Sri Wahyuni', 'Bambang Setiawan', 'Lestari Indah', 'Eko Purwanto',
        'Anita Susilowati', 'Wahyu Hidayat', 'Mega Wulandari', 'Irfan Maulana', 'Yuli Astuti',
    ];

    private array $alamat = [
        'Jl. Malioboro No. 45, Kel. Sosromenduran, Kec. Gedongtengen, Kota Yogyakarta, DIY 55271',
        'Jl. Pesindon No. 12, Kel. Panjang Baru, Kec. Pekalongan Utara, Kota Pekalongan, Jateng 51111',
        'Jl. Slamet Riyadi No. 88, Kel. Kauman, Kec. Pasarkliwon, Kota Solo, Jawa Tengah 57112',
        'Jl. Diponegoro No. 101, Kel. Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',
        'Jl. Pemuda No. 56, Kel. Pandansari, Kec. Semarang Tengah, Kota Semarang, Jateng 50138',
        'Jl. Thamrin No. 14, Kel. Gondangdia, Kec. Menteng, Jakarta Pusat, DKI Jakarta 10350',
        'Jl. Pahlawan No. 7, Kel. Embong Kaliasin, Kec. Genteng, Kota Surabaya, Jatim 60271',
        'Jl. Gajah Mada No. 33, Kel. Ps. Lama, Kec. Pontianak Barat, Kota Pontianak, Kalbar 78112',
        'Jl. Sudirman No. 78, Kel. Tengah, Kec. Kramat Jati, Jakarta Timur, DKI Jakarta 13540',
        'Jl. Ahmad Yani No. 22, Kel. Kauman, Kec. Klojen, Kota Malang, Jawa Timur 65119',
    ];

    private array $metodePembayaran = [
        'Transfer Bank', 'Transfer Bank', 'Transfer Bank',
        'QRIS', 'QRIS',
        'COD',
    ];

    private array $ekspedisi = ['JNE', 'J&T', 'SiCepat', 'Anteraja', 'Pos Indonesia'];

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            'Belum Dibayar',
            'Menunggu Konfirmasi',
            'Diproses',
            'Dikirim',
            'Selesai',
            'Dibatalkan',
        ]);

        $userId = User::where('role', 'customer')->inRandomOrder()->value('id');

        // Tracking number hanya ada jika sudah dikirim / selesai
        $trackingNumber = null;
        if (in_array($status, ['Dikirim', 'Selesai'])) {
            $ekspedisi = $this->faker->randomElement($this->ekspedisi);
            $trackingNumber = strtoupper(str_replace('&', '', $ekspedisi))
                . $this->faker->numerify('##########');
        }

        // Bukti bayar ada jika sudah melewati tahap Belum Dibayar
        $paymentProof = in_array($status, ['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai'])
            ? null   // dummy, tidak ada file asli
            : null;

        return [
            'user_id'         => $userId,
            'code'            => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name'   => $this->faker->randomElement($this->namaCustomer),
            'address'         => $this->faker->randomElement($this->alamat),
            'phone'           => '08' . $this->faker->numerify('##########'),
            'total_price'     => $this->faker->randomElement([
                175000, 250000, 320000, 450000, 580000,
                750000, 890000, 1150000, 1500000, 2250000,
            ]),
            'payment_method'  => $this->faker->randomElement($this->metodePembayaran),
            'payment_proof'   => $paymentProof,
            'status'          => $status,
            'tracking_number' => $trackingNumber,
            'created_at'      => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    // ── States untuk setiap status ───────────────────────────────────────────

    public function belumDibayar(): static
    {
        return $this->state([
            'status'          => 'Belum Dibayar',
            'payment_proof'   => null,
            'tracking_number' => null,
        ]);
    }

    public function menungguKonfirmasi(): static
    {
        return $this->state([
            'status'          => 'Menunggu Konfirmasi',
            'tracking_number' => null,
        ]);
    }

    public function diproses(): static
    {
        return $this->state([
            'status'          => 'Diproses',
            'tracking_number' => null,
        ]);
    }

    public function dikirim(): static
    {
        return $this->state(function () {
            $ekspedisi = $this->faker->randomElement($this->ekspedisi);
            return [
                'status'          => 'Dikirim',
                'tracking_number' => strtoupper(str_replace('&', '', $ekspedisi))
                    . $this->faker->numerify('##########'),
            ];
        });
    }

    public function selesai(): static
    {
        return $this->state(function () {
            $ekspedisi = $this->faker->randomElement($this->ekspedisi);
            return [
                'status'          => 'Selesai',
                'tracking_number' => strtoupper(str_replace('&', '', $ekspedisi))
                    . $this->faker->numerify('##########'),
            ];
        });
    }

    public function dibatalkan(): static
    {
        return $this->state([
            'status'          => 'Dibatalkan',
            'payment_proof'   => null,
            'tracking_number' => null,
        ]);
    }
}
