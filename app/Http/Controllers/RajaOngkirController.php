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
        ]);

        $destination = (int) $validated['destination'];
        $weight = isset($validated['weight']) ? (int) $validated['weight'] : 1000;
        $courier = $validated['courier'] ?? $this->rajaOngkirService->getSupportedCouriers();

        $services = $this->rajaOngkirService->calculateCost(
            destinationCityId: $destination,
            weight: $weight,
            couriers: $courier
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
