<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $produk_id
 * @property string|null $ukuran
 * @property string|null $varian
 * @property int $qty
 * @property bool $selected
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Produk|null $produk
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem forCurrentVisitor()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem with($relations, $callback = null)
 * @method static CartItem create(array $attributes = [])
 *
 * @mixin Builder
 */
class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'size',
        'variant',
        'quantity',
        'selected',
        // Compatibility:
        'produk_id',
        'ukuran',
        'varian',
        'qty',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'qty' => 'integer',
        'selected' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (CartItem $item): void {
            $pid = $item->attributes['product_id'] ?? $item->attributes['produk_id'] ?? null;
            if ($pid !== null) {
                $item->attributes['product_id'] = $pid;
                $item->attributes['produk_id'] = $pid;
            }

            $size = $item->attributes['size'] ?? $item->attributes['ukuran'] ?? null;
            $item->attributes['size'] = $size;
            $item->attributes['ukuran'] = $size;

            $variant = $item->attributes['variant'] ?? $item->attributes['varian'] ?? null;
            $item->attributes['variant'] = $variant;
            $item->attributes['varian'] = $variant;

            $qty = (int) ($item->attributes['quantity'] ?? $item->attributes['qty'] ?? 1);
            $item->attributes['quantity'] = $qty;
            $item->attributes['qty'] = $qty;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function produk(): BelongsTo
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
    }

    public function getUkuranAttribute(): ?string
    {
        return $this->attributes['size'] ?? $this->attributes['ukuran'] ?? null;
    }

    public function setUkuranAttribute(mixed $value): void
    {
        $this->attributes['size'] = $value;
    }

    public function getVarianAttribute(): ?string
    {
        return $this->attributes['variant'] ?? $this->attributes['varian'] ?? null;
    }

    public function setVarianAttribute(mixed $value): void
    {
        $this->attributes['variant'] = $value;
    }

    public function getQtyAttribute(): int
    {
        return (int) ($this->attributes['quantity'] ?? $this->attributes['qty'] ?? 1);
    }

    public function setQtyAttribute(mixed $value): void
    {
        $this->attributes['quantity'] = (int) $value;
    }

    /**
     * Scope query to current visitor (by user_id if logged in, or session_id).
     */
    public function scopeForCurrentVisitor(Builder $query): Builder
    {
        if (Auth::check()) {
            $sessionId = session()->getId();

            return $query->where(function ($q) use ($sessionId) {
                $q->where('user_id', Auth::id())
                    ->orWhere('session_id', $sessionId);
            });
        }

        return $query->where('session_id', session()->getId());
    }
}
