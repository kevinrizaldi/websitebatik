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
        'produk_id',
        'ukuran',
        'varian',
        'qty',
        'selected',
    ];

    protected $casts = [
        'qty' => 'integer',
        'selected' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
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
