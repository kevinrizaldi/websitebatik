<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PembayaranManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_pembayaran_index(): void
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
            'payment_method' => 'Midtrans Snap',
            'status' => 'Menunggu Pembayaran',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pembayaran.index'));

        $response->assertOk()
            ->assertSee('Manajemen Pembayaran')
            ->assertSee('HSO-20250218-0849')
            ->assertSee('Budi Santoso');
    }

    public function test_admin_can_sync_pembayaran_with_midtrans(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'HSO-20250218-0849',
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jakarta Selatan',
            'total_price' => 871000,
            'payment_method' => 'Midtrans Snap',
            'status' => 'Menunggu Pembayaran',
        ]);

        $mock = $this->mock(MidtransService::class);
        $mock->shouldReceive('refreshFromMidtrans')->andReturn($order);

        $response = $this->actingAs($admin)->post(route('admin.pembayaran.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_sync_individual_order_payment_with_midtrans(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'code' => 'HSO-20250218-0849',
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jakarta Selatan',
            'total_price' => 871000,
            'payment_method' => 'Midtrans Snap',
            'status' => 'Menunggu Pembayaran',
        ]);

        $mock = $this->mock(MidtransService::class);
        $mock->shouldReceive('refreshFromMidtrans')->with(Mockery::type(Order::class))->andReturn($order);

        $response = $this->actingAs($admin)->post(route('admin.orders.sync-payment', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_guest_cannot_view_pembayaran_index(): void
    {
        $response = $this->get(route('admin.pembayaran.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_sync_pembayaran(): void
    {
        $response = $this->post(route('admin.pembayaran.sync'));
        $response->assertRedirect(route('login'));
    }
}
