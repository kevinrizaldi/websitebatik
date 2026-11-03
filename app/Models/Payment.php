<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

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
        'snap_redirect_url',
        'status',
        'payment_type',
        'transaction_id',
        'payment_details',
        'gross_amount',
        'expires_at',
        'paid_at',
        'raw_notification',
        // Indonesian compatibility fillables:
        'metode_pembayaran',
        'status_pembayaran',
        'jumlah_bayar',
        'tanggal_bayar',
        'payment_response',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'payment_details' => 'array',
        'raw_notification' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Payment $p): void {
            $type = $p->attributes['payment_type'] ?? $p->attributes['metode_pembayaran'] ?? null;
            if ($type !== null) {
                $p->attributes['payment_type'] = $type;
                $p->attributes['metode_pembayaran'] = $type;
            }

            $st = $p->attributes['status'] ?? $p->attributes['status_pembayaran'] ?? 'pending';
            $p->attributes['status'] = $st;
            $p->attributes['status_pembayaran'] = $st;

            $amt = $p->attributes['gross_amount'] ?? $p->attributes['jumlah_bayar'] ?? 0;
            $p->attributes['gross_amount'] = $amt;
            $p->attributes['jumlah_bayar'] = $amt;

            $paid = $p->attributes['paid_at'] ?? $p->attributes['tanggal_bayar'] ?? null;
            if ($paid !== null) {
                $p->attributes['paid_at'] = $paid;
                $p->attributes['tanggal_bayar'] = $paid;
            }
        });
    }

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

    // ── Indonesian Accessors & Mutators ────────────────────────────────────────

    public function getMetodePembayaranAttribute(): ?string
    {
        return $this->attributes['payment_type'] ?? $this->attributes['metode_pembayaran'] ?? null;
    }

    public function setMetodePembayaranAttribute(mixed $value): void
    {
        $this->attributes['payment_type'] = $value;
    }

    public function getStatusPembayaranAttribute(): string
    {
        return $this->attributes['status'] ?? $this->attributes['status_pembayaran'] ?? 'pending';
    }

    public function setStatusPembayaranAttribute(mixed $value): void
    {
        $this->attributes['status'] = $value;
    }

    public function getJumlahBayarAttribute(): mixed
    {
        return $this->attributes['gross_amount'] ?? $this->attributes['jumlah_bayar'] ?? 0;
    }

    public function setJumlahBayarAttribute(mixed $value): void
    {
        $this->attributes['gross_amount'] = $value;
    }

    public function getTanggalBayarAttribute(): mixed
    {
        return $this->paid_at ?? $this->attributes['paid_at'] ?? $this->attributes['tanggal_bayar'] ?? null;
    }

    public function setTanggalBayarAttribute(mixed $value): void
    {
        $this->attributes['paid_at'] = $value;
    }

    public function getPaymentResponseAttribute(): mixed
    {
        return $this->raw_notification ?? $this->attributes['raw_notification'] ?? $this->attributes['payment_response'] ?? null;
    }

    public function setPaymentResponseAttribute(mixed $value): void
    {
        $this->attributes['raw_notification'] = is_array($value) ? json_encode($value) : $value;
    }
}
