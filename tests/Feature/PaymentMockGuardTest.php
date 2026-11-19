<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentMockGuardTest extends TestCase
{
    use RefreshDatabase;

    /** Buat order pending milik customer tertentu. */
    private function makePendingOrder(User $user): Order
    {
        return Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'Menunggu Pembayaran',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);
    }

    public function test_mock_finish_marks_paid_only_in_non_production(): void
    {
        config(['midtrans.is_production' => false]);
        Http::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePendingOrder($user);

        $response = $this->actingAs($user)->get(
            route('midtrans.finish', ['order_id' => $order->code, 'mock' => 1])
        );

        $response->assertRedirect(route('pesanan.index'));

        $order->refresh();
        $this->assertSame('Diproses', $order->status);
        $this->assertSame('Berhasil', $order->pembayaran->status_pembayaran);
    }

    public function test_mock_finish_is_ignored_in_production(): void
    {
        config(['midtrans.is_production' => true]);
        Http::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePendingOrder($user);

        $response = $this->actingAs($user)->get(
            route('midtrans.finish', ['order_id' => $order->code, 'mock' => 1])
        );

        $response->assertRedirect(route('pesanan.index'));

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertNotSame('Diproses', $order->status);
    }

    public function test_mock_fallback_token_not_created_in_production(): void
    {
        config(['midtrans.is_production' => true]);
        // Paksa request Snap gagal/void agar jalur fallback mock teruji.
        Http::fake(['*' => Http::response('', 500)]);

        $user = User::factory()->create(['role' => 'customer']);
        $order = $this->makePendingOrder($user);

        $result = app(MidtransService::class)->createSnapTransaction($order);

        $this->assertFalse(str_starts_with((string) $result['token'], 'MOCK_SNAP_'));

        $pembayaran = $order->pembayaran()->first();
        $this->assertNotNull($pembayaran);
        $this->assertFalse(str_starts_with((string) $pembayaran->snap_token, 'MOCK_SNAP_'));
    }
}
