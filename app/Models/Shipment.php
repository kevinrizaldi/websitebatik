<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Shipment extends Model
{
    use HasFactory;

    protected static ?string $resolvedTable = null;

    public function getTable(): string
    {
        if (static::$resolvedTable !== null) {
            return static::$resolvedTable;
        }

        if (Schema::hasTable('shipments')) {
            return static::$resolvedTable = 'shipments';
        }

        if (Schema::hasTable('pengirimans')) {
            return static::$resolvedTable = 'pengirimans';
        }

        return static::$resolvedTable = 'shipments';
    }

    protected $fillable = [
        'order_id',
        'courier',
        'tracking_number',
        'shipped_at',
        'shipping_status',
        'notes',
        // Indonesian compatibility fillables:
        'ekspedisi',
        'no_resi',
        'tanggal_kirim',
        'status_pengiriman',
        'catatan',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Shipment $s): void {
            $courier = $s->attributes['courier'] ?? $s->attributes['ekspedisi'] ?? 'JNE Reguler';
            $s->attributes['courier'] = $courier;
            $s->attributes['ekspedisi'] = $courier;

            $tracking = $s->attributes['tracking_number'] ?? $s->attributes['no_resi'] ?? null;
            $s->attributes['tracking_number'] = $tracking;
            $s->attributes['no_resi'] = $tracking;

            $shipped = $s->attributes['shipped_at'] ?? $s->attributes['tanggal_kirim'] ?? null;
            if ($shipped !== null) {
                $s->attributes['shipped_at'] = $shipped;
                $s->attributes['tanggal_kirim'] = $shipped;
            }

            $st = $s->attributes['shipping_status'] ?? $s->attributes['status_pengiriman'] ?? 'Menunggu Pengiriman';
            $s->attributes['shipping_status'] = $st;
            $s->attributes['status_pengiriman'] = $st;

            $notes = $s->attributes['notes'] ?? $s->attributes['catatan'] ?? null;
            $s->attributes['notes'] = $notes;
            $s->attributes['catatan'] = $notes;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // ── Indonesian Accessors & Mutators ────────────────────────────────────────

    public function getEkspedisiAttribute(): string
    {
        return (string) ($this->attributes['courier'] ?? $this->attributes['ekspedisi'] ?? 'JNE Reguler');
    }

    public function setEkspedisiAttribute(mixed $value): void
    {
        $this->attributes['courier'] = $value;
    }

    public function getNoResiAttribute(): ?string
    {
        return $this->attributes['tracking_number'] ?? $this->attributes['no_resi'] ?? null;
    }

    public function setNoResiAttribute(mixed $value): void
    {
        $this->attributes['tracking_number'] = $value;
    }

    public function getTanggalKirimAttribute(): mixed
    {
        return $this->shipped_at ?? $this->attributes['shipped_at'] ?? $this->attributes['tanggal_kirim'] ?? null;
    }

    public function setTanggalKirimAttribute(mixed $value): void
    {
        $this->attributes['shipped_at'] = $value;
    }

    public function getStatusPengirimanAttribute(): string
    {
        return (string) ($this->attributes['shipping_status'] ?? $this->attributes['status_pengiriman'] ?? 'Menunggu Pengiriman');
    }

    public function setStatusPengirimanAttribute(mixed $value): void
    {
        $this->attributes['shipping_status'] = $value;
    }

    public function getCatatanAttribute(): ?string
    {
        return $this->attributes['notes'] ?? $this->attributes['catatan'] ?? null;
    }

    public function setCatatanAttribute(mixed $value): void
    {
        $this->attributes['notes'] = $value;
    }
}
