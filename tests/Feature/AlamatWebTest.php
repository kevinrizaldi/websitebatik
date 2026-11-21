<?php

namespace Tests\Feature;

use App\Models\Alamat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlamatWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_address_with_coordinates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('alamat.store'), [
                'label_alamat' => 'Rumah',
                'penerima' => 'Budi Santoso',
                'no_telepon' => '081234567890',
                'alamat_lengkap' => 'Jl. Slamet Riyadi No. 12',
                'kota' => 'Kota Surakarta',
                'provinsi' => 'Jawa Tengah',
                'kode_pos' => '57111',
                'latitude' => -7.566600,
                'longitude' => 110.829000,
                'is_utama' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'penerima' => 'Budi Santoso',
            'latitude' => -7.566600,
            'longitude' => 110.829000,
        ]);
    }

    public function test_user_can_update_address_coordinates(): void
    {
        $user = User::factory()->create();
        $alamat = Alamat::create([
            'user_id' => $user->id,
            'label_alamat' => 'Rumah',
            'penerima' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'alamat_lengkap' => 'Jl. Slamet Riyadi No. 12',
            'kota' => 'Kota Surakarta',
            'provinsi' => 'Jawa Tengah',
            'kode_pos' => '57111',
        ]);

        $response = $this->actingAs($user)
            ->put(route('alamat.update', $alamat), [
                'label_alamat' => 'Kantor',
                'penerima' => 'Budi Santoso',
                'no_telepon' => '081234567890',
                'alamat_lengkap' => 'Jl. Jenderal Sudirman No. 45',
                'kota' => 'Kota Surakarta',
                'provinsi' => 'Jawa Tengah',
                'kode_pos' => '57111',
                'latitude' => -7.570000,
                'longitude' => 110.830000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $alamat->id,
            'label_alamat' => 'Kantor',
            'latitude' => -7.570000,
            'longitude' => 110.830000,
        ]);
    }

    public function test_user_can_create_address_with_kecamatan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('alamat.store'), [
                'label_alamat' => 'Rumah',
                'penerima' => 'Budi Santoso',
                'no_telepon' => '081234567890',
                'alamat_lengkap' => 'Jl. Slamet Riyadi No. 12',
                'kota' => 'Surakarta',
                'kecamatan' => 'Pasar Kliwon',
                'district_id' => 5263,
                'provinsi' => 'Jawa Tengah',
                'kode_pos' => '57111',
                'is_utama' => true,
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'kecamatan' => 'Pasar Kliwon',
            'district_id' => 5263,
        ]);
    }

    public function test_rajaongkir_districts_endpoint_returns_success(): void
    {
        $response = $this->getJson(route('rajaongkir.districts', ['city_id' => 444]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'is_live',
                'districts',
            ]);
    }

    public function test_rajaongkir_postal_codes_endpoint_returns_success(): void
    {
        $response = $this->getJson(route('rajaongkir.postal-codes', [
            'district_name' => 'Pasar Kliwon',
            'city_name' => 'Surakarta',
            'city_id' => 444,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'is_live',
                'postal_codes',
                'items',
            ]);
    }

    public function test_alamat_geocode_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('alamat.geocode', [
            'jalan' => 'Jl. Slamet Riyadi',
            'kota' => 'Surakarta',
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'found',
            ]);
    }

    public function test_alamat_reverse_geocode_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('alamat.reverse-geocode', [
            'lat' => -7.5666,
            'lng' => 110.8290,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
            ]);
    }
}
