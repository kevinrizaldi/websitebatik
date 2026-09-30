<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $fillable = [
        'user_id',
        'code',
        'customer_name',
        'address',
        'phone',
        'total_price',
        'payment_method',
        'payment_proof',
        'status',
        'tracking_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
