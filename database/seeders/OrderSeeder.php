<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    /**
     * Seed dummy orders, order items, payments, and courier shipments.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $userMap = User::pluck('id', 'email')->toArray();
        $budiId = $userMap['customer@webstorebatik.com'] ?? 2;
        $anshrId = $userMap['alanshorrr43@gmail.com'] ?? 3;
        $dewiId = $userMap['dewi.laksmi@email.com'] ?? 4;
        $hendrikId = $userMap['hendrik.pratama@email.com'] ?? 5;
        $ratnaId = $userMap['ratna.k@email.com'] ?? 6;
        $anindyaId = $userMap['anindya.p@email.com'] ?? 7;

        $orders = [
            // ── Admin Mockup Orders ──────────────────────────────────────────
            [
                'code' => 'HSO-20250218-0849',
                'user_id' => $budiId,
                'customer_name' => 'Budi Santoso',
                'address' => 'Jl. Senopati No. 42, RT 02 / RW 03, Kel. Senayan, Kec. Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12190',
                'phone' => '081234567890',
                'total_price' => 871000,
                'shipping_cost' => 20000,
                'payment_method' => 'BCA Manual',
                'payment_proof' => 'bukti_transfer/struk_bca_871k.jpg',
                'status' => 'Menunggu Verifikasi',
                'payment_status' => 'pending',
                'tracking_number' => null,
                'created_at' => Carbon::parse('2025-02-18 10:14:00'),
                'items' => [
                    ['product_id' => 9, 'product_name' => 'Kemeja Batik Parang Seling Kencana (L)', 'price' => 385000, 'quantity' => 1],
                    ['product_id' => 10, 'product_name' => 'Rompi Kawung Sisa Tenun', 'price' => 330000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler Service',
                'shipping_status' => 'Menunggu Pengiriman',
            ],
            [
                'code' => 'HSO-20250218-0842',
                'user_id' => $dewiId,
                'customer_name' => 'Dewi Laksmi',
                'address' => 'Jl. Pemuda No. 78, Embong Kaliasin, Surabaya, Jawa Timur 60271',
                'phone' => '081388772210',
                'total_price' => 485000,
                'shipping_cost' => 20000,
                'payment_method' => 'Mandiri VA',
                'payment_proof' => null,
                'status' => 'Diproses',
                'payment_status' => 'paid',
                'tracking_number' => 'JNE-88291039912',
                'created_at' => Carbon::parse('2025-02-18 09:30:00'),
                'items' => [
                    ['product_id' => 7, 'product_name' => 'Artisan Patchwork Batik Tote Bag', 'price' => 245000, 'quantity' => 2],
                ],
                'courier' => 'SiCepat BEST (Next Day)',
                'shipping_status' => 'Dalam Perjalanan',
            ],
            [
                'code' => 'HSO-20250218-0810',
                'user_id' => $hendrikId,
                'customer_name' => 'Hendrik Pratama',
                'address' => 'Jl. Diponegoro No. 101, Citarum, Bandung, Jawa Barat 40115',
                'phone' => '085611223344',
                'total_price' => 620000,
                'shipping_cost' => 20000,
                'payment_method' => 'QRIS Terpadu',
                'payment_proof' => null,
                'status' => 'Dikirim',
                'payment_status' => 'paid',
                'tracking_number' => 'JNE-77182901239',
                'created_at' => Carbon::parse('2025-02-18 08:12:00'),
                'items' => [
                    ['product_id' => 8, 'product_name' => 'Dompet Zipper Pouch Eco Batik (2x)', 'price' => 155000, 'quantity' => 2],
                    ['product_id' => 5, 'product_name' => 'Outer Batik Kombinasi Tenun Etnik', 'price' => 310000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Dikirim',
            ],
            [
                'code' => 'HSO-20250217-1934',
                'user_id' => $ratnaId,
                'customer_name' => 'Ratna Kusuma',
                'address' => 'Jl. Kaliurang Km 9, Sinduharjo, Sleman, D.I. Yogyakarta 55581',
                'phone' => '081198765432',
                'total_price' => 1450000,
                'shipping_cost' => 0,
                'payment_method' => 'BCA Manual',
                'payment_proof' => 'bukti_transfer/struk_ratna_kusuma.jpg',
                'status' => 'Selesai',
                'payment_status' => 'paid',
                'tracking_number' => 'EXP-YOG-0042',
                'created_at' => Carbon::parse('2025-02-17 19:34:00'),
                'items' => [
                    ['product_id' => 3, 'product_name' => 'Kain Batik Tulis Motif Truntum Masterpiece', 'price' => 1450000, 'quantity' => 1],
                ],
                'courier' => 'Kurir Internal Studio',
                'shipping_status' => 'Diterima',
            ],
            [
                'code' => 'HSO-20250217-1420',
                'user_id' => $anindyaId,
                'customer_name' => 'Anindya Putri',
                'address' => 'Jl. Pandanaran No. 12, Mugassari, Semarang, Jawa Tengah 50249',
                'phone' => '082177889900',
                'total_price' => 520000,
                'shipping_cost' => 20000,
                'payment_method' => 'BCA VA',
                'payment_proof' => null,
                'status' => 'Belum Dibayar',
                'payment_status' => 'pending',
                'tracking_number' => '002938192831',
                'created_at' => Carbon::parse('2025-02-17 14:20:00'),
                'items' => [
                    ['product_id' => 4, 'product_name' => 'Dress Asimetris Kawung Sutera', 'price' => 500000, 'quantity' => 1],
                ],
                'courier' => 'SiCepat Reguler',
                'shipping_status' => 'Menunggu Pengiriman',
            ],

            // ── Previous Database Customer Orders (Al anshr) ───────────────────
            [
                'code' => 'ORD-20261001-IWSD',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Yodium, Jombang [Kurir: JNE Reguler]',
                'phone' => '081932697354',
                'total_price' => 306000,
                'shipping_cost' => 20000,
                'payment_method' => 'Midtrans Gateway',
                'status' => 'Selesai',
                'payment_status' => 'paid',
                'tracking_number' => 'k1111',
                'created_at' => Carbon::parse('2026-10-01 00:15:41'),
                'items' => [
                    ['product_id' => 2, 'product_name' => 'Blouse Batik Megamendung Modern (M)', 'price' => 285000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Diterima',
            ],
            [
                'code' => 'ORD-20261001-RJT8',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Kimia Farma, Jombang [Kurir: JNE Reguler]',
                'phone' => '081932697354',
                'total_price' => 471000,
                'shipping_cost' => 20000,
                'payment_method' => 'Midtrans Gateway',
                'status' => 'Dibatalkan',
                'payment_status' => 'cancelled',
                'tracking_number' => null,
                'created_at' => Carbon::parse('2026-10-01 01:25:30'),
                'items' => [
                    ['product_id' => 3, 'product_name' => 'Kain Batik Tulis Motif Kawung Solo (M)', 'price' => 450000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Menunggu Pengiriman',
            ],
            [
                'code' => 'ORD-20261001-JNCQ',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Yodium, Jombang [Kurir: JNE Reguler]',
                'phone' => '081932697354',
                'total_price' => 306000,
                'shipping_cost' => 20000,
                'payment_method' => 'Midtrans Gateway',
                'status' => 'Selesai',
                'payment_status' => 'paid',
                'tracking_number' => 'JNE31313131',
                'created_at' => Carbon::parse('2026-10-01 01:27:02'),
                'items' => [
                    ['product_id' => 2, 'product_name' => 'Blouse Batik Megamendung Modern (M)', 'price' => 285000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Diterima',
            ],
            [
                'code' => 'ORD-20261001-CNME',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Kimia Farma, Jombang [Kurir: SiCepat BEST]',
                'phone' => '081932697354',
                'total_price' => 936000,
                'shipping_cost' => 35000,
                'payment_method' => 'Midtrans (QRIS)',
                'status' => 'Diproses',
                'payment_status' => 'paid',
                'tracking_number' => null,
                'created_at' => Carbon::parse('2026-10-01 02:21:55'),
                'items' => [
                    ['product_id' => 3, 'product_name' => 'Kain Batik Tulis Motif Kawung Solo (M)', 'price' => 450000, 'quantity' => 2],
                ],
                'courier' => 'SiCepat BEST',
                'shipping_status' => 'Menunggu Pengiriman',
            ],
            [
                'code' => 'ORD-20261001-I5XB',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Yodium, Jombang [Kurir: JNE Reguler]',
                'phone' => '081932697354',
                'total_price' => 306000,
                'shipping_cost' => 20000,
                'payment_method' => 'Midtrans (QRIS)',
                'status' => 'Selesai',
                'payment_status' => 'paid',
                'tracking_number' => 'jan141564516',
                'created_at' => Carbon::parse('2026-10-01 02:44:06'),
                'items' => [
                    ['product_id' => 2, 'product_name' => 'Blouse Batik Megamendung Modern (M)', 'price' => 285000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Diterima',
            ],
            [
                'code' => 'ORD-20261002-1K55',
                'user_id' => $anshrId,
                'customer_name' => 'Al anshr',
                'address' => 'Jl. Yodium, Jombang, Indonesia [Kurir: JNE Reguler]',
                'phone' => '081932697354',
                'total_price' => 681000,
                'shipping_cost' => 20000,
                'payment_method' => 'Midtrans (QRIS)',
                'status' => 'Selesai',
                'payment_status' => 'paid',
                'tracking_number' => 'JKW10929019',
                'created_at' => Carbon::parse('2026-10-01 19:28:16'),
                'items' => [
                    ['product_id' => 1, 'product_name' => 'Kemeja Batik Parang Kusumo Pria (L)', 'price' => 375000, 'quantity' => 1],
                    ['product_id' => 2, 'product_name' => 'Blouse Batik Megamendung Modern (S)', 'price' => 285000, 'quantity' => 1],
                ],
                'courier' => 'JNE Reguler',
                'shipping_status' => 'Diterima',
            ],
        ];

        foreach ($orders as $o) {
            $items = $o['items'];
            $courier = $o['courier'] ?? 'JNE Reguler';
            $shippingStatus = $o['shipping_status'] ?? 'Menunggu Pengiriman';
            unset($o['items'], $o['courier'], $o['shipping_status']);

            $order = Order::updateOrCreate(
                ['code' => $o['code']],
                $o
            );

            // Populate Items
            foreach ($items as $item) {
                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'produk_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'produk_name' => $item['product_name'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]
                );
            }

            // Populate Payment
            $isPaid = in_array($order->status, ['Diproses', 'Dikirim', 'Selesai']);
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'status' => $isPaid ? 'settlement' : ($order->status === 'Dibatalkan' ? 'cancelled' : 'pending'),
                    'status_pembayaran' => $isPaid ? 'Berhasil' : ($order->status === 'Dibatalkan' ? 'Dibatalkan' : 'Menunggu Pembayaran'),
                    'payment_type' => str_contains($order->payment_method, 'QRIS') ? 'qris' : (str_contains($order->payment_method, 'VA') ? 'bank_transfer' : 'manual_transfer'),
                    'metode_pembayaran' => $order->payment_method,
                    'gross_amount' => $order->total_price,
                    'jumlah_bayar' => $order->total_price,
                    'paid_at' => $isPaid ? ($order->created_at ?? now()) : null,
                    'tanggal_bayar' => $isPaid ? ($order->created_at ?? now()) : null,
                ]
            );

            // Populate Shipment
            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier' => $courier,
                    'ekspedisi' => $courier,
                    'tracking_number' => $order->tracking_number,
                    'no_resi' => $order->tracking_number,
                    'shipping_status' => $shippingStatus,
                    'status_pengiriman' => $shippingStatus,
                    'shipped_at' => in_array($shippingStatus, ['Dikirim', 'Diterima', 'Dalam Perjalanan']) ? $order->created_at : null,
                    'tanggal_kirim' => in_array($shippingStatus, ['Dikirim', 'Diterima', 'Dalam Perjalanan']) ? $order->created_at : null,
                ]
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}
