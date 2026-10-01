<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alamat extends Model
{
    use HasFactory;

    protected $table = 'alamats';

    protected $fillable = [
        'user_id',
        'label_alamat',
        'penerima',
        'no_telepon',
        'alamat_lengkap',
        'kota',
        'provinsi',
        'kode_pos',
        'is_utama',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
