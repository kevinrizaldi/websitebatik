<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_pelanggan_index(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'code' => 'ORD-TEST-001',
            'customer_name' => $customer->name,
            'phone' => $customer->phone,
            'address' => 'Jl. Senopati No. 42',
            'total_price' => 3450000,
            'payment_method' => 'BCA Manual',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.index'));

        $response->assertOk()
            ->assertSee('Manajemen Pelanggan')
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com');
    }

    public function test_pelanggan_can_be_filtered_by_search(): void
    {
        $admin = $this->createAdmin();
        $cust1 = User::factory()->create(['role' => 'customer', 'name' => 'Dewi Laksmi']);
        $cust2 = User::factory()->create(['role' => 'customer', 'name' => 'Hendrik Pratama']);

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.index', ['search' => 'Dewi']));

        $response->assertOk()
            ->assertSee('Dewi Laksmi')
            ->assertDontSee('Hendrik Pratama');
    }

    public function test_non_admin_cannot_access_pelanggan_index(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.pelanggan.index'));

        $response->assertForbidden();
    }
}
