<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use App\Models\User;
use Carbon\Carbon;
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

        // Buat beberapa produk sampel jika belum ada
        if (Produk::count() === 0) {
            $sampleProducts = [
                ['nama' => 'Kemeja Batik Parang Kusumo Pria', 'sku' => 'BTK-PRG-001', 'kategori' => 'Pria', 'harga' => 250000, 'stok' => 20, 'status' => 'Tersedia', 'material' => 'Katun Primisima', 'deskripsi' => 'Batik cap motif parang klasik elegan.'],
                ['nama' => 'Blouse Batik Megamendung Modern', 'sku' => 'BTK-MGM-002', 'kategori' => 'Wanita', 'harga' => 220000, 'stok' => 15, 'status' => 'Tersedia', 'material' => 'Sutra Crepe', 'deskripsi' => 'Motif megamendung warna pastel.'],
                ['nama' => 'Kain Batik Tulis Motif Kawung', 'sku' => 'BTK-KWG-003', 'kategori' => 'Kain', 'harga' => 450000, 'stok' => 10, 'status' => 'Tersedia', 'material' => 'Katun Mori Halus', 'deskripsi' => 'Kain batik tulis tradisional halus 2 meter.'],
                ['nama' => 'Dress Batik Tulis Truntum', 'sku' => 'BTK-TRN-004', 'kategori' => 'Wanita', 'harga' => 380000, 'stok' => 12, 'status' => 'Tersedia', 'material' => 'Katun Rayon Premium', 'deskripsi' => 'Dress formal santai motif truntum.'],
                ['nama' => 'Outer Batik Tenun Etnik Nusantara', 'sku' => 'BTK-TNN-005', 'kategori' => 'Unisex', 'harga' => 320000, 'stok' => 18, 'status' => 'Tersedia', 'material' => 'Tenun Troso Kombinasi', 'deskripsi' => 'Outer etnik kombinasi batik dan tenun.'],
            ];

            foreach ($sampleProducts as $p) {
                Produk::create($p);
            }
        }

        $allProduks = Produk::all();
        $user = User::first();

        // 10 Data Dummy Pesanan dengan variasi status:
        // - Belum Dibayar (2 data)
        // - Sudah Dibayar (2 data)
        // - Diproses (2 data)
        // - Dikirim (2 data)
        // - Selesai (1 data)
        // - Batal (1 data)
        $ordersData = [
            [
                'code' => 'ORD-20260922-001',
                'customer_name' => 'Siti Rahmawati',
                'phone' => '081234567891',
                'address' => 'Jl. Malioboro No. 45, Danurejan, Kota Yogyakarta, DIY',
                'total_price' => 380000,
                'payment_method' => 'Transfer Bank BCA',
                'status' => 'Belum Dibayar',
                'created_at' => Carbon::now()->subHours(2),
                'items' => [
                    ['name' => 'Dress Batik Tulis Truntum', 'price' => 380000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260922-002',
                'customer_name' => 'Budi Santoso',
                'phone' => '085712345678',
                'address' => 'Jl. Diponegoro No. 12, Menteng, Jakarta Pusat',
                'total_price' => 500000,
                'payment_method' => 'QRIS',
                'status' => 'Belum Dibayar',
                'created_at' => Carbon::now()->subHours(4),
                'items' => [
                    ['name' => 'Kemeja Batik Parang Kusumo Pria', 'price' => 250000, 'qty' => 2],
                ],
            ],
            [
                'code' => 'ORD-20260921-003',
                'customer_name' => 'Dewi Lestari',
                'phone' => '081398765432',
                'address' => 'Perum Griya Indah Blok C3 No. 8, Sleman, DIY',
                'total_price' => 450000,
                'payment_method' => 'Transfer Bank Mandiri',
                'status' => 'Sudah Dibayar',
                'created_at' => Carbon::now()->subDay()->subHours(3),
                'items' => [
                    ['name' => 'Kain Batik Tulis Motif Kawung', 'price' => 450000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260921-004',
                'customer_name' => 'Ahmad Fauzi',
                'phone' => '082155667788',
                'address' => 'Jl. Pemuda No. 78, Semarang Tengah, Semarang',
                'total_price' => 470000,
                'payment_method' => 'Transfer Bank BRI',
                'status' => 'Sudah Dibayar',
                'created_at' => Carbon::now()->subDay()->subHours(6),
                'items' => [
                    ['name' => 'Kemeja Batik Parang Kusumo Pria', 'price' => 250000, 'qty' => 1],
                    ['name' => 'Blouse Batik Megamendung Modern', 'price' => 220000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260920-005',
                'customer_name' => 'Rina Kurniawati',
                'phone' => '087811223344',
                'address' => 'Jl. Braga No. 25, Sumur Bandung, Kota Bandung',
                'total_price' => 640000,
                'payment_method' => 'QRIS',
                'status' => 'Diproses',
                'created_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['name' => 'Outer Batik Tenun Etnik Nusantara', 'price' => 320000, 'qty' => 2],
                ],
            ],
            [
                'code' => 'ORD-20260920-006',
                'customer_name' => 'Hendra Setiawan',
                'phone' => '081900112233',
                'address' => 'Jl. Gajah Mada No. 102, Banjarsari, Solo',
                'total_price' => 700000,
                'payment_method' => 'Transfer Bank BCA',
                'status' => 'Diproses',
                'created_at' => Carbon::now()->subDays(2)->subHours(5),
                'items' => [
                    ['name' => 'Kain Batik Tulis Motif Kawung', 'price' => 450000, 'qty' => 1],
                    ['name' => 'Kemeja Batik Parang Kusumo Pria', 'price' => 250000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260919-007',
                'customer_name' => 'Putri Ayu Wandira',
                'phone' => '081299887766',
                'address' => 'Jl. Basuki Rahmat No. 55, Tegalsari, Surabaya',
                'total_price' => 220000,
                'payment_method' => 'GoPay',
                'status' => 'Dikirim',
                'tracking_number' => 'JP88291048291',
                'created_at' => Carbon::now()->subDays(3),
                'items' => [
                    ['name' => 'Blouse Batik Megamendung Modern', 'price' => 220000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260919-008',
                'customer_name' => 'Aris Munandar',
                'phone' => '085233445566',
                'address' => 'Jl. Kartini No. 14, Pekalongan Barat, Pekalongan',
                'total_price' => 320000,
                'payment_method' => 'COD (Bayar di Tempat)',
                'status' => 'Dikirim',
                'tracking_number' => 'JNE0192837465',
                'created_at' => Carbon::now()->subDays(3)->subHours(4),
                'items' => [
                    ['name' => 'Outer Batik Tenun Etnik Nusantara', 'price' => 320000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260918-009',
                'customer_name' => 'Maya Anggraini',
                'phone' => '081377889900',
                'address' => 'Komplek Permata Hijau Blok D No. 19, Jakarta Selatan',
                'total_price' => 1080000,
                'payment_method' => 'Transfer Bank BCA',
                'status' => 'Selesai',
                'tracking_number' => 'SICEPAT9928172635',
                'created_at' => Carbon::now()->subDays(4),
                'items' => [
                    ['name' => 'Kain Batik Tulis Motif Kawung', 'price' => 450000, 'qty' => 1],
                    ['name' => 'Dress Batik Tulis Truntum', 'price' => 380000, 'qty' => 1],
                    ['name' => 'Kemeja Batik Parang Kusumo Pria', 'price' => 250000, 'qty' => 1],
                ],
            ],
            [
                'code' => 'ORD-20260917-010',
                'customer_name' => 'Dimas Pratama',
                'phone' => '087766554433',
                'address' => 'Jl. Pahlawan No. 89, Bogor Tengah, Kota Bogor',
                'total_price' => 250000,
                'payment_method' => 'Transfer Bank Mandiri',
                'status' => 'Batal',
                'created_at' => Carbon::now()->subDays(5),
                'items' => [
                    ['name' => 'Kemeja Batik Parang Kusumo Pria', 'price' => 250000, 'qty' => 1],
                ],
            ],
        ];

        foreach ($ordersData as $o) {
            $items = $o['items'];
            unset($o['items']);
            $o['user_id'] = $user?->id;

            $createdOrder = Order::create($o);

            foreach ($items as $it) {
                $matchedProduk = $allProduks->firstWhere('nama', $it['name']);
                OrderItem::create([
                    'order_id' => $createdOrder->id,
                    'produk_id' => $matchedProduk?->id,
                    'produk_name' => $it['name'],
                    'price' => $it['price'],
                    'quantity' => $it['qty'],
                    'subtotal' => $it['price'] * $it['qty'],
                ]);
            }
        }
    }
}
