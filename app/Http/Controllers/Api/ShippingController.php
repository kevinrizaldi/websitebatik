<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        protected RajaOngkirService $rajaOngkirService
    ) {}

    /**
     * Calculate shipping costs from the store origin to the customer's
     * address, using the RajaOngkir service (with offline fallback).
     */
    public function costs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['nullable', 'integer'],
            'weight' => ['nullable', 'integer', 'min:1'],
            'couriers' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $destinationCityId = null;
        if (! empty($validated['address_id'])) {
            $address = Address::query()
                ->where('user_id', $user->id)
                ->find($validated['address_id']);

            if ($address) {
                $destinationCityId = $address->city_id;
                if (! $destinationCityId) {
                    $cityName = (string) ($address->city ?? $address->kota ?? '');
                    $destinationCityId = $this->resolveCityId($cityName);
                }
            }
        }

        $weight = max(1000, (int) ($validated['weight'] ?? 1000));
        $destinationCityId ??= (int) config('rajaongkir.origin_city_id', 444);
        $couriers = isset($validated['couriers'])
            ? explode(',', $validated['couriers'])
            : ['jne', 'sicepat', 'jnt', 'pos', 'tiki'];

        $services = $this->rajaOngkirService->calculateCost(
            destinationCityId: $destinationCityId,
            weight: $weight,
            couriers: $couriers,
        );

        return response()->json([
            'success' => true,
            'is_live' => $this->rajaOngkirService->isApiKeyConfigured(),
            'origin' => $this->rajaOngkirService->getOriginInfo(),
            'destination_city_id' => $destinationCityId,
            'weight' => $weight,
            'services' => $services,
        ]);
    }

    /**
     * Resolve a free-text city name to a Komerce/RajaOngkir city id by
     * matching against the cached cities list.
     */
    private function resolveCityId(string $cityName): ?int
    {
        $cityName = trim($cityName);
        if ($cityName === '') {
            return null;
        }

        $needle = mb_strtolower($cityName);
        foreach ($this->rajaOngkirService->getCities() as $city) {
            $name = mb_strtolower((string) ($city['city_name'] ?? ''));
            if ($name === '' ) {
                continue;
            }
            if ($name === $needle || str_contains($name, $needle)) {
                return (int) ($city['city_id'] ?? 0) ?: null;
            }
        }

        return null;
    }
}
