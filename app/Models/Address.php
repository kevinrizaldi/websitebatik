<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'label',
        'label_alamat',
        'recipient_name',
        'penerima',
        'phone',
        'no_telepon',
        'full_address',
        'alamat_lengkap',
        'city',
        'kota',
        'province',
        'provinsi',
        'province_id',
        'city_id',
        'postal_code',
        'kode_pos',
        'is_primary',
        'is_utama',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_utama' => 'boolean',
        'province_id' => 'integer',
        'city_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Address $a): void {
            $label = $a->attributes['label'] ?? $a->attributes['label_alamat'] ?? 'Rumah';
            $a->attributes['label'] = $label;
            $a->attributes['label_alamat'] = $label;

            $recipient = $a->attributes['recipient_name'] ?? $a->attributes['penerima'] ?? '';
            $a->attributes['recipient_name'] = $recipient;
            $a->attributes['penerima'] = $recipient;

            $phone = $a->attributes['phone'] ?? $a->attributes['no_telepon'] ?? '';
            $a->attributes['phone'] = $phone;
            $a->attributes['no_telepon'] = $phone;

            $addr = $a->attributes['full_address'] ?? $a->attributes['alamat_lengkap'] ?? '';
            $a->attributes['full_address'] = $addr;
            $a->attributes['alamat_lengkap'] = $addr;

            $city = $a->attributes['city'] ?? $a->attributes['kota'] ?? '';
            $a->attributes['city'] = $city;
            $a->attributes['kota'] = $city;

            $prov = $a->attributes['province'] ?? $a->attributes['provinsi'] ?? null;
            $a->attributes['province'] = $prov;
            $a->attributes['provinsi'] = $prov;

            $postal = $a->attributes['postal_code'] ?? $a->attributes['kode_pos'] ?? null;
            $a->attributes['postal_code'] = $postal;
            $a->attributes['kode_pos'] = $postal;

            $primary = (bool) ($a->attributes['is_primary'] ?? $a->attributes['is_utama'] ?? false);
            $a->attributes['is_primary'] = $primary;
            $a->attributes['is_utama'] = $primary;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Mutators ───────────────────────────────────────────────────────────────

    public function setLabelAlamatAttribute(mixed $value): void
    {
        $this->attributes['label_alamat'] = $value;
        $this->attributes['label'] = $value;
    }

    public function setLabelAttribute(mixed $value): void
    {
        $this->attributes['label'] = $value;
        $this->attributes['label_alamat'] = $value;
    }

    public function setPenerimaAttribute(mixed $value): void
    {
        $this->attributes['penerima'] = $value;
        $this->attributes['recipient_name'] = $value;
    }

    public function setRecipientNameAttribute(mixed $value): void
    {
        $this->attributes['recipient_name'] = $value;
        $this->attributes['penerima'] = $value;
    }

    public function setNoTeleponAttribute(mixed $value): void
    {
        $this->attributes['no_telepon'] = $value;
        $this->attributes['phone'] = $value;
    }

    public function setPhoneAttribute(mixed $value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['no_telepon'] = $value;
    }

    public function setAlamatLengkapAttribute(mixed $value): void
    {
        $this->attributes['alamat_lengkap'] = $value;
        $this->attributes['full_address'] = $value;
    }

    public function setFullAddressAttribute(mixed $value): void
    {
        $this->attributes['full_address'] = $value;
        $this->attributes['alamat_lengkap'] = $value;
    }

    public function setKotaAttribute(mixed $value): void
    {
        $this->attributes['kota'] = $value;
        $this->attributes['city'] = $value;
    }

    public function setCityAttribute(mixed $value): void
    {
        $this->attributes['city'] = $value;
        $this->attributes['kota'] = $value;
    }

    public function setProvinsiAttribute(mixed $value): void
    {
        $this->attributes['provinsi'] = $value;
        $this->attributes['province'] = $value;
    }

    public function setProvinceAttribute(mixed $value): void
    {
        $this->attributes['province'] = $value;
        $this->attributes['provinsi'] = $value;
    }

    public function setKodePosAttribute(mixed $value): void
    {
        $this->attributes['kode_pos'] = $value;
        $this->attributes['postal_code'] = $value;
    }

    public function setPostalCodeAttribute(mixed $value): void
    {
        $this->attributes['postal_code'] = $value;
        $this->attributes['kode_pos'] = $value;
    }

    public function setIsUtamaAttribute(mixed $value): void
    {
        $this->attributes['is_utama'] = (bool) $value;
        $this->attributes['is_primary'] = (bool) $value;
    }

    public function setIsPrimaryAttribute(mixed $value): void
    {
        $this->attributes['is_primary'] = (bool) $value;
        $this->attributes['is_utama'] = (bool) $value;
    }
}
