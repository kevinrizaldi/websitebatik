<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
        // Compatibility:
        'produk_id',
        'produk_name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item): void {
            $pid = $item->attributes['product_id'] ?? $item->attributes['produk_id'] ?? null;
            if ($pid !== null) {
                $item->attributes['product_id'] = $pid;
                $item->attributes['produk_id'] = $pid;
            }

            $pname = $item->attributes['product_name'] ?? $item->attributes['produk_name'] ?? null;
            if ($pname !== null) {
                $item->attributes['product_name'] = $pname;
                $item->attributes['produk_name'] = $pname;
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function produk()
    {
        return $this->product();
    }

    public function getProdukIdAttribute(): mixed
    {
        return $this->attributes['product_id'] ?? $this->attributes['produk_id'] ?? null;
    }

    public function setProdukIdAttribute(mixed $value): void
    {
        $this->attributes['product_id'] = $value;
        $this->attributes['produk_id'] = $value;
    }

    public function getProdukNameAttribute(): string
    {
        return (string) ($this->attributes['product_name'] ?? $this->attributes['produk_name'] ?? '');
    }

    public function setProdukNameAttribute(mixed $value): void
    {
        $this->attributes['product_name'] = $value;
        $this->attributes['produk_name'] = $value;
    }
}
