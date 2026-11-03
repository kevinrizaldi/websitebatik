<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengirimanManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_pengiriman_index(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'HSO-20250218-0849',
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jakarta Selatan, DKI Jakarta',
            'total_price' => 871000,
            'payment_method' => 'BCA Manual',
            'status' => 'Diproses',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengiriman.index'));

        $response->assertOk()
            ->assertSee('Manajemen Pengiriman')
            ->assertSee('HSO-20250218-0849')
            ->assertSee('Budi Santoso');
    }

    public function test_pengiriman_can_be_filtered_by_search(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'code' => 'HSO-20250218-0849',
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jakarta Selatan',
            'total_price' => 871000,
            'payment_method' => 'BCA Manual',
            'status' => 'Diproses',
        ]);

        Order::create([
            'user_id' => $user->id,
            'code' => 'HSO-20250218-0842',
            'customer_name' => 'Dewi Laksmi',
            'phone' => '081234567891',
            'address' => 'Surabaya',
            'total_price' => 485000,
            'payment_method' => 'Mandiri VA',
            'status' => 'Diproses',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengiriman.index', ['search' => 'Budi']));

        $response->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Dewi Laksmi');
    }

    public function test_guest_cannot_view_pengiriman_index(): void
    {
        $response = $this->get(route('admin.pengiriman.index'));
        $response->assertRedirect(route('login'));
    }
}
