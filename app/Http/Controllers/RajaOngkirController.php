<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    public function __construct(
        protected RajaOngkirService $rajaOngkirService
    ) {}

    /**
     * Get list of provinces.
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->rajaOngkirService->getProvinces();

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'provinces' => $provinces,
        ]);
    }

    /**
     * Get list of cities by province ID.
     */
    public function cities(Request $request): JsonResponse
    {
        $provinceId = $request->query('province_id') ? (int) $request->query('province_id') : null;
        $cities = $this->rajaOngkirService->getCities($provinceId);

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'cities' => $cities,
        ]);
    }

    /**
     * Get list of districts (kecamatan) by city ID.
     */
    public function districts(Request $request): JsonResponse
    {
        $cityId = $request->query('city_id') ? (int) $request->query('city_id') : null;
        $districts = $cityId ? $this->rajaOngkirService->getDistricts($cityId) : [];

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'districts' => $districts,
        ]);
    }

    /**
     * Get postal codes for a district or city.
     */
    public function postalCodes(Request $request): JsonResponse
    {
        $districtName = trim((string) $request->query('district_name', ''));
        $cityName = trim((string) $request->query('city_name', ''));
        $cityId = $request->query('city_id') ? (int) $request->query('city_id') : null;
        $search = $districtName !== '' ? $districtName : $cityName;

        $items = [];
        $uniqueZipCodes = [];

        if ($search !== '') {
            $destinations = $this->rajaOngkirService->searchDestination($search);

            foreach ($destinations as $dest) {
                $zip = trim($dest['zip_code'] ?? '');
                if ($zip === '') {
                    continue;
                }

                $subdistrict = trim($dest['subdistrict_name'] ?? '');
                $district = trim($dest['district_name'] ?? '');
                $city = trim($dest['city_name'] ?? '');

                // Filter by city if both are provided to avoid cross-city district collisions
                if ($cityName !== '' && $city !== '') {
                    $cleanedCityName = trim(preg_replace('/\b(kota|kabupaten|kab\.)\b/i', '', $cityName));
                    if (stripos($city, $cleanedCityName) === false && stripos($cleanedCityName, $city) === false) {
                        continue;
                    }
                }

                if (! in_array($zip, $uniqueZipCodes)) {
                    $uniqueZipCodes[] = $zip;
                    $items[] = [
                        'postal_code' => $zip,
                        'subdistrict' => $subdistrict,
                        'district' => $district,
                        'city' => $city,
                        'label' => $subdistrict ? "{$zip} ({$subdistrict})" : $zip,
                    ];
                }
            }
        }

        // Fallback: If no postal code found via API search, check city's default postal code
        if (empty($uniqueZipCodes) && $cityId) {
            $cities = $this->rajaOngkirService->getCities();
            foreach ($cities as $c) {
                if ((int) ($c['city_id'] ?? 0) === $cityId && ! empty($c['postal_code'])) {
                    $uniqueZipCodes[] = (string) $c['postal_code'];
                    $items[] = [
                        'postal_code' => (string) $c['postal_code'],
                        'subdistrict' => '',
                        'district' => $districtName,
                        'city' => $c['city_name'] ?? '',
                        'label' => (string) $c['postal_code'],
                    ];
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'postal_codes' => $uniqueZipCodes,
            'items' => $items,
            'default_postal_code' => $uniqueZipCodes[0] ?? null,
        ]);
    }

    /**
     * Search domestic destinations (subdistricts/cities).
     */
    public function destinations(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $results = $this->rajaOngkirService->searchDestination($search);

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'destinations' => $results,
        ]);
    }

    /**
     * Calculate shipping costs for destination city and package weight.
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination' => ['required'],
            'weight' => ['nullable', 'integer', 'min:1'],
            'courier' => ['nullable'],
            'city_name' => ['nullable', 'string'],
            'province_name' => ['nullable', 'string'],
            'province_id' => ['nullable'],
        ]);

        $destination = (int) $validated['destination'];
        $weight = isset($validated['weight']) ? (int) $validated['weight'] : 1000;
        $courier = $validated['courier'] ?? $this->rajaOngkirService->getSupportedCouriers();
        $cityName = $validated['city_name'] ?? null;
        $provinceName = $validated['province_name'] ?? null;
        $provinceId = ! empty($validated['province_id']) ? (int) $validated['province_id'] : null;

        $services = $this->rajaOngkirService->calculateCost(
            destinationCityId: $destination,
            weight: $weight,
            couriers: $courier,
            cityName: $cityName,
            provinceName: $provinceName,
            provinceId: $provinceId
        );

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'origin' => $this->rajaOngkirService->getOriginInfo(),
            'destination_id' => $destination,
            'weight' => $weight,
            'services' => $services,
        ]);
    }
}
