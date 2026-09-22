<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        OrderItem::truncate();
        Order::truncate();
        Schema::enableForeignKeyConstraints();

        // Pastikan ada produk untuk dijadikan item pesanan
        if (Produk::count() === 0) {
            $this->command->warn('Tidak ada produk. Membuat produk sampel...');
            $produks = [
                ['nama' => 'Kemeja Batik Parang Kusumo Pria',  'sku' => 'BTK-PRG-001', 'kategori' => 'Pria',   'harga' => 250000, 'stok' => 20, 'status' => 'Tersedia', 'material' => 'Katun Primisima',     'deskripsi' => 'Batik cap motif parang klasik elegan.'],
                ['nama' => 'Blouse Batik Megamendung Modern',  'sku' => 'BTK-MGM-002', 'kategori' => 'Wanita', 'harga' => 220000, 'stok' => 15, 'status' => 'Tersedia', 'material' => 'Sutra Crepe',         'deskripsi' => 'Motif megamendung warna pastel.'],
                ['nama' => 'Kain Batik Tulis Motif Kawung',    'sku' => 'BTK-KWG-003', 'kategori' => 'Kain',   'harga' => 450000, 'stok' => 10, 'status' => 'Tersedia', 'material' => 'Katun Mori Halus',    'deskripsi' => 'Kain batik tulis tradisional halus 2m.'],
                ['nama' => 'Dress Batik Tulis Truntum',        'sku' => 'BTK-TRN-004', 'kategori' => 'Wanita', 'harga' => 380000, 'stok' => 12, 'status' => 'Tersedia', 'material' => 'Katun Rayon Premium', 'deskripsi' => 'Dress formal santai motif truntum.'],
                ['nama' => 'Outer Batik Tenun Etnik Nusantara','sku' => 'BTK-TNN-005', 'kategori' => 'Unisex', 'harga' => 320000, 'stok' => 18, 'status' => 'Tersedia', 'material' => 'Tenun Troso',         'deskripsi' => 'Outer etnik kombinasi batik dan tenun.'],
                ['nama' => 'Sarung Batik Sido Mulyo',          'sku' => 'BTK-SDM-006', 'kategori' => 'Pria',   'harga' => 175000, 'stok' => 25, 'status' => 'Tersedia', 'material' => 'Katun Tapis',         'deskripsi' => 'Sarung batik motif sido mulyo premium.'],
            ];
            foreach ($produks as $p) {
                Produk::create($p);
            }
        }

        $allProduks = Produk::all();

        // Pastikan ada customer
        $customerIds = User::where('role', 'customer')->pluck('id');
        if ($customerIds->isEmpty()) {
            $this->command->warn('Tidak ada user customer. Pesanan dibuat tanpa user_id.');
        }

        // ── Buat 20 pesanan via factory ───────────────────────────────────────
        // Spread semua 6 status: 4 Belum Dibayar, 3 Menunggu Konfirmasi,
        //                        4 Diproses, 4 Dikirim, 3 Selesai, 2 Dibatalkan
        $distributions = [
            ['state' => 'belumDibayar',       'count' => 4],
            ['state' => 'menungguKonfirmasi',  'count' => 3],
            ['state' => 'diproses',            'count' => 4],
            ['state' => 'dikirim',             'count' => 4],
            ['state' => 'selesai',             'count' => 3],
            ['state' => 'dibatalkan',          'count' => 2],
        ];

        foreach ($distributions as $dist) {
            $orders = Order::factory()
                ->{$dist['state']}()
                ->count($dist['count'])
                ->create();

            // Buat 1–3 item untuk tiap pesanan
            foreach ($orders as $order) {
                $itemCount   = rand(1, 3);
                $selectedProducts = $allProduks->random(min($itemCount, $allProduks->count()));
                $totalPrice  = 0;

                foreach ($selectedProducts as $produk) {
                    $qty      = rand(1, 3);
                    $subtotal = $produk->harga * $qty;
                    $totalPrice += $subtotal;

                    OrderItem::create([
                        'order_id'    => $order->id,
                        'produk_id'   => $produk->id,
                        'produk_name' => $produk->nama,
                        'price'       => $produk->harga,
                        'quantity'    => $qty,
                        'subtotal'    => $subtotal,
                    ]);
                }

                // Update total_price sesuai item nyata
                $order->update(['total_price' => $totalPrice]);
            }
        }

        $total = Order::count();
        $this->command->info("✓ {$total} pesanan dummy berhasil dibuat.");
    }
}
