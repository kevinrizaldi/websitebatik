<?php

return [
    'key' => env('RAJAONGKIR_API_KEY'),
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter'),

    // Default origin (toko)
    'origin' => [
        'kota_id' => (int) env('RAJAONGKIR_ORIGIN_KOTA_ID', 153), // default Bandung
        'provinsi_id' => (int) env('RAJAONGKIR_ORIGIN_PROVINSI_ID', 9),
    ],

    // Packaging weight per item (gram) – bisa di-override per produk via kolom berat
    'packaging' => [
        'default_per_item' => (int) env('RAJAONGKIR_PACKAGING_PER_ITEM', 100), // 100 gram per item (box, bubble wrap, dll)
        'minimum' => (int) env('RAJAONGKIR_PACKAGING_MINIMUM', 200), // minimum 200 gram
    ],
];