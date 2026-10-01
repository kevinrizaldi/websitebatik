<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'midtrans_order_id' => 'ORD-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)),
            'snap_token' => 'tok-'.Str::uuid()->toString(),
            'status' => Payment::STATUS_PENDING,
            'payment_type' => null,
            'transaction_id' => null,
            'payment_details' => null,
            'gross_amount' => 100000,
            'expires_at' => now()->addHours(24),
            'paid_at' => null,
            'raw_notification' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_CANCELLED,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_EXPIRED,
        ]);
    }

    public function replaced(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_REPLACED,
        ]);
    }
}
