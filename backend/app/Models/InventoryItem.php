<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_id',
        'inventory_code',
        'serial_number',
        'status',
        'admin_note',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Returns the booking allocations of this physical machine.
     */
    public function bookingAllocations(): HasMany
    {
        return $this->hasMany(BookingItemAllocation::class);
    }
}
