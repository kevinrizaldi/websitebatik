<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'order_id',
        'product_id',
        'user_id',
        'customer_name',
        'rating',
        'comment',
        'image_path',
        'status',
        // Indonesian compatibility fillables:
        'produk_id',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Review $review): void {
            $pid = $review->attributes['product_id'] ?? $review->attributes['produk_id'] ?? null;
            if ($pid !== null) {
                $review->attributes['product_id'] = $pid;
                $review->attributes['produk_id'] = $pid;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function produk(): BelongsTo
    {
        return $this->product();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
}
