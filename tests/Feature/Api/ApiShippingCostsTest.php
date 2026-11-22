<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiShippingCostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_fetch_shipping_costs(): void
    {
        $this->postJson('/api/shipping/costs', [])->assertStatus(401);
    }

    public function test_user_gets_shipping_services_for_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'full_address' => 'Jl. Merdeka 1, Jakarta Barat',
            'city' => 'Jakarta Barat',
            'kota' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
            'provinsi' => 'DKI Jakarta',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/shipping/costs', [
            'address_id' => $address->id,
            'weight' => 600,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'is_live',
                'origin',
                'destination_city_id',
                'weight',
                'services' => [],
            ]);

        $this->assertGreaterThanOrEqual(1000, $response->json('weight'));
        $this->assertNotEmpty($response->json('services'));
    }

    public function test_unknown_or_foreign_address_falls_back_to_origin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/shipping/costs', [
            'address_id' => 999999,
            'weight' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $originCityId = (int) $response->json('origin.city_id');
        $this->assertSame($originCityId, (int) $response->json('destination_city_id'));
    }
}
