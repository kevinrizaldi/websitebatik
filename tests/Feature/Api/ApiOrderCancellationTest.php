<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('mobile-app')->plainTextToken;
    }

    private function paidOrderFor(User $user, string $status = 'Diproses'): Order
    {
        return Order::factory()->for($user)->create([
            'status' => $status,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
    }

    public function test_cancel_paid_diproses_order_submits_cancellation_request(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = $this->paidOrderFor($user);

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'Diproses')
            ->assertJsonPath('data.cancel_requested', true);

        $this->assertStringContainsString(
            'menunggu persetujuan admin',
            strtolower((string) $response->json('message'))
        );

        $order->refresh();
        $this->assertSame('Diproses', $order->status);
        $this->assertNotNull($order->cancel_requested_at);
    }

    public function test_cancel_paid_order_twice_returns_422_already_requested(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = $this->paidOrderFor($user);
        $order->update(['cancel_requested_at' => now()]);

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422)->assertJsonStructure(['message']);
        $this->assertStringContainsString(
            'sudah diajukan',
            strtolower((string) $response->json('message'))
        );
        $this->assertNotNull($order->refresh()->cancel_requested_at);
    }

    public function test_cancel_shipped_order_returns_422_and_no_request(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->dikirim()->create();

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422)->assertJsonStructure(['message']);
        $order->refresh();
        $this->assertSame('Dikirim', $order->status);
        $this->assertNull($order->cancel_requested_at);
    }

    public function test_cancel_unpaid_order_still_cancels_directly(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->belumDibayar()->create();

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertOk()->assertJsonPath('data.status', 'Dibatalkan');
        $order->refresh();
        $this->assertSame('Dibatalkan', $order->status);
        $this->assertNull($order->cancel_requested_at);
    }

    public function test_cancel_already_cancelled_order_returns_422(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = Order::factory()->for($user)->dibatalkan()->create();

        $response = $this->withToken($token)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422)->assertJsonStructure(['message']);
        $this->assertNull($order->refresh()->cancel_requested_at);
    }

    public function test_order_list_includes_cancel_requested_flag(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenFor($user);
        $order = $this->paidOrderFor($user);
        $order->update(['cancel_requested_at' => now()]);

        $response = $this->withToken($token)->getJson('/api/orders');

        $response->assertOk();
        $flag = collect($response->json('data'))->firstWhere('id', $order->id)['cancel_requested'] ?? null;
        $this->assertTrue($flag);
    }
}
