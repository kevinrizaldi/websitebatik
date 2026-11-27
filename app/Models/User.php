<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getInitialsAttribute(): string
    {
        $nameParts = preg_split('/\s+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY);

        if ($nameParts === false) {
            return '';
        }

        $initials = array_map(
            static fn (string $part): string => mb_substr($part, 0, 1),
            array_slice($nameParts, 0, 2)
        );

        return mb_strtoupper(implode('', $initials));
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $avatar = $this->attributes['avatar'] ?? null;
        $avatarDirectory = 'avatars/'.$this->getKey().'/';

        if (! is_string($avatar) || ! str_starts_with($avatar, $avatarDirectory)) {
            return null;
        }

        return Storage::disk('public')->url($avatar);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function alamats(): HasMany
    {
        return $this->addresses();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function ulasans(): HasMany
    {
        return $this->reviews();
    }

    /**
     * Send the email verification notification using our custom branded template.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification using our custom branded template.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
