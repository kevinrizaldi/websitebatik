<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RajaOngkirService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected int $originId;

    protected int $originCityId;

    protected string $originCityName;

    protected string $originProvinceName;

    /**
     * @var array<int, string>
     */
    protected array $supportedCouriers;

    protected int $cacheDuration;

    protected bool $isKomerce;

    public function __construct()
    {
        $this->apiKey = (string) config('rajaongkir.api_key', '');
        $this->baseUrl = rtrim((string) config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $this->originId = (int) config('rajaongkir.origin_id', 61663);
        $this->originCityId = (int) config('rajaongkir.origin_city_id', 444);
        $this->originCityName = (string) config('rajaongkir.origin_city_name', 'Kota Surakarta (Solo)');
        $this->originProvinceName = (string) config('rajaongkir.origin_province_name', 'Jawa Tengah');
        $this->supportedCouriers = config('rajaongkir.couriers', ['jne', 'sicepat', 'jnt', 'pos', 'tiki']);
        $this->cacheDuration = (int) config('rajaongkir.cache_duration', 86400);
        $this->isKomerce = str_contains($this->baseUrl, 'komerce.id');
    }

    /**
     * Check whether RajaOngkir API key is configured.
     */
    public function isApiKeyConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Whether the configured provider is RajaOngkir V2 by Komerce.
     */
    public function isKomerce(): bool
    {
        return $this->isKomerce;
    }

    /**
     * Get supported couriers list.
     *
     * @return array<int, string>
     */
    public function getSupportedCouriers(): array
    {
        return $this->supportedCouriers;
    }

    /**
     * Get origin store location details.
     *
     * @return array{origin_id: int, city_id: int, city_name: string, province: string, is_komerce: bool}
     */
    public function getOriginInfo(): array
    {
        return [
            'origin_id' => $this->originId,
            'city_id' => $this->originCityId,
            'city_name' => $this->originCityName,
            'province' => $this->originProvinceName,
            'is_komerce' => $this->isKomerce,
        ];
    }

    /**
     * Retrieve all provinces either from live RajaOngkir API or offline fallback.
     *
     * @return array<int, array{province_id: string|int, province: string}>
     */
    public function getProvinces(): array
    {
        if ($this->isApiKeyConfigured()) {
            try {
                return Cache::remember('rajaongkir_provinces', $this->cacheDuration, function () {
                    $endpoint = $this->isKomerce ? "{$this->baseUrl}/destination/province" : "{$this->baseUrl}/province";

                    $response = Http::timeout(4)
                        ->withHeaders(['key' => $this->apiKey])
                        ->get($endpoint);

                    if ($response->successful()) {
                        $data = $response->json();
                        if ($this->isKomerce) {
                            $items = $data['data'] ?? [];
                            if (! empty($items) && is_array($items)) {
                                return array_map(function ($item) {
                                    return [
                                        'province_id' => (string) ($item['id'] ?? ''),
                                        'province' => ucwords(strtolower((string) ($item['name'] ?? ''))),
                                    ];
                                }, $items);
                            }
                        } else {
                            $results = $data['rajaongkir']['results'] ?? [];
                            if (! empty($results)) {
                                return $results;
                            }
                        }
                    }

                    Log::warning('RajaOngkir: Failed fetching live provinces, using fallback dataset', [
                        'status' => $response->status(),
                    ]);

                    return $this->getFallbackProvinces();
                });
            } catch (Throwable $e) {
                Log::warning('RajaOngkir Exception: getProvinces', ['message' => $e->getMessage()]);

                return $this->getFallbackProvinces();
            }
        }

        return $this->getFallbackProvinces();
    }

    /**
     * Retrieve cities by province ID.
     *
     * @return array<int, array{city_id: string|int, province_id: string|int, province: string, type: string, city_name: string, postal_code: string}>
     */
    public function getCities(?int $provinceId = null): array
    {
        $cacheKey = 'rajaongkir_cities_'.($provinceId ?: 'all');

        if ($this->isApiKeyConfigured()) {
            try {
                return Cache::remember($cacheKey, $this->cacheDuration, function () use ($provinceId) {
                    if ($this->isKomerce) {
                        if ($provinceId) {
                            $response = Http::timeout(4)
                                ->withHeaders(['key' => $this->apiKey])
                                ->get("{$this->baseUrl}/destination/city/{$provinceId}");

                            if ($response->successful()) {
                                $data = $response->json();
                                $items = $data['data'] ?? [];
                                if (! empty($items) && is_array($items)) {
                                    return array_map(function ($item) use ($provinceId) {
                                        $rawName = (string) ($item['name'] ?? '');
                                        $cityName = ucwords(strtolower($rawName));
                                        $type = str_contains(strtolower($rawName), 'kabupaten') ? 'Kabupaten' : 'Kota';

                                        return [
                                            'city_id' => (string) ($item['id'] ?? ''),
                                            'province_id' => (string) $provinceId,
                                            'province' => '',
                                            'type' => $type,
                                            'city_name' => $cityName,
                                            'postal_code' => '',
                                        ];
                                    }, $items);
                                }
                            }
                        }

                        return $this->getFallbackCities($provinceId);
                    }

                    // V1 Starter API
                    $url = "{$this->baseUrl}/city";
                    $params = $provinceId ? ['province' => $provinceId] : [];

                    $response = Http::timeout(4)
                        ->withHeaders(['key' => $this->apiKey])
                        ->get($url, $params);

                    if ($response->successful()) {
                        $data = $response->json();
                        $results = $data['rajaongkir']['results'] ?? [];
                        if (! empty($results)) {
                            return $results;
                        }
                    }

                    return $this->getFallbackCities($provinceId);
                });
            } catch (Throwable $e) {
                Log::warning('RajaOngkir Exception: getCities', ['message' => $e->getMessage()]);

                return $this->getFallbackCities($provinceId);
            }
        }

        return $this->getFallbackCities($provinceId);
    }

    /**
     * Search domestic destinations via Komerce API.
     *
     * @return array<int, array{id: int, label: string, province_name: string, city_name: string, district_name: string, subdistrict_name: string, zip_code: string}>
     */
    public function searchDestination(string $search): array
    {
        $search = trim($search);
        if (strlen($search) < 2) {
            return [];
        }

        if ($this->isApiKeyConfigured() && $this->isKomerce) {
            try {
                $cacheKey = 'rajaongkir_dest_'.md5(strtolower($search));

                return Cache::remember($cacheKey, $this->cacheDuration, function () use ($search) {
                    $response = Http::timeout(4)
                        ->withHeaders(['key' => $this->apiKey])
                        ->get("{$this->baseUrl}/destination/domestic-destination", [
                            'search' => $search,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        return $data['data'] ?? [];
                    }

                    return [];
                });
            } catch (Throwable $e) {
                Log::warning('RajaOngkir searchDestination Exception', ['search' => $search, 'message' => $e->getMessage()]);

                return [];
            }
        }

        return [];
    }

    /**
     * Resolve a Komerce destination ID.
     * Komerce /calculate/domestic-cost requires a 5-digit subdistrict destination ID.
     * If a 2-3 digit city ID is provided, map it to the corresponding subdistrict ID
     * so it doesn't mistakenly calculate to an arbitrary small subdistrict ID in Aceh.
     */
    protected function resolveKomerceDestination(int $destinationCityId, ?string $cityName = null): int
    {
        if ($destinationCityId >= 10000) {
            return $destinationCityId;
        }

        // Map known Komerce city IDs to their major central subdistrict ID
        $map = [
            // DKI Jakarta (Komerce Province 10)
            135 => 17473, // Kota Jakarta Barat (Grogol Petamburan)
            136 => 17610, // Kota Jakarta Selatan (Kebayoran Baru)
            137 => 17565, // Kota Jakarta Pusat (Gambir)
            138 => 17730, // Kota Jakarta Utara (Tanjung Priok)
            139 => 17680, // Kota Jakarta Timur (Jatinegara)
            141 => 17770, // Kab Kepulauan Seribu

            // Jawa Barat (Komerce Province 5 / V1 9)
            22 => 19850,  // Kota Bandung
            23 => 19900,  // Kab Bandung
            54 => 20150,  // Kota Bekasi
            55 => 20160,  // Kab Bekasi
            78 => 20320,  // Kota Bogor
            79 => 20330,  // Kab Bogor
            115 => 20480, // Kota Depok
            107 => 20550, // Kota Cirebon
            468 => 20600, // Kota Tasikmalaya

            // Banten (Komerce Province 11 / V1 3)
            455 => 13210, // Kota Tangerang
            456 => 13220, // Kota Tangerang Selatan
            457 => 13230, // Kab Tangerang
            106 => 13300, // Kota Cilegon
            418 => 13350, // Kota Serang

            // Jawa Tengah (Komerce Province 12 / V1 10)
            444 => 61663, // Kota Surakarta (Solo) - Pasar Kliwon
            399 => 61580, // Kota Semarang
            427 => 61680, // Kab Sukoharjo
            196 => 61700, // Kab Karanganyar
            433 => 61720, // Kab Sragen
            92 => 61740,  // Kab Boyolali
            200 => 61760, // Kab Klaten
            497 => 61780, // Kab Wonogiri
            249 => 61800, // Kota Magelang
            349 => 61820, // Kota Pekalongan
            386 => 61860, // Kota Salatiga
            209 => 61880, // Kab Kudus
            344 => 61900, // Kab Pati
            186 => 61920, // Kab Jepara
            41 => 61940,  // Kab Banyumas (Purwokerto)
            103 => 61960, // Kab Cilacap

            // DI Yogyakarta (Komerce Province 19 / V1 5)
            501 => 42100, // Kota Yogyakarta
            419 => 42120, // Kab Sleman
            39 => 42140,  // Kab Bantul
            210 => 42180, // Kab Kulon Progo

            // Jawa Timur (Komerce Province 18 / V1 11)
            445 => 64250, // Kota Surabaya
            255 => 64300, // Kota Malang
            256 => 64320, // Kab Malang
            417 => 64350, // Kab Sidoarjo
            160 => 64400, // Kab Jember
            19 => 64450,  // Kota Banyuwangi
            247 => 64500, // Kota Madiun
            178 => 64550, // Kota Kediri

            // Bali (Komerce Province 15 / V1 1)
            114 => 11000, // Kota Denpasar
            32 => 11050,  // Kab Badung

            // Sumatera Selatan (Komerce Province 26 / V1 33)
            327 => 28000, // Kota Palembang

            // Lampung (Komerce Province 30 / V1 18)
            21 => 26000,  // Kota Bandar Lampung

            // Sumatera Utara (Komerce Province 16 / V1 34)
            278 => 29000, // Kota Medan
        ];

        if (isset($map[$destinationCityId])) {
            return $map[$destinationCityId];
        }

        if (! empty($cityName)) {
            $cn = strtolower($cityName);
            if (str_contains($cn, 'jakarta barat')) {
                return 17473;
            }
            if (str_contains($cn, 'jakarta selatan')) {
                return 17610;
            }
            if (str_contains($cn, 'jakarta pusat')) {
                return 17565;
            }
            if (str_contains($cn, 'jakarta utara')) {
                return 17730;
            }
            if (str_contains($cn, 'jakarta timur')) {
                return 17680;
            }
            if (str_contains($cn, 'surakarta') || str_contains($cn, 'solo')) {
                return 61663;
            }
            if (str_contains($cn, 'semarang')) {
                return 61580;
            }
            if (str_contains($cn, 'jogja') || str_contains($cn, 'yogyakarta')) {
                return 42100;
            }
            if (str_contains($cn, 'bandung')) {
                return 19850;
            }
            if (str_contains($cn, 'surabaya')) {
                return 64250;
            }
        }

        return $destinationCityId;
    }

    /**
     * Calculate shipping cost for a given destination and weight across requested couriers.
     *
     * @param  array<int, string>|string  $couriers
     * @return array<int, array{
     *     code: string,
     *     name: string,
     *     service: string,
     *     description: string,
     *     cost: int,
     *     etd: string
     * }>
     */
    public function calculateCost(
        int $destinationCityId,
        int $weight = 1000,
        array|string $couriers = ['jne', 'sicepat', 'jnt', 'pos', 'tiki'],
        ?string $cityName = null,
        ?string $provinceName = null,
        ?int $provinceId = null
    ): array
    {
        $courierList = is_string($couriers) ? explode(',', $couriers) : $couriers;
        $courierList = array_map('strtolower', array_map('trim', $courierList));

        // Berat minimal 1000 gram (1 kg)
        $weight = max(1000, $weight);

        $results = [];

        if ($this->isApiKeyConfigured()) {
            foreach ($courierList as $courier) {
                if (! in_array($courier, ['jne', 'sicepat', 'jnt', 'pos', 'tiki'], true)) {
                    continue;
                }

                $cacheKey = "rajaongkir_cost_{$this->originCityId}_{$destinationCityId}_{$weight}_{$courier}";

                $courierCosts = Cache::remember($cacheKey, 1800, function () use ($destinationCityId, $weight, $courier, $cityName) {
                    try {
                        if ($this->isKomerce) {
                            $origin = $this->originId ?: $this->originCityId;
                            $destination = $this->resolveKomerceDestination($destinationCityId, $cityName);

                            $response = Http::asForm()
                                ->timeout(4)
                                ->withHeaders(['key' => $this->apiKey])
                                ->post("{$this->baseUrl}/calculate/domestic-cost", [
                                    'origin' => $origin,
                                    'destination' => $destination,
                                    'weight' => $weight,
                                    'courier' => $courier,
                                ]);

                            if ($response->successful()) {
                                $data = $response->json();
                                $items = $data['data'] ?? [];
                                if (is_array($items) && ! empty($items)) {
                                    $parsed = [];
                                    foreach ($items as $item) {
                                        $parsed[] = [
                                            'code' => strtoupper((string) ($item['code'] ?? $courier)),
                                            'name' => (string) ($item['name'] ?? strtoupper($courier)),
                                            'service' => (string) ($item['service'] ?? ''),
                                            'description' => (string) ($item['description'] ?? $item['service'] ?? ''),
                                            'cost' => (int) ($item['cost'] ?? 0),
                                            'etd' => ! empty($item['etd']) ? (string) $item['etd'] : '-',
                                        ];
                                    }

                                    return $parsed;
                                }
                            }

                            // 429 Daily limit exceeded, 404 or courier error -> return null to fallback
                            return null;
                        }

                        // V1 Starter API
                        $response = Http::asForm()
                            ->timeout(4)
                            ->withHeaders(['key' => $this->apiKey])
                            ->post("{$this->baseUrl}/cost", [
                                'origin' => $this->originCityId,
                                'destination' => $destinationCityId,
                                'weight' => $weight,
                                'courier' => $courier,
                            ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            $parsed = [];
                            $courierResult = $data['rajaongkir']['results'][0] ?? null;

                            if ($courierResult && ! empty($courierResult['costs'])) {
                                foreach ($courierResult['costs'] as $costItem) {
                                    $costDetail = $costItem['cost'][0] ?? [];
                                    $parsed[] = [
                                        'code' => strtoupper($courierResult['code'] ?? $courier),
                                        'name' => $courierResult['name'] ?? strtoupper($courier),
                                        'service' => $costItem['service'],
                                        'description' => $costItem['description'] ?? $costItem['service'],
                                        'cost' => (int) ($costDetail['value'] ?? 0),
                                        'etd' => ! empty($costDetail['etd']) ? $costDetail['etd'].' hari' : '-',
                                    ];
                                }
                            }

                            return $parsed;
                        }

                        return null;
                    } catch (Throwable $e) {
                        Log::warning('RajaOngkir Cost Exception', ['courier' => $courier, 'message' => $e->getMessage()]);

                        return null;
                    }
                });

                if (! empty($courierCosts)) {
                    foreach ($courierCosts as $item) {
                        $results[] = $item;
                    }
                } else {
                    // Fallback for this courier
                    $results = array_merge($results, $this->getFallbackCostForCourier(
                        $destinationCityId,
                        $weight,
                        $courier,
                        $cityName,
                        $provinceName,
                        $provinceId
                    ));
                }
            }

            if (! empty($results)) {
                return $results;
            }
        }

        // Offline calculation fallback when API key is missing or calls fail
        foreach ($courierList as $courier) {
            $results = array_merge($results, $this->getFallbackCostForCourier(
                $destinationCityId,
                $weight,
                $courier,
                $cityName,
                $provinceName,
                $provinceId
            ));
        }

        return $results;
    }

    /**
     * Calculate fallback shipping cost with realistic Indonesian courier estimates based on origin Surakarta (Solo).
     *
     * @return array<int, array{code: string, name: string, service: string, description: string, cost: int, etd: string}>
     */
    protected function getFallbackCostForCourier(
        int $destinationCityId,
        int $weight,
        string $courier,
        ?string $cityName = null,
        ?string $provinceName = null,
        ?int $provinceId = null
    ): array
    {
        $weightInKg = (int) ceil($weight / 1000);

        $cityNameLower = strtolower(trim((string) $cityName));
        $provinceNameLower = strtolower(trim((string) $provinceName));
        $combined = $cityNameLower.' '.$provinceNameLower;

        // Cari tahu info kota tujuan dari fallback list
        $allCities = $this->getFallbackCities();
        $targetCity = collect($allCities)->firstWhere('city_id', (string) $destinationCityId)
            ?? collect($allCities)->firstWhere('city_id', $destinationCityId);

        $fallbackCityName = $targetCity ? strtolower((string) $targetCity['city_name']) : '';
        $fallbackProvinceId = $targetCity ? (int) $targetCity['province_id'] : null;

        // Determinasi zona tarif dari Solo (Surakarta, Jateng):
        // 1. Solo Raya
        if ($destinationCityId === 444
            || str_contains($combined, 'surakarta')
            || str_contains($combined, 'solo')
            || str_contains($fallbackCityName, 'surakarta')
        ) {
            $zone = 'solo';
        }
        // 2. DKI Jakarta (Komerce city_id: 135..141, Komerce province_id: 10, RajaOngkir V1: 151..155, prov 6)
        elseif (in_array($destinationCityId, [135, 136, 137, 138, 139, 141, 151, 152, 153, 154, 155], true)
            || str_contains($combined, 'jakarta')
            || str_contains($combined, 'dki')
            || ($this->isKomerce && $provinceId === 10)
            || (! $this->isKomerce && ($provinceId === 6 || $fallbackProvinceId === 6))
        ) {
            $zone = 'jawa';
        }
        // 3. Jateng & DIY (Solo's home region)
        // Di Komerce Jateng: 12, DIY: 19. Di RajaOngkir V1 Jateng: 10, DIY: 5.
        elseif (($this->isKomerce && in_array($provinceId, [12, 19], true))
            || (! $this->isKomerce && in_array($fallbackProvinceId ?: $provinceId, [10, 5], true))
            || str_contains($combined, 'jawa tengah')
            || str_contains($combined, 'jateng')
            || str_contains($combined, 'yogyakarta')
            || str_contains($combined, 'jogja')
            || str_contains($fallbackCityName, 'semarang')
            || str_contains($fallbackCityName, 'sukoharjo')
            || str_contains($fallbackCityName, 'karanganyar')
            || str_contains($fallbackCityName, 'sragen')
            || str_contains($fallbackCityName, 'boyolali')
            || str_contains($fallbackCityName, 'klaten')
            || str_contains($fallbackCityName, 'wonogiri')
            || str_contains($fallbackCityName, 'sleman')
            || str_contains($fallbackCityName, 'bantul')
        ) {
            $zone = 'jateng_diy';
        }
        // 4. Pulau Jawa lainnya (Jabar, Jatim, Banten)
        // Di Komerce: Jabar 5, Banten 11, Jatim 18. Di V1: Jabar 9, Banten 3, Jatim 11.
        elseif (($this->isKomerce && in_array($provinceId, [5, 11, 18], true))
            || (! $this->isKomerce && in_array($fallbackProvinceId ?: $provinceId, [9, 3, 11], true))
            || str_contains($combined, 'jawa barat')
            || str_contains($combined, 'jabar')
            || str_contains($combined, 'jawa timur')
            || str_contains($combined, 'jatim')
            || str_contains($combined, 'banten')
            || str_contains($combined, 'bandung')
            || str_contains($combined, 'surabaya')
            || str_contains($combined, 'tangerang')
            || str_contains($combined, 'bekasi')
            || str_contains($combined, 'bogor')
            || str_contains($combined, 'depok')
            || str_contains($combined, 'malang')
            || str_contains($combined, 'jember')
        ) {
            $zone = 'jawa';
        }
        // 5. Dekat Luar Jawa (Bali, Lampung, Sumatera Selatan)
        elseif (in_array($provinceId, [15, 26, 30, 1, 18, 33], true)
            || str_contains($combined, 'bali')
            || str_contains($combined, 'lampung')
            || str_contains($combined, 'sumatera selatan')
            || str_contains($combined, 'palembang')
            || str_contains($combined, 'denpasar')
        ) {
            $zone = 'dekat_luar_jawa';
        }
        // 6. Luar Jawa lainnya
        else {
            $zone = 'luar_jawa';
        }

        $rates = [
            'jne' => [
                'solo' => [
                    ['service' => 'OKE', 'desc' => 'Ongkos Kirim Ekonomis', 'rate' => 7000, 'etd' => '2-3 hari'],
                    ['service' => 'REG', 'desc' => 'Layanan Reguler', 'rate' => 9000, 'etd' => '1-2 hari'],
                    ['service' => 'YES', 'desc' => 'Yakin Esok Sampai', 'rate' => 15000, 'etd' => '1 hari'],
                ],
                'jateng_diy' => [
                    ['service' => 'OKE', 'desc' => 'Ongkos Kirim Ekonomis', 'rate' => 9000, 'etd' => '2-3 hari'],
                    ['service' => 'REG', 'desc' => 'Layanan Reguler', 'rate' => 12000, 'etd' => '1-2 hari'],
                    ['service' => 'YES', 'desc' => 'Yakin Esok Sampai', 'rate' => 20000, 'etd' => '1 hari'],
                ],
                'jawa' => [
                    ['service' => 'REG', 'desc' => 'Layanan Reguler', 'rate' => 15000, 'etd' => '1-2 hari'],
                    ['service' => 'YES', 'desc' => 'Yakin Esok Sampai', 'rate' => 25000, 'etd' => '1 hari'],
                    ['service' => 'JTR', 'desc' => 'JNE Trucking', 'rate' => 55000, 'etd' => '3-4 hari'],
                ],
                'dekat_luar_jawa' => [
                    ['service' => 'OKE', 'desc' => 'Ongkos Kirim Ekonomis', 'rate' => 24000, 'etd' => '3-5 hari'],
                    ['service' => 'REG', 'desc' => 'Layanan Reguler', 'rate' => 30000, 'etd' => '2-3 hari'],
                ],
                'luar_jawa' => [
                    ['service' => 'OKE', 'desc' => 'Ongkos Kirim Ekonomis', 'rate' => 36000, 'etd' => '4-7 hari'],
                    ['service' => 'REG', 'desc' => 'Layanan Reguler', 'rate' => 45000, 'etd' => '3-5 hari'],
                ],
            ],
            'sicepat' => [
                'solo' => [
                    ['service' => 'SIUNT', 'desc' => 'SiUntung (Reguler)', 'rate' => 8000, 'etd' => '1-2 hari'],
                    ['service' => 'BEST', 'desc' => 'Besok Sampai Tujuan', 'rate' => 14000, 'etd' => '1 hari'],
                ],
                'jateng_diy' => [
                    ['service' => 'SIUNT', 'desc' => 'SiUntung (Reguler)', 'rate' => 11000, 'etd' => '1-2 hari'],
                    ['service' => 'BEST', 'desc' => 'Besok Sampai Tujuan', 'rate' => 19000, 'etd' => '1 hari'],
                ],
                'jawa' => [
                    ['service' => 'SIUNT', 'desc' => 'SiUntung (Reguler)', 'rate' => 18000, 'etd' => '2-3 hari'],
                    ['service' => 'BEST', 'desc' => 'Besok Sampai Tujuan', 'rate' => 30000, 'etd' => '1 hari'],
                ],
                'dekat_luar_jawa' => [
                    ['service' => 'SIUNT', 'desc' => 'SiUntung (Reguler)', 'rate' => 28000, 'etd' => '2-4 hari'],
                ],
                'luar_jawa' => [
                    ['service' => 'SIUNT', 'desc' => 'SiUntung (Reguler)', 'rate' => 42000, 'etd' => '3-5 hari'],
                ],
            ],
            'jnt' => [
                'solo' => [
                    ['service' => 'EZ', 'desc' => 'Reguler Service', 'rate' => 9000, 'etd' => '1-2 hari'],
                    ['service' => 'Super', 'desc' => 'Next Day Service', 'rate' => 15000, 'etd' => '1 hari'],
                ],
                'jateng_diy' => [
                    ['service' => 'EZ', 'desc' => 'Reguler Service', 'rate' => 12000, 'etd' => '1-2 hari'],
                    ['service' => 'Super', 'desc' => 'Next Day Service', 'rate' => 20000, 'etd' => '1 hari'],
                ],
                'jawa' => [
                    ['service' => 'EZ', 'desc' => 'Reguler Service', 'rate' => 19000, 'etd' => '2-3 hari'],
                    ['service' => 'Super', 'desc' => 'Next Day Service', 'rate' => 32000, 'etd' => '1 hari'],
                ],
                'dekat_luar_jawa' => [
                    ['service' => 'EZ', 'desc' => 'Reguler Service', 'rate' => 29000, 'etd' => '2-4 hari'],
                ],
                'luar_jawa' => [
                    ['service' => 'EZ', 'desc' => 'Reguler Service', 'rate' => 44000, 'etd' => '3-5 hari'],
                ],
            ],
            'pos' => [
                'solo' => [
                    ['service' => 'Pos Reguler', 'desc' => 'Pos Reguler Domestik', 'rate' => 8000, 'etd' => '2 hari'],
                    ['service' => 'Pos Nextday', 'desc' => 'Pos Nextday Domestik', 'rate' => 14000, 'etd' => '1 hari'],
                ],
                'jateng_diy' => [
                    ['service' => 'Pos Reguler', 'desc' => 'Pos Reguler Domestik', 'rate' => 10000, 'etd' => '2-3 hari'],
                    ['service' => 'Pos Nextday', 'desc' => 'Pos Nextday Domestik', 'rate' => 18000, 'etd' => '1 hari'],
                ],
                'jawa' => [
                    ['service' => 'Pos Reguler', 'desc' => 'Pos Reguler Domestik', 'rate' => 18000, 'etd' => '2-4 hari'],
                    ['service' => 'Pos Nextday', 'desc' => 'Pos Nextday Domestik', 'rate' => 28000, 'etd' => '1 hari'],
                ],
                'dekat_luar_jawa' => [
                    ['service' => 'Pos Reguler', 'desc' => 'Pos Reguler Domestik', 'rate' => 26000, 'etd' => '3-5 hari'],
                ],
                'luar_jawa' => [
                    ['service' => 'Pos Reguler', 'desc' => 'Pos Reguler Domestik', 'rate' => 38000, 'etd' => '4-6 hari'],
                ],
            ],
            'tiki' => [
                'solo' => [
                    ['service' => 'ECO', 'desc' => 'Economy Service', 'rate' => 7000, 'etd' => '3 hari'],
                    ['service' => 'REG', 'desc' => 'Regular Service', 'rate' => 9000, 'etd' => '1-2 hari'],
                    ['service' => 'ONS', 'desc' => 'Over Night Services', 'rate' => 16000, 'etd' => '1 hari'],
                ],
                'jateng_diy' => [
                    ['service' => 'ECO', 'desc' => 'Economy Service', 'rate' => 9000, 'etd' => '3-4 hari'],
                    ['service' => 'REG', 'desc' => 'Regular Service', 'rate' => 11000, 'etd' => '2 hari'],
                    ['service' => 'ONS', 'desc' => 'Over Night Services', 'rate' => 19000, 'etd' => '1 hari'],
                ],
                'jawa' => [
                    ['service' => 'ECO', 'desc' => 'Economy Service', 'rate' => 15000, 'etd' => '3-5 hari'],
                    ['service' => 'REG', 'desc' => 'Regular Service', 'rate' => 19000, 'etd' => '2-3 hari'],
                    ['service' => 'ONS', 'desc' => 'Over Night Services', 'rate' => 30000, 'etd' => '1 hari'],
                ],
                'dekat_luar_jawa' => [
                    ['service' => 'ECO', 'desc' => 'Economy Service', 'rate' => 23000, 'etd' => '4-6 hari'],
                    ['service' => 'REG', 'desc' => 'Regular Service', 'rate' => 29000, 'etd' => '3-4 hari'],
                ],
                'luar_jawa' => [
                    ['service' => 'ECO', 'desc' => 'Economy Service', 'rate' => 35000, 'etd' => '5-7 hari'],
                    ['service' => 'REG', 'desc' => 'Regular Service', 'rate' => 43000, 'etd' => '3-5 hari'],
                ],
            ],
        ];

        $courierRates = $rates[$courier][$zone] ?? ($rates['jne'][$zone] ?? []);
        $courierName = match ($courier) {
            'jne' => 'Jalur Nugraha Ekakurir (JNE)',
            'sicepat' => 'SiCepat Ekspres (SICEPAT)',
            'jnt' => 'J&T Express (JNT)',
            'pos' => 'POS Indonesia (POS)',
            'tiki' => 'Citra Van Titipan Kilat (TIKI)',
            default => strtoupper($courier),
        };

        $result = [];
        foreach ($courierRates as $row) {
            $cost = (int) ($row['rate'] * $weightInKg);
            $result[] = [
                'code' => strtoupper($courier),
                'name' => $courierName,
                'service' => $row['service'],
                'description' => $row['desc'],
                'cost' => $cost,
                'etd' => $row['etd'],
            ];
        }

        return $result;
    }

    /**
     * Fallback provinces matching RajaOngkir IDs.
     *
     * @return array<int, array{province_id: string, province: string}>
     */
    public function getFallbackProvinces(): array
    {
        return [
            ['province_id' => '1', 'province' => 'Bali'],
            ['province_id' => '2', 'province' => 'Bangka Belitung'],
            ['province_id' => '3', 'province' => 'Banten'],
            ['province_id' => '4', 'province' => 'Bengkulu'],
            ['province_id' => '5', 'province' => 'DI Yogyakarta'],
            ['province_id' => '6', 'province' => 'DKI Jakarta'],
            ['province_id' => '7', 'province' => 'Gorontalo'],
            ['province_id' => '8', 'province' => 'Jambi'],
            ['province_id' => '9', 'province' => 'Jawa Barat'],
            ['province_id' => '10', 'province' => 'Jawa Tengah'],
            ['province_id' => '11', 'province' => 'Jawa Timur'],
            ['province_id' => '12', 'province' => 'Kalimantan Barat'],
            ['province_id' => '13', 'province' => 'Kalimantan Selatan'],
            ['province_id' => '14', 'province' => 'Kalimantan Tengah'],
            ['province_id' => '15', 'province' => 'Kalimantan Timur'],
            ['province_id' => '16', 'province' => 'Kalimantan Utara'],
            ['province_id' => '17', 'province' => 'Kepulauan Riau'],
            ['province_id' => '18', 'province' => 'Lampung'],
            ['province_id' => '19', 'province' => 'Maluku'],
            ['province_id' => '20', 'province' => 'Maluku Utara'],
            ['province_id' => '21', 'province' => 'Nanggroe Aceh Darussalam (NAD)'],
            ['province_id' => '22', 'province' => 'Nusa Tenggara Barat (NTB)'],
            ['province_id' => '23', 'province' => 'Nusa Tenggara Timur (NTT)'],
            ['province_id' => '24', 'province' => 'Papua'],
            ['province_id' => '25', 'province' => 'Papua Barat'],
            ['province_id' => '26', 'province' => 'Riau'],
            ['province_id' => '27', 'province' => 'Sulawesi Barat'],
            ['province_id' => '28', 'province' => 'Sulawesi Selatan'],
            ['province_id' => '29', 'province' => 'Sulawesi Tengah'],
            ['province_id' => '30', 'province' => 'Sulawesi Tenggara'],
            ['province_id' => '31', 'province' => 'Sulawesi Utara'],
            ['province_id' => '32', 'province' => 'Sumatera Barat'],
            ['province_id' => '33', 'province' => 'Sumatera Selatan'],
            ['province_id' => '34', 'province' => 'Sumatera Utara'],
        ];
    }

    /**
     * Fallback cities matching RajaOngkir starter IDs and Komerce IDs for popular destinations and surrounding areas.
     *
     * @return array<int, array{city_id: string, province_id: string, province: string, type: string, city_name: string, postal_code: string}>
     */
    public function getFallbackCities(?int $provinceId = null): array
    {
        // When configured with Komerce V2 API, province IDs differ from RajaOngkir Starter V1:
        // In Komerce: DKI Jakarta is 10, Jabar is 5, Banten is 11, Jateng is 12, DIY is 19, Jatim is 18.
        if ($this->isKomerce) {
            $komerceCities = [
                // DKI Jakarta (10)
                ['city_id' => '135', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Barat', 'postal_code' => '11220'],
                ['city_id' => '136', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Selatan', 'postal_code' => '12110'],
                ['city_id' => '137', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Pusat', 'postal_code' => '10540'],
                ['city_id' => '138', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Utara', 'postal_code' => '14140'],
                ['city_id' => '139', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Timur', 'postal_code' => '13330'],
                ['city_id' => '141', 'province_id' => '10', 'province' => 'DKI Jakarta', 'type' => 'Kabupaten', 'city_name' => 'Kepulauan Seribu', 'postal_code' => '14510'],

                // Jawa Tengah (12)
                ['city_id' => '444', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Surakarta (Solo)', 'postal_code' => '57100'],
                ['city_id' => '399', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Semarang', 'postal_code' => '50135'],
                ['city_id' => '427', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Sukoharjo', 'postal_code' => '57514'],
                ['city_id' => '196', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Karanganyar', 'postal_code' => '57718'],
                ['city_id' => '433', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Sragen', 'postal_code' => '57211'],
                ['city_id' => '92', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Boyolali', 'postal_code' => '57312'],
                ['city_id' => '200', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Klaten', 'postal_code' => '57411'],
                ['city_id' => '497', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Wonogiri', 'postal_code' => '57612'],
                ['city_id' => '249', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Magelang', 'postal_code' => '56133'],
                ['city_id' => '349', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Pekalongan', 'postal_code' => '51122'],
                ['city_id' => '468', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Tegal', 'postal_code' => '52114'],
                ['city_id' => '386', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Salatiga', 'postal_code' => '50711'],
                ['city_id' => '209', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Kudus', 'postal_code' => '59311'],
                ['city_id' => '344', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Pati', 'postal_code' => '59114'],
                ['city_id' => '186', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Jepara', 'postal_code' => '59411'],
                ['city_id' => '41', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Banyumas (Purwokerto)', 'postal_code' => '53114'],
                ['city_id' => '103', 'province_id' => '12', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Cilacap', 'postal_code' => '53211'],

                // DI Yogyakarta (19)
                ['city_id' => '501', 'province_id' => '19', 'province' => 'DI Yogyakarta', 'type' => 'Kota', 'city_name' => 'Yogyakarta', 'postal_code' => '55111'],
                ['city_id' => '419', 'province_id' => '19', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Sleman', 'postal_code' => '55511'],
                ['city_id' => '39', 'province_id' => '19', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Bantul', 'postal_code' => '55711'],
                ['city_id' => '210', 'province_id' => '19', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Kulon Progo', 'postal_code' => '55611'],

                // Jawa Barat (5)
                ['city_id' => '22', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bandung', 'postal_code' => '40111'],
                ['city_id' => '23', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bandung', 'postal_code' => '40311'],
                ['city_id' => '54', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bekasi', 'postal_code' => '17121'],
                ['city_id' => '55', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bekasi', 'postal_code' => '17530'],
                ['city_id' => '78', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bogor', 'postal_code' => '16119'],
                ['city_id' => '79', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bogor', 'postal_code' => '16911'],
                ['city_id' => '115', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Depok', 'postal_code' => '16416'],
                ['city_id' => '109', 'province_id' => '5', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Cirebon', 'postal_code' => '45116'],

                // Banten (11)
                ['city_id' => '455', 'province_id' => '11', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Tangerang', 'postal_code' => '15111'],
                ['city_id' => '456', 'province_id' => '11', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Tangerang Selatan', 'postal_code' => '15310'],
                ['city_id' => '457', 'province_id' => '11', 'province' => 'Banten', 'type' => 'Kabupaten', 'city_name' => 'Tangerang', 'postal_code' => '15710'],
                ['city_id' => '106', 'province_id' => '11', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Cilegon', 'postal_code' => '42411'],
                ['city_id' => '418', 'province_id' => '11', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Serang', 'postal_code' => '42111'],

                // Jawa Timur (18)
                ['city_id' => '444', 'province_id' => '18', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Surabaya', 'postal_code' => '60111'],
                ['city_id' => '255', 'province_id' => '18', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Malang', 'postal_code' => '65111'],
                ['city_id' => '417', 'province_id' => '18', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Sidoarjo', 'postal_code' => '61212'],
                ['city_id' => '160', 'province_id' => '18', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Jember', 'postal_code' => '68121'],
                ['city_id' => '178', 'province_id' => '18', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Kediri', 'postal_code' => '64121'],
            ];

            if ($provinceId !== null) {
                return array_values(array_filter($komerceCities, fn ($city) => (string) $city['province_id'] === (string) $provinceId));
            }

            return $komerceCities;
        }

        $all = [
            // Jawa Tengah (10)
            ['city_id' => '444', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Surakarta (Solo)', 'postal_code' => '57100'],
            ['city_id' => '399', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Semarang', 'postal_code' => '50135'],
            ['city_id' => '427', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Sukoharjo', 'postal_code' => '57514'],
            ['city_id' => '196', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Karanganyar', 'postal_code' => '57718'],
            ['city_id' => '433', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Sragen', 'postal_code' => '57211'],
            ['city_id' => '92', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Boyolali', 'postal_code' => '57312'],
            ['city_id' => '200', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Klaten', 'postal_code' => '57411'],
            ['city_id' => '497', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Wonogiri', 'postal_code' => '57612'],
            ['city_id' => '249', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Magelang', 'postal_code' => '56133'],
            ['city_id' => '349', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Pekalongan', 'postal_code' => '51122'],
            ['city_id' => '468', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Tegal', 'postal_code' => '52114'],
            ['city_id' => '386', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kota', 'city_name' => 'Salatiga', 'postal_code' => '50711'],
            ['city_id' => '398', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Semarang', 'postal_code' => '50511'],
            ['city_id' => '209', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Kudus', 'postal_code' => '59311'],
            ['city_id' => '344', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Pati', 'postal_code' => '59114'],
            ['city_id' => '186', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Jepara', 'postal_code' => '59411'],
            ['city_id' => '41', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Banyumas (Purwokerto)', 'postal_code' => '53114'],
            ['city_id' => '103', 'province_id' => '10', 'province' => 'Jawa Tengah', 'type' => 'Kabupaten', 'city_name' => 'Cilacap', 'postal_code' => '53211'],

            // DI Yogyakarta (5)
            ['city_id' => '501', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kota', 'city_name' => 'Yogyakarta', 'postal_code' => '55111'],
            ['city_id' => '419', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Sleman', 'postal_code' => '55511'],
            ['city_id' => '39', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Bantul', 'postal_code' => '55711'],
            ['city_id' => '135', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Gunung Kidul', 'postal_code' => '55812'],
            ['city_id' => '210', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Kulon Progo', 'postal_code' => '55611'],

            // DKI Jakarta (6)
            ['city_id' => '151', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Barat', 'postal_code' => '11220'],
            ['city_id' => '152', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Pusat', 'postal_code' => '10540'],
            ['city_id' => '153', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Selatan', 'postal_code' => '12110'],
            ['city_id' => '154', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Timur', 'postal_code' => '13330'],
            ['city_id' => '155', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Utara', 'postal_code' => '14140'],

            // Jawa Barat (9)
            ['city_id' => '22', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bandung', 'postal_code' => '40111'],
            ['city_id' => '23', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bandung', 'postal_code' => '40311'],
            ['city_id' => '24', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bandung Barat', 'postal_code' => '40552'],
            ['city_id' => '54', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bekasi', 'postal_code' => '17121'],
            ['city_id' => '55', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bekasi', 'postal_code' => '17530'],
            ['city_id' => '78', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bogor', 'postal_code' => '16119'],
            ['city_id' => '79', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Bogor', 'postal_code' => '16911'],
            ['city_id' => '115', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Depok', 'postal_code' => '16416'],
            ['city_id' => '109', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Cirebon', 'postal_code' => '45116'],
            ['city_id' => '469', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Tasikmalaya', 'postal_code' => '46116'],
            ['city_id' => '106', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Cimahi', 'postal_code' => '40512'],
            ['city_id' => '149', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Indramayu', 'postal_code' => '45214'],
            ['city_id' => '171', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kabupaten', 'city_name' => 'Karawang', 'postal_code' => '41311'],

            // Banten (3)
            ['city_id' => '455', 'province_id' => '3', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Tangerang', 'postal_code' => '15111'],
            ['city_id' => '456', 'province_id' => '3', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Tangerang Selatan', 'postal_code' => '15310'],
            ['city_id' => '457', 'province_id' => '3', 'province' => 'Banten', 'type' => 'Kabupaten', 'city_name' => 'Tangerang', 'postal_code' => '15914'],
            ['city_id' => '402', 'province_id' => '3', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Serang', 'postal_code' => '42111'],
            ['city_id' => '102', 'province_id' => '3', 'province' => 'Banten', 'type' => 'Kota', 'city_name' => 'Cilegon', 'postal_code' => '42417'],

            // Jawa Timur (11)
            ['city_id' => '445', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Surabaya', 'postal_code' => '60119'],
            ['city_id' => '255', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Malang', 'postal_code' => '65112'],
            ['city_id' => '418', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Sidoarjo', 'postal_code' => '61219'],
            ['city_id' => '133', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Gresik', 'postal_code' => '61115'],
            ['city_id' => '178', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Kediri', 'postal_code' => '64125'],
            ['city_id' => '252', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Madiun', 'postal_code' => '63122'],
            ['city_id' => '363', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Probolinggo', 'postal_code' => '67215'],
            ['city_id' => '342', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kota', 'city_name' => 'Pasuruan', 'postal_code' => '67118'],
            ['city_id' => '42', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Banyuwangi', 'postal_code' => '68416'],
            ['city_id' => '160', 'province_id' => '11', 'province' => 'Jawa Timur', 'type' => 'Kabupaten', 'city_name' => 'Jember', 'postal_code' => '68113'],

            // Bali (1)
            ['city_id' => '114', 'province_id' => '1', 'province' => 'Bali', 'type' => 'Kota', 'city_name' => 'Denpasar', 'postal_code' => '80227'],
            ['city_id' => '17', 'province_id' => '1', 'province' => 'Bali', 'type' => 'Kabupaten', 'city_name' => 'Badung', 'postal_code' => '80351'],
            ['city_id' => '128', 'province_id' => '1', 'province' => 'Bali', 'type' => 'Kabupaten', 'city_name' => 'Gianyar', 'postal_code' => '80511'],

            // Sumatera Utara (34)
            ['city_id' => '278', 'province_id' => '34', 'province' => 'Sumatera Utara', 'type' => 'Kota', 'city_name' => 'Medan', 'postal_code' => '20111'],
            ['city_id' => '118', 'province_id' => '34', 'province' => 'Sumatera Utara', 'type' => 'Kabupaten', 'city_name' => 'Deli Serdang', 'postal_code' => '20511'],

            // Sumatera Selatan (33)
            ['city_id' => '327', 'province_id' => '33', 'province' => 'Sumatera Selatan', 'type' => 'Kota', 'city_name' => 'Palembang', 'postal_code' => '30111'],

            // Lampung (18)
            ['city_id' => '21', 'province_id' => '18', 'province' => 'Lampung', 'type' => 'Kota', 'city_name' => 'Bandar Lampung', 'postal_code' => '35139'],

            // Sulawesi Selatan (28)
            ['city_id' => '254', 'province_id' => '28', 'province' => 'Sulawesi Selatan', 'type' => 'Kota', 'city_name' => 'Makassar', 'postal_code' => '90111'],

            // Kalimantan Timur (15)
            ['city_id' => '387', 'province_id' => '15', 'province' => 'Kalimantan Timur', 'type' => 'Kota', 'city_name' => 'Samarinda', 'postal_code' => '75111'],
            ['city_id' => '19', 'province_id' => '15', 'province' => 'Kalimantan Timur', 'type' => 'Kota', 'city_name' => 'Balikpapan', 'postal_code' => '76111'],
        ];

        if ($provinceId !== null) {
            return array_values(array_filter($all, fn ($city) => (string) $city['province_id'] === (string) $provinceId));
        }

        return $all;
    }
}
