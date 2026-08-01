<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_date',
        'end_date',
        'pickup_type',
        'planned_pickup_at',
        'delivery_postal_code',
        'delivery_city',
        'delivery_street',
        'delivery_house_number',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_distance_km',
        'status',
        'customer_note',
        'admin_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_pickup_at' => 'datetime',
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
        'delivery_distance_km' => 'decimal:2',
    ];
    protected $appends = [
        'rental_total',
        'deposit_total',
        'total_payable',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }
    /**
     * Returns the total rental fee of all booking items.
     */
    protected function rentalTotal(): Attribute
    {
        return Attribute::get(
            fn(): float => (float) $this->items->sum('rental_subtotal')
        );
    }

    /**
     * Returns the total deposit amount of all booking items.
     */
    protected function depositTotal(): Attribute
    {
        return Attribute::get(
            fn(): float => (float) $this->items->sum('deposit_subtotal')
        );
    }

    /**
     * Returns the combined rental fee and deposit amount.
     */
    protected function totalPayable(): Attribute
    {
        return Attribute::get(
            fn(): float => $this->rental_total + $this->deposit_total
        );
    }
}
