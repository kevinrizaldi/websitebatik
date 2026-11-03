<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RajaOngkir (Komerce Logistics) API Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and default settings for RajaOngkir shipping cost calculation.
    | Powered by RajaOngkir V2 by Komerce.
    |
    */

    'api_key' => env('RAJAONGKIR_API_KEY', '1FsGgWTJ63b4681c1cd32c5dTJRdnYyt'),

    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),

    // Origin Store Location: 61663 = Pasar Kliwon, Surakarta (Solo), Jawa Tengah
    'origin_id' => (int) env('RAJAONGKIR_ORIGIN_ID', 61663),

    'origin_city_id' => (int) env('RAJAONGKIR_ORIGIN_CITY_ID', 444),

    'origin_name' => env('RAJAONGKIR_ORIGIN_NAME', 'Pasar Kliwon, Surakarta (Solo)'),

    'origin_province_name' => env('RAJAONGKIR_ORIGIN_PROVINCE_NAME', 'Jawa Tengah'),

    // Kurir didukung: JNE, SiCepat, J&T, POS, TIKI
    'couriers' => explode(',', env('RAJAONGKIR_COURIERS', 'jne,sicepat,jnt,pos,tiki')),

    // Durasi cache data lokasi & ongkir (detik)
    'cache_duration' => (int) env('RAJAONGKIR_CACHE_DURATION', 86400),

    // Estimasi berat per potong pakaian/batik (gram)
    'default_weight_per_item' => (int) env('RAJAONGKIR_DEFAULT_ITEM_WEIGHT', 300),

    // Minimal berat paket yang dihitung (1 kg = 1000 gram)
    'min_weight' => 1000,

];
