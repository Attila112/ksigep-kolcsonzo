<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;
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
    /**
     * Returns the full status history of this physical machine.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(InventoryStatusHistory::class);
    }
}
