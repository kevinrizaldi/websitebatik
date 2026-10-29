<?php

namespace App\Models;

use Carbon\Carbon;
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

    const STATUS_CANCELLED = 'Dibatalkan';

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
        'email',
        'address',
        'phone',
        'total_price',
        'shipping_cost',
        'shipping_option',
        'payment_method',
        'payment_proof',
        'status',
        'tracking_number',
        'payment_status',
        'payment_deadline',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'payment_deadline' => 'datetime',
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
     * Get the effective payment deadline for this order.
     */
    public function getEffectivePaymentDeadline(): Carbon
    {
        $activePayment = $this->payments()
            ->whereNotNull('expires_at')
            ->latest('id')
            ->first();

        if ($activePayment && $activePayment->expires_at !== null) {
            return $activePayment->expires_at;
        }

        if ($this->payment_deadline !== null) {
            return $this->payment_deadline;
        }

        $deadlineHours = max(1, (int) config('midtrans.payment_deadline_hours', self::DEFAULT_DEADLINE_HOURS));

        return $this->created_at ? $this->created_at->copy()->addHours($deadlineHours) : now()->addHours($deadlineHours);
    }

    /**
     * Whether the payment time for this order has expired.
     */
    public function isPaymentExpired(): bool
    {
        if ($this->payment_status === self::PAYMENT_EXPIRED) {
            return true;
        }

        if (! in_array($this->status, [self::STATUS_UNPAID, self::STATUS_WAITING, 'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi'], true)) {
            return false;
        }

        return $this->getEffectivePaymentDeadline()->isPast();
    }

    /**
     * Cancel this order due to payment expiration and restore inventory.
     */
    public function markAsExpired(): void
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, 'Dibatalkan'], true)) {
            return;
        }

        if (! in_array($this->status, [self::STATUS_UNPAID, self::STATUS_WAITING, 'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi'], true)) {
            return;
        }

        $this->status = 'Dibatalkan';
        $this->payment_status = self::PAYMENT_EXPIRED;
        $this->save();

        // Expire pending payments
        $this->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->update(['status' => Payment::STATUS_EXPIRED]);

        // Restore product stock
        foreach ($this->items as $item) {
            if ($item->produk) {
                $extractedUkuran = null;
                if (preg_match('/\((S|M|L|XL|XXL|All Size)\)$/i', (string) $item->produk_name, $m)) {
                    $extractedUkuran = strtoupper($m[1]);
                }
                $item->produk->incrementStokForUkuran($extractedUkuran, $item->quantity);
            }
        }
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
