<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RajaOngkirShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_provinces(): void
    {
        $response = $this->getJson(route('rajaongkir.provinces'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'is_live',
                'provinces' => [
                    '*' => ['province_id', 'province'],
                ],
            ]);
    }

    public function test_can_fetch_cities_by_province(): void
    {
        // 10 = Jawa Tengah
        $response = $this->getJson(route('rajaongkir.cities', ['province_id' => 10]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'is_live',
                'cities' => [
                    '*' => ['city_id', 'province_id', 'city_name'],
                ],
            ]);

        $cities = $response->json('cities');
        $this->assertNotEmpty($cities);
    }

    public function test_can_search_destinations(): void
    {
        $response = $this->getJson(route('rajaongkir.destinations', ['search' => 'surakarta']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_can_calculate_shipping_cost(): void
    {
        $response = $this->postJson(route('rajaongkir.cost'), [
            'destination' => 444, // Surakarta
            'weight' => 1000,
            'courier' => ['jne'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'destination_id' => 444,
                'weight' => 1000,
            ])
            ->assertJsonStructure([
                'success',
                'services' => [
                    '*' => ['code', 'name', 'service', 'description', 'cost', 'etd'],
                ],
            ]);

        $services = $response->json('services');
        $this->assertNotEmpty($services);
        $this->assertEquals('JNE', $services[0]['code']);
        $this->assertGreaterThan(0, $services[0]['cost']);
    }

    public function test_jakarta_barat_shipping_cost_matches_expected_fifteen_thousand_rate(): void
    {
        $response = $this->postJson(route('rajaongkir.cost'), [
            'destination' => 135, // Jakarta Barat
            'weight' => 1000,
            'courier' => ['jne'],
            'city_name' => 'Kota Jakarta Barat',
            'province_name' => 'Dki Jakarta',
            'province_id' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'destination_id' => 135,
            ]);

        $services = $response->json('services');
        $this->assertNotEmpty($services);

        $regService = collect($services)->firstWhere('service', 'REG');
        $this->assertNotNull($regService, 'JNE REG service should be present');
        $this->assertEquals(15000, $regService['cost'], 'JNE REG from Solo to Jakarta Barat must be Rp 15.000');
    }

    public function test_user_can_save_address_with_rajaongkir_city_and_province_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('alamat.store'), [
            'label_alamat' => 'Rumah Utama',
            'penerima' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'provinsi' => 'Jawa Tengah',
            'province_id' => 10,
            'kota' => 'Kota Surakarta (Solo)',
            'city_id' => 444,
            'kode_pos' => '57126',
            'alamat_lengkap' => 'Jl. Slamet Riyadi No. 123, Laweyan',
            'is_utama' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $table = Schema::hasTable('addresses') ? 'addresses' : 'alamats';
        $this->assertDatabaseHas($table, [
            'user_id' => $user->id,
            'penerima' => 'Budi Santoso',
            'province_id' => 10,
            'city_id' => 444,
            'kota' => 'Kota Surakarta (Solo)',
            'is_utama' => 1,
        ]);
    }

    public function test_checkout_records_selected_rajaongkir_shipping_cost(): void
    {
        $user = User::factory()->create();
        $produk = Produk::create([
            'nama' => 'Kemeja Batik Solo Parang',
            'sku' => 'BTK-SOLO-01',
            'kategori' => 'Kemeja Pria',
            'harga' => 250000,
            'stok' => 10,
            'status' => 'Tersedia',
            'deskripsi' => 'Batik katun premium',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'qty' => 1,
            'selected' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('checkout.store'), [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Slamet Riyadi No. 123, Surakarta',
            'city' => 'Kota Surakarta (Solo)',
            'city_id' => 444,
            'province' => 'Jawa Tengah',
            'province_id' => 10,
            'postal_code' => '57126',
            'shipping_option' => 'JNE - REG (Layanan Reguler)',
            'shipping_cost' => 12000,
            'payment_method' => 'Midtrans Gateway',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Budi Santoso',
            'shipping_cost' => 12000,
            'total_price' => 262000, // 250.000 + 12.000
        ]);
    }
}
