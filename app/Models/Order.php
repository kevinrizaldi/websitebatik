<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $code
 * @property string $customer_name
 * @property string $phone
 * @property string $address
 * @property float $total_price
 * @property string $payment_method
 * @property string $status
 *
 * @method static Order create(array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order where($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @mixin Builder
 */
class Order extends Model
{
    use HasFactory;

    // ── Order status constants ────────────────────────────────────────────────
    const STATUS_UNPAID = 'Belum Dibayar';

    const STATUS_WAITING = 'Menunggu Konfirmasi';

    const STATUS_PAID = 'Sudah Dibayar';

    const STATUS_CANCELLED = 'Batal';

    /** Hours a new payment deadline lasts (can be overridden in config). */
    const DEFAULT_DEADLINE_HOURS = 24;

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
        'payment_deadline',
        'raw_notification',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_deadline' => 'datetime',
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

    /**
     * Whether the payment_deadline has passed (null deadline = never expires).
     */
    public function isPastDeadline(): bool
    {
        return $this->payment_deadline !== null && $this->payment_deadline->isPast();
    }

    /**
     * The most recent pending payment attempt for this order.
     */
    public function activePayment(): ?Payment
    {
        return $this->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('snap_token')
            ->where('snap_token', '!=', '')
            ->latest('id')
            ->first();
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'order_id');
    }

    public function pengiriman(): HasOne
    {
        return $this->hasOne(Pengiriman::class, 'order_id');
    }

    /**
     * Get or create pengiriman relation
     */
    public function getOrInitPengiriman(): Pengiriman
    {
        return $this->pengiriman ?: $this->pengiriman()->create([
            'ekspedisi' => 'JNE Reguler',
            'no_resi' => $this->tracking_number,
            'status_pengiriman' => $this->status === 'Dikirim' ? 'Dikirim' : ($this->status === 'Selesai' ? 'Diterima' : 'Menunggu Pengiriman'),
        ]);
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
