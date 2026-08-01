<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    /**
     * Returns the bookings created by the registered user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    /**
     * Returns the inventory status changes performed by this user.
     */
    public function inventoryStatusChanges(): HasMany
    {
        return $this->hasMany(
            InventoryStatusHistory::class,
            'changed_by_user_id'
        );
    }
}
