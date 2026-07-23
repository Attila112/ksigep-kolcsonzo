<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'product_id',
        'inventory_item_id',
        'quantity',
        'price_per_day',
        'deposit_per_item',
        'rental_days',
        'rental_subtotal',
        'deposit_subtotal',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'deposit_per_item' => 'decimal:2',
        'rental_subtotal' => 'decimal:2',
        'deposit_subtotal' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
