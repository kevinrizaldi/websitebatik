<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AlamatController extends Controller
{
    protected RajaOngkirService $rajaOngkirService;

    public function __construct(?RajaOngkirService $rajaOngkirService = null)
    {
        $this->rajaOngkirService = $rajaOngkirService ?? app(RajaOngkirService::class);
    }

    /**
     * Halaman kelola alamat + API daftar alamat untuk checkout.
     * Menyesuaikan tampilan storefront yang sudah ada bila diakses via browser.
     */
    public function index(): JsonResponse|View
    {
        $user = Auth::user();
        $alamats = $user->alamats()->latest()->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'alamats' => $alamats,
            ]);
        }

        $pesananAktif = $user->orders()->whereIn('status', [
            'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi',
            'Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi', 'Dikirim',
        ])->count();
        $ulasanCount = $user->ulasans()->count();

        return view('alamat.index', compact('alamats', 'user', 'pesananAktif', 'ulasanCount'));
    }

    /**
     * Store new address.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->has('kode_pos') && trim((string) $request->input('kode_pos')) === '') {
            $request->merge(['kode_pos' => null]);
        }
        if ($request->has('provinsi') && trim((string) $request->input('provinsi')) === '') {
            $request->merge(['provinsi' => null]);
        }
        if ($request->has('label_alamat') && trim((string) $request->input('label_alamat')) === '') {
            $request->merge(['label_alamat' => null]);
        }
        if ($request->has('no_telepon')) {
            $request->merge(['no_telepon' => preg_replace('/\D/', '', (string) $request->input('no_telepon'))]);
        }

        $validated = $request->validate([
            'label_alamat' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s.,\-()]+$/u'],
            'penerima' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.()\-]+$/u'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-()\[\]#&\'":;]+$/u'],
            'kota' => ['required', 'string', 'max:100', 'regex:/^[\pL\s.,\-()]+$/u'],
            'kecamatan' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s.,\-()]+$/u'],
            'provinsi' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s.,\-()]+$/u'],
            'province_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'kode_pos' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_utama' => 'nullable|boolean',
        ], [
            'penerima.required' => 'Nama penerima wajib diisi.',
            'penerima.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, tanda petik, atau tanda kurung.',
            'label_alamat.regex' => 'Label alamat hanya boleh berupa huruf, angka, titik, dan spasi.',
            'no_telepon.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'no_telepon.regex' => 'Nomor WhatsApp / HP hanya boleh berisi angka (10 sampai 15 digit).',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.regex' => 'Alamat lengkap tidak boleh mengandung simbol khusus yang tidak wajar.',
            'kota.required' => 'Kabupaten / Kota wajib diisi.',
            'kota.regex' => 'Kabupaten / Kota hanya boleh berupa huruf, spasi, atau singkatan daerah.',
            'provinsi.regex' => 'Provinsi hanya boleh berupa huruf, spasi, atau singkatan daerah.',
            'kode_pos.regex' => 'Kode pos harus berupa 5 digit angka.',
        ]);

        $user = Auth::user();

        // If explicitly requested, respect the boolean flag; otherwise default to true only for first address
        $isUtama = $request->has('is_utama')
            ? $request->boolean('is_utama')
            : ($user->alamats()->count() === 0);

        if ($isUtama) {
            $user->alamats()->update(['is_utama' => false, 'is_primary' => false]);
        }

        $alamat = $user->alamats()->create([
            'label_alamat' => $validated['label_alamat'] ?: 'Rumah',
            'penerima' => $validated['penerima'],
            'no_telepon' => $validated['no_telepon'],
            'alamat_lengkap' => $validated['alamat_lengkap'],
            'kota' => $validated['kota'],
            'kecamatan' => $validated['kecamatan'] ?? null,
            'district' => $validated['kecamatan'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'provinsi' => $validated['provinsi'] ?? 'Indonesia',
            'province_id' => $validated['province_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'kode_pos' => $validated['kode_pos'] ?? '',
            'latitude' => $request->filled('latitude') ? (float) $request->input('latitude') : null,
            'longitude' => $request->filled('longitude') ? (float) $request->input('longitude') : null,
            'is_utama' => $isUtama,
            'is_primary' => $isUtama,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil disimpan.',
                'alamat' => $alamat,
            ]);
        }

        return back()->with('success', 'Alamat berhasil disimpan.');
    }

    /**
     * Update address.
     */
    public function update(Request $request, Address $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->has('kode_pos') && trim((string) $request->input('kode_pos')) === '') {
            $request->merge(['kode_pos' => null]);
        }
        if ($request->has('provinsi') && trim((string) $request->input('provinsi')) === '') {
            $request->merge(['provinsi' => null]);
        }
        if ($request->has('label_alamat') && trim((string) $request->input('label_alamat')) === '') {
            $request->merge(['label_alamat' => null]);
        }
        if ($request->has('no_telepon')) {
            $request->merge(['no_telepon' => preg_replace('/\D/', '', (string) $request->input('no_telepon'))]);
        }

        $validated = $request->validate([
            'label_alamat' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s.,\-()]+$/u'],
            'penerima' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.()\-]+$/u'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-()\[\]#&\'":;]+$/u'],
            'kota' => ['required', 'string', 'max:100', 'regex:/^[\pL\s.,\-()]+$/u'],
            'kecamatan' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s.,\-()]+$/u'],
            'provinsi' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s.,\-()]+$/u'],
            'province_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'kode_pos' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_utama' => 'nullable|boolean',
        ], [
            'penerima.required' => 'Nama penerima wajib diisi.',
            'penerima.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, tanda petik, atau tanda kurung.',
            'label_alamat.regex' => 'Label alamat hanya boleh berupa huruf, angka, titik, dan spasi.',
            'no_telepon.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'no_telepon.regex' => 'Nomor WhatsApp / HP hanya boleh berisi angka (10 sampai 15 digit).',
            'alamat_lengkap.required' => 'Alamat lengkap wajib diisi.',
            'alamat_lengkap.regex' => 'Alamat lengkap tidak boleh mengandung simbol khusus yang tidak wajar.',
            'kota.required' => 'Kabupaten / Kota wajib diisi.',
            'kota.regex' => 'Kabupaten / Kota hanya boleh berupa huruf, spasi, atau singkatan daerah.',
            'provinsi.regex' => 'Provinsi hanya boleh berupa huruf, spasi, atau singkatan daerah.',
            'kode_pos.regex' => 'Kode pos harus berupa 5 digit angka.',
        ]);

        if (! empty($validated['is_utama'])) {
            Auth::user()->alamats()->where('id', '!=', $alamat->id)->update([
                'is_utama' => false,
                'is_primary' => false,
            ]);
            $validated['is_primary'] = true;
        }

        if ($request->has('latitude')) {
            $validated['latitude'] = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        }
        if ($request->has('longitude')) {
            $validated['longitude'] = $request->filled('longitude') ? (float) $request->input('longitude') : null;
        }

        $alamat->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui.',
                'alamat' => $alamat,
            ]);
        }

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Set address as primary.
     */
    public function setDefault(Address $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        Auth::user()->alamats()->update(['is_utama' => false, 'is_primary' => false]);
        $alamat->update(['is_utama' => true, 'is_primary' => true]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat utama berhasil diubah.',
            ]);
        }

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }

    /**
     * Delete address.
     */
    public function destroy(Address $alamat): JsonResponse|RedirectResponse
    {
        if ($alamat->user_id !== Auth::id()) {
            abort(403);
        }

        $alamat->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * Proxy geocoding to OpenStreetMap Nominatim with server-side User-Agent.
     */
    public function geocode(Request $request): JsonResponse
    {
        $jalan = trim((string) $request->query('jalan', ''));
        $kecamatan = trim((string) $request->query('kecamatan', ''));
        $kota = trim((string) $request->query('kota', ''));
        $provinsi = trim((string) $request->query('provinsi', ''));
        $query = trim((string) $request->query('q', ''));

        $cleanedKota = trim((string) preg_replace('~\b(kota|kabupaten|kab\.)\b~i', '', $kota));

        // Build cascading fallback queries from specific to broad
        $queries = [];
        if ($query !== '') {
            $queries[] = $query;
        }

        if ($jalan !== '') {
            $cleanedJalan = trim((string) preg_replace('~\b(rt[\s./]*\d+|rw[\s./]*\d+|no[\s./]*\d+|nomor[\s./]*\d+|blok[\s./]*\w+)\b~i', '', $jalan));
            $cleanedJalan = trim(preg_replace('/\s+/', ' ', $cleanedJalan));

            if ($cleanedJalan !== '' && $cleanedJalan !== $jalan) {
                if ($kecamatan !== '' && $cleanedKota !== '') {
                    $queries[] = "{$cleanedJalan}, {$kecamatan}, {$cleanedKota}, Indonesia";
                }
                if ($cleanedKota !== '') {
                    $queries[] = "{$cleanedJalan}, {$cleanedKota}, Indonesia";
                }
                $queries[] = "{$cleanedJalan}, Indonesia";
            }

            if ($kecamatan !== '' && $cleanedKota !== '') {
                $queries[] = "{$jalan}, {$kecamatan}, {$cleanedKota}, Indonesia";
            }
            if ($cleanedKota !== '') {
                $queries[] = "{$jalan}, {$cleanedKota}, Indonesia";
            }
            $queries[] = "{$jalan}, Indonesia";
        }

        if ($kecamatan !== '' && $cleanedKota !== '') {
            $queries[] = "{$kecamatan}, {$cleanedKota}, Indonesia";
        }
        if ($cleanedKota !== '') {
            $queries[] = "{$cleanedKota}, Indonesia";
        }

        $queries = array_values(array_unique(array_filter($queries)));

        foreach ($queries as $q) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'WebsiteBatik-App/1.0 (contact@websitebatik.test)',
                    'Accept-Language' => 'id',
                ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'q' => $q,
                    'countrycodes' => 'id',
                    'limit' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (! empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                        return response()->json([
                            'success' => true,
                            'found' => true,
                            'latitude' => (float) $data[0]['lat'],
                            'longitude' => (float) $data[0]['lon'],
                            'display_name' => $data[0]['display_name'] ?? '',
                            'matched_query' => $q,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Try next query
            }
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'message' => 'Titik lokasi belum ditemukan otomatis di peta.',
        ]);
    }

    /**
     * Proxy reverse geocoding to OpenStreetMap Nominatim with server-side User-Agent.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat');
        $lon = (float) $request->query('lng', $request->query('lon'));

        if (! $lat || ! $lon) {
            return response()->json(['success' => false, 'message' => 'Koordinat tidak valid.'], 422);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'WebsiteBatik-App/1.0 (contact@websitebatik.test)',
                'Accept-Language' => 'id',
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lon,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $matchedRegion = $this->matchRegionFromNominatim(
                    $data['address'] ?? [],
                    $data['address']['postcode'] ?? null
                );

                return response()->json([
                    'success' => true,
                    'display_name' => $data['display_name'] ?? '',
                    'postcode' => $data['address']['postcode'] ?? null,
                    'address' => $data['address'] ?? [],
                    'matched_region' => $matchedRegion,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengambil alamat dari titik koordinat.']);
    }

    /**
     * Map Nominatim reverse-geocode address parts to RajaOngkir province, city, district, and postal code.
     */
    protected function matchRegionFromNominatim(array $address, ?string $postcode = null): ?array
    {
        if (empty($address)) {
            return null;
        }

        $rawState = trim((string) ($address['state'] ?? $address['province'] ?? $address['region'] ?? ''));
        $rawCity = trim((string) ($address['city'] ?? $address['county'] ?? $address['town'] ?? $address['city_district'] ?? $address['municipality'] ?? ''));
        $rawDistrict = trim((string) ($address['suburb'] ?? $address['district'] ?? $address['city_district'] ?? $address['municipality'] ?? $address['village'] ?? $address['neighbourhood'] ?? ''));
        $rawPostcode = trim((string) ($postcode ?? $address['postcode'] ?? ''));

        // Standardize common Indonesian province aliases
        $provinceAliases = [
            'central java' => 'Jawa Tengah',
            'east java' => 'Jawa Timur',
            'west java' => 'Jawa Barat',
            'special region of yogyakarta' => 'DI Yogyakarta',
            'daerah istimewa yogyakarta' => 'DI Yogyakarta',
            'yogyakarta' => 'DI Yogyakarta',
            'special capital region of jakarta' => 'DKI Jakarta',
            'daerah khusus ibukota jakarta' => 'DKI Jakarta',
            'jakarta' => 'DKI Jakarta',
        ];

        $stateLower = strtolower($rawState);
        $stateClean = $provinceAliases[$stateLower] ?? trim(preg_replace('/\b(provinsi|daerah khusus ibukota|daerah istimewa)\b/i', '', $rawState));

        $provinces = $this->rajaOngkirService->getProvinces();
        $matchedProvince = null;

        // 1. Match Province
        foreach ($provinces as $p) {
            $pName = (string) ($p['province'] ?? '');
            if ($pName === '') {
                continue;
            }

            if (strcasecmp($pName, $rawState) === 0 || strcasecmp($pName, $stateClean) === 0) {
                $matchedProvince = $p;
                break;
            }

            if ($stateClean !== '' && (stripos($pName, $stateClean) !== false || stripos($stateClean, $pName) !== false)) {
                $matchedProvince = $p;
                break;
            }
        }

        if (! $matchedProvince) {
            return null;
        }

        $provId = (int) $matchedProvince['province_id'];
        $provName = (string) $matchedProvince['province'];

        // 2. Match City in that Province
        $cities = $this->rajaOngkirService->getCities($provId);
        $matchedCity = null;

        $cleanCitySearch = trim(preg_replace('/\b(kota|kabupaten|kab\.)\b/i', '', $rawCity));

        foreach ($cities as $c) {
            $cName = (string) ($c['city_name'] ?? '');
            $type = (string) ($c['type'] ?? '');
            $fullName = trim("{$type} {$cName}");

            if (strcasecmp($cName, $cleanCitySearch) === 0 || strcasecmp($fullName, $rawCity) === 0 || strcasecmp($cName, $rawCity) === 0) {
                $matchedCity = $c;
                break;
            }

            if ($cleanCitySearch !== '' && (stripos($cName, $cleanCitySearch) !== false || stripos($cleanCitySearch, $cName) !== false)) {
                $matchedCity = $c;
                break;
            }

            // Solo / Surakarta alias
            if ((stripos($cleanCitySearch, 'Solo') !== false || stripos($cleanCitySearch, 'Surakarta') !== false) &&
                (stripos($cName, 'Surakarta') !== false || stripos($cName, 'Solo') !== false)) {
                $matchedCity = $c;
                break;
            }
        }

        // If not found in that province, try searching across all cities as fallback
        if (! $matchedCity && $cleanCitySearch !== '') {
            foreach ($this->rajaOngkirService->getCities() as $c) {
                $cName = (string) ($c['city_name'] ?? '');
                if (strcasecmp($cName, $cleanCitySearch) === 0 ||
                    ((stripos($cleanCitySearch, 'Solo') !== false || stripos($cleanCitySearch, 'Surakarta') !== false) &&
                     (stripos($cName, 'Surakarta') !== false || stripos($cName, 'Solo') !== false))) {
                    $matchedCity = $c;
                    $provId = (int) ($c['province_id'] ?? $provId);
                    $provName = (string) ($c['province'] ?? $provName);
                    break;
                }
            }
        }

        if (! $matchedCity) {
            return [
                'province' => $provName,
                'province_id' => $provId,
                'city' => null,
                'city_id' => null,
                'district' => null,
                'district_id' => null,
                'postal_code' => $rawPostcode ?: null,
            ];
        }

        $cityId = (int) $matchedCity['city_id'];
        $type = (string) ($matchedCity['type'] ?? '');
        $cName = (string) ($matchedCity['city_name'] ?? '');
        $cityDisplayName = $type ? "{$type} {$cName}" : $cName;
        $cityDefaultPostal = (string) ($matchedCity['postal_code'] ?? '');

        // 3. Match District (Kecamatan) in that City
        $districts = $this->rajaOngkirService->getDistricts($cityId);
        $matchedDistrict = null;

        $cleanDistrictSearch = trim(preg_replace('/\b(kecamatan|kec\.|kelurahan|desa)\b/i', '', $rawDistrict));

        if (! empty($districts)) {
            $candidateNames = array_filter([
                $cleanDistrictSearch,
                (string) ($address['suburb'] ?? ''),
                (string) ($address['village'] ?? ''),
                (string) ($address['neighbourhood'] ?? ''),
                (string) ($address['quarter'] ?? ''),
                (string) ($address['city_district'] ?? ''),
            ]);

            foreach ($districts as $d) {
                $dName = (string) ($d['district_name'] ?? '');
                if ($dName === '') {
                    continue;
                }

                foreach ($candidateNames as $cand) {
                    $candClean = trim(preg_replace('/\b(kecamatan|kec\.|kelurahan|desa)\b/i', '', $cand));
                    if ($candClean === '') {
                        continue;
                    }

                    if (strcasecmp($dName, $candClean) === 0 || stripos($dName, $candClean) !== false || stripos($candClean, $dName) !== false) {
                        $matchedDistrict = $d;
                        break 2;
                    }
                }
            }
        }

        $districtId = $matchedDistrict ? (int) ($matchedDistrict['district_id'] ?? 0) : null;
        $districtName = $matchedDistrict ? (string) $matchedDistrict['district_name'] : ($cleanDistrictSearch ?: null);

        // 4. Postal Code
        $finalPostal = $rawPostcode ?: $cityDefaultPostal;

        return [
            'province' => $provName,
            'province_id' => $provId,
            'city' => $cityDisplayName,
            'city_id' => $cityId,
            'district' => $districtName,
            'district_id' => $districtId,
            'postal_code' => $finalPostal ?: null,
        ];
    }
}
