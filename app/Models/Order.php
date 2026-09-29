<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // ── Order status constants ────────────────────────────────────────────────
    const STATUS_UNPAID = 'Belum Dibayar';

    const STATUS_WAITING = 'Menunggu Konfirmasi';

    const STATUS_PAID = 'Sudah Dibayar';

    const STATUS_CANCELLED = 'Batal';

    // ── Payment status constants ──────────────────────────────────────────────
    const PAYMENT_PENDING = 'pending';

    const PAYMENT_PAID = 'paid';

    const PAYMENT_FAILED = 'failed';

    const PAYMENT_CANCELLED = 'cancelled';

    const PAYMENT_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'code',
        'customer_name',
        'address',
        'phone',
        'total_price',
        'shipping_cost',
        'payment_method',
        'payment_proof',
        'status',
        'tracking_number',
        // Midtrans columns set by server code only:
        'midtrans_order_id',
        'snap_token',
        'payment_status',
        'payment_type',
        'transaction_id',
        'paid_at',
        'raw_notification',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'paid_at' => 'datetime',
        'raw_notification' => 'array',
    ];

    /**
     * The set of payment statuses considered final (no further transitions).
     *
     * @return string[]
     */
    public static function finalPaymentStatuses(): array
    {
        return [
            self::PAYMENT_PAID,
            self::PAYMENT_FAILED,
            self::PAYMENT_CANCELLED,
            self::PAYMENT_EXPIRED,
        ];
    }

    /**
     * Whether the current payment_status is a final state.
     */
    public function isPaymentFinal(): bool
    {
        return in_array($this->payment_status, self::finalPaymentStatuses(), true);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
