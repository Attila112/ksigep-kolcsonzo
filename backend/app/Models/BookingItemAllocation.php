<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItemAllocation extends Model
{
    protected $fillable = [
        'booking_item_id',
        'inventory_item_id',
        'assigned_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * Returns the booking item this physical machine belongs to.
     */
    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    /**
     * Returns the allocated physical inventory item.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
