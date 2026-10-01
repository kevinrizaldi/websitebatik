<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // ── Payment attempt status constants ──────────────────────────────────────
    const STATUS_PENDING = 'pending';

    const STATUS_PAID = 'paid';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_EXPIRED = 'expired';

    const STATUS_REPLACED = 'replaced';

    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'snap_token',
        'status',
        'payment_type',
        'transaction_id',
        'payment_details',
        'gross_amount',
        'expires_at',
        'paid_at',
        'raw_notification',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'payment_details' => 'array',
        'raw_notification' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * An attempt is reusable if it is pending, has a snap_token, and has not yet expired.
     */
    public function isReusable(): bool
    {
        return $this->status === self::STATUS_PENDING
            && ! empty($this->snap_token)
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /**
     * The order this payment attempt belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
