<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItemAllocationBatteryItem extends Model
{
    protected $fillable = [
        'booking_item_allocation_id',
        'battery_item_id',
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

    public function bookingItemAllocation(): BelongsTo
    {
        return $this->belongsTo(
            BookingItemAllocation::class
        );
    }

    public function batteryItem(): BelongsTo
    {
        return $this->belongsTo(
            BatteryItem::class
        );
    }
}