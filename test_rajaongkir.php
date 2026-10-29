<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$svc = app(App\Services\RajaOngkirService::class);

echo "=== Testing Provinsi ===\n";
$provinsi = $svc->provinsi();
echo "Count: " . count($provinsi) . "\n";
if (!empty($provinsi)) {
    echo "First 3: \n";
    print_r(array_slice($provinsi, 0, 3));
}

echo "\n=== Testing Kota (Provinsi 9 - Jawa Barat) ===\n";
$kota = $svc->kota(9);
echo "Count: " . count($kota) . "\n";
if (!empty($kota)) {
    echo "First 5: \n";
    print_r(array_slice($kota, 0, 5));
}

echo "\n=== Testing Hitung Ongkir (Bandung 153 -> Jakarta 154, 1000g, JNE) ===\n";
$ongkir = $svc->hitungOngkir(154, 1000, 'jne');
print_r($ongkir);

echo "\n=== Testing Hitung Total Berat ===\n";
// Mock cart items
class MockProduk {
    public $berat = 300;
}
class MockItem {
    public $qty = 2;
    public $produk;
    public function __construct() { $this->produk = new MockProduk(); }
}
$items = collect([new MockItem(), new MockItem()]);
$totalBerat = $svc->hitungTotalBerat($items);
echo "Total Berat (2 items x 300g + packaging): $totalBerat gram\n";