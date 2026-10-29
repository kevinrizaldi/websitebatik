<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    protected string $key;
    protected string $baseUrl;
    protected int $originKotaId;
    protected int $packagingPerItem;
    protected int $packagingMinimum;

    public function __construct()
    {
        $this->key = config('rajaongkir.key');
        $this->baseUrl = config('rajaongkir.base_url');
        $this->originKotaId = config('rajaongkir.origin.kota_id');
        $this->packagingPerItem = config('rajaongkir.packaging.default_per_item');
        $this->packagingMinimum = config('rajaongkir.packaging.minimum');
    }

    // ===== Wilayah =====
    public function provinsi(): array
    {
        return Cache::remember('rajaongkir:provinsi', 86400, fn() =>
            $this->get('province')->json('rajaongkir.results') ?? []
        );
    }

    public function kota(int $provinsiId): array
    {
        return Cache::remember("rajaongkir:kota:{$provinsiId}", 86400, fn() =>
            $this->get('city', ['province' => $provinsiId])->json('rajaongkir.results') ?? []
        );
    }

    // ===== Ongkir =====
    /**
     * Hitung ongkir berdasarkan total berat (sudah termasuk packaging).
     *
     * @param int $tujuanKotaId Kota tujuan customer
     * @param int $totalBeratGram Total berat produk + packaging (gram)
     * @param string $kurir 'jne'|'tiki'|'pos'|'all'
     */
    public function hitungOngkir(int $tujuanKotaId, int $totalBeratGram, string $kurir = 'jne'): array
    {
        $cacheKey = "rajaongkir:cost:{$this->originKotaId}:{$tujuanKotaId}:{$totalBeratGram}:{$kurir}";

        return Cache::remember($cacheKey, 3600, function () use ($tujuanKotaId, $totalBeratGram, $kurir) {
            $response = Http::withHeaders(['key' => $this->key])
                ->asForm()
                ->post("{$this->baseUrl}/cost", [
                    'origin'      => $this->originKotaId,
                    'destination' => $tujuanKotaId,
                    'weight'      => $totalBeratGram,
                    'courier'     => $kurir,
                ]);

            if (!$response->ok()) {
                return ['error' => 'Gagal menghitung ongkir', 'details' => $response->json()];
            }

            $results = $response->json('rajaongkir.results');
            if (empty($results)) return [];

            $first = $results[0];
            $costs = $first['costs'] ?? [];

            return collect($costs)->map(function ($c) use ($first) {
                $svc = $c['service'] ?? '';
                $cost = $c['cost'][0]['value'] ?? 0;
                $etd = $c['cost'][0]['etd'] ?? '';
                $note = $c['cost'][0]['note'] ?? '';

                return [
                    'kurir'       => strtoupper($first['code']),
                    'service'     => $svc,
                    'description' => $first['name'] . ' ' . $svc,
                    'cost'        => $cost,
                    'etd'         => $etd ? "{$etd} hari" : '-',
                    'note'        => $note,
                ];
            })->values()->all();
        });
    }

    // ===== Helper =====
    protected function get(string $endpoint, array $query = [])
    {
        return Http::withHeaders(['key' => $this->key])
            ->get("{$this->baseUrl}/{$endpoint}", $query);
    }

    /** Hitung total berat termasuk packaging.
     *  $items = collection of CartItem with produk relation (produk.berat in gram)
     */
    public function hitungTotalBerat($items): int
    {
        $totalProduk = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            if ($item->produk) {
                $beratItem = (int) ($item->produk->berat ?? 0);
                $totalProduk += $beratItem * $item->qty;
                $totalItems += $item->qty;
            }
        }

        $packaging = $totalItems * $this->packagingPerItem;
        if ($packaging < $this->packagingMinimum) {
            $packaging = $this->packagingMinimum;
        }

        return $totalProduk + $packaging;
    }
}